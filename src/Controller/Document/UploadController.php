<?php

namespace App\Controller\Document;

use App\Dto\DocumentUpload;
use App\Entity\Document;
use App\Entity\Project;
use App\Entity\User;
use App\Form\DocumentUploadForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/documents', name: 'app_documents_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class UploadController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(default::APP_UPLOAD_DIR)%')]
        private readonly string $uploadDir,
    ) {
    }

    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $upload = new DocumentUpload();
        $form = $this->createForm(DocumentUploadForm::class, $upload);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid() || !$upload->file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $this->addFlash('error', 'Upload invalide.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
        }

        $storageDir = ('' !== $this->uploadDir ? $this->uploadDir : \dirname(__DIR__, 3).'/var/uploads').'/'.$project->getId();
        if (!is_dir($storageDir) && !mkdir($storageDir, 0o775, true) && !is_dir($storageDir)) {
            throw new \RuntimeException('Cannot create upload directory.');
        }

        $file = $upload->file;
        $original = $file->getClientOriginalName();
        $safe = bin2hex(random_bytes(8)).'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
        $file->move($storageDir, $safe);

        /** @var User $user */
        $user = $this->getUser();
        $doc = new Document();
        $doc->setProject($project);
        $doc->setFilename($original);
        $doc->setStoragePath($project->getId().'/'.$safe);
        $doc->setMimeType($file->getClientMimeType() ?: 'application/octet-stream');
        $doc->setSize(filesize($storageDir.'/'.$safe) ?: 0);
        $doc->setCategory($upload->category);
        $doc->setUploadedBy($user);
        $em->persist($doc);
        $em->flush();

        $this->addFlash('success', 'Document ajouté.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
    }
}

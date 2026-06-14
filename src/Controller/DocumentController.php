<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\DocumentCategory;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(default::APP_UPLOAD_DIR)%')]
        private readonly string $uploadDir,
    ) {
    }

    private function storageDir(): string
    {
        return '' !== $this->uploadDir ? $this->uploadDir : \dirname(__DIR__, 2).'/var/uploads';
    }

    #[Route('/projets/{reference}/documents', name: 'app_documents_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
    public function upload(string $reference, Request $request, ProjectRepository $projectRepo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $project = $projectRepo->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('document_'.$project->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $file = $request->files->get('file');
        if (null === $file) {
            $this->addFlash('error', 'Aucun fichier reçu.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
        }

        $projectDir = $this->storageDir().'/'.$project->getId();
        if (!is_dir($projectDir) && !mkdir($projectDir, 0775, true) && !is_dir($projectDir)) {
            throw new \RuntimeException('Cannot create upload directory.');
        }

        $original = $file->getClientOriginalName();
        $safe = bin2hex(random_bytes(8)).'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $original);
        $file->move($projectDir, $safe);

        $doc = new Document();
        $doc->setProject($project);
        $doc->setFilename($original);
        $doc->setStoragePath($project->getId().'/'.$safe);
        $doc->setMimeType($file->getClientMimeType() ?: 'application/octet-stream');
        $doc->setSize(filesize($projectDir.'/'.$safe) ?: 0);
        $doc->setCategory(DocumentCategory::tryFrom($request->request->getString('category', 'other')) ?? DocumentCategory::OTHER);
        /** @var User $user */
        $user = $this->getUser();
        $doc->setUploadedBy($user);
        $em->persist($doc);
        $em->flush();

        $this->addFlash('success', 'Document ajouté.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
    }

    #[Route('/documents/{id}', name: 'app_documents_download', requirements: ['id' => '\d+'])]
    public function download(int $id, DocumentRepository $repo): BinaryFileResponse
    {
        $doc = $repo->find($id);
        if (!$doc instanceof Document) {
            throw $this->createNotFoundException();
        }
        $path = $this->storageDir().'/'.$doc->getStoragePath();
        if (!file_exists($path)) {
            throw $this->createNotFoundException('Fichier manquant.');
        }
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $doc->getMimeType());
        if (!$doc->isImage()) {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $doc->getFilename());
        }

        return $response;
    }

    #[Route('/documents/{id}/supprimer', name: 'app_documents_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, DocumentRepository $repo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $doc = $repo->find($id);
        if (!$doc instanceof Document) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('document_delete_'.$doc->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $projectRef = $doc->getProject()?->getReference();
        $path = $this->storageDir().'/'.$doc->getStoragePath();
        if (file_exists($path)) {
            @unlink($path);
        }
        $em->remove($doc);
        $em->flush();
        $this->addFlash('success', 'Document supprimé.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $projectRef]);
    }
}

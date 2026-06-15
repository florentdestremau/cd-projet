<?php

namespace App\Controller\Document;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/documents/{id}/supprimer', name: 'app_documents_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class DeleteController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(default::APP_UPLOAD_DIR)%')]
        private readonly string $uploadDir,
    ) {
    }

    public function __invoke(
        #[MapEntity] Document $doc,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('document_delete_'.$doc->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $projectRef = $doc->getProject()?->getReference();
        $base = '' !== $this->uploadDir ? $this->uploadDir : \dirname(__DIR__, 3).'/var/uploads';
        $path = $base.'/'.$doc->getStoragePath();
        if (file_exists($path)) {
            @unlink($path);
        }
        $em->remove($doc);
        $em->flush();
        $this->addFlash('success', 'Document supprimé.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $projectRef]);
    }
}

<?php

namespace App\Controller\Document;

use App\Entity\Document;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/documents/{id}', name: 'app_documents_download', requirements: ['id' => '\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final readonly class DownloadController
{
    public function __construct(
        #[Autowire('%env(default::APP_UPLOAD_DIR)%')]
        private string $uploadDir,
    ) {
    }

    public function __invoke(#[MapEntity] Document $doc): BinaryFileResponse
    {
        $base = '' !== $this->uploadDir ? $this->uploadDir : \dirname(__DIR__, 3).'/var/uploads';
        $path = $base.'/'.$doc->getStoragePath();
        if (!file_exists($path)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Fichier manquant.');
        }
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $doc->getMimeType());
        if (!$doc->isImage()) {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $doc->getFilename());
        }

        return $response;
    }
}

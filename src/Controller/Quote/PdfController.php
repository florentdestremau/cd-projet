<?php

namespace App\Controller\Quote;

use App\Entity\Quote;
use App\Service\PdfRenderer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/devis/{reference}/pdf', name: 'app_quotes_pdf', requirements: ['reference' => 'DEV-\d+-\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class PdfController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Quote $quote,
        PdfRenderer $pdf,
    ): Response {
        $output = $pdf->render('quote/pdf.html.twig', ['quote' => $quote]);

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => \sprintf('inline; filename="%s.pdf"', $quote->getReference()),
        ]);
    }
}

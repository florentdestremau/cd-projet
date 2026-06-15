<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Service\PdfRenderer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/factures/{reference}/pdf', name: 'app_invoices_pdf', requirements: ['reference' => 'FAC-\d+-\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class PdfController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Invoice $invoice,
        PdfRenderer $pdf,
    ): Response {
        $output = $pdf->render('invoice/pdf.html.twig', ['invoice' => $invoice]);

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => \sprintf('inline; filename="%s.pdf"', $invoice->getReference()),
        ]);
    }
}

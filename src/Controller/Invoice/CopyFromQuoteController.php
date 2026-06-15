<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Quote;
use App\Enum\QuoteStatus;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/devis/{reference}/facturer', name: 'app_invoices_from_quote', requirements: ['reference' => 'DEV-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CopyFromQuoteController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Quote $quote,
        Request $request,
        InvoiceRepository $invoiceRepository,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('quote_invoice_'.$quote->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        if (QuoteStatus::ACCEPTED !== $quote->getStatus()) {
            $this->addFlash('error', 'Seul un devis accepté peut être facturé.');

            return $this->redirectToRoute('app_quotes_show', ['reference' => $quote->getReference()]);
        }

        $invoice = new Invoice();
        $invoice->setProject($quote->getProject());
        $invoice->setQuote($quote);
        $invoice->setReference($invoiceRepository->generateNextReference((int) date('Y')));
        $invoice->setVatRate($quote->getVatRate());
        foreach ($quote->getItems() as $qi) {
            $item = new InvoiceItem();
            $item->setDescription($qi->getDescription());
            $item->setQuantity($qi->getQuantity());
            $item->setUnitPriceHt($qi->getUnitPriceHt());
            $invoice->addItem($item);
        }
        $em->persist($invoice);
        $em->flush();

        return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
    }
}

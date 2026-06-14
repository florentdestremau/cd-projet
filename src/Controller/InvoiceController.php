<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Payment;
use App\Entity\Project;
use App\Entity\Quote;
use App\Enum\InvoiceStatus;
use App\Enum\PaymentMethod;
use App\Enum\QuoteStatus;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Repository\QuoteRepository;
use App\Service\PdfRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InvoiceController extends AbstractController
{
    #[Route('/projets/{reference}/facture/nouvelle', name: 'app_invoices_new', requirements: ['reference' => 'BAG-\d+-\d+'])]
    public function create(string $reference, Request $request, ProjectRepository $projectRepo, InvoiceRepository $invoiceRepo, QuoteRepository $quoteRepo, EntityManagerInterface $em): Response
    {
        $project = $projectRepo->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('invoice_new_'.$project->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $invoice = new Invoice();
            $invoice->setProject($project);
            $invoice->setReference($invoiceRepo->generateNextReference((int) date('Y')));

            $quoteRef = $request->request->getString('quote');
            if ($quoteRef !== '') {
                $quote = $quoteRepo->findOneBy(['reference' => $quoteRef]);
                if ($quote instanceof Quote && $quote->getStatus() === QuoteStatus::ACCEPTED) {
                    $invoice->setQuote($quote);
                    $invoice->setVatRate($quote->getVatRate());
                    foreach ($quote->getItems() as $qi) {
                        $item = new InvoiceItem();
                        $item->setDescription($qi->getDescription());
                        $item->setQuantity($qi->getQuantity());
                        $item->setUnitPriceHt($qi->getUnitPriceHt());
                        $invoice->addItem($item);
                    }
                }
            } else {
                $invoice->setVatRate((int) ($request->request->getString('vatRate', '20.00') * 100));
                $descriptions = $request->request->all('descriptions');
                $quantities = $request->request->all('quantities');
                $prices = $request->request->all('prices');
                foreach ($descriptions as $idx => $desc) {
                    $desc = trim((string) $desc);
                    if ($desc === '') continue;
                    $item = new InvoiceItem();
                    $item->setDescription($desc);
                    $item->setQuantity((int) ($quantities[$idx] ?? 1));
                    $item->setUnitPriceHt((int) round(((float) ($prices[$idx] ?? 0)) * 100));
                    $invoice->addItem($item);
                }
            }

            $dueDate = $request->request->getString('dueDate');
            if ($dueDate !== '') {
                $invoice->setDueDate(new \DateTimeImmutable($dueDate));
            }

            $em->persist($invoice);
            $em->flush();
            $this->addFlash('success', 'Facture '.$invoice->getReference().' créée.');
            return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
        }

        $acceptedQuotes = array_filter(
            $quoteRepo->findBy(['project' => $project]),
            fn (Quote $q) => $q->getStatus() === QuoteStatus::ACCEPTED,
        );

        return $this->render('invoice/new.html.twig', [
            'project' => $project,
            'accepted_quotes' => $acceptedQuotes,
        ]);
    }

    #[Route('/factures/{reference}', name: 'app_invoices_show', requirements: ['reference' => 'FAC-\d+-\d+'])]
    public function show(string $reference, InvoiceRepository $repo): Response
    {
        $invoice = $repo->findOneBy(['reference' => $reference]);
        if (!$invoice instanceof Invoice) throw $this->createNotFoundException();
        return $this->render('invoice/show.html.twig', ['invoice' => $invoice, 'methods' => PaymentMethod::cases()]);
    }

    #[Route('/factures/{reference}/pdf', name: 'app_invoices_pdf', requirements: ['reference' => 'FAC-\d+-\d+'])]
    public function pdf(string $reference, InvoiceRepository $repo, PdfRenderer $pdf): Response
    {
        $invoice = $repo->findOneBy(['reference' => $reference]);
        if (!$invoice instanceof Invoice) throw $this->createNotFoundException();
        $output = $pdf->render('invoice/pdf.html.twig', ['invoice' => $invoice]);
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s.pdf"', $invoice->getReference()),
        ]);
    }

    #[Route('/factures/{reference}/paiement', name: 'app_invoices_payment', methods: ['POST'], requirements: ['reference' => 'FAC-\d+-\d+'])]
    public function payment(string $reference, Request $request, InvoiceRepository $repo, EntityManagerInterface $em): Response
    {
        $invoice = $repo->findOneBy(['reference' => $reference]);
        if (!$invoice instanceof Invoice) throw $this->createNotFoundException();
        if (!$this->isCsrfTokenValid('payment_'.$invoice->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $amount = (int) round(((float) $request->request->getString('amount')) * 100);
        if ($amount <= 0) {
            $this->addFlash('error', 'Montant invalide.');
            return $this->redirectToRoute('app_invoices_show', ['reference' => $reference]);
        }

        $payment = new Payment();
        $payment->setInvoice($invoice);
        $payment->setAmount($amount);
        $payment->setMethod(PaymentMethod::from($request->request->getString('method', 'transfer')));
        $payment->setReference($request->request->getString('reference', null) ?: null);
        $em->persist($payment);

        $invoice->addPayment($payment);
        if ($invoice->getAmountPaid() + $amount >= $invoice->getTotalTtc()) {
            $invoice->setStatus(InvoiceStatus::PAID);
            $invoice->setPaidAt(new \DateTimeImmutable());
        }

        $em->flush();
        $this->addFlash('success', 'Paiement enregistré.');
        return $this->redirectToRoute('app_invoices_show', ['reference' => $reference]);
    }

    #[Route('/factures/{reference}/statut', name: 'app_invoices_status', methods: ['POST'], requirements: ['reference' => 'FAC-\d+-\d+'])]
    public function status(string $reference, Request $request, InvoiceRepository $repo, EntityManagerInterface $em): Response
    {
        $invoice = $repo->findOneBy(['reference' => $reference]);
        if (!$invoice instanceof Invoice) throw $this->createNotFoundException();
        if (!$this->isCsrfTokenValid('invoice_status_'.$invoice->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $status = InvoiceStatus::tryFrom($request->request->getString('status'));
        if ($status !== null) {
            $invoice->setStatus($status);
            if ($status === InvoiceStatus::SENT && $invoice->getSentAt() === null) {
                $invoice->setSentAt(new \DateTimeImmutable());
            }
            $em->flush();
        }
        return $this->redirectToRoute('app_invoices_show', ['reference' => $reference]);
    }
}

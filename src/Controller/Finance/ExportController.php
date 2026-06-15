<?php

namespace App\Controller\Finance;

use App\Dto\FinanceExportFilters;
use App\Entity\Expense;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Repository\ExpenseRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/finances/export.csv', name: 'app_finances_export', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ExportController extends AbstractController
{
    public function __invoke(
        InvoiceRepository $invoiceRepository,
        PaymentRepository $paymentRepository,
        ExpenseRepository $expenseRepository,
        #[MapQueryString] FinanceExportFilters $filters = new FinanceExportFilters(),
    ): StreamedResponse {
        $from = $filters->fromDate();
        $to = $filters->toDate();

        $response = new StreamedResponse(function () use ($filters, $invoiceRepository, $paymentRepository, $expenseRepository, $from, $to): void {
            $out = fopen('php://output', 'w');
            \assert(\is_resource($out));
            match ($filters->type) {
                'invoices' => $this->streamInvoices($out, $invoiceRepository, $from, $to),
                'payments' => $this->streamPayments($out, $paymentRepository, $from, $to),
                'expenses' => $this->streamExpenses($out, $expenseRepository, $from, $to),
                default => null,
            };
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', \sprintf('attachment; filename="%s_%s_%s.csv"', $filters->type, $from->format('Ymd'), $to->format('Ymd')));

        return $response;
    }

    /**
     * @param resource     $out
     * @param array<mixed> $row
     */
    private function writeRow($out, array $row): void
    {
        fputcsv($out, $row, ',', '"', '\\');
    }

    /** @param resource $out */
    private function streamInvoices($out, InvoiceRepository $repo, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $this->writeRow($out, ['Référence', 'Projet', 'Client', 'Statut', 'Émise le', 'Échéance', 'Total HT', 'TVA', 'Total TTC', 'Payée le']);
        $invoices = $repo->createQueryBuilder('i')
            ->where('i.createdAt BETWEEN :from AND :to')
            ->setParameter('from', $from)->setParameter('to', $to)
            ->orderBy('i.createdAt', 'ASC')
            ->getQuery()->getResult();
        foreach ($invoices as $invoice) {
            \assert($invoice instanceof Invoice);
            $this->writeRow($out, [
                $invoice->getReference(),
                $invoice->getProject()?->getReference(),
                $invoice->getProject()?->getClient()?->getDisplayName(),
                $invoice->getStatus()->label(),
                $invoice->getSentAt()?->format('Y-m-d'),
                $invoice->getDueDate()?->format('Y-m-d'),
                number_format($invoice->getTotalHt() / 100, 2, '.', ''),
                number_format(($invoice->getTotalTtc() - $invoice->getTotalHt()) / 100, 2, '.', ''),
                number_format($invoice->getTotalTtc() / 100, 2, '.', ''),
                $invoice->getPaidAt()?->format('Y-m-d'),
            ]);
        }
    }

    /** @param resource $out */
    private function streamPayments($out, PaymentRepository $repo, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $this->writeRow($out, ['Date', 'Facture', 'Méthode', 'Référence', 'Montant']);
        $payments = $repo->createQueryBuilder('p')
            ->where('p.receivedAt BETWEEN :from AND :to')
            ->setParameter('from', $from)->setParameter('to', $to)
            ->orderBy('p.receivedAt', 'ASC')
            ->getQuery()->getResult();
        foreach ($payments as $p) {
            \assert($p instanceof Payment);
            $this->writeRow($out, [
                $p->getReceivedAt()->format('Y-m-d'),
                $p->getInvoice()?->getReference(),
                $p->getMethod()->label(),
                $p->getReference() ?? '',
                number_format($p->getAmount() / 100, 2, '.', ''),
            ]);
        }
    }

    /** @param resource $out */
    private function streamExpenses($out, ExpenseRepository $repo, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $this->writeRow($out, ['Date', 'Projet', 'Catégorie', 'Fournisseur', 'Description', 'Montant HT', 'TVA']);
        $expenses = $repo->createQueryBuilder('e')
            ->where('e.occurredAt BETWEEN :from AND :to')
            ->setParameter('from', $from)->setParameter('to', $to)
            ->orderBy('e.occurredAt', 'ASC')
            ->getQuery()->getResult();
        foreach ($expenses as $e) {
            \assert($e instanceof Expense);
            $this->writeRow($out, [
                $e->getOccurredAt()->format('Y-m-d'),
                $e->getProject()?->getReference(),
                $e->getCategory()->label(),
                $e->getSupplierName() ?? '',
                $e->getDescription(),
                number_format($e->getAmountHt() / 100, 2, '.', ''),
                number_format($e->getVatAmount() / 100, 2, '.', ''),
            ]);
        }
    }
}

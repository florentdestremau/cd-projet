<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Repository\ExpenseRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FinanceController extends AbstractController
{
    #[Route('/finances', name: 'app_finances_dashboard')]
    public function dashboard(InvoiceRepository $invoiceRepo, ExpenseRepository $expenseRepo): Response
    {
        $now = new \DateTimeImmutable();
        $caMonth = array_sum(array_map(
            fn (Invoice $i) => $i->getTotalHt(),
            $invoiceRepo->findPaidInMonth($now),
        ));
        $overdue = $invoiceRepo->findOverdue();
        $overdueTotal = array_sum(array_map(fn (Invoice $i) => $i->getBalanceDue(), $overdue));

        return $this->render('finance/dashboard.html.twig', [
            'ca_month' => $caMonth,
            'overdue' => $overdue,
            'overdue_total' => $overdueTotal,
        ]);
    }

    #[Route('/finances/export.csv', name: 'app_finances_export')]
    public function export(Request $request, InvoiceRepository $invoiceRepo, PaymentRepository $paymentRepo, ExpenseRepository $expenseRepo): StreamedResponse
    {
        $type = $request->query->getString('type', 'invoices');
        $from = new \DateTimeImmutable($request->query->getString('from', date('Y-01-01')));
        $to = new \DateTimeImmutable($request->query->getString('to', date('Y-m-d')));

        $response = new StreamedResponse(function () use ($type, $invoiceRepo, $paymentRepo, $expenseRepo, $from, $to) {
            $out = fopen('php://output', 'wb');
            assert(is_resource($out));
            match ($type) {
                'invoices' => $this->streamInvoices($out, $invoiceRepo, $from, $to),
                'payments' => $this->streamPayments($out, $paymentRepo, $from, $to),
                'expenses' => $this->streamExpenses($out, $expenseRepo, $from, $to),
                default => null,
            };
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s_%s_%s.csv"', $type, $from->format('Ymd'), $to->format('Ymd')));
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
            assert($invoice instanceof Invoice);
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
            assert($p instanceof Payment);
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
            assert($e instanceof Expense);
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

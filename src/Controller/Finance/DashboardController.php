<?php

namespace App\Controller\Finance;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/finances', name: 'app_finances_dashboard', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    public function __invoke(InvoiceRepository $invoiceRepository): Response
    {
        $now = new \DateTimeImmutable();
        $caMonth = array_sum(array_map(
            static fn (Invoice $i): int => $i->getTotalHt(),
            $invoiceRepository->findPaidInMonth($now),
        ));
        $overdue = $invoiceRepository->findOverdue();
        $overdueTotal = array_sum(array_map(static fn (Invoice $i): int => $i->getBalanceDue(), $overdue));

        return $this->render('finance/dashboard.html.twig', [
            'ca_month' => $caMonth,
            'overdue' => $overdue,
            'overdue_total' => $overdueTotal,
        ]);
    }
}

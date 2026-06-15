<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/factures/{reference}/statut', name: 'app_invoices_status', requirements: ['reference' => 'FAC-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class StatusController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Invoice $invoice,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('invoice_status_'.$invoice->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $status = InvoiceStatus::tryFrom((string) $request->request->get('status', ''));
        if (null !== $status) {
            $invoice->setStatus($status);
            if (InvoiceStatus::SENT === $status && !$invoice->getSentAt() instanceof \DateTimeImmutable) {
                $invoice->setSentAt(new \DateTimeImmutable());
            }
            $em->flush();
        }

        return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
    }
}

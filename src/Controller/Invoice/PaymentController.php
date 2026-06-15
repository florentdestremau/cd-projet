<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Entity\Payment;
use App\Enum\InvoiceStatus;
use App\Form\PaymentForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/factures/{reference}/paiement', name: 'app_invoices_payment', requirements: ['reference' => 'FAC-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class PaymentController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Invoice $invoice,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $payment = new Payment();
        $form = $this->createForm(PaymentForm::class, $payment);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Paiement invalide.');

            return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
        }

        $payment->setInvoice($invoice);
        $em->persist($payment);

        $invoice->addPayment($payment);
        if ($invoice->getAmountPaid() + $payment->getAmount() >= $invoice->getTotalTtc()) {
            $invoice->setStatus(InvoiceStatus::PAID);
            $invoice->setPaidAt(new \DateTimeImmutable());
        }

        $em->flush();
        $this->addFlash('success', 'Paiement enregistré.');

        return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
    }
}

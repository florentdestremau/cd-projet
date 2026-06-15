<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Enum\PaymentMethod;
use App\Form\PaymentForm;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/factures/{reference}', name: 'app_invoices_show', requirements: ['reference' => 'FAC-\d+-\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(#[MapEntity(mapping: ['reference' => 'reference'])] Invoice $invoice): Response
    {
        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
            'methods' => PaymentMethod::cases(),
            'payment_form' => $this->createForm(PaymentForm::class)->createView(),
        ]);
    }
}

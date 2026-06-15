<?php

namespace App\Controller\Invoice;

use App\Entity\Invoice;
use App\Entity\Project;
use App\Entity\Quote;
use App\Enum\QuoteStatus;
use App\Form\InvoiceForm;
use App\Repository\InvoiceRepository;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/facture/nouvelle', name: 'app_invoices_new', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        InvoiceRepository $invoiceRepository,
        QuoteRepository $quoteRepository,
        EntityManagerInterface $em,
    ): Response {
        $invoice = new Invoice();
        $invoice->setProject($project);

        $form = $this->createForm(InvoiceForm::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setReference($invoiceRepository->generateNextReference((int) date('Y')));
            $em->persist($invoice);
            $em->flush();

            $this->addFlash('success', 'Facture '.$invoice->getReference().' créée.');

            return $this->redirectToRoute('app_invoices_show', ['reference' => $invoice->getReference()]);
        }

        $acceptedQuotes = array_filter(
            $quoteRepository->findBy(['project' => $project]),
            static fn (Quote $q): bool => QuoteStatus::ACCEPTED === $q->getStatus(),
        );

        return $this->render('invoice/new.html.twig', [
            'project' => $project,
            'form' => $form,
            'accepted_quotes' => $acceptedQuotes,
        ]);
    }
}

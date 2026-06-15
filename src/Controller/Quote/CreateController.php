<?php

namespace App\Controller\Quote;

use App\Entity\Project;
use App\Entity\Quote;
use App\Form\QuoteForm;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/devis/nouveau', name: 'app_quotes_new', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        QuoteRepository $quoteRepository,
        EntityManagerInterface $em,
    ): Response {
        $quote = new Quote();
        $quote->setProject($project);

        $form = $this->createForm(QuoteForm::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quote->setReference($quoteRepository->generateNextReference((int) date('Y')));
            $em->persist($quote);
            $em->flush();

            $this->addFlash('success', 'Devis '.$quote->getReference().' créé.');

            return $this->redirectToRoute('app_quotes_show', ['reference' => $quote->getReference()]);
        }

        return $this->render('quote/new.html.twig', ['project' => $project, 'form' => $form]);
    }
}

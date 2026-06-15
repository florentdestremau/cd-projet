<?php

namespace App\Controller\Quote;

use App\Entity\Quote;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/devis/{reference}', name: 'app_quotes_show', requirements: ['reference' => 'DEV-\d+-\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(#[MapEntity(mapping: ['reference' => 'reference'])] Quote $quote): Response
    {
        return $this->render('quote/show.html.twig', ['quote' => $quote]);
    }
}

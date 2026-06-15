<?php

namespace App\Controller\Client;

use App\Entity\Client;
use App\Form\ClientForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients/nouveau', name: 'app_clients_new', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientForm::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Client créé.');

            return $this->redirectToRoute('app_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('client/new.html.twig', ['form' => $form]);
    }
}

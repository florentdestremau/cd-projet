<?php

namespace App\Controller\Client;

use App\Entity\Client;
use App\Form\ClientForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients/{id}/modifier', name: 'app_clients_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class UpdateController extends AbstractController
{
    public function __invoke(
        #[MapEntity] Client $client,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(ClientForm::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Client mis à jour.');

            return $this->redirectToRoute('app_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', ['client' => $client, 'form' => $form]);
    }
}

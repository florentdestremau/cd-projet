<?php

namespace App\Controller\Catalogue;

use App\Entity\Stone;
use App\Form\StoneForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/catalogues/pierres', name: 'app_catalogues_stone_create', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateStoneController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $stone = new Stone();
        $form = $this->createForm(StoneForm::class, $stone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stone);
            $em->flush();
            $this->addFlash('success', 'Pierre ajoutée.');
        } else {
            $this->addFlash('error', 'Pierre invalide.');
        }

        return $this->redirectToRoute('app_catalogues_index');
    }
}

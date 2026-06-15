<?php

namespace App\Controller\Catalogue;

use App\Entity\Material;
use App\Form\MaterialForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/catalogues/materiaux', name: 'app_catalogues_material_create', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateMaterialController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $material = new Material();
        $form = $this->createForm(MaterialForm::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($material);
            $em->flush();
            $this->addFlash('success', 'Matière ajoutée.');
        } else {
            $this->addFlash('error', 'Matière invalide.');
        }

        return $this->redirectToRoute('app_catalogues_index');
    }
}

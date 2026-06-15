<?php

namespace App\Controller\Catalogue;

use App\Entity\Supplier;
use App\Form\SupplierForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/catalogues/fournisseurs', name: 'app_catalogues_supplier_create', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateSupplierController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierForm::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supplier);
            $em->flush();
            $this->addFlash('success', 'Fournisseur ajouté.');
        } else {
            $this->addFlash('error', 'Fournisseur invalide.');
        }

        return $this->redirectToRoute('app_catalogues_index');
    }
}

<?php

namespace App\Controller\Catalogue;

use App\Form\MaterialForm;
use App\Form\StoneForm;
use App\Form\SupplierForm;
use App\Repository\MaterialRepository;
use App\Repository\StoneRepository;
use App\Repository\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/catalogues', name: 'app_catalogues_index', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class IndexController extends AbstractController
{
    public function __invoke(
        MaterialRepository $materialRepository,
        StoneRepository $stoneRepository,
        SupplierRepository $supplierRepository,
    ): Response {
        return $this->render('catalogue/index.html.twig', [
            'materials' => $materialRepository->findBy([], ['type' => 'ASC', 'name' => 'ASC']),
            'stones' => $stoneRepository->findBy([], ['type' => 'ASC', 'caratWeight' => 'DESC']),
            'suppliers' => $supplierRepository->findBy([], ['name' => 'ASC']),
            'material_form' => $this->createForm(MaterialForm::class)->createView(),
            'stone_form' => $this->createForm(StoneForm::class)->createView(),
            'supplier_form' => $this->createForm(SupplierForm::class)->createView(),
        ]);
    }
}

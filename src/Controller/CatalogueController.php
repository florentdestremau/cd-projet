<?php

namespace App\Controller;

use App\Entity\Material;
use App\Entity\Stone;
use App\Entity\Supplier;
use App\Enum\MaterialType;
use App\Enum\StoneType;
use App\Enum\SupplierSpecialty;
use App\Repository\MaterialRepository;
use App\Repository\StoneRepository;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogueController extends AbstractController
{
    #[Route('/catalogues', name: 'app_catalogues_index')]
    public function index(MaterialRepository $matRepo, StoneRepository $stoneRepo, SupplierRepository $supRepo): Response
    {
        return $this->render('catalogue/index.html.twig', [
            'materials' => $matRepo->findBy([], ['type' => 'ASC', 'name' => 'ASC']),
            'stones' => $stoneRepo->findBy([], ['type' => 'ASC', 'caratWeight' => 'DESC']),
            'suppliers' => $supRepo->findBy([], ['name' => 'ASC']),
            'material_types' => MaterialType::cases(),
            'stone_types' => StoneType::cases(),
            'supplier_specialties' => SupplierSpecialty::cases(),
        ]);
    }

    #[Route('/catalogues/materiaux', name: 'app_catalogues_material_create', methods: ['POST'])]
    public function createMaterial(Request $request, EntityManagerInterface $em, SupplierRepository $supRepo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->isCsrfTokenValid('catalogue_material', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $m = new Material();
        $m->setName($request->request->getString('name'));
        $m->setType(MaterialType::from($request->request->getString('type')));
        $m->setPricePerGram((int) round(((float) $request->request->getString('pricePerGram')) * 100));
        $supplierId = (int) $request->request->getString('supplier');
        if ($supplierId > 0 && ($supplier = $supRepo->find($supplierId)) instanceof Supplier) {
            $m->setSupplier($supplier);
        }
        $em->persist($m);
        $em->flush();
        $this->addFlash('success', 'Matière ajoutée.');

        return $this->redirectToRoute('app_catalogues_index');
    }

    #[Route('/catalogues/pierres', name: 'app_catalogues_stone_create', methods: ['POST'])]
    public function createStone(Request $request, EntityManagerInterface $em, SupplierRepository $supRepo): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->isCsrfTokenValid('catalogue_stone', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $s = new Stone();
        $s->setType(StoneType::from($request->request->getString('type')));
        $s->setCaratWeight((int) round(((float) $request->request->getString('carats')) * 1000));
        $s->setQuality($request->request->getString('quality', null) ?: null);
        $s->setColor($request->request->getString('color', null) ?: null);
        $s->setCostPrice((int) round(((float) $request->request->getString('cost')) * 100));
        $supplierId = (int) $request->request->getString('supplier');
        if ($supplierId > 0 && ($supplier = $supRepo->find($supplierId)) instanceof Supplier) {
            $s->setSupplier($supplier);
        }
        $em->persist($s);
        $em->flush();
        $this->addFlash('success', 'Pierre ajoutée.');

        return $this->redirectToRoute('app_catalogues_index');
    }

    #[Route('/catalogues/fournisseurs', name: 'app_catalogues_supplier_create', methods: ['POST'])]
    public function createSupplier(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->isCsrfTokenValid('catalogue_supplier', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $s = new Supplier();
        $s->setName($request->request->getString('name'));
        $s->setSpecialty(SupplierSpecialty::from($request->request->getString('specialty')));
        $s->setContactEmail($request->request->getString('contactEmail', null) ?: null);
        $s->setContactPhone($request->request->getString('contactPhone', null) ?: null);
        $em->persist($s);
        $em->flush();
        $this->addFlash('success', 'Fournisseur ajouté.');

        return $this->redirectToRoute('app_catalogues_index');
    }
}

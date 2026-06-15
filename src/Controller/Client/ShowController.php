<?php

namespace App\Controller\Client;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients/{id}', name: 'app_clients_show', requirements: ['id' => '\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(
        #[MapEntity] Client $client,
        ProjectRepository $projectRepo,
        InvoiceRepository $invoiceRepo,
    ): Response {
        $projects = $projectRepo->findBy(['client' => $client], ['updatedAt' => 'DESC']);
        $invoices = $invoiceRepo->createQueryBuilder('i')
            ->innerJoin('i.project', 'p')
            ->where('p.client = :client')->setParameter('client', $client)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()->getResult();

        $revenue = array_sum(array_map(
            static fn (Invoice $inv): int => 'paid' === $inv->getStatus()->value ? $inv->getTotalHt() : 0,
            $invoices,
        ));

        return $this->render('client/show.html.twig', [
            'client' => $client,
            'projects' => $projects,
            'invoices' => $invoices,
            'revenue' => $revenue,
        ]);
    }
}

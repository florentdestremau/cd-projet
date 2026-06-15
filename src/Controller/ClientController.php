<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_clients_index')]
    public function index(Request $request, ClientRepository $repo): Response
    {
        $search = trim($request->query->getString('q', ''));
        $qb = $repo->createQueryBuilder('c')->orderBy('c.displayName', 'ASC');
        if ('' !== $search) {
            $qb->andWhere('c.displayName LIKE :q OR c.companyName LIKE :q OR c.contactEmail LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return $this->render('client/index.html.twig', [
            'clients' => $qb->setMaxResults(200)->getQuery()->getResult(),
            'search' => $search,
        ]);
    }

    #[Route('/clients/nouveau', name: 'app_clients_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('client_new', $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }
            $client = new Client();
            $client->setDisplayName($request->request->getString('displayName'));
            $client->setCompanyName($request->request->getString('companyName', null) ?: null);
            $client->setContactEmail($request->request->getString('contactEmail', null) ?: null);
            $client->setContactPhone($request->request->getString('contactPhone', null) ?: null);
            $client->setAddress($request->request->getString('address', null) ?: null);
            $client->setNotes($request->request->getString('notes', null) ?: null);
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Client créé.');

            return $this->redirectToRoute('app_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('client/new.html.twig');
    }

    #[Route('/clients/{id}', name: 'app_clients_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ClientRepository $repo, ProjectRepository $projectRepo, InvoiceRepository $invoiceRepo): Response
    {
        $client = $repo->find($id);
        if (!$client instanceof Client) {
            throw $this->createNotFoundException();
        }

        $projects = $projectRepo->findBy(['client' => $client], ['updatedAt' => 'DESC']);
        $invoices = $invoiceRepo->createQueryBuilder('i')
            ->innerJoin('i.project', 'p')
            ->where('p.client = :client')->setParameter('client', $client)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()->getResult();

        $revenue = array_sum(array_map(
            static fn ($inv) => 'paid' === $inv->getStatus()->value ? $inv->getTotalHt() : 0,
            $invoices,
        ));

        return $this->render('client/show.html.twig', [
            'client' => $client,
            'projects' => $projects,
            'invoices' => $invoices,
            'revenue' => $revenue,
        ]);
    }

    #[Route('/clients/{id}/modifier', name: 'app_clients_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, ClientRepository $repo, EntityManagerInterface $em): Response
    {
        $client = $repo->find($id);
        if (!$client instanceof Client) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('client_edit_'.$client->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }
            $client->setDisplayName($request->request->getString('displayName'));
            $client->setCompanyName($request->request->getString('companyName', null) ?: null);
            $client->setContactEmail($request->request->getString('contactEmail', null) ?: null);
            $client->setContactPhone($request->request->getString('contactPhone', null) ?: null);
            $client->setAddress($request->request->getString('address', null) ?: null);
            $client->setNotes($request->request->getString('notes', null) ?: null);
            $em->flush();
            $this->addFlash('success', 'Client mis à jour.');

            return $this->redirectToRoute('app_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', ['client' => $client]);
    }
}

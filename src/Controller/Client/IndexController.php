<?php

namespace App\Controller\Client;

use App\Dto\ClientFilters;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/clients', name: 'app_clients_index', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class IndexController extends AbstractController
{
    public function __invoke(
        ClientRepository $repository,
        #[MapQueryString] ClientFilters $filters = new ClientFilters(),
    ): Response {
        $qb = $repository->createQueryBuilder('c')->orderBy('c.displayName', 'ASC');
        if ('' !== $filters->q) {
            $qb->andWhere('c.displayName LIKE :q OR c.companyName LIKE :q OR c.contactEmail LIKE :q')
                ->setParameter('q', '%'.$filters->q.'%');
        }

        return $this->render('client/index.html.twig', [
            'clients' => $qb->setMaxResults(200)->getQuery()->getResult(),
            'search' => $filters->q,
        ]);
    }
}

<?php

namespace App\Controller\Project;

use App\Dto\ProjectFilters;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets', name: 'app_projects_index', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class IndexController extends AbstractController
{
    public function __invoke(
        ProjectRepository $repository,
        #[MapQueryString] ProjectFilters $filters = new ProjectFilters(),
    ): Response {
        $qb = $repository->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')->addSelect('c')
            ->orderBy('p.updatedAt', 'DESC');

        if ($filters->status instanceof \App\Enum\ProjectStatus) {
            $qb->andWhere('p.status = :status')->setParameter('status', $filters->status);
        } else {
            $qb->andWhere('p.status = :defaultStatus')->setParameter('defaultStatus', ProjectStatus::ACTIVE);
        }

        if ($filters->stage instanceof \App\Enum\ProjectStage) {
            $qb->andWhere('p.currentStage = :stage')->setParameter('stage', $filters->stage);
        }

        if ('' !== $filters->q) {
            $qb->andWhere('p.title LIKE :q OR p.reference LIKE :q OR c.displayName LIKE :q')
                ->setParameter('q', '%'.$filters->q.'%');
        }

        return $this->render('project/index.html.twig', [
            'projects' => $qb->setMaxResults(100)->getQuery()->getResult(),
            'filters' => $filters,
            'statuses' => ProjectStatus::cases(),
            'stages' => ProjectStage::ordered(),
        ]);
    }
}

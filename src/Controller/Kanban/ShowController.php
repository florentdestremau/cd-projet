<?php

namespace App\Controller\Kanban;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/vue/kanban', name: 'app_projects_kanban', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(ProjectRepository $repository): Response
    {
        $projects = $repository->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')->addSelect('c')
            ->where('p.status = :s')->setParameter('s', ProjectStatus::ACTIVE)
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.targetDeliveryDate', 'ASC')
            ->getQuery()->getResult();

        $columns = [];
        foreach (ProjectStage::ordered() as $stage) {
            $columns[$stage->value] = [
                'stage' => $stage,
                'projects' => array_values(array_filter(
                    $projects,
                    static fn (Project $p): bool => $p->getCurrentStage() === $stage,
                )),
            ];
        }

        return $this->render('project/kanban.html.twig', [
            'columns' => $columns,
            'stages' => ProjectStage::ordered(),
        ]);
    }
}

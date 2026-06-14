<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ActivityLog;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class KanbanController extends AbstractController
{
    #[Route('/projets/vue/kanban', name: 'app_projects_kanban')]
    public function index(ProjectRepository $repository): Response
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
                    fn (Project $p) => $p->getCurrentStage() === $stage,
                )),
            ];
        }

        return $this->render('project/kanban.html.twig', [
            'columns' => $columns,
            'stages' => ProjectStage::ordered(),
        ]);
    }

    #[Route('/api/projets/{reference}/etape', name: 'app_projects_change_stage', methods: ['POST'], requirements: ['reference' => 'BAG-\d+-\d+'])]
    public function changeStage(
        string $reference,
        Request $request,
        ProjectRepository $repository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $project = $repository->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }
        if (!$this->isCsrfTokenValid('stage_'.$project->getId(), $request->headers->get('X-CSRF-Token', ''))) {
            return new JsonResponse(['error' => 'csrf'], 403);
        }

        $payload = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $stage = ProjectStage::tryFrom($payload['stage'] ?? '');
        if ($stage === null) {
            return new JsonResponse(['error' => 'invalid_stage'], 400);
        }

        $previous = $project->getCurrentStage();
        if ($previous === $stage) {
            return new JsonResponse(['ok' => true]);
        }

        // Marquer les étapes précédentes comme complétées si on avance
        $now = new \DateTimeImmutable();
        foreach ($project->getStageStatuses() as $status) {
            if ($status->getStage()->position() < $stage->position() && $status->getCompletedAt() === null) {
                $status->setCompletedAt($now);
            }
            if ($status->getStage() === $stage && $status->getStartedAt() === null) {
                $status->setStartedAt($now);
            }
        }
        $project->setCurrentStage($stage);
        $project->touch();

        /** @var User $user */
        $user = $this->getUser();
        $activity = new ActivityLog();
        $activity->setProject($project);
        $activity->setActor($user);
        $activity->setEventType('project.stage_changed');
        $activity->setPayload(['from' => $previous->value, 'to' => $stage->value]);
        $em->persist($activity);

        $em->flush();

        return new JsonResponse([
            'ok' => true,
            'from' => $previous->value,
            'to' => $stage->value,
        ]);
    }
}

<?php

namespace App\Controller\Kanban;

use App\Entity\ActivityLog;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\ProjectStage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/projets/{reference}/etape', name: 'app_projects_change_stage', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class ChangeStageController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('stage_'.$project->getId(), (string) $request->headers->get('X-CSRF-Token', ''))) {
            return new JsonResponse(['error' => 'csrf'], 403);
        }
        $payload = json_decode((string) $request->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        $stage = ProjectStage::tryFrom($payload['stage'] ?? '');
        if (null === $stage) {
            return new JsonResponse(['error' => 'invalid_stage'], 400);
        }

        $previous = $project->getCurrentStage();
        if ($previous === $stage) {
            return new JsonResponse(['ok' => true]);
        }

        $now = new \DateTimeImmutable();
        foreach ($project->getStageStatuses() as $status) {
            if ($status->getStage()->position() < $stage->position() && null === $status->getCompletedAt()) {
                $status->setCompletedAt($now);
            }
            if ($status->getStage() === $stage && null === $status->getStartedAt()) {
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

        return new JsonResponse(['ok' => true, 'from' => $previous->value, 'to' => $stage->value]);
    }
}

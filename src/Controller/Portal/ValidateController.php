<?php

namespace App\Controller\Portal;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/portail/{token}/valider', name: 'app_portal_validate', requirements: ['token' => '[a-f0-9]{64}'], methods: ['POST'])]
final class ValidateController extends AbstractController
{
    public function __invoke(
        string $token,
        ProjectRepository $repository,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $project = $repository->findOneBy(['clientAccessToken' => $token]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        if (ProjectStage::CLIENT_VALIDATION === $project->getCurrentStage()) {
            $now = new \DateTimeImmutable();
            foreach ($project->getStageStatuses() as $status) {
                if (ProjectStage::CLIENT_VALIDATION === $status->getStage()) {
                    $status->setCompletedAt($now);
                }
                if (ProjectStage::CAD_3D === $status->getStage() && null === $status->getStartedAt()) {
                    $status->setStartedAt($now);
                }
            }
            $project->setCurrentStage(ProjectStage::CAD_3D);
            $project->touch();
            $em->flush();
            $this->addFlash('success', 'Merci pour votre validation, la modélisation 3D va commencer.');
        }

        return $this->redirectToRoute('app_portal_show', ['token' => $token]);
    }
}

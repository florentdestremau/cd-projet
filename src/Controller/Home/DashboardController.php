<?php

namespace App\Controller\Home;

use App\Entity\User;
use App\Repository\ActivityLogRepository;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'app_home', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    public function __invoke(
        ProjectRepository $projectRepository,
        CommentRepository $commentRepository,
        TaskRepository $taskRepository,
        ActivityLogRepository $activityLogRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'mentions' => $commentRepository->findRecentMentions($user, 5),
            'tasks' => $taskRepository->findOpenForUser($user, 8),
            'recent_activity' => $activityLogRepository->findRecent(15),
            'my_projects' => $projectRepository->findBy(
                ['assignedDesigner' => $user],
                ['updatedAt' => 'DESC'],
                6,
            ),
        ]);
    }
}

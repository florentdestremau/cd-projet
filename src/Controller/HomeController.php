<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ActivityLogRepository;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
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

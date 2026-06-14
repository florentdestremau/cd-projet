<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ActivityLog;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Service\MentionParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('/projets/{reference}/commentaires', name: 'app_comments_create', methods: ['POST'], requirements: ['reference' => 'BAG-\d+-\d+'])]
    public function create(
        string $reference,
        Request $request,
        ProjectRepository $projectRepository,
        MentionParser $mentionParser,
        EntityManagerInterface $em,
    ): Response {
        $project = $projectRepository->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('comment_'.$project->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $body = trim($request->request->getString('body'));
        if ($body === '') {
            $this->addFlash('error', 'Le message ne peut pas être vide.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $comment = new Comment();
        $comment->setProject($project);
        $comment->setAuthor($user);
        $comment->setBody($body);
        $mentionParser->attachMentions($comment);
        $em->persist($comment);

        $project->touch();

        $activity = new ActivityLog();
        $activity->setProject($project);
        $activity->setActor($user);
        $activity->setEventType('comment.created');
        $activity->setPayload(['comment_excerpt' => mb_substr($body, 0, 100)]);
        $em->persist($activity);

        $em->flush();

        return $this->redirectToRoute('app_projects_show', ['reference' => $reference], Response::HTTP_SEE_OTHER);
    }
}

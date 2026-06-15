<?php

namespace App\Controller\Comment;

use App\Entity\ActivityLog;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\User;
use App\Form\CommentForm;
use App\Service\ActivityPublisher;
use App\Service\MentionParser;
use App\Service\WebPushSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/commentaires', name: 'app_comments_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        MentionParser $mentionParser,
        EntityManagerInterface $em,
        ActivityPublisher $activityPublisher,
        WebPushSender $webPushSender,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $comment = new Comment();
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Message invalide.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $comment->setProject($project);
        $comment->setAuthor($user);
        $mentionParser->attachMentions($comment);
        $em->persist($comment);

        $project->touch();

        $activity = new ActivityLog();
        $activity->setProject($project);
        $activity->setActor($user);
        $activity->setEventType('comment.created');
        $activity->setPayload(['comment_excerpt' => mb_substr($comment->getBody(), 0, 100)]);
        $em->persist($activity);

        $em->flush();

        $activityPublisher->publishComment($comment);
        foreach ($comment->getMentions() as $mentioned) {
            $webPushSender->notify(
                $mentioned,
                \sprintf('%s vous mentionne', $user->getFirstName()),
                mb_substr($comment->getBody(), 0, 180),
                $this->generateUrl('app_projects_show', ['reference' => $project->getReference()]),
            );
        }

        return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()], Response::HTTP_SEE_OTHER);
    }
}

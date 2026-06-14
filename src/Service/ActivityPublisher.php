<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Project;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class ActivityPublisher
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function publishComment(Comment $comment): void
    {
        $project = $comment->getProject();
        if (!$project instanceof Project) {
            return;
        }

        try {
            $stream = $this->twig->render('project/_comment_stream.html.twig', [
                'comment' => $comment,
                'project' => $project,
            ]);
            $this->hub->publish(new Update(
                sprintf('project/%d', $project->getId()),
                $stream,
            ));

            foreach ($comment->getMentions() as $user) {
                $payload = json_encode([
                    'type' => 'mention',
                    'project_reference' => $project->getReference(),
                    'project_title' => $project->getTitle(),
                    'author' => $comment->getAuthor()?->getFullName(),
                    'excerpt' => mb_substr($comment->getBody(), 0, 140),
                ], JSON_THROW_ON_ERROR);
                $this->hub->publish(new Update(
                    sprintf('user/%d/notifications', $user->getId()),
                    $payload,
                    private: true,
                ));
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Mercure publish failed', ['exception' => $e]);
        }
    }
}

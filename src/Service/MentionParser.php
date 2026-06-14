<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\UserRepository;

final readonly class MentionParser
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function attachMentions(Comment $comment): void
    {
        $comment->clearMentions();

        $body = $comment->getBody();
        preg_match_all('/@([a-zA-Z]+(?:[._-][a-zA-Z]+)*)/', $body, $matches);
        if (!isset($matches[1])) {
            return;
        }

        $handles = array_unique(array_map(strtolower(...), $matches[1]));
        if ([] === $handles) {
            return;
        }

        foreach ($handles as $handle) {
            $user = $this->findUserByHandle($handle);
            if ($user instanceof User && $user !== $comment->getAuthor()) {
                $comment->addMention($user);
            }
        }
    }

    private function findUserByHandle(string $handle): ?User
    {
        foreach ($this->userRepository->findAll() as $user) {
            $candidate = strtolower($user->getFirstName());
            if ($candidate === $handle) {
                return $user;
            }
            $full = strtolower($user->getFirstName().'.'.$user->getLastName());
            if ($full === $handle) {
                return $user;
            }
        }

        return null;
    }
}

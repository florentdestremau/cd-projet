<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserPushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Log\LoggerInterface;

final readonly class WebPushSender
{
    public function __construct(
        private UserPushSubscriptionRepository $repository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private string $vapidPublicKey,
        private string $vapidPrivateKey,
        private string $vapidSubject,
    ) {
    }

    public function notify(User $user, string $title, string $body, ?string $url = null): void
    {
        if ('' === $this->vapidPublicKey || '' === $this->vapidPrivateKey) {
            return;
        }

        $subscriptions = $this->repository->findForUser($user);
        if ([] === $subscriptions) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $this->vapidSubject,
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ]);

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
        ], \JSON_THROW_ON_ERROR);

        $map = [];
        foreach ($subscriptions as $sub) {
            $minishlink = Subscription::create([
                'endpoint' => $sub->getEndpoint(),
                'publicKey' => $sub->getP256dhKey(),
                'authToken' => $sub->getAuthToken(),
            ]);
            $webPush->queueNotification($minishlink, $payload);
            $map[$sub->getEndpoint()] = $sub;
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $endpoint = $report->getEndpoint();
                $this->logger->warning('Web push failed', [
                    'endpoint' => $endpoint,
                    'reason' => $report->getReason(),
                ]);
                if ($report->isSubscriptionExpired() && isset($map[$endpoint])) {
                    $this->em->remove($map[$endpoint]);
                    $this->em->flush();
                }
            }
        }
    }
}

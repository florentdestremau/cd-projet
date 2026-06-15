<?php

namespace App\Controller\Notification;

use App\Entity\User;
use App\Entity\UserPushSubscription;
use App\Repository\UserPushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/push/subscribe', name: 'app_push_subscribe', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class SubscribeController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserPushSubscriptionRepository $repository,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $payload = json_decode((string) $request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        $endpoint = $payload['endpoint'] ?? '';
        $p256dh = $payload['keys']['p256dh'] ?? '';
        $auth = $payload['keys']['auth'] ?? '';
        if (in_array('', [$endpoint, $p256dh, $auth], true)) {
            return new JsonResponse(['error' => 'missing_fields'], 400);
        }

        $existing = $repository->findByEndpoint($endpoint);
        if ($existing instanceof UserPushSubscription) {
            $existing->setUser($user);
            $existing->setP256dhKey($p256dh);
            $existing->setAuthToken($auth);
        } else {
            $sub = new UserPushSubscription();
            $sub->setUser($user);
            $sub->setEndpoint($endpoint);
            $sub->setP256dhKey($p256dh);
            $sub->setAuthToken($auth);
            $em->persist($sub);
        }

        $em->flush();

        return new JsonResponse(['ok' => true]);
    }
}

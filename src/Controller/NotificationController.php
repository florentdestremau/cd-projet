<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPushSubscription;
use App\Repository\UserPushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(string:VAPID_PUBLIC_KEY)%')]
        private readonly string $vapidPublicKey,
    ) {
    }

    #[Route('/profil/notifications', name: 'app_notifications_prefs')]
    public function prefs(UserPushSubscriptionRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('notification/prefs.html.twig', [
            'vapid_public_key' => $this->vapidPublicKey,
            'subscriptions' => $repo->findForUser($user),
        ]);
    }

    #[Route('/api/push/subscribe', name: 'app_push_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        UserPushSubscriptionRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        $endpoint = $payload['endpoint'] ?? '';
        $p256dh = $payload['keys']['p256dh'] ?? '';
        $auth = $payload['keys']['auth'] ?? '';
        if (\in_array('', [$endpoint, $p256dh, $auth], true)) {
            return new JsonResponse(['error' => 'missing_fields'], 400);
        }

        $existing = $repo->findByEndpoint($endpoint);
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

    #[Route('/api/push/unsubscribe', name: 'app_push_unsubscribe', methods: ['POST'])]
    public function unsubscribe(
        Request $request,
        UserPushSubscriptionRepository $repo,
        EntityManagerInterface $em,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        $endpoint = $payload['endpoint'] ?? '';
        if ('' === $endpoint) {
            return new JsonResponse(['error' => 'missing_endpoint'], 400);
        }
        $sub = $repo->findByEndpoint($endpoint);
        if ($sub instanceof UserPushSubscription) {
            $em->remove($sub);
            $em->flush();
        }

        return new JsonResponse(['ok' => true]);
    }
}

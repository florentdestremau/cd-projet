<?php

namespace App\Controller\Notification;

use App\Entity\UserPushSubscription;
use App\Repository\UserPushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/push/unsubscribe', name: 'app_push_unsubscribe', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class UnsubscribeController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserPushSubscriptionRepository $repository,
        EntityManagerInterface $em,
    ): JsonResponse {
        $payload = json_decode((string) $request->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        $endpoint = $payload['endpoint'] ?? '';
        if ('' === $endpoint) {
            return new JsonResponse(['error' => 'missing_endpoint'], 400);
        }
        $sub = $repository->findByEndpoint($endpoint);
        if ($sub instanceof UserPushSubscription) {
            $em->remove($sub);
            $em->flush();
        }

        return new JsonResponse(['ok' => true]);
    }
}

<?php

namespace App\Controller\Notification;

use App\Entity\User;
use App\Repository\UserPushSubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil/notifications', name: 'app_notifications_prefs', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class PrefsController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(string:VAPID_PUBLIC_KEY)%')]
        private readonly string $vapidPublicKey,
    ) {
    }

    public function __invoke(UserPushSubscriptionRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('notification/prefs.html.twig', [
            'vapid_public_key' => $this->vapidPublicKey,
            'subscriptions' => $repository->findForUser($user),
        ]);
    }
}

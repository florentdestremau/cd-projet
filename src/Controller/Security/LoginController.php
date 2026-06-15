<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
final class LoginController extends AbstractController
{
    public function __invoke(
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepository,
    ): Response {
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'demo_users' => $this->buildDemoUsers($userRepository),
        ]);
    }

    /**
     * @return list<array{email: string, name: string, role: string}>
     */
    private function buildDemoUsers(UserRepository $repo): array
    {
        $users = $repo->findBy([], ['firstName' => 'ASC']);

        return array_map(static fn (User $u): array => [
            'email' => $u->getEmail(),
            'name' => $u->getFullName(),
            'role' => self::primaryRoleLabel($u),
        ], $users);
    }

    private static function primaryRoleLabel(User $user): string
    {
        foreach (UserRole::cases() as $role) {
            if (\in_array($role->value, $user->getRoles(), true)) {
                return $role->label();
            }
        }

        return 'Utilisateur';
    }
}

<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/logout', name: 'app_logout')]
final class LogoutController
{
    public function __invoke(): never
    {
        throw new \LogicException('Intercepté par le firewall logout.');
    }
}

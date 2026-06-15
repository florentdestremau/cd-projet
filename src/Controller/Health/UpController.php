<?php

namespace App\Controller\Health;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/up', name: 'app_health', methods: ['GET', 'HEAD'])]
final class UpController
{
    public function __invoke(): Response
    {
        return new Response('OK', 200, ['Content-Type' => 'text/plain']);
    }
}

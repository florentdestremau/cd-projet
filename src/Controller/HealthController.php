<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    #[Route('/up', name: 'app_health', methods: ['GET', 'HEAD'])]
    public function up(): Response
    {
        return new Response('OK', 200, ['Content-Type' => 'text/plain']);
    }
}

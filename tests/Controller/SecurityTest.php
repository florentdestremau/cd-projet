<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityTest extends WebTestCase
{
    public function testHomeRedirectsToLoginWhenAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testLoginPageRenders(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Maison');
        self::assertSelectorTextContains('p', 'Atelier — Connexion');
        self::assertCount(1, $crawler->filter('input[name="_username"]'));
        self::assertCount(1, $crawler->filter('input[name="_password"]'));
        self::assertCount(1, $crawler->filter('input[name="_csrf_token"]'));
    }

    public function testLoggedInUserSeesDashboard(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $marie = $userRepository->findByEmail('designer1@maison.test');
        self::assertNotNull($marie);

        $client->loginUser($marie);
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Hey, Marie');
        self::assertSelectorExists('h2:contains("Mentions et réponses")');
        self::assertSelectorExists('h2:contains("Activité récente")');
    }

    public function testLogoutSendsBackToLogin(): void
    {
        $client = static::createClient();
        $admin = static::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        self::assertNotNull($admin);
        $client->loginUser($admin);

        $client->request('GET', '/logout');
        self::assertResponseRedirects();

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'Atelier — Connexion');
    }
}

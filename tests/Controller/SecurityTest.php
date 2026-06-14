<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityTest extends WebTestCase
{
    public function testHomeRedirectsToLoginWhenAnonymous(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testLoginPageRenders(): void
    {
        $client = self::createClient();
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
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
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
        $client = self::createClient();
        $admin = self::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        self::assertNotNull($admin);
        $client->loginUser($admin);

        $client->request('GET', '/logout');
        self::assertResponseRedirects();

        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'Atelier — Connexion');
    }
}

<?php

namespace App\Tests\Controller\Security;

use App\Tests\WebTestCase;

final class LoginControllerTest extends WebTestCase
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
    }

    public function testLoggedInUserRedirectedFromLogin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }
}

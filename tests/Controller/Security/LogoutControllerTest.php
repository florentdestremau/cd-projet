<?php

namespace App\Tests\Controller\Security;

use App\Tests\WebTestCase;

final class LogoutControllerTest extends WebTestCase
{
    public function testLogoutRedirectsToLogin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/logout');
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('p', 'Atelier — Connexion');
    }
}

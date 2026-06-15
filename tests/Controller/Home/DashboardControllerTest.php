<?php

namespace App\Tests\Controller\Home;

use App\Tests\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    public function testDashboardRequiresAuth(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }

    public function testDashboardRendersForUser(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Hey, Marie');
        self::assertStringContainsString('Mentions et réponses', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Activité récente', (string) $client->getResponse()->getContent());
    }
}

<?php

namespace App\Tests\Controller\Finance;

use App\Tests\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    public function testRenders(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/finances');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Finances');
    }
}

<?php

namespace App\Tests\Controller\Client;

use App\Tests\WebTestCase;

final class IndexControllerTest extends WebTestCase
{
    public function testRequiresAuth(): void
    {
        $client = self::createClient();
        $client->request('GET', '/clients');
        self::assertResponseRedirects('/login');
    }

    public function testListsClients(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/clients');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Clients');
        self::assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
    }

    public function testFiltersBySearch(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/clients?q=Maison');
        self::assertResponseIsSuccessful();
        $rows = $crawler->filter('table tbody tr');
        self::assertGreaterThan(0, $rows->count());
        foreach ($rows as $row) {
            self::assertStringContainsString('Maison', $row->textContent);
        }
    }
}

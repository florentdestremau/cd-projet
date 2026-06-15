<?php

namespace App\Tests\Controller\Project;

use App\Tests\WebTestCase;

final class IndexControllerTest extends WebTestCase
{
    public function testRequiresAuth(): void
    {
        $client = self::createClient();
        $client->request('GET', '/projets');
        self::assertResponseRedirects('/login');
    }

    public function testListsActiveProjects(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/projets');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Projets');
        self::assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
        self::assertMatchesRegularExpression('/BAG-\d{4}-\d+/', (string) $client->getResponse()->getContent());
    }

    public function testFiltersByStage(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/projets?stage=brief');
        self::assertResponseIsSuccessful();
        $rows = $crawler->filter('table tbody tr');
        foreach ($rows as $row) {
            self::assertStringContainsString('Brief', $row->textContent);
        }
    }

    public function testSearchByReference(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/projets?q=BAG-');
        self::assertResponseIsSuccessful();
    }
}

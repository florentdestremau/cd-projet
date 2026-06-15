<?php

namespace App\Tests\Controller\Kanban;

use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testRendersWithColumns(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/projets/vue/kanban');
        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('.kanban__column')->count());
    }
}

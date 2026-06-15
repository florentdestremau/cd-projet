<?php

namespace App\Tests\Controller\Client;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testShowsClientWithProjects(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $repo = self::getContainer()->get(ClientRepository::class);
        \assert($repo instanceof ClientRepository);
        $aClient = $repo->findOneBy([]);
        self::assertInstanceOf(Client::class, $aClient);

        $client->request('GET', '/clients/'.$aClient->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $aClient->getDisplayName());
        self::assertStringContainsString('Projets', (string) $client->getResponse()->getContent());
    }

    public function testNotFound(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/clients/999999');
        self::assertResponseStatusCodeSame(404);
    }
}

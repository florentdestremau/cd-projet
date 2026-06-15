<?php

namespace App\Tests\Controller\Client;

use App\Repository\ClientRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testShowsForm(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/clients/nouveau');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorTextContains('h1', 'Nouveau client');
    }

    public function testCreatesValidClient(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/clients/nouveau');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['client_form[displayName]'] = 'M. Test E2E';
        $form['client_form[contactEmail]'] = 'test-e2e@example.com';
        $client->submit($form);

        self::assertResponseRedirects();
        $repo = self::getContainer()->get(ClientRepository::class);
        \assert($repo instanceof ClientRepository);
        $created = $repo->findOneBy(['displayName' => 'M. Test E2E']);
        self::assertNotNull($created);
        self::assertSame('test-e2e@example.com', $created->getContactEmail());
    }

    public function testInvalidFormRejected(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $crawler = $client->request('GET', '/clients/nouveau');
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['client_form[displayName]'] = '';
        $client->submit($form);
        // Form re-renders with errors → 422 (Unprocessable Content)
        self::assertResponseStatusCodeSame(422);
    }
}

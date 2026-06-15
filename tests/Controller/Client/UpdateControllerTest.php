<?php

namespace App\Tests\Controller\Client;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Tests\WebTestCase;

final class UpdateControllerTest extends WebTestCase
{
    public function testUpdatesClient(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $repo = self::getContainer()->get(ClientRepository::class);
        \assert($repo instanceof ClientRepository);
        $existing = $repo->findOneBy([]);
        self::assertInstanceOf(Client::class, $existing);

        $crawler = $client->request('GET', '/clients/'.$existing->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['client_form[notes]'] = 'Notes mises à jour par le test';
        $client->submit($form);

        self::assertResponseRedirects('/clients/'.$existing->getId());
        self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class)->clear();
        $updated = $repo->find($existing->getId());
        self::assertStringContainsString('mises à jour', $updated->getNotes());
    }
}

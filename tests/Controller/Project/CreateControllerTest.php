<?php

namespace App\Tests\Controller\Project;

use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testShowsForm(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/projets/nouveau');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouveau projet');
        self::assertSelectorExists('form');
    }

    public function testCreatesValidProject(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $aClient = self::getContainer()->get(ClientRepository::class)->findOneBy([]);
        $crawler = $client->request('GET', '/projets/nouveau');

        $form = $crawler->selectButton('Créer')->form();
        $form['project_form[title]'] = 'Bague test e2e';
        $form['project_form[client]'] = (string) $aClient->getId();
        $form['project_form[budgetTarget]'] = '5000';
        $form['project_form[sellingPrice]'] = '7000';
        $client->submit($form);

        self::assertResponseRedirects();
        $location = (string) $client->getResponse()->headers->get('Location');
        self::assertMatchesRegularExpression('#/projets/BAG-\d+-\d+#', $location);

        $created = self::getContainer()->get(ProjectRepository::class)->findOneBy(['title' => 'Bague test e2e']);
        self::assertNotNull($created);
        self::assertCount(10, $created->getStageStatuses());
    }
}

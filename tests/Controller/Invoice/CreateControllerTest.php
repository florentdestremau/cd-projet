<?php

namespace App\Tests\Controller\Invoice;

use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testShowsForm(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $client->request('GET', '/projets/'.$project->getReference().'/facture/nouvelle');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouvelle facture');
    }
}

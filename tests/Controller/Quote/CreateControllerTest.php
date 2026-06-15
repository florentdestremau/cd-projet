<?php

namespace App\Tests\Controller\Quote;

use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testShowsForm(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $client->request('GET', '/projets/'.$project->getReference().'/devis/nouveau');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouveau devis');
    }
}

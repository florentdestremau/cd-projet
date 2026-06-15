<?php

namespace App\Tests\Controller\Project;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class RegenerateTokenControllerTest extends WebTestCase
{
    public function testRegeneratesToken(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        self::assertInstanceOf(Project::class, $project);

        // Charge la fiche projet pour démarrer la session + lire le token
        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action*="/portail/regenerer"]')->form();
        $client->submit($form);
        self::assertResponseRedirects();

        self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class)->clear();
        $refreshed = self::getContainer()->get(ProjectRepository::class)->find($project->getId());
        self::assertNotEmpty($refreshed->getClientAccessToken());
        self::assertSame(64, \strlen((string) $refreshed->getClientAccessToken()));
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $client->request('POST', '/projets/'.$project->getReference().'/portail/regenerer', ['_token' => 'invalid']);
        self::assertResponseStatusCodeSame(403);
    }
}

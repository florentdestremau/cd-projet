<?php

namespace App\Tests\Controller\Project;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testShowRendersWithSections(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $repo = self::getContainer()->get(ProjectRepository::class);
        \assert($repo instanceof ProjectRepository);
        $project = $repo->findActiveOrdered(1)[0] ?? null;
        self::assertInstanceOf(Project::class, $project);

        $client->request('GET', '/projets/'.$project->getReference());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $project->getTitle());
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Fil de discussion', $content);
        self::assertStringContainsString('Équipe', $content);
        self::assertStringContainsString('Avancement', $content);
        self::assertStringContainsString('Finances', $content);
    }

    public function test404OnUnknownReference(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/projets/BAG-9999-999');
        self::assertResponseStatusCodeSame(404);
    }
}

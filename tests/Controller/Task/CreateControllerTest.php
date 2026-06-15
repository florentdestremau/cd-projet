<?php

namespace App\Tests\Controller\Task;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testCreatesTask(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action$="/taches"]')->form();
        $form['task_form[title]'] = 'Tâche e2e unique';
        $client->submit($form);
        self::assertResponseRedirects('/projets/'.$project->getReference());

        $repo = self::getContainer()->get(TaskRepository::class);
        $created = $repo->findOneBy(['title' => 'Tâche e2e unique']);
        self::assertNotNull($created);
        self::assertSame($project->getId(), $created->getProject()->getId());
    }
}

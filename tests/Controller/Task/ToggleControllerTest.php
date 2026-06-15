<?php

namespace App\Tests\Controller\Task;

use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class ToggleControllerTest extends WebTestCase
{
    public function testTogglesTaskComplete(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $task = new Task();
        $task->setProject($project);
        $task->setTitle('Test toggle e2e');
        $em->persist($task);
        $em->flush();
        $id = $task->getId();

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $toggleForm = $crawler->filter('form[action="/taches/'.$id.'/toggle"]')->form();
        $client->submit($toggleForm);
        self::assertResponseRedirects();

        $em->clear();
        $refreshed = self::getContainer()->get(TaskRepository::class)->find($id);
        self::assertNotNull($refreshed->getCompletedAt());

        // Toggle back
        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $toggleForm = $crawler->filter('form[action="/taches/'.$id.'/toggle"]')->form();
        $client->submit($toggleForm);

        $em->clear();
        $refreshed = self::getContainer()->get(TaskRepository::class)->find($id);
        self::assertNull($refreshed->getCompletedAt());
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $task = self::getContainer()->get(TaskRepository::class)->findOneBy([]);
        $client->request('POST', '/taches/'.$task->getId().'/toggle', ['_token' => 'invalid']);
        self::assertResponseStatusCodeSame(403);
    }
}

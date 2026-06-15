<?php

namespace App\Tests\Controller\Task;

use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteControllerTest extends WebTestCase
{
    public function testDeletesTask(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $task = new Task();
        $task->setProject($project);
        $task->setTitle('À supprimer e2e');
        $em->persist($task);
        $em->flush();
        $id = $task->getId();

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action="/taches/'.$id.'/supprimer"]')->form();
        $client->submit($form);
        self::assertResponseRedirects('/projets/'.$project->getReference());

        $em->clear();
        self::assertNull(self::getContainer()->get(TaskRepository::class)->find($id));
    }
}

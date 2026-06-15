<?php

namespace App\Tests\Controller\Expense;

use App\Entity\Expense;
use App\Entity\Project;
use App\Repository\ExpenseRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteControllerTest extends WebTestCase
{
    public function testDeletesExpense(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        \assert($project instanceof Project);

        $expense = new Expense();
        $expense->setProject($project);
        $expense->setDescription('UNIQUE_DELETE_TARGET_E2E');
        $expense->setAmountHt(1000);
        $em->persist($expense);
        $em->flush();
        $id = $expense->getId();

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action="/depenses/'.$id.'/supprimer"]')->form();
        $client->submit($form);
        self::assertResponseRedirects('/projets/'.$project->getReference());

        $em->clear();
        self::assertNull(self::getContainer()->get(ExpenseRepository::class)->find($id));
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $expense = self::getContainer()->get(ExpenseRepository::class)->findOneBy([]);
        $client->request('POST', '/depenses/'.$expense->getId().'/supprimer', ['_token' => 'invalid']);
        self::assertResponseStatusCodeSame(403);
    }
}

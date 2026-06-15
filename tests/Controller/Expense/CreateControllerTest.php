<?php

namespace App\Tests\Controller\Expense;

use App\Repository\ExpenseRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testCreatesExpenseFromProject(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $crawler = $client->request('GET', '/projets/'.$project->getReference());

        $form = $crawler->filter('form[action$="/depenses"]')->form();
        $form['expense_form[description]'] = 'Test expense e2e';
        $form['expense_form[amountHt]'] = '50.00';
        $form['expense_form[category]'] = 'material';
        $form['expense_form[occurredAt]'] = date('Y-m-d');
        $client->submit($form);

        self::assertResponseRedirects('/projets/'.$project->getReference());

        $repo = self::getContainer()->get(ExpenseRepository::class);
        $created = $repo->findOneBy(['description' => 'Test expense e2e']);
        self::assertNotNull($created);
        self::assertSame(5000, $created->getAmountHt());
    }
}

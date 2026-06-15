<?php

namespace App\Tests\Controller\Catalogue;

use App\Repository\MaterialRepository;
use App\Tests\WebTestCase;

final class CreateMaterialControllerTest extends WebTestCase
{
    public function testCreatesMaterial(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/catalogues');
        $form = $crawler->filter('form[action*="/materiaux"]')->form();
        $form['material_form[name]'] = 'Test Or 18k jaune e2e';
        $form['material_form[type]'] = 'gold_18k';
        $form['material_form[pricePerGram]'] = '65.00';
        $client->submit($form);
        self::assertResponseRedirects('/catalogues');

        $repo = self::getContainer()->get(MaterialRepository::class);
        $created = $repo->findOneBy(['name' => 'Test Or 18k jaune e2e']);
        self::assertNotNull($created);
        self::assertSame(6500, $created->getPricePerGram());
    }
}

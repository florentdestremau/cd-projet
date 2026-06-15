<?php

namespace App\Tests\Controller\Catalogue;

use App\Repository\SupplierRepository;
use App\Tests\WebTestCase;

final class CreateSupplierControllerTest extends WebTestCase
{
    public function testCreatesSupplier(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/catalogues');
        $form = $crawler->filter('form[action*="/fournisseurs"]')->form();
        $form['supplier_form[name]'] = 'Test Supplier Co.';
        $form['supplier_form[specialty]'] = 'metals';
        $client->submit($form);
        self::assertResponseRedirects('/catalogues');

        $repo = self::getContainer()->get(SupplierRepository::class);
        $created = $repo->findOneBy(['name' => 'Test Supplier Co.']);
        self::assertNotNull($created);
    }
}

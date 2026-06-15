<?php

namespace App\Tests\Controller\Catalogue;

use App\Repository\StoneRepository;
use App\Tests\WebTestCase;

final class CreateStoneControllerTest extends WebTestCase
{
    public function testCreatesStone(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/catalogues');
        $form = $crawler->filter('form[action*="/pierres"]')->form();
        $form['stone_form[type]'] = 'diamond';
        $form['stone_form[caratWeight]'] = '1000';
        $form['stone_form[quality]'] = 'VVS1';
        $form['stone_form[color]'] = 'D';
        $form['stone_form[costPrice]'] = '4200.00';
        $client->submit($form);
        self::assertResponseRedirects('/catalogues');

        $repo = self::getContainer()->get(StoneRepository::class);
        $created = $repo->findOneBy(['quality' => 'VVS1', 'color' => 'D', 'caratWeight' => 1000, 'costPrice' => 420000]);
        self::assertNotNull($created);
    }
}

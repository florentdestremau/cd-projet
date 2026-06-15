<?php

namespace App\Tests\Controller\Catalogue;

use App\Tests\WebTestCase;

final class IndexControllerTest extends WebTestCase
{
    public function testRenders(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/catalogues');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Catalogues');
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Matières', $content);
        self::assertStringContainsString('Pierres', $content);
        self::assertStringContainsString('Fournisseurs', $content);
    }
}

<?php

namespace App\Tests\Controller\Calendar;

use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testRenders(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/projets/vue/calendrier');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.calendar');
    }

    public function testCursorM(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/projets/vue/calendrier?m=2026-07');
        self::assertResponseIsSuccessful();
    }
}

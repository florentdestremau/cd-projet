<?php

namespace App\Tests\Controller\Notification;

use App\Tests\WebTestCase;

final class PrefsControllerTest extends WebTestCase
{
    public function testRendersPrefs(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/profil/notifications');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Notifications');
    }
}

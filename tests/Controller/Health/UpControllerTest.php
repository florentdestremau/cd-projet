<?php

namespace App\Tests\Controller\Health;

use App\Tests\WebTestCase;

final class UpControllerTest extends WebTestCase
{
    public function testUpReturnsOk(): void
    {
        $client = self::createClient();
        $client->request('GET', '/up');
        self::assertResponseIsSuccessful();
        self::assertSame('OK', $client->getResponse()->getContent());
    }
}

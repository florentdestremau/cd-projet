<?php

namespace App\Tests\Controller\Notification;

use App\Repository\UserPushSubscriptionRepository;
use App\Tests\WebTestCase;

final class SubscribeControllerTest extends WebTestCase
{
    public function testSubscribesPush(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $payload = [
            'endpoint' => 'https://example.test/push/endpoint-e2e',
            'keys' => ['p256dh' => 'pubkey', 'auth' => 'authtoken'],
        ];
        $client->request('POST', '/api/push/subscribe',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload),
        );
        self::assertResponseIsSuccessful();

        $repo = self::getContainer()->get(UserPushSubscriptionRepository::class);
        self::assertNotNull($repo->findByEndpoint('https://example.test/push/endpoint-e2e'));
    }

    public function testRejectsMissingFields(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('POST', '/api/push/subscribe',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([]),
        );
        self::assertResponseStatusCodeSame(400);
    }
}

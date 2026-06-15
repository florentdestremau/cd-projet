<?php

namespace App\Tests\Controller\Notification;

use App\Entity\UserPushSubscription;
use App\Repository\UserPushSubscriptionRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class UnsubscribeControllerTest extends WebTestCase
{
    public function testUnsubscribesPush(): void
    {
        $client = self::createClient();
        $marie = $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $sub = new UserPushSubscription();
        $sub->setUser($marie);
        $sub->setEndpoint('https://example.test/push/to-remove');
        $sub->setP256dhKey('k');
        $sub->setAuthToken('a');
        $em->persist($sub);
        $em->flush();

        $client->request('POST', '/api/push/unsubscribe',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['endpoint' => 'https://example.test/push/to-remove']),
        );
        self::assertResponseIsSuccessful();
        self::assertNull(self::getContainer()->get(UserPushSubscriptionRepository::class)->findByEndpoint('https://example.test/push/to-remove'));
    }
}

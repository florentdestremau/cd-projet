<?php

namespace App\Tests\Controller\Quote;

use App\Repository\QuoteRepository;
use App\Tests\WebTestCase;

final class PdfControllerTest extends WebTestCase
{
    public function testRendersPdf(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy([]);
        $client->request('GET', '/devis/'.$quote->getReference().'/pdf');
        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));
        self::assertGreaterThan(1000, \strlen((string) $client->getResponse()->getContent()));
    }
}

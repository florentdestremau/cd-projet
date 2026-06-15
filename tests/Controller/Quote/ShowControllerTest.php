<?php

namespace App\Tests\Controller\Quote;

use App\Entity\Quote;
use App\Repository\QuoteRepository;
use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testShowsQuote(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy([]);
        self::assertInstanceOf(Quote::class, $quote);
        $client->request('GET', '/devis/'.$quote->getReference());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $quote->getReference());
    }
}

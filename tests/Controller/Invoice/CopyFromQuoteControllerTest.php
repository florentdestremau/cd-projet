<?php

namespace App\Tests\Controller\Invoice;

use App\Enum\QuoteStatus;
use App\Repository\QuoteRepository;
use App\Tests\WebTestCase;

final class CopyFromQuoteControllerTest extends WebTestCase
{
    public function testCopiesAcceptedQuoteToInvoice(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy(['status' => QuoteStatus::ACCEPTED]);
        self::assertNotNull($quote);

        $crawler = $client->request('GET', '/projets/'.$quote->getProject()->getReference().'/facture/nouvelle');
        $form = $crawler->filter('form[action="/devis/'.$quote->getReference().'/facturer"]')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        self::assertMatchesRegularExpression('#/factures/FAC-\d+-\d+#', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testRejectsCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy(['status' => QuoteStatus::ACCEPTED]);
        $client->request('POST', '/devis/'.$quote->getReference().'/facturer', ['_token' => 'invalid']);
        self::assertResponseStatusCodeSame(403);
    }
}

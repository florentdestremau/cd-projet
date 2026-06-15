<?php

namespace App\Tests\Controller\Invoice;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testShowsInvoice(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $invoice = self::getContainer()->get(InvoiceRepository::class)->findOneBy([]);
        self::assertInstanceOf(Invoice::class, $invoice);
        $client->request('GET', '/factures/'.$invoice->getReference());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $invoice->getReference());
    }
}

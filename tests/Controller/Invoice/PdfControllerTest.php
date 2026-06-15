<?php

namespace App\Tests\Controller\Invoice;

use App\Repository\InvoiceRepository;
use App\Tests\WebTestCase;

final class PdfControllerTest extends WebTestCase
{
    public function testRendersPdf(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $invoice = self::getContainer()->get(InvoiceRepository::class)->findOneBy([]);
        $client->request('GET', '/factures/'.$invoice->getReference().'/pdf');
        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));
    }
}

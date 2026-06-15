<?php

namespace App\Tests\Controller\Finance;

use App\Tests\WebTestCase;

final class ExportControllerTest extends WebTestCase
{
    public function testExportInvoicesCsv(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/finances/export.csv?type=invoices');
        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/csv', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('.csv', (string) $client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testExportPaymentsCsv(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/finances/export.csv?type=payments');
        self::assertResponseIsSuccessful();
    }

    public function testExportExpensesCsv(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/finances/export.csv?type=expenses');
        self::assertResponseIsSuccessful();
    }
}

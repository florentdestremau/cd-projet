<?php

namespace App\Tests\Controller;

use App\Repository\InvoiceRepository;
use App\Repository\QuoteRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FinanceTest extends WebTestCase
{
    public function testFinanceDashboardRenders(): void
    {
        $client = self::createClient();
        $admin = self::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        $client->loginUser($admin);

        $client->request('GET', '/finances');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Finances');
    }

    public function testCsvExportInvoices(): void
    {
        $client = self::createClient();
        $admin = self::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        $client->loginUser($admin);

        $client->catchExceptions(false);
        $client->request('GET', '/finances/export.csv?type=invoices');
        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/csv', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('.csv', (string) $client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testQuotePdfRenders(): void
    {
        $client = self::createClient();
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy([]);
        self::assertNotNull($quote);
        $admin = self::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        $client->loginUser($admin);

        $client->request('GET', '/devis/'.$quote->getReference().'/pdf');
        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));
        self::assertGreaterThan(1000, \strlen((string) $client->getResponse()->getContent()));
    }

    public function testInvoicePdfRenders(): void
    {
        $client = self::createClient();
        $invoice = self::getContainer()->get(InvoiceRepository::class)->findOneBy([]);
        self::assertNotNull($invoice);
        $admin = self::getContainer()->get(UserRepository::class)->findByEmail('admin@maison.test');
        $client->loginUser($admin);

        $client->request('GET', '/factures/'.$invoice->getReference().'/pdf');
        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));
    }
}

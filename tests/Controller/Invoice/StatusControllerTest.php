<?php

namespace App\Tests\Controller\Invoice;

use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class StatusControllerTest extends WebTestCase
{
    public function testMarksInvoiceSent(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $repo = self::getContainer()->get(InvoiceRepository::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $invoice = $repo->findOneBy(['status' => InvoiceStatus::DRAFT]);
        if (!$invoice) {
            $invoice = $repo->findOneBy([]);
            $invoice->setStatus(InvoiceStatus::DRAFT);
            $invoice->setSentAt(null);
            $em->flush();
        }

        $crawler = $client->request('GET', '/factures/'.$invoice->getReference());
        $form = $crawler->selectButton('Marquer envoyée')->form();
        $client->submit($form);
        self::assertResponseRedirects('/factures/'.$invoice->getReference());

        $em->clear();
        $refreshed = $repo->find($invoice->getId());
        self::assertNotNull($refreshed->getSentAt());
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $invoice = self::getContainer()->get(InvoiceRepository::class)->findOneBy([]);
        $client->request('POST', '/factures/'.$invoice->getReference().'/statut', ['_token' => 'invalid', 'status' => 'sent']);
        self::assertResponseStatusCodeSame(403);
    }
}

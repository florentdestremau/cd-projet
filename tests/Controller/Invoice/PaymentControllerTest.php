<?php

namespace App\Tests\Controller\Invoice;

use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use App\Tests\WebTestCase;

final class PaymentControllerTest extends WebTestCase
{
    public function testRegistersPayment(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $invoice = self::getContainer()->get(InvoiceRepository::class)->createQueryBuilder('i')
            ->where('i.status != :paid')->setParameter('paid', InvoiceStatus::PAID)
            ->andWhere('SIZE(i.payments) = 0')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        self::assertNotNull($invoice);

        $crawler = $client->request('GET', '/factures/'.$invoice->getReference());
        $form = $crawler->filter('form[action*="/paiement"]')->form();
        $form['payment_form[amount]'] = '100.00';
        $form['payment_form[method]'] = 'transfer';
        $form['payment_form[reference]'] = 'TEST-E2E';
        $client->submit($form);
        self::assertResponseRedirects('/factures/'.$invoice->getReference());

        self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class)->clear();
        $invoice = self::getContainer()->get(InvoiceRepository::class)->find($invoice->getId());
        $found = false;
        foreach ($invoice->getPayments() as $p) {
            if ('TEST-E2E' === $p->getReference()) {
                self::assertSame(10000, $p->getAmount());
                $found = true;
            }
        }
        self::assertTrue($found, 'Payment with reference TEST-E2E not found');
    }
}

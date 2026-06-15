<?php

namespace App\Tests\Controller\Quote;

use App\Enum\QuoteStatus;
use App\Repository\QuoteRepository;
use App\Tests\WebTestCase;

final class StatusControllerTest extends WebTestCase
{
    public function testMarksQuoteSent(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        // Trouve un devis qui peut passer en "sent" (DRAFT)
        $repo = self::getContainer()->get(QuoteRepository::class);
        $em = self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $quote = $repo->findOneBy(['status' => QuoteStatus::DRAFT]);
        if (!$quote) {
            // Repasse un devis en draft pour tester
            $quote = $repo->findOneBy([]);
            $quote->setStatus(QuoteStatus::DRAFT);
            $em->flush();
        }

        $crawler = $client->request('GET', '/devis/'.$quote->getReference());
        $form = $crawler->selectButton('Marquer envoyé')->form();
        $client->submit($form);
        self::assertResponseRedirects('/devis/'.$quote->getReference());

        $em->clear();
        $refreshed = $repo->find($quote->getId());
        self::assertSame(QuoteStatus::SENT, $refreshed->getStatus());
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $quote = self::getContainer()->get(QuoteRepository::class)->findOneBy([]);
        $client->request('POST', '/devis/'.$quote->getReference().'/statut', ['_token' => 'invalid', 'status' => 'sent']);
        self::assertResponseStatusCodeSame(403);
    }
}

<?php

namespace App\Tests\Controller\Portal;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class ShowControllerTest extends WebTestCase
{
    public function testRendersWithoutAuth(): void
    {
        $client = self::createClient();
        $project = self::getContainer()->get(ProjectRepository::class)
            ->createQueryBuilder('p')
            ->where('p.clientAccessToken IS NOT NULL')
            ->andWhere('p.status = :s')->setParameter('s', ProjectStatus::ACTIVE)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        self::assertInstanceOf(Project::class, $project);

        $client->request('GET', '/portail/'.$project->getClientAccessToken());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $project->getTitle());
    }

    public function test404OnUnknownToken(): void
    {
        $client = self::createClient();
        $client->request('GET', '/portail/'.str_repeat('a', 64));
        self::assertResponseStatusCodeSame(404);
    }
}

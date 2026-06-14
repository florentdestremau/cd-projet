<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PortalTest extends WebTestCase
{
    public function testPortalRendersWithoutAuth(): void
    {
        $client = static::createClient();
        $project = static::getContainer()->get(ProjectRepository::class)
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

    public function testPortalRefuses404OnUnknownToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portail/'.str_repeat('a', 64));
        self::assertResponseStatusCodeSame(404);
    }

    public function testAdminSettingsRequiresAdminRole(): void
    {
        $client = static::createClient();
        $designer = static::getContainer()->get(\App\Repository\UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($designer);
        $client->request('GET', '/admin/parametres');
        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminSettingsAccessibleForAdmin(): void
    {
        $client = static::createClient();
        $admin = static::getContainer()->get(\App\Repository\UserRepository::class)->findByEmail('admin@maison.test');
        $client->loginUser($admin);
        $client->request('GET', '/admin/parametres');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', "Paramètres");
    }
}

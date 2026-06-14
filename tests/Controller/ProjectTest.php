<?php

namespace App\Tests\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectTest extends WebTestCase
{
    public function testProjectsListIsProtected(): void
    {
        $client = self::createClient();
        $client->request('GET', '/projets');
        self::assertResponseRedirects('/login');
    }

    public function testProjectsListRendersActiveProjects(): void
    {
        $client = self::createClient();
        $marie = self::getContainer()->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $crawler = $client->request('GET', '/projets');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Projets');
        // Au moins une ligne dans le tableau
        self::assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
        // Format référence présent
        self::assertMatchesRegularExpression('/BAG-\d{4}-\d+/', $client->getResponse()->getContent());
    }

    public function testProjectsListFiltersByStage(): void
    {
        $client = self::createClient();
        $marie = self::getContainer()->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $crawler = $client->request('GET', '/projets?stage=brief');

        self::assertResponseIsSuccessful();
        $rows = $crawler->filter('table tbody tr');
        foreach ($rows as $row) {
            self::assertStringContainsString('Brief', $row->textContent);
        }
    }

    public function testProjectShowPageRenders(): void
    {
        $client = self::createClient();
        self::getContainer()->get(EntityManagerInterface::class);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0] ?? null;
        self::assertInstanceOf(Project::class, $project);

        $marie = self::getContainer()->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $client->request('GET', '/projets/'.$project->getReference());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $project->getTitle());
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Fil de discussion', $content);
        self::assertStringContainsString('Équipe', $content);
        self::assertStringContainsString('Avancement', $content);
    }

    public function testProjectShowReturns404WhenUnknown(): void
    {
        $client = self::createClient();
        $marie = self::getContainer()->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $client->request('GET', '/projets/BAG-9999-999');
        self::assertResponseStatusCodeSame(404);
    }
}

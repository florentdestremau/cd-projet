<?php

namespace App\Tests\Controller\Portal;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class ValidateControllerTest extends WebTestCase
{
    public function testValidatesClientValidationStage(): void
    {
        $client = self::createClient();
        // Find a project with a token + put it in CLIENT_VALIDATION
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(ProjectRepository::class);
        $project = $repo->createQueryBuilder('p')
            ->where('p.clientAccessToken IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        self::assertInstanceOf(Project::class, $project);

        $project->setCurrentStage(ProjectStage::CLIENT_VALIDATION);
        $em->flush();

        $token = $project->getClientAccessToken();
        $client->request('POST', '/portail/'.$token.'/valider');
        self::assertResponseRedirects('/portail/'.$token);

        $em->clear();
        $refreshed = $repo->find($project->getId());
        self::assertSame(ProjectStage::CAD_3D, $refreshed->getCurrentStage());
    }
}

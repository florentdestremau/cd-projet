<?php

namespace App\Tests\Controller\Kanban;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class ChangeStageControllerTest extends WebTestCase
{
    public function testChangesStage(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);

        // Charge la page pour démarrer la session et lire un token CSRF
        $crawler = $client->request('GET', '/projets/vue/kanban');
        self::assertResponseIsSuccessful();
        $card = $crawler->filter('.kanban__card')->first();
        self::assertCount(1, $card);
        $token = $card->attr('data-csrf');
        $reference = $card->attr('data-reference');

        $project = self::getContainer()->get(ProjectRepository::class)->findOneBy(['reference' => $reference]);
        self::assertInstanceOf(Project::class, $project);
        $targetStage = ProjectStage::DELIVERY === $project->getCurrentStage() ? ProjectStage::BRIEF : $project->getCurrentStage()->next() ?? ProjectStage::SKETCH;

        $client->request(
            'POST',
            '/api/projets/'.$reference.'/etape',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => $token],
            content: json_encode(['stage' => $targetStage->value]),
        );
        self::assertResponseIsSuccessful();
        $body = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame($targetStage->value, $body['to'] ?? null);
    }

    public function testRejectsInvalidCsrf(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $client->request(
            'POST',
            '/api/projets/'.$project->getReference().'/etape',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => 'invalid'],
            content: json_encode(['stage' => 'sketch']),
        );
        self::assertResponseStatusCodeSame(403);
    }
}

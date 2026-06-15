<?php

namespace App\Tests\Controller\Comment;

use App\Entity\Project;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class CreateControllerTest extends WebTestCase
{
    public function testCreatesCommentWithMention(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        self::assertInstanceOf(Project::class, $project);

        // Récupère le form depuis la page projet (token CSRF inclus)
        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->selectButton('Envoyer')->form();
        $form['comment_form[body]'] = 'Test e2e @paul peux-tu confirmer la fonte ?';
        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Test e2e @paul peux-tu confirmer la fonte ?', (string) $client->getResponse()->getContent());

        self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class)->clear();
        $repo = self::getContainer()->get(CommentRepository::class);
        $project = self::getContainer()->get(ProjectRepository::class)->find($project->getId());
        $comments = $repo->findForProject($project);
        $last = end($comments);
        self::assertCount(1, $last->getMentions());
    }

    public function testEmptyBodyRejected(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->selectButton('Envoyer')->form();
        $form['comment_form[body]'] = '';
        $client->submit($form);
        // Redirige avec flash error
        self::assertResponseRedirects();
    }
}

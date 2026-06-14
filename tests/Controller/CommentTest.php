<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CommentTest extends WebTestCase
{
    public function testPostingCommentAttachesMention(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $project = $container->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $paul = $container->get(UserRepository::class)->findByEmail('joaillier1@maison.test');

        $client->loginUser($marie);

        // Récupère le token CSRF depuis la fiche projet
        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        self::assertNotEmpty($token);

        $client->request(
            'POST',
            '/projets/'.$project->getReference().'/commentaires',
            [
                'body' => 'Test webtest @paul peux-tu confirmer la fonte ?',
                '_token' => $token,
            ],
        );

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Test webtest @paul peux-tu confirmer la fonte ?', (string) $client->getResponse()->getContent());

        // Le commentaire est en base + mention attachée
        $em = $container->get(EntityManagerInterface::class);
        $em->clear();
        $comments = $container->get(CommentRepository::class)->findForProject(
            $container->get(ProjectRepository::class)->find($project->getId()),
        );
        $last = end($comments);
        self::assertInstanceOf(Comment::class, $last);
        self::assertSame('Test webtest @paul peux-tu confirmer la fonte ?', $last->getBody());
        self::assertCount(1, $last->getMentions());
        self::assertSame($paul->getId(), $last->getMentions()->first()->getId());
    }

    public function testPostingEmptyCommentIsRejected(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $project = $container->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request(
            'POST',
            '/projets/'.$project->getReference().'/commentaires',
            ['body' => '   ', '_token' => $token],
        );

        // Redirige sans persister
        self::assertResponseRedirects();
    }

    public function testPostingCommentWithoutCsrfTokenIsRejected(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $project = $container->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');
        $client->loginUser($marie);

        $client->request(
            'POST',
            '/projets/'.$project->getReference().'/commentaires',
            ['body' => 'hello', '_token' => 'invalid'],
        );

        self::assertResponseStatusCodeSame(403);
    }
}

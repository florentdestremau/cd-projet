<?php

namespace App\Tests\Service;

use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Service\MentionParser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MentionParserTest extends KernelTestCase
{
    public function testParsesSingleFirstNameMention(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $parser = $container->get(MentionParser::class);
        $paul = $container->get(UserRepository::class)->findByEmail('joaillier1@maison.test');
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');

        $comment = new Comment();
        $comment->setAuthor($marie);
        $comment->setBody('Salut @paul, tu peux jeter un œil ?');

        $parser->attachMentions($comment);

        self::assertCount(1, $comment->getMentions());
        self::assertSame($paul->getId(), $comment->getMentions()->first()->getId());
    }

    public function testIgnoresSelfMention(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $parser = $container->get(MentionParser::class);
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');

        $comment = new Comment();
        $comment->setAuthor($marie);
        $comment->setBody('@marie note pour moi-même');

        $parser->attachMentions($comment);

        self::assertCount(0, $comment->getMentions());
    }

    public function testIgnoresUnknownHandle(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $parser = $container->get(MentionParser::class);
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');

        $comment = new Comment();
        $comment->setAuthor($marie);
        $comment->setBody('Hello @inconnu');

        $parser->attachMentions($comment);

        self::assertCount(0, $comment->getMentions());
    }

    public function testParsesMultipleMentionsAndDeduplicates(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $parser = $container->get(MentionParser::class);
        $marie = $container->get(UserRepository::class)->findByEmail('designer1@maison.test');

        $comment = new Comment();
        $comment->setAuthor($marie);
        $comment->setBody('@paul peux-tu voir avec @sophie ? Merci @paul');

        $parser->attachMentions($comment);

        self::assertCount(2, $comment->getMentions());
    }
}

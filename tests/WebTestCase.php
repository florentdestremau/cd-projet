<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function loginAs(KernelBrowser $client, string $email): User
    {
        $repo = static::getContainer()->get(UserRepository::class);
        \assert($repo instanceof UserRepository);
        $user = $repo->findByEmail($email);
        self::assertNotNull($user, \sprintf('User %s not found', $email));
        $client->loginUser($user);

        return $user;
    }

    protected function loginAsDesigner(KernelBrowser $client): User
    {
        return $this->loginAs($client, 'designer1@maison.test');
    }

    protected function loginAsAdmin(KernelBrowser $client): User
    {
        return $this->loginAs($client, 'admin@maison.test');
    }
}

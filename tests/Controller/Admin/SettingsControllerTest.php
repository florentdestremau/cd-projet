<?php

namespace App\Tests\Controller\Admin;

use App\Tests\WebTestCase;

final class SettingsControllerTest extends WebTestCase
{
    public function testRequiresAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/admin/parametres');
        self::assertResponseStatusCodeSame(403);
    }

    public function testRenderAccessibleForAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin/parametres');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Paramètres');
    }

    public function testUpdatesSettings(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/admin/parametres');
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['settings_form[companyTagline]'] = 'Test tagline e2e';
        $client->submit($form);
        self::assertResponseRedirects('/admin/parametres');

        $repo = self::getContainer()->get(\App\Repository\SettingRepository::class);
        self::assertSame('Test tagline e2e', $repo->get('company_tagline'));
    }
}

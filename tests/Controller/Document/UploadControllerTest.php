<?php

namespace App\Tests\Controller\Document;

use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;

final class UploadControllerTest extends WebTestCase
{
    public function testUploadsDocument(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $tmpFile = tempnam(sys_get_temp_dir(), 'doc-up-').'.txt';
        file_put_contents($tmpFile, 'Hello upload test');

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action$="/documents"]')->form();
        $form['document_upload_form[file]']->upload($tmpFile);
        $client->submit($form);
        self::assertResponseRedirects('/projets/'.$project->getReference());

        $repo = self::getContainer()->get(DocumentRepository::class);
        self::assertGreaterThan(0, $repo->count(['project' => $project]));
    }
}

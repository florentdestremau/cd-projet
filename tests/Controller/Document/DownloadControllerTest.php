<?php

namespace App\Tests\Controller\Document;

use App\Entity\Document;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class DownloadControllerTest extends WebTestCase
{
    public function testDownloadsDocument(): void
    {
        $client = self::createClient();
        $marie = $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];
        \assert($project instanceof Project);

        // Crée un fichier physiquement
        $base = self::getContainer()->getParameter('kernel.project_dir').'/var/uploads/'.$project->getId();
        @mkdir($base, 0o775, true);
        $fileName = 'download-test.txt';
        file_put_contents($base.'/'.$fileName, 'content');

        $doc = new Document();
        $doc->setProject($project);
        $doc->setFilename('download-test.txt');
        $doc->setStoragePath($project->getId().'/'.$fileName);
        $doc->setMimeType('text/plain');
        $doc->setSize(7);
        $doc->setUploadedBy($marie);
        $em->persist($doc);
        $em->flush();

        $client->request('GET', '/documents/'.$doc->getId());
        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/plain', (string) $client->getResponse()->headers->get('Content-Type'));
    }

    public function test404OnMissingFile(): void
    {
        $client = self::createClient();
        $this->loginAsDesigner($client);
        $client->request('GET', '/documents/999999');
        self::assertResponseStatusCodeSame(404);
    }
}

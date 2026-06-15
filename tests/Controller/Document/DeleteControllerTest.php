<?php

namespace App\Tests\Controller\Document;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use App\Tests\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteControllerTest extends WebTestCase
{
    public function testDeletesDocument(): void
    {
        $client = self::createClient();
        $marie = $this->loginAsDesigner($client);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $project = self::getContainer()->get(ProjectRepository::class)->findActiveOrdered(1)[0];

        $base = self::getContainer()->getParameter('kernel.project_dir').'/var/uploads/'.$project->getId();
        @mkdir($base, 0o775, true);
        $fileName = 'delete-test-'.bin2hex(random_bytes(4)).'.txt';
        file_put_contents($base.'/'.$fileName, 'x');

        $doc = new Document();
        $doc->setProject($project);
        $doc->setFilename($fileName);
        $doc->setStoragePath($project->getId().'/'.$fileName);
        $doc->setMimeType('text/plain');
        $doc->setSize(1);
        $doc->setUploadedBy($marie);
        $em->persist($doc);
        $em->flush();
        $id = $doc->getId();

        $crawler = $client->request('GET', '/projets/'.$project->getReference());
        $form = $crawler->filter('form[action="/documents/'.$id.'/supprimer"]')->form();
        $client->submit($form);
        self::assertResponseRedirects('/projets/'.$project->getReference());

        $em->clear();
        self::assertNull(self::getContainer()->get(DocumentRepository::class)->find($id));
    }
}

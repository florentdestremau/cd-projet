<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectTokenController extends AbstractController
{
    #[Route('/projets/{reference}/portail/regenerer', name: 'app_projects_token', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
    public function regenerate(
        string $reference,
        Request $request,
        ProjectRepository $repo,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $project = $repo->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('token_'.$project->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $project->setClientAccessToken(bin2hex(random_bytes(32)));
        $em->flush();

        $this->addFlash('success', 'Lien portail client régénéré.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
    }
}

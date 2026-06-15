<?php

namespace App\Controller\Project;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/portail/regenerer', name: 'app_projects_token', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class RegenerateTokenController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('token_'.$project->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $project->setClientAccessToken(bin2hex(random_bytes(32)));
        $em->flush();

        $this->addFlash('success', 'Lien portail client régénéré.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
    }
}

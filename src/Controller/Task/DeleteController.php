<?php

namespace App\Controller\Task;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/taches/{id}/supprimer', name: 'app_tasks_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class DeleteController extends AbstractController
{
    public function __invoke(
        #[MapEntity] Task $task,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('task_delete_'.$task->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $projectRef = $task->getProject()?->getReference();
        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('app_projects_show', ['reference' => $projectRef]);
    }
}

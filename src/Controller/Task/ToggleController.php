<?php

namespace App\Controller\Task;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/taches/{id}/toggle', name: 'app_tasks_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class ToggleController extends AbstractController
{
    public function __invoke(
        #[MapEntity] Task $task,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('task_toggle_'.$task->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($task->isCompleted()) {
            $task->setCompletedAt(null);
            $task->setCompletedBy(null);
        } else {
            $task->setCompletedAt(new \DateTimeImmutable());
            $task->setCompletedBy($user);
        }
        $em->flush();

        $referer = (string) $request->headers->get('Referer', '');
        if ('' !== $referer && parse_url($referer, \PHP_URL_HOST) === $request->getHost()) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_projects_show', ['reference' => $task->getProject()->getReference()]);
    }
}

<?php

namespace App\Controller\Task;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/taches', name: 'app_tasks_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $task = new Task();
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Tâche invalide.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
        }

        $task->setProject($project);
        $em->persist($task);
        $em->flush();

        $this->addFlash('success', 'Tâche ajoutée.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
    }
}

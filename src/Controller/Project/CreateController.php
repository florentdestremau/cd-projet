<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Form\ProjectForm;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/nouveau', name: 'app_projects_new', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(Request $request, ProjectRepository $repository, EntityManagerInterface $em): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectForm::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setReference($repository->generateNextReference((int) date('Y')));

            // Crée les ProjectStageStatus pour toutes les étapes
            foreach (ProjectStage::ordered() as $stage) {
                $status = new \App\Entity\ProjectStageStatus($stage);
                $project->addStageStatus($status);
            }

            $em->persist($project);
            $em->flush();
            $this->addFlash('success', 'Projet '.$project->getReference().' créé.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
        }

        return $this->render('project/new.html.twig', ['form' => $form]);
    }
}

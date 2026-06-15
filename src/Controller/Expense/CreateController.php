<?php

namespace App\Controller\Expense;

use App\Entity\Expense;
use App\Entity\Project;
use App\Form\ExpenseForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}/depenses', name: 'app_expenses_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class CreateController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $expense = new Expense();
        $form = $this->createForm(ExpenseForm::class, $expense);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Dépense invalide.');

            return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
        }

        $expense->setProject($project);
        $em->persist($expense);
        $em->flush();

        $this->addFlash('success', 'Dépense enregistrée.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $project->getReference()]);
    }
}

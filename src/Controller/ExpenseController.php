<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Project;
use App\Enum\ExpenseCategory;
use App\Repository\ExpenseRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ExpenseController extends AbstractController
{
    #[Route('/projets/{reference}/depenses', name: 'app_expenses_create', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['POST'])]
    public function create(string $reference, Request $request, ProjectRepository $repo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $project = $repo->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('expense_'.$project->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $exp = new Expense();
        $exp->setProject($project);
        $exp->setDescription($request->request->getString('description'));
        $exp->setSupplierName($request->request->getString('supplier', null) ?: null);
        $exp->setCategory(ExpenseCategory::from($request->request->getString('category', 'other')));
        $exp->setAmountHt((int) round(((float) $request->request->getString('amountHt')) * 100));
        $exp->setVatAmount((int) round(((float) $request->request->getString('vatAmount', '0')) * 100));
        $occurredAt = $request->request->getString('occurredAt');
        if ('' !== $occurredAt) {
            $exp->setOccurredAt(new \DateTimeImmutable($occurredAt));
        }
        $em->persist($exp);
        $em->flush();

        $this->addFlash('success', 'Dépense enregistrée.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
    }

    #[Route('/projets/{reference}/depenses/{id}/supprimer', name: 'app_expenses_delete', requirements: ['reference' => 'BAG-\d+-\d+', 'id' => '\d+'], methods: ['POST'])]
    public function delete(string $reference, int $id, Request $request, ExpenseRepository $repo, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $expense = $repo->find($id);
        if ($expense instanceof Expense) {
            if (!$this->isCsrfTokenValid('expense_delete_'.$expense->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }
            $em->remove($expense);
            $em->flush();
            $this->addFlash('success', 'Dépense supprimée.');
        }

        return $this->redirectToRoute('app_projects_show', ['reference' => $reference]);
    }
}

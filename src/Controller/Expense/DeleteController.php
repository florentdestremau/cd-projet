<?php

namespace App\Controller\Expense;

use App\Entity\Expense;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/depenses/{id}/supprimer', name: 'app_expenses_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class DeleteController extends AbstractController
{
    public function __invoke(
        #[MapEntity] Expense $expense,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('expense_delete_'.$expense->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $projectRef = $expense->getProject()?->getReference();
        $em->remove($expense);
        $em->flush();
        $this->addFlash('success', 'Dépense supprimée.');

        return $this->redirectToRoute('app_projects_show', ['reference' => $projectRef]);
    }
}

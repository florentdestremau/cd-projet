<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Enum\DocumentCategory;
use App\Enum\ExpenseCategory;
use App\Enum\ProjectStage;
use App\Form\CommentForm;
use App\Form\DocumentUploadForm;
use App\Form\ExpenseForm;
use App\Repository\CommentRepository;
use App\Repository\DocumentRepository;
use App\Repository\ExpenseRepository;
use App\Repository\InvoiceRepository;
use App\Repository\QuoteRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/{reference}', name: 'app_projects_show', requirements: ['reference' => 'BAG-\d+-\d+'], methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Project $project,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        QuoteRepository $quoteRepository,
        InvoiceRepository $invoiceRepository,
        ExpenseRepository $expenseRepository,
        DocumentRepository $documentRepository,
    ): Response {
        $usersHandles = array_map(
            static fn (\App\Entity\User $u): array => ['handle' => strtolower($u->getFirstName()), 'label' => $u->getFullName()],
            $userRepository->findAll(),
        );

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'comments' => $commentRepository->findForProject($project),
            'comment_form' => $this->createForm(CommentForm::class)->createView(),
            'expense_form' => $this->createForm(ExpenseForm::class)->createView(),
            'document_upload_form' => $this->createForm(DocumentUploadForm::class)->createView(),
            'stages' => ProjectStage::ordered(),
            'users_handles' => $usersHandles,
            'quotes' => $quoteRepository->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'invoices' => $invoiceRepository->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'expenses' => $expenseRepository->findBy(['project' => $project], ['occurredAt' => 'DESC']),
            'expenses_total' => $expenseRepository->totalForProject($project),
            'margin' => $project->getSellingPrice() - $expenseRepository->totalForProject($project),
            'expense_categories' => ExpenseCategory::cases(),
            'documents' => $documentRepository->findBy(['project' => $project], ['uploadedAt' => 'DESC']),
            'document_categories' => DocumentCategory::cases(),
        ]);
    }
}

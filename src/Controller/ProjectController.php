<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Repository\CommentRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projets', name: 'app_projects_index')]
    public function index(Request $request, ProjectRepository $repository): Response
    {
        $statusFilter = $request->query->getString('status', '');
        $stageFilter = $request->query->getString('stage', '');
        $search = trim($request->query->getString('q', ''));

        $qb = $repository->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')->addSelect('c')
            ->orderBy('p.updatedAt', 'DESC');

        if ($statusFilter !== '' && ($status = ProjectStatus::tryFrom($statusFilter)) !== null) {
            $qb->andWhere('p.status = :status')->setParameter('status', $status);
        } else {
            $qb->andWhere('p.status = :defaultStatus')->setParameter('defaultStatus', ProjectStatus::ACTIVE);
        }

        if ($stageFilter !== '' && ($stage = ProjectStage::tryFrom($stageFilter)) !== null) {
            $qb->andWhere('p.currentStage = :stage')->setParameter('stage', $stage);
        }

        if ($search !== '') {
            $qb->andWhere('p.title LIKE :q OR p.reference LIKE :q OR c.displayName LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        $projects = $qb->setMaxResults(100)->getQuery()->getResult();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
            'status_filter' => $statusFilter,
            'stage_filter' => $stageFilter,
            'search' => $search,
            'statuses' => ProjectStatus::cases(),
            'stages' => ProjectStage::ordered(),
        ]);
    }

    #[Route('/projets/{reference}', name: 'app_projects_show', requirements: ['reference' => 'BAG-\d+-\d+'])]
    public function show(
        string $reference,
        ProjectRepository $repository,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        \App\Repository\QuoteRepository $quoteRepository,
        \App\Repository\InvoiceRepository $invoiceRepository,
        \App\Repository\ExpenseRepository $expenseRepository,
        \App\Repository\DocumentRepository $documentRepository,
    ): Response {
        $project = $repository->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        $comments = $commentRepository->findForProject($project);

        $usersHandles = array_map(
            fn ($u) => [
                'handle' => strtolower($u->getFirstName()),
                'label' => $u->getFullName(),
            ],
            $userRepository->findAll(),
        );

        $quotes = $quoteRepository->findBy(['project' => $project], ['createdAt' => 'DESC']);
        $invoices = $invoiceRepository->findBy(['project' => $project], ['createdAt' => 'DESC']);
        $expenses = $expenseRepository->findBy(['project' => $project], ['occurredAt' => 'DESC']);
        $expensesTotal = $expenseRepository->totalForProject($project);
        $margin = $project->getSellingPrice() - $expensesTotal;
        $documents = $documentRepository->findBy(['project' => $project], ['uploadedAt' => 'DESC']);

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'comments' => $comments,
            'stages' => ProjectStage::ordered(),
            'users_handles' => $usersHandles,
            'quotes' => $quotes,
            'invoices' => $invoices,
            'expenses' => $expenses,
            'expenses_total' => $expensesTotal,
            'margin' => $margin,
            'expense_categories' => \App\Enum\ExpenseCategory::cases(),
            'documents' => $documents,
            'document_categories' => \App\Enum\DocumentCategory::cases(),
        ]);
    }
}

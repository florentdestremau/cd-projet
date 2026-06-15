<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Repository\QuoteRepository;
use App\Service\SettingsBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PortalController extends AbstractController
{
    #[Route('/portail/{token}', name: 'app_portal_show', requirements: ['token' => '[a-f0-9]{64}'])]
    public function show(
        string $token,
        ProjectRepository $repository,
        QuoteRepository $quoteRepo,
        InvoiceRepository $invoiceRepo,
        SettingsBag $settings,
    ): Response {
        $project = $repository->findOneBy(['clientAccessToken' => $token]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        return $this->render('portal/show.html.twig', [
            'project' => $project,
            'stages' => \App\Enum\ProjectStage::ordered(),
            'quotes' => $quoteRepo->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'invoices' => $invoiceRepo->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'company_name' => $settings->get('company_name', 'Maison Atelier'),
            'company_tagline' => $settings->get('company_tagline', 'Bijouterie luxe'),
        ]);
    }

    #[Route('/portail/{token}/valider', name: 'app_portal_validate', requirements: ['token' => '[a-f0-9]{64}'], methods: ['POST'])]
    public function validate(
        string $token,
        ProjectRepository $repository,
        \Doctrine\ORM\EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $project = $repository->findOneBy(['clientAccessToken' => $token]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        if (\App\Enum\ProjectStage::CLIENT_VALIDATION === $project->getCurrentStage()) {
            foreach ($project->getStageStatuses() as $status) {
                if (\App\Enum\ProjectStage::CLIENT_VALIDATION === $status->getStage()) {
                    $status->setCompletedAt(new \DateTimeImmutable());
                }
                if (\App\Enum\ProjectStage::CAD_3D === $status->getStage() && null === $status->getStartedAt()) {
                    $status->setStartedAt(new \DateTimeImmutable());
                }
            }
            $project->setCurrentStage(\App\Enum\ProjectStage::CAD_3D);
            $project->touch();
            $em->flush();
            $this->addFlash('success', 'Merci pour votre validation, la modélisation 3D va commencer.');
        }

        return $this->redirectToRoute('app_portal_show', ['token' => $token]);
    }
}

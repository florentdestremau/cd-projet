<?php

namespace App\Controller\Portal;

use App\Entity\Project;
use App\Enum\ProjectStage;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Repository\QuoteRepository;
use App\Service\SettingsBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/portail/{token}', name: 'app_portal_show', requirements: ['token' => '[a-f0-9]{64}'], methods: ['GET'])]
final class ShowController extends AbstractController
{
    public function __invoke(
        string $token,
        ProjectRepository $repository,
        QuoteRepository $quoteRepository,
        InvoiceRepository $invoiceRepository,
        SettingsBag $settings,
    ): Response {
        $project = $repository->findOneBy(['clientAccessToken' => $token]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        return $this->render('portal/show.html.twig', [
            'project' => $project,
            'stages' => ProjectStage::ordered(),
            'quotes' => $quoteRepository->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'invoices' => $invoiceRepository->findBy(['project' => $project], ['createdAt' => 'DESC']),
            'company_name' => $settings->get('company_name', 'Maison Atelier'),
            'company_tagline' => $settings->get('company_tagline', 'Bijouterie luxe'),
        ]);
    }
}

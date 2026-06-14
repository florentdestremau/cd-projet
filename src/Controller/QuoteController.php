<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Quote;
use App\Entity\QuoteItem;
use App\Enum\QuoteStatus;
use App\Repository\ProjectRepository;
use App\Repository\QuoteRepository;
use App\Service\PdfRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuoteController extends AbstractController
{
    #[Route('/projets/{reference}/devis/nouveau', name: 'app_quotes_new', requirements: ['reference' => 'BAG-\d+-\d+'])]
    public function create(string $reference, Request $request, ProjectRepository $projectRepo, QuoteRepository $quoteRepo, EntityManagerInterface $em): Response
    {
        $project = $projectRepo->findOneBy(['reference' => $reference]);
        if (!$project instanceof Project) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('quote_new_'.$project->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $quote = new Quote();
            $quote->setProject($project);
            $quote->setReference($quoteRepo->generateNextReference((int) date('Y')));
            $validUntil = $request->request->getString('validUntil');
            if ($validUntil !== '') {
                $quote->setValidUntil(new \DateTimeImmutable($validUntil));
            }
            $quote->setVatRate((int) ($request->request->getString('vatRate', '20.00') * 100));

            $descriptions = $request->request->all('descriptions');
            $quantities = $request->request->all('quantities');
            $prices = $request->request->all('prices');
            foreach ($descriptions as $idx => $desc) {
                $desc = trim((string) $desc);
                if ($desc === '') continue;
                $item = new QuoteItem();
                $item->setDescription($desc);
                $item->setQuantity((int) ($quantities[$idx] ?? 1));
                $item->setUnitPriceHt((int) round(((float) ($prices[$idx] ?? 0)) * 100));
                $quote->addItem($item);
            }
            $em->persist($quote);
            $em->flush();

            $this->addFlash('success', 'Devis '.$quote->getReference().' créé.');
            return $this->redirectToRoute('app_quotes_show', ['reference' => $quote->getReference()]);
        }

        return $this->render('quote/new.html.twig', ['project' => $project]);
    }

    #[Route('/devis/{reference}', name: 'app_quotes_show', requirements: ['reference' => 'DEV-\d+-\d+'])]
    public function show(string $reference, QuoteRepository $repo): Response
    {
        $quote = $repo->findOneBy(['reference' => $reference]);
        if (!$quote instanceof Quote) {
            throw $this->createNotFoundException();
        }
        return $this->render('quote/show.html.twig', ['quote' => $quote]);
    }

    #[Route('/devis/{reference}/pdf', name: 'app_quotes_pdf', requirements: ['reference' => 'DEV-\d+-\d+'])]
    public function pdf(string $reference, QuoteRepository $repo, PdfRenderer $pdf): Response
    {
        $quote = $repo->findOneBy(['reference' => $reference]);
        if (!$quote instanceof Quote) {
            throw $this->createNotFoundException();
        }
        $output = $pdf->render('quote/pdf.html.twig', ['quote' => $quote]);
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s.pdf"', $quote->getReference()),
        ]);
    }

    #[Route('/devis/{reference}/statut', name: 'app_quotes_status', methods: ['POST'], requirements: ['reference' => 'DEV-\d+-\d+'])]
    public function status(string $reference, Request $request, QuoteRepository $repo, EntityManagerInterface $em): Response
    {
        $quote = $repo->findOneBy(['reference' => $reference]);
        if (!$quote instanceof Quote) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('quote_status_'.$quote->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $status = QuoteStatus::tryFrom($request->request->getString('status'));
        if ($status !== null) {
            $quote->setStatus($status);
            if ($status === QuoteStatus::SENT && $quote->getSentAt() === null) {
                $quote->setSentAt(new \DateTimeImmutable());
            }
            if ($status === QuoteStatus::ACCEPTED && $quote->getAcceptedAt() === null) {
                $quote->setAcceptedAt(new \DateTimeImmutable());
            }
            $em->flush();
            $this->addFlash('success', 'Devis marqué « '.$status->label().' ».');
        }
        return $this->redirectToRoute('app_quotes_show', ['reference' => $reference]);
    }
}

<?php

namespace App\Controller\Quote;

use App\Entity\Quote;
use App\Enum\QuoteStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/devis/{reference}/statut', name: 'app_quotes_status', requirements: ['reference' => 'DEV-\d+-\d+'], methods: ['POST'])]
#[IsGranted('ROLE_USER')]
final class StatusController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['reference' => 'reference'])] Quote $quote,
        Request $request,
        EntityManagerInterface $em,
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        if (!$this->isCsrfTokenValid('quote_status_'.$quote->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $status = QuoteStatus::tryFrom((string) $request->request->get('status', ''));
        if (null !== $status) {
            $quote->setStatus($status);
            if (QuoteStatus::SENT === $status && !$quote->getSentAt() instanceof \DateTimeImmutable) {
                $quote->setSentAt(new \DateTimeImmutable());
            }
            if (QuoteStatus::ACCEPTED === $status && !$quote->getAcceptedAt() instanceof \DateTimeImmutable) {
                $quote->setAcceptedAt(new \DateTimeImmutable());
            }
            $em->flush();
            $this->addFlash('success', 'Devis marqué « '.$status->label().' ».');
        }

        return $this->redirectToRoute('app_quotes_show', ['reference' => $quote->getReference()]);
    }
}

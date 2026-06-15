<?php

namespace App\Controller\Calendar;

use App\Dto\CalendarCursor;
use App\Entity\Project;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/projets/vue/calendrier', name: 'app_projects_calendar', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
final class ShowController extends AbstractController
{
    public function __invoke(
        ProjectRepository $repository,
        #[MapQueryString] CalendarCursor $cursor = new CalendarCursor(),
    ): Response {
        $date = $cursor->date();
        $start = $date;
        $end = $date->modify('first day of next month');

        $projects = $repository->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')->addSelect('c')
            ->where('p.status = :s')->setParameter('s', ProjectStatus::ACTIVE)
            ->andWhere('p.targetDeliveryDate >= :start AND p.targetDeliveryDate < :end')
            ->setParameter('start', $start)->setParameter('end', $end)
            ->orderBy('p.targetDeliveryDate', 'ASC')
            ->getQuery()->getResult();

        $byDay = [];
        foreach ($projects as $project) {
            \assert($project instanceof Project);
            $day = (int) $project->getTargetDeliveryDate()->format('j');
            $byDay[$day][] = $project;
        }

        return $this->render('project/calendar.html.twig', [
            'cursor' => $date,
            'days_in_month' => (int) $date->format('t'),
            'first_dow' => ((int) $date->format('N')) - 1,
            'by_day' => $byDay,
            'prev' => $date->modify('-1 month')->format('Y-m'),
            'next' => $date->modify('+1 month')->format('Y-m'),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarController extends AbstractController
{
    #[Route('/projets/vue/calendrier', name: 'app_projects_calendar')]
    public function index(Request $request, ProjectRepository $repo): Response
    {
        $yearMonth = $request->query->getString('m', date('Y-m'));
        try {
            $cursor = new \DateTimeImmutable($yearMonth.'-01');
        } catch (\Exception) {
            $cursor = new \DateTimeImmutable('first day of this month');
        }

        $start = $cursor;
        $end = $cursor->modify('first day of next month');

        $projects = $repo->createQueryBuilder('p')
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

        $daysInMonth = (int) $cursor->format('t');
        $firstDow = ((int) $cursor->format('N')) - 1; // 0 = lundi

        return $this->render('project/calendar.html.twig', [
            'cursor' => $cursor,
            'days_in_month' => $daysInMonth,
            'first_dow' => $firstDow,
            'by_day' => $byDay,
            'prev' => $cursor->modify('-1 month')->format('Y-m'),
            'next' => $cursor->modify('+1 month')->format('Y-m'),
        ]);
    }
}

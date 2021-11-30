<?php

namespace App\Controller;

use App\Entity\FootballMatch;
use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class TeamController extends AbstractController
{
    #[Route('/', name: 'team_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $teams = $manager->getRepository(Team::class)->findRankings();
        return $this->render('team/index.html.twig', [
            'teams' => $teams
        ]);
    }

    #[Route('/stats', name: 'team_stats')]
    public function stats(EntityManagerInterface $manager, ChartBuilderInterface $chartBuilder): Response
    {
        $teams = $manager->getRepository(Team::class)->findAll();
        $stats = [];
        foreach ($teams as $team) {
            $stats[$team->getName()] = [
                'scored' => round($team->getScoredGoalsPerMatch(), 2),
                'taken' => round($team->getTakenGoalsPerMatch(), 2),
                'color' => $team->getColor()
            ];
        }

        uasort($stats, static function($a, $b) { return $a['scored'] < $b['scored']; });
        $statValues = array_values($stats);



        $scoredGoalsChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $scoredGoalsChart->setData([
            'labels' => array_keys($stats),
            'datasets' => [
                [
                    'label' => 'Buts marqués par match',
                    'data' => array_map(static function($i) { return $i['scored']; }, $statValues),
                    'backgroundColor' => array_map(static function($i) { return $i['color']; }, $statValues),
                    'minBarLength' => 2,
                ]
            ]
        ]);
        $scoredGoalsChart->setOptions([
            'scales' => [
                'yAxes' => [
                    [
                        'ticks' => [
                            'beginAtZero' => true
                        ]
                    ]
                ]
            ]
        ]);

        uasort($stats, static function($a, $b) { return $a['taken'] < $b['taken']; });
        $statValues = array_values($stats);

        $takenGoalsChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $takenGoalsChart->setData([
            'labels' => array_keys($stats),
            'datasets' => [
                [
                    'label' => 'Buts encaissés par match',
                    'data' => array_map(static function($i) { return $i['taken']; }, $statValues),
                    'backgroundColor' => array_map(static function($i) { return $i['color']; }, $statValues),
                    'minBarLength' => 2,
                ]
            ]
        ]);
        $takenGoalsChart->setOptions([
            'scales' => [
                'yAxes' => [
                    [
                        'ticks' => [
                            'beginAtZero' => true
                        ]
                    ]
                ]
            ]
        ]);

        return $this->render('team/stats.html.twig', [
            'teams' => $teams,
            'scoredChart' => $scoredGoalsChart,
            'takenChart' => $takenGoalsChart
        ]);
    }

    #[Route('/team/create', name: 'team_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($team);
            $manager->flush();

            $this->addFlash('success', "L'équipe \"" . $team->getName() . "\" a bien été créée.");
        }

        return $this->render('team/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/team/{id}', name: 'team_view', requirements: [
        'id' => '\d+'
    ])]
    public function view(Team $team, Request $request, EntityManagerInterface $manager, ChartBuilderInterface $chartBuilder): Response
    {
        $matches = $manager->getRepository(FootballMatch::class)->findWithTeam($team);

        $victories = array_filter($matches, static function($match) use ($team) {
            return $match->getWinner() === $team;
        });

        $draws = array_filter($matches, static function($match) use ($team) {
            return $match->getWinner() === null;
        });

        $defeats = array_filter($matches, static function($match) use ($team) {
            return $match->getLoser() === $team;
        });

        $eloHistory = [];
        $eloHistoryArray = $manager->getRepository(LogEntry::class)->getLogEntries($team);
        $i = 1;
        foreach ($eloHistoryArray as $elo) {
            $eloHistory[] = $elo->getData()['rating'];
            $i++;
            if ($i >= 100) {
                break;
            }
        }
        $eloHistory = array_reverse($eloHistory);
        $labels = [];
        $i = 1;
        while ($i < count($eloHistory)) {
            $labels[] = $i;
            ++$i;
        }


        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($victories as $victory) {
            $goalsFor += max($victory->getHostingTeamScore(), $victory->getReceivingTeamScore());
            $goalsAgainst += min($victory->getHostingTeamScore(), $victory->getReceivingTeamScore());
        }
        foreach ($defeats as $defeat) {
            $goalsFor += min($defeat->getHostingTeamScore(), $defeat->getReceivingTeamScore());
            $goalsAgainst += max($defeat->getHostingTeamScore(), $defeat->getReceivingTeamScore());
        }
        foreach ($draws as $draw) {
            $goalsFor += $draw->getHostingTeamScore();
            $goalsAgainst += $draw->getHostingTeamScore();
        }

        $eloHistoryChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $eloHistoryChart->setData([
           'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Historique du classement',
                    'data' => $eloHistory,
                    'fill' => true,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1
                ],
            ]
        ]);


        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => [
                'Victoires',
                'Nuls',
                'Défaites'
            ],
            'datasets' => [
                [
                    'label' => 'Statistiques',
                    'data' => [count($victories), count($draws), count($defeats)],
                    'backgroundColor' => [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ]
                ],
            ]
        ]);

        $goalChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $goalChart->setData([
            'labels' => [
                'Buts marqués',
                'Buts encaissés',
            ],
            'datasets' => [
                [
                    'label' => 'Statistiques',
                    'data' => [$goalsFor, $goalsAgainst],
                    'backgroundColor' => [
                        '#28a745',
                        '#dc3545'
                    ]
                ],
            ]
        ]);

        return $this->render('team/view.html.twig', [
            'team' => $team,
            'matches' => $matches,
            'chart' => $chart,
            'goalChart' => $goalChart,
            'eloHistoryChart' => $eloHistoryChart
        ]);
    }

    #[Route('/team/edit/{id}', name: 'team_edit', requirements: [
        'id' => '\d+'
    ])]
    #[IsGranted('ROLE_USER')]
    public function edit(Team $team, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', "L'équipe \"" . $team->getName() . "\" a bien été éditée.");
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }
}

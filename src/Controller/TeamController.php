<?php

namespace App\Controller;

use App\Elo\EloCalculator;
use App\Entity\FootballMatch;
use App\Entity\Team;
use App\Entity\Tournament;
use App\Form\MatchupType;
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
        $lastMatches = $manager->getRepository(FootballMatch::class)->findBy([], ['id' => 'desc'], 4);

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'lastMatches' => $lastMatches
        ]);
    }

    #[Route('/stats', name: 'team_stats')]
    public function stats(EntityManagerInterface $manager, ChartBuilderInterface $chartBuilder): Response
    {
        $teams = $manager->getRepository(Team::class)->findWithStats();
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
            'maintainAspectRatio' => false,
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
            'maintainAspectRatio' => false,
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

    #[Route('/team/matchup', name: 'team_matchup')]
    public function matchup(Request $request, EntityManagerInterface $manager, EloCalculator $calculator, ChartBuilderInterface $chartBuilder): Response
    {
        $matchup = $request->query->get('matchup');

        if ($matchup && ($id1 = $matchup['team1']) && ($id2 = $matchup['team2'])) {
            $team1 = $manager->getRepository(Team::class)->find($id1);
            $team2 = $manager->getRepository(Team::class)->find($id2);

            if ($team1 && $team2) {
                $eloDiff = $team1->getRating() - $team2->getRating();
                $matchesHistory = $manager->getRepository(FootballMatch::class)->findMatchup($team1, $team2);
                $winProbability = round($calculator->getWinProbability($team1->getRating(), $team2->getRating()), 2);
                $eloDiffWin = $calculator->getEloEvolution($team1->getRating(), $team2->getRating(), EloCalculator::WIN);
                $eloDiffDraw = $calculator->getEloEvolution($team1->getRating(), $team2->getRating(), EloCalculator::DRAW);
                $eloDiffLose = $calculator->getEloEvolution($team1->getRating(), $team2->getRating(), EloCalculator::LOSE);

                $team1Position = $manager->getRepository(Team::class)->findPosition($team1->getRating());
                $team2Position = $manager->getRepository(Team::class)->findPosition($team2->getRating());

                $team1PositionWin = $manager->getRepository(Team::class)->findPosition($team1->getRating() + $eloDiffWin);
                $team1PositionWin4Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffWin * 1.5));
                $team1PositionWin6Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffWin * 1.75));
                $team1PositionWin8Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffWin * 1.875));
                $team1PositionWin10Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffWin * 2));
                $team1PositionDraw = $manager->getRepository(Team::class)->findPosition($team1->getRating() + $eloDiffDraw);
                $team1PositionLose = $manager->getRepository(Team::class)->findPosition($team1->getRating() + $eloDiffLose) - 1;
                $team1PositionLose4Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffLose * 1.5)) - 1;
                $team1PositionLose6Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffLose * 1.75)) - 1;
                $team1PositionLose8Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffLose * 1.875)) - 1;
                $team1PositionLose10Goals = $manager->getRepository(Team::class)->findPosition($team1->getRating() + ($eloDiffLose * 2)) - 1;

                if ($eloDiffDraw < 0 ) {
                    $team1PositionDraw--;
                }

                $team2PositionWin = $manager->getRepository(Team::class)->findPosition($team2->getRating() - $eloDiffLose);
                $team2PositionWin4Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffLose * 1.5));
                $team2PositionWin6Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffLose * 1.75));
                $team2PositionWin8Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffLose * 1.875));
                $team2PositionWin10Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffLose * 2));
                $team2PositionDraw = $manager->getRepository(Team::class)->findPosition($team2->getRating() - $eloDiffDraw);
                $team2PositionLose = $manager->getRepository(Team::class)->findPosition($team2->getRating() - $eloDiffWin) - 1;
                $team2PositionLose4Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffWin * 1.5)) - 1;
                $team2PositionLose6Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffWin * 1.75)) - 1;
                $team2PositionLose8Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffWin * 1.875)) - 1;
                $team2PositionLose10Goals = $manager->getRepository(Team::class)->findPosition($team2->getRating() - ($eloDiffWin * 2)) - 1;

                if ($team1->getRating() > $team2->getRating() &&
                    $team1->getRating() + $eloDiffLose < $team2->getRating() - $eloDiffLose) {
                    $team1PositionLose++;
                    $team2PositionWin--;
                }
                if ($team1->getRating() < $team2->getRating() &&
                    $team1->getRating() + $eloDiffWin < $team2->getRating() - $eloDiffWin) {
                    $team1PositionWin--;
                    $team2PositionLose++;
                }

                if ($eloDiffDraw > 0 ) {
                    $team2PositionDraw--;
                }

                $team1Wins = $team2Wins = $draws = 0;
                $team1Goals = $team2Goals = 0;

                foreach ($matchesHistory as $match) {
                    if ($match->getWinner() === $team1) {
                        $team1Wins++;
                    } else if ($match->getWinner() === $team2) {
                        $team2Wins++;
                    } else {
                        $draws++;
                    }

                    if ($match->getHostingTeam() === $team1) {
                        $team1Goals += $match->getHostingTeamScore();
                        $team2Goals += $match->getReceivingTeamScore();
                    } else {
                        $team2Goals += $match->getHostingTeamScore();
                        $team1Goals += $match->getReceivingTeamScore();
                    }
                }

                $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
                $chart->setData([
                    'labels' => [
                        $team1->getName(),
                        'Nuls',
                        $team2->getName()
                    ],
                    'datasets' => [
                        [
                            'label' => 'Statistiques',
                            'data' => [$team1Wins, $draws, $team2Wins],
                            'backgroundColor' => [
                                $team1->getColor(),
                                '#000000',
                                $team2->getColor()
                            ]
                        ],
                    ]
                ]);

                $goalsChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
                $goalsChart->setData([
                    'labels' => [
                        $team1->getName(),
                        $team2->getName()
                    ],
                    'datasets' => [
                        [
                            'label' => 'Buts marqués contre l\'équipe adverse',
                            'data' => [$team1Goals, $team2Goals],
                            'backgroundColor' => [
                                $team1->getColor(),
                                $team2->getColor()
                            ]
                        ],
                    ]
                ]);
            }

        }

        $form = $this->createForm(MatchupType::class);



        return $this->render('team/matchup.html.twig', [
            'team1' => $team1 ?? null,
            'team1Position' => $team1Position ?? 0,
            'team1PositionWin' => $team1PositionWin ?? 0,
            'team1PositionWin4Goals' => $team1PositionWin4Goals ?? 0,
            'team1PositionWin6Goals' => $team1PositionWin6Goals ?? 0,
            'team1PositionWin8Goals' => $team1PositionWin8Goals ?? 0,
            'team1PositionWin10Goals' => $team1PositionWin10Goals ?? 0,
            'team1PositionDraw' => $team1PositionDraw ?? 0,
            'team1PositionLose' => $team1PositionLose ?? 0,
            'team1PositionLose4Goals' => $team1PositionLose4Goals ?? 0,
            'team1PositionLose6Goals' => $team1PositionLose6Goals ?? 0,
            'team1PositionLose8Goals' => $team1PositionLose8Goals ?? 0,
            'team1PositionLose10Goals' => $team1PositionLose10Goals ?? 0,
            'team2' => $team2 ?? null,
            'team2Position' => $team2Position ?? 0,
            'team2PositionWin' => $team2PositionWin ?? 0,
            'team2PositionWin4Goals' => $team2PositionWin4Goals ?? 0,
            'team2PositionWin6Goals' => $team2PositionWin6Goals ?? 0,
            'team2PositionWin8Goals' => $team2PositionWin8Goals ?? 0,
            'team2PositionWin10Goals' => $team2PositionWin10Goals ?? 0,
            'team2PositionDraw' => $team2PositionDraw ?? 0,
            'team2PositionLose' => $team2PositionLose ?? 0,
            'team2PositionLose4Goals' => $team2PositionLose4Goals ?? 0,
            'team2PositionLose6Goals' => $team2PositionLose6Goals ?? 0,
            'team2PositionLose8Goals' => $team2PositionLose8Goals ?? 0,
            'team2PositionLose10Goals' => $team2PositionLose10Goals ?? 0,
            'eloDiff' => $eloDiff ?? 0,
            'form' => $form->createView(),
            'winProbability' => $winProbability ?? 0.,
            'matchesHistory' => $matchesHistory ?? [],
            'chart' => $chart ?? null,
            'goalsChart' => $goalsChart ?? null,
            'eloDiffWin' => $eloDiffWin ?? 0,
            'eloDiffDraw' => $eloDiffDraw ?? 0,
            'eloDiffLose' => $eloDiffLose ?? 0,
        ]);
    }

    #[Route('/team/{id}', name: 'team_view', requirements: [
        'id' => '\d+'
    ])]
    public function view(Team $team, EntityManagerInterface $manager, ChartBuilderInterface $chartBuilder): Response
    {
        $matches = $manager->getRepository(FootballMatch::class)->findWithTeam($team);

        $victories = array_filter($matches, static function($match) use ($team) {
            return $match->getWinner() === $team;
        });

        $draws = array_filter($matches, static function($match) {
            return $match->getWinner() === null;
        });

        $defeats = array_filter($matches, static function($match) use ($team) {
            return $match->getLoser() === $team;
        });

        $eloHistory = [];
        $eloHistoryArray = $manager->getRepository(LogEntry::class)->getLogEntries($team);
        $tournaments = $manager->getRepository(Tournament::class)->findAll();

        $i = 1;
        foreach ($eloHistoryArray as $elo) {
            $eloHistory[] = $elo->getData()['rating'];
            $i++;
            if ($i >= 100) {
                break;
            }
        }
        $eloHistory = array_reverse($eloHistory);
        $eloHistory = array_merge($eloHistory, [$team->getRating()]);
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
            'eloHistoryChart' => $eloHistoryChart,
            'tournaments' => $tournaments
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

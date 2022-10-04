<?php

declare(strict_types=1);

namespace App\Airline\Volotea\Console;

use App\Airline\Enum\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Core\Service\Curl;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: self::COMMAND_NAME
)]
final class GenerateRoutesCommand extends Command
{
    public const COMMAND_NAME = 'airline:volotea:generate-routes';

    private const URL = 'https://json.volotea.com/dist/stations/stations.json';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private AirportRepository $airportRepository,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting Volotea route import...');
        $airline = $this->airlineRepository->getByIcao(Airline::VOLOTEA->getInfo()->icao);
        $airports = Curl::performSingleGetAndDecode(self::URL);

        $io->progressStart(count($airports));
        foreach ($airports as $fromIata => $airport) {
            $io->progressAdvance();
            $markets = $airport['Markets'];
            if ($markets === []) {
                continue;
            }

            $airportA = $this->airportRepository->getByIata($fromIata);
            foreach ($markets as $toIata => $market) {
                if ($market['Enabled'] !== true || $market['IsConnectionMarket'] !== false) {
                    continue;
                }

                $airportB = $this->airportRepository->getByIata($toIata);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }
        }
        $io->progressFinish();

        return Command::SUCCESS;
    }
}

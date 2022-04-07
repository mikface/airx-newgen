<?php

declare(strict_types=1);

namespace App\Airline\Ryanair\Console;

use App\Airline\Enum\Airline as AirlineEnum;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Core\Service\Curl;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function explode;

#[AsCommand(
    name: self::COMMAND_NAME,
)]
final class GenerateRoutesCommand extends Command
{
    public const COMMAND_NAME = 'airline:ryanair:generate-routes';

    private const URL = 'https://www.ryanair.com/api/locate/4/common?embedded=airports&market=en-gb';

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
        $io->note('Starting Ryanair route import...');
        $airline = $this->airlineRepository->getByIcao(AirlineEnum::RYANAIR->getInfo()->icao);
        $airports = Curl::performSingleGetAndDecode(self::URL)['airports'] ?? [];
        $io->progressStart(count($airports));
        foreach ($airports ?? [] as $airportData) {
            $airportA = $this->airportRepository->findByIata($airportData['iataCode']);
            foreach ($airportData['routes'] as $route) {
                $routeParts = explode(':', $route);
                if ($routeParts[0] !== 'airport') {
                    continue;
                }

                $airportB = $this->airportRepository->findByIata($routeParts[1]);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }
}

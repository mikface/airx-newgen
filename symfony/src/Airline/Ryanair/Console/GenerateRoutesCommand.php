<?php

declare(strict_types=1);

namespace App\Airline\Ryanair\Console;

use App\Airline\Entity\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Core\Service\Curl;
use App\Core\Service\MultiCurl;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function intval;
use function json_decode;
use function sprintf;

#[AsCommand(
    name: self::COMMAND_NAME,
)]
final class GenerateRoutesCommand extends Command
{
    public const COMMAND_NAME = 'airline:ryanair:generate-routes';

    private const BATCH_SIZE = 100;
    private const RYAINAIR_ICAO = 'RYR';
    private const URL = 'https://www.ryanair.com/api/locate/v1/autocomplete/routes?arrivalPhrase=&departurePhrase=%s';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private AirportRepository $airportRepository,
        private MultiCurl $multiCurl,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting Ryanair route import...');
        $allAirports = $this->airportRepository->getAll();
        $ryanair = $this->airlineRepository->getByIcao(self::RYAINAIR_ICAO);
        $io->progressStart(intval(count($allAirports) / 100) + 1);
        for ($i = 0; $i < count($allAirports); $i++) {
            $currentAirportIata = $allAirports[$i]->getIata();
            if ($i % self::BATCH_SIZE === 0) {
                $io->progressAdvance();
                $this->saveRoutes($ryanair, $this->multiCurl->execute());
            }

            $url = sprintf(self::URL, $currentAirportIata);
            $this->multiCurl->addHandle(Curl::getFromUrl($url), $currentAirportIata);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    /** @param array<string, mixed> $routes */
    private function saveRoutes(Airline $airline, array $routes) : void
    {
        foreach ($routes as $airportACode => $oneAirportRoutes) {
            $airportA = $this->airportRepository->findByIata($airportACode);
            $oneAirportRoutes = json_decode($oneAirportRoutes, true);
            if ($oneAirportRoutes === []) {
                continue;
            }

            foreach ($oneAirportRoutes as $oneAirportRoute) {
                if ($oneAirportRoute['connectingAirport'] !== null) {
                    continue;
                }

                $airportB = $this->airportRepository->findByIata($oneAirportRoute['arrivalAirport']['code']);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }
        }
    }
}

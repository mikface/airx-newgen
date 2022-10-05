<?php

declare(strict_types=1);

namespace App\Airline\Wizzair\Console;

use App\Airline\Enum\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Airport\Entity\Airport;
use App\Core\Service\Curl;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function in_array;

use const PHP_EOL;

#[AsCommand(
    name: self::COMMAND_NAME
)]
final class GenerateRoutesCommand extends Command
{
    public const COMMAND_NAME = 'airline:wizzair:generate-routes';

    private const METADATA_URL = 'https://wizzair.com/static_fe/metadata.json';
    private const ROUTES_ENDPOINT = '/asset/map';

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
        $io->note('Starting Wizzair route import...');
        $apiUrl = $this->getApiUrl();
        if ($apiUrl === null) {
            echo 'WIZZAIR METADATA ERROR' . PHP_EOL;

            return Command::FAILURE;
        }

        $airline = $this->airlineRepository->getByIcao(Airline::WIZZAIR->getInfo()->icao);
        $cities = Curl::performSingleGetAndDecode($apiUrl . self::ROUTES_ENDPOINT)['cities'];

        $io->progressStart(count($cities));
        foreach ($cities ?? [] as $city) {
            $io->progressAdvance();
            $airportAiata = $city['iata'];
            if (in_array($airportAiata, Airport::METROPOLITAN_IATAS, true)) {
                continue;
            }

            $airportA = $this->airportRepository->getByIata($airportAiata);
            foreach ($city['connections'] as $connection) {
                $airportBiata = $connection['iata'];
                if (in_array($airportBiata, Airport::METROPOLITAN_IATAS, true)) {
                    continue;
                }

                $airportB = $this->airportRepository->getByIata($airportBiata);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    private function getApiUrl() : string|null
    {
        return Curl::performSingleGetAndDecode(self::METADATA_URL)['apiUrl'] ?? null;
    }
}

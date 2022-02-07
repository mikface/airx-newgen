<?php

declare(strict_types=1);

namespace App\Airline\Wizzair\Console;

use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Core\Service\Curl;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use const PHP_EOL;

#[AsCommand(
    name: 'wizzair:generate-routes'
)]
final class GenerateRoutesCommand extends Command
{
    private const WIZZAIR_ICAO = 'WZZ';
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
        $apiUrl = $this->getApiUrl();
        if ($apiUrl === null) {
            echo 'WIZZAIR METADATA ERROR' . PHP_EOL;

            return Command::FAILURE;
        }

        $airline = $this->airlineRepository->getByIcao(self::WIZZAIR_ICAO);
        foreach (Curl::performSingleGetAndDecode($apiUrl . self::ROUTES_ENDPOINT)['cities'] ?? [] as $city) {
            $airportA = $this->airportRepository->findByIata($city['iata']);
            foreach ($city['connections'] as $connection) {
                $airportB = $this->airportRepository->findByIata($connection['iata']);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }
        }

        return Command::SUCCESS;
    }

    private function getApiUrl() : string|null
    {
        return Curl::performSingleGetAndDecode(self::METADATA_URL)['apiUrl'] ?? null;
    }
}

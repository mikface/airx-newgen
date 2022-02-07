<?php

declare(strict_types=1);

namespace App\Airline\Easyjet\Console;

use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Core\Service\Curl;
use App\Route\Repository\Domain\RouteRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function explode;
use function json_decode;
use function preg_match;
use function str_replace;
use function str_starts_with;

#[AsCommand(
    name: 'easyjet:generate-routes',
)]
final class GenerateRoutesCommand extends Command
{
    private const DATA_URL = 'https://www.easyjet.com/EN/linkedAirportsJSON';
    private const EASYJET_ICAO = 'EZY';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private AirportRepository $airportRepository,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $airline = $this->airlineRepository->getByIcao(self::EASYJET_ICAO);
        $airports = Curl::performSingleGet(self::DATA_URL);
        preg_match('/^var ac_la = (.*);/', $airports, $matches);
        $airportJsonString = str_replace("'", '"', $matches[1]);
        foreach (json_decode($airportJsonString, true) as $connection) {
            $parts = explode('|', $connection);
            $routeEnd = new DateTimeImmutable($parts[3]);
            $now = new DateTimeImmutable();
            if (str_starts_with($parts[0], '*') || str_starts_with($parts[1], '*') || $routeEnd < $now) {
                continue;
            }

            $airportA = $this->airportRepository->findByIata($parts[0]);
            $airportB = $this->airportRepository->findByIata($parts[1]);
            $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
        }

        return Command::SUCCESS;
    }
}

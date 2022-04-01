<?php

declare(strict_types=1);

namespace App\Airline\Vueling\Console;

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

use function in_array;
use function json_decode;
use function preg_match;

#[AsCommand(
    name: self::COMMAND_NAME,
)]
final class GenerateRoutesCommand extends Command
{
    public const COMMAND_NAME = 'airline:vueling:generate-routes';

    private const DATA_URL = 'https://www.vueling.com/en/book-your-flight/where-we-fly';
    private const VUELING_ICAO = 'VLG';

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

        $airline = $this->airlineRepository->getByIcao(self::VUELING_ICAO);
        $airports = Curl::performSingleGet(self::DATA_URL);
        preg_match('/var JSonCities = (.*);/', $airports, $matches);

        $cities = json_decode($matches[1], true);
        foreach ($cities as $city) {
            $airportAiata = $city['IATA'];
            if (in_array($airportAiata, Airport::METROPOLITAN_IATAS, true)) {
                continue;
            }

            $airportA = $this->airportRepository->findByIata($airportAiata);
            foreach ($city['conForDest'] as $connection) {
                $airportBiata = $connection['dest'];
                if ($connection['con'] !== '' || in_array($airportBiata, Airport::METROPOLITAN_IATAS, true)) {
                    continue;
                }

                $airportB = $this->airportRepository->findByIata($airportBiata);
                $this->routeRepository->addIfNotExists($airline, $airportA, $airportB);
            }
        }

        return Command::SUCCESS;
    }
}

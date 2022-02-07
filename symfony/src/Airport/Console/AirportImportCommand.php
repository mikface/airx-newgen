<?php

declare(strict_types=1);

namespace App\Airport\Console;

use App\Airport\Domain\AirportRepository;
use App\Airport\Entity\Airport;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_shift;
use function count;
use function explode;
use function file_get_contents;
use function in_array;
use function str_getcsv;
use function strlen;

use const PHP_EOL;

#[AsCommand(
    name: 'airport:import',
    description: 'Import airports',
)]
final class AirportImportCommand extends Command
{
    private const AIRPORTS_CSV_URL =
        'https://raw.githubusercontent.com/davidmegginson/ourairports-data/main/airports.csv';
    private const ALLOWED_AIRPORT_TYPES = ['medium_airport', 'large_airport'];

    public function __construct(private AirportRepository $airportRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $lines = explode(PHP_EOL, file_get_contents(self::AIRPORTS_CSV_URL));
        array_shift($lines);
        $io->progressStart(count($lines));
        foreach ($lines as $line) {
            $io->progressAdvance();
            if ($line === '') {
                continue;
            }

            $fields = str_getcsv($line);
            if (! in_array($fields[2], self::ALLOWED_AIRPORT_TYPES, true)) {
                continue;
            }

            $iata = $fields[13];
            $icao = $fields[12];
            if (strlen($icao) !== 4 || strlen($iata) !== 3 || $this->airportRepository->findByIata($iata) !== null) {
                continue;
            }

            $airport = new Airport();
            $airport->setName($fields[3]);
            $airport->setLatitude($fields[4]);
            $airport->setLongtitude($fields[5]);
            $airport->setCountry($fields[8]);
            $airport->setMunicipality($fields[10]);
            $airport->setIcao($icao);
            $airport->setIata($iata);
            $this->airportRepository->add($airport);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }
}

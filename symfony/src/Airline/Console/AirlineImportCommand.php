<?php

declare(strict_types=1);

namespace App\Airline\Console;

use App\Airline\Entity\Airline;
use App\Airline\Enum\Airline as AirlineEnum;
use App\Airline\Repository\Domain\AirlineRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: self::COMMAND_NAME,
)]
final class AirlineImportCommand extends Command
{
    public const COMMAND_NAME = 'airline:import';

    public function __construct(private AirlineRepository $airlineRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting airline import...');
        foreach (AirlineEnum::cases() as $case) {
            $airlineInfo = $case->getInfo();
            $airline = $this->airlineRepository->findByIcao($airlineInfo->icao);
            if ($airline === null) {
                $airline = new Airline();
            }

            $airline->setName($case->value);
            $airline->setIata($airlineInfo->iata);
            $airline->setIcao($airlineInfo->icao);
            $airline->setFullName($airlineInfo->fullName);
            $this->airlineRepository->add($airline);
        }

        return Command::SUCCESS;
    }
}

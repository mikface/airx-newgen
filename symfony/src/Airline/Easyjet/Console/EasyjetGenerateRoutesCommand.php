<?php

declare(strict_types=1);

namespace App\Airline\Easyjet\Console;

use App\Airline\Repository\Domain\AirlineRepository;
use App\Airport\Domain\AirportRepository;
use App\Route\Repository\Domain\RouteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'easyjet:generate-routes',
)]
final class EasyjetGenerateRoutesCommand extends Command
{
    private const DATA_URL = 'https://www.easyjet.com/EN/linkedAirportsJSON';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private AirportRepository $airportRepository,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return Command::SUCCESS;
    }
}

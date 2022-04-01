<?php

declare(strict_types=1);

namespace App\Core\Console;

use App\Airline\Console\AirlineImportCommand;
use App\Airline\Easyjet\Console\GenerateRoutesCommand as EasyjetRoutesCommand;
use App\Airline\Ryanair\Console\GenerateRoutesCommand as RyanairRoutesCommand;
use App\Airline\Volotea\Console\GenerateRoutesCommand as VoloteaRoutesCommand;
use App\Airline\Vueling\Console\GenerateRoutesCommand as VuelingRoutesCommand;
use App\Airline\Wizzair\Console\GenerateRoutesCommand as WizzairRoutesCommand;
use App\Airport\Console\AirportImportCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'core:import-all',
    description: 'Import all data for cold start'
)]
final class ImportAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $this->getApplication()->find(AirlineImportCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(AirportImportCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(EasyjetRoutesCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(RyanairRoutesCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(VoloteaRoutesCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(VuelingRoutesCommand::COMMAND_NAME)->run($input, $output);
        $this->getApplication()->find(WizzairRoutesCommand::COMMAND_NAME)->run($input, $output);
    }
}

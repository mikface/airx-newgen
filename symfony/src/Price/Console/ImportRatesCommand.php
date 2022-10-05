<?php

declare(strict_types=1);

namespace App\Price\Console;

use App\Core\Service\Curl;
use App\Price\Domain\Repository\RateRepository;
use App\Price\Entity\Rate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function strlen;

#[AsCommand(
    name: self::COMMAND_NAME,
)]
final class ImportRatesCommand extends Command
{
    public const COMMAND_NAME = 'price:rate:import';

    private const API_URL = 'https://api.currencyapi.com/v3/latest?base_currency=' . Rate::BASE_CURRENCY . '&apikey=';

    public function __construct(private RateRepository $rateRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting currency rate import...');

        $rates = Curl::performSingleGetAndDecode(self::API_URL . $_ENV['CURRENCY_API_KEY']);

        $io->progressStart(count($rates['data']));
        foreach ($rates['data'] as $currencyCode => $data) {
            $io->progressAdvance();
            if (strlen($currencyCode) > 3) {
                continue;
            }

            $rate = ($this->rateRepository->findByCode($currencyCode) ?? new Rate())
                ->setRate($data['value'])
                ->setCurrencyCode($currencyCode);
            $this->rateRepository->add($rate);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }
}

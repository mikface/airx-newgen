<?php

declare(strict_types=1);

namespace App\Airline\Ryanair\Console;

use App\Airline\Enum\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Core\Service\Curl;
use App\Price\Domain\DTO\Price;
use App\Price\Domain\Service\PriceImporter;
use App\Route\Entity\Route;
use App\Route\Enum\RouteDirection;
use App\Route\Repository\Domain\RouteRepository;
use DateInterval;
use Lcobucci\Clock\Clock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function intval;
use function sprintf;

#[AsCommand(
    name: 'airline:ryanair:prices',
)]
final class GetPricesCommand extends Command
{
    private const ARGUMENT_LIMIT = 'limit';
    private const ARGUMENT_OFFSET = 'offset';
    private const GENERATION_MONTHS_FORWARD = 3;
    private const GENERATION_INTERVAL = 'P' . self::GENERATION_MONTHS_FORWARD . 'M';
    private const INTERVAL = 'P1M';
    private const URL_BASE = 'https://www.ryanair.com/api/farfnd/3/roundTripFares' .
    '/%s/%s/cheapestPerDay?market=en-gb&outboundMonthOfDate=%s&inboundMonthOfDate=%s';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private Clock $clock,
        private PriceImporter $priceImporter,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->addArgument(self::ARGUMENT_LIMIT, InputArgument::OPTIONAL)
            ->addArgument(self::ARGUMENT_OFFSET, InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting Ryanair price import...');
        $interval = new DateInterval(self::INTERVAL);
        $now = $this->clock->now();
        $stopDate = $this->clock->now()->add(new DateInterval(self::GENERATION_INTERVAL));
        $airline = $this->airlineRepository->findByIcao(Airline::RYANAIR->getInfo()->icao);
        $limit = $input->getArgument(self::ARGUMENT_LIMIT);
        $offset = $input->getArgument(self::ARGUMENT_OFFSET);
        $allRoutes = $this->routeRepository->findByAirline(
            $airline,
            $limit === null ? null : intval($limit),
            $offset === null ? null : intval($offset),
        );
        $io->progressStart(count($allRoutes) * self::GENERATION_MONTHS_FORWARD);
        while ($now < $stopDate) {
            foreach ($allRoutes as $route) {
                $io->progressAdvance();

                $stringDate = $now->format('Y-m-d');
                $url = sprintf(
                    self::URL_BASE,
                    $route->getAirportA()->getIata(),
                    $route->getAirportB()->getIata(),
                    $stringDate,
                    $stringDate,
                );
                $this->savePrices(Curl::performSingleGetAndDecode($url), $route);
            }

            $now = $now->add($interval);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    /** @param array<string, string> $prices */
    private function savePrices(array $prices, Route $route) : void
    {
        foreach ($prices['outbound']['fares'] as $normalTrip) {
            $price = $normalTrip['price'];
            if ($price === null) {
                continue;
            }

            $priceDTOs[] = new Price(
                $route,
                $price['currencyCode'],
                $price['value'],
                RouteDirection::NORMAL,
                $this->clock->now()->modify($normalTrip['departureDate']),
                $this->clock->now()->modify($normalTrip['arrivalDate']),
            );
        }

        foreach ($prices['inbound']['fares'] as $reverseTrip) {
            $price = $reverseTrip['price'];
            if ($price === null) {
                continue;
            }

            $priceDTOs[] = new Price(
                $route,
                $price['currencyCode'],
                $price['value'],
                RouteDirection::REVERSE,
                $this->clock->now()->modify($reverseTrip['departureDate']),
                $this->clock->now()->modify($reverseTrip['arrivalDate']),
            );
        }

        $this->priceImporter->importFromPriceDTOs($priceDTOs ?? []);
    }
}

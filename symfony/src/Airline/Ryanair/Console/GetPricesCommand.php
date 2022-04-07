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

use function array_key_exists;
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
    private const GENERATION_WEEKS_FORWARD = 13;
    private const GENERATION_INTERVAL = 'P' . self::GENERATION_WEEKS_FORWARD . 'W';
    private const INTERVAL = 'P7D';
    private const START_INTERVAL = 'P3D';
    private const URL_BASE = 'https://www.ryanair.com/api/booking/v4/cs-cz/availability?' .
    'ADT=2&CHD=0&DateIn=%s&DateOut=%s&Destination=%s&Disc=0&INF=0&Origin=%s&TEEN=0' .
    '&promoCode=&IncludeConnectingFlights=false' .
    '&FlexDaysBeforeIn=3&FlexDaysIn=3&RoundTrip=true&FlexDaysBeforeOut=3&FlexDaysOut=3&ToUs=AGREED';

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
        $now = $this->clock->now()->add(new DateInterval(self::START_INTERVAL));
        $stopDate = $this->clock->now()->add(new DateInterval(self::GENERATION_INTERVAL));
        $airline = $this->airlineRepository->findByIcao(Airline::RYANAIR->getInfo()->icao);
        $limit = $input->getArgument(self::ARGUMENT_LIMIT);
        $offset = $input->getArgument(self::ARGUMENT_OFFSET);
        $allRoutes = $this->routeRepository->findByAirline(
            $airline,
            $limit === null ? null : intval($limit),
            $offset === null ? null : intval($offset),
        );
        $io->progressStart(count($allRoutes) * self::GENERATION_WEEKS_FORWARD);
        while ($now < $stopDate) {
            foreach ($allRoutes as $route) {
                $io->progressAdvance();

                $stringDate = $now->format('Y-m-d');
                $url = sprintf(
                    self::URL_BASE,
                    $stringDate,
                    $stringDate,
                    $route->getAirportB()->getIata(),
                    $route->getAirportA()->getIata(),
                );
                $this->savePrices(Curl::performSingleGetAndDecode($url, useProxy: true), $route);
            }

            $now = $now->add($interval);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    /** @param array<string, string> $prices */
    private function savePrices(array $prices, Route $route) : void
    {
        if (! array_key_exists('trips', $prices)) {
            return;
        }

        $currency = $prices['currency'];
        foreach ($prices['trips'][0]['dates'] as $normalTrip) {
            foreach ($normalTrip['flights'] as $flight) {
                if (! array_key_exists('regularFare', $flight)) {
                    continue;
                }

                $priceDTOs[] = new Price(
                    $route,
                    $currency,
                    $flight['regularFare']['fares'][0]['amount'],
                    RouteDirection::NORMAL,
                    $this->clock->now()->modify($flight['time'][0]),
                    $this->clock->now()->modify($flight['time'][1]),
                );
            }
        }

        foreach ($prices['trips'][1]['dates'] as $reverseTrip) {
            foreach ($reverseTrip['flights'] as $flight) {
                if (! array_key_exists('regularFare', $flight)) {
                    continue;
                }

                $priceDTOs[] = new Price(
                    $route,
                    $currency,
                    $flight['regularFare']['fares'][0]['amount'],
                    RouteDirection::REVERSE,
                    $this->clock->now()->modify($flight['time'][0]),
                    $this->clock->now()->modify($flight['time'][1]),
                );
            }
        }

        $this->priceImporter->importFromPriceDTOs($priceDTOs ?? []);
    }
}

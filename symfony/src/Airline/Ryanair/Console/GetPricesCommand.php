<?php

declare(strict_types=1);

namespace App\Airline\Ryanair\Console;

use App\Airline\Enum\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Core\Service\Curl;
use App\Core\Service\MultiCurl;
use App\Price\Domain\DTO\Price;
use App\Price\Domain\Service\PriceImporter;
use App\Route\Enum\RouteDirection;
use App\Route\Repository\Domain\RouteRepository;
use DateInterval;
use Lcobucci\Clock\Clock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_key_exists;
use function count;
use function sprintf;
use function usleep;

#[AsCommand(
    name: 'airline:ryanair:prices',
)]
final class GetPricesCommand extends Command
{
    private const GENERATION_WEEKS_FORWARD = 13;
    private const BATCH_SIZE = 10;
    private const GENERATION_INTERVAL = 'P' . self::GENERATION_WEEKS_FORWARD . 'W';
    private const INTERVAL = 'P7D';
    private const START_INTERVAL = 'P3D';
    private const URL_BASE = 'https://www.ryanair.com/api/booking/v5/cs-cz/availability?' .
    'ADT=2&CHD=0&DateIn=%s&DateOut=%s&Destination=%s&Disc=0&INF=0&Origin=%s&TEEN=0' .
    '&promoCode=&IncludeConnectingFlights=false' .
    '&FlexDaysBeforeIn=3&FlexDaysIn=3&RoundTrip=true&FlexDaysBeforeOut=3&FlexDaysOut=3&ToUs=AGREED';

    public function __construct(
        private AirlineRepository $airlineRepository,
        private Clock $clock,
        private MultiCurl $multiCurl,
        private PriceImporter $priceImporter,
        private RouteRepository $routeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Starting Ryanair price import...');
        $interval = new DateInterval(self::INTERVAL);
        $now = $this->clock->now()->add(new DateInterval(self::START_INTERVAL));
        $stopDate = $this->clock->now()->add(new DateInterval(self::GENERATION_INTERVAL));
        $airline = $this->airlineRepository->findByIcao(Airline::RYANAIR->getInfo()->icao);
        $allRoutes = $this->routeRepository->findByAirline($airline);
        $io->progressStart(count($allRoutes) * self::GENERATION_WEEKS_FORWARD);
        $batchCount = 0;
        while ($now < $stopDate) {
            foreach ($allRoutes as $route) {
                if (($batchCount > 0) && ($batchCount % self::BATCH_SIZE === 0)) {
                    $this->savePrices($this->multiCurl->execute());
                    sleep(2);
                }

                $io->progressAdvance();

                $stringDate = $now->format('Y-m-d');
                $url = sprintf(
                    self::URL_BASE,
                    $stringDate,
                    $stringDate,
                    $route->getAirportB()->getIata(),
                    $route->getAirportA()->getIata(),
                );

                $this->multiCurl->addHandle(Curl::getFromUrl($url, true), $route->getId()->toString());
                $batchCount++;
            }

            $now = $now->add($interval);
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    /** @param array<string, string> $routePrices */
    private function savePrices(array $routePrices) : void
    {
        foreach ($routePrices as $routeId => $prices) {
            $prices = json_decode($prices, true);

            if (! array_key_exists('trips', $prices)) {
                continue;
            }

            $currency = $prices['currency'];
            foreach ($prices['trips'][0]['dates'] as $normalTrip) {
                foreach ($normalTrip['flights'] as $flight) {
                    if (! array_key_exists('regularFare', $flight)) {
                        continue;
                    }

                    $priceDTOs[] = new Price(
                        $this->routeRepository->get($routeId),
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
                        $this->routeRepository->get($routeId),
                        $currency,
                        $flight['regularFare']['fares'][0]['amount'],
                        RouteDirection::REVERSE,
                        $this->clock->now()->modify($flight['time'][0]),
                        $this->clock->now()->modify($flight['time'][1]),
                    );
                }
            }
        }

        $this->priceImporter->importFromPriceDTOs($priceDTOs ?? []);
    }
}

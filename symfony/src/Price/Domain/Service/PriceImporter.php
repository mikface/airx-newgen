<?php

declare(strict_types=1);

namespace App\Price\Domain\Service;

use App\Price\Domain\DTO\Price as PriceDTO;
use App\Price\Domain\Repository\PriceRepository;
use App\Price\Domain\Repository\RateRepository;
use App\Price\Entity\Price;
use App\Price\Entity\Rate;

final class PriceImporter
{
    public function __construct(private PriceRepository $priceRepository, private RateRepository $rateRepository)
    {
    }

    /** @param list<PriceDTO> $priceDTOs */
    public function importFromPriceDTOs(array $priceDTOs) : void
    {
        foreach ($priceDTOs as $priceDTO) {
            $originalCurrencyCode = $priceDTO->currencyCode;
            $originalPrice = $priceDTO->price;
            $priceEur = $originalPrice;
            if ($originalCurrencyCode !== Rate::BASE_CURRENCY) {
                $priceEur = $originalPrice / $this->rateRepository->getByCode($originalCurrencyCode)->getRate();
            }

            $departure = $priceDTO->departure;
            $route = $priceDTO->route;
            $routeDirection = $priceDTO->routeDirection;

            $price = (
                $this->priceRepository->findForRouteAndDeparture($route, $routeDirection, $departure) ?? new Price()
            )
                ->setPriceOriginal($originalPrice)
                ->setCurrencyOriginal($originalCurrencyCode)
                ->setDeparture($departure)
                ->setArrival($priceDTO->arrival)
                ->setPrice($priceEur)
                ->setRoute($route)
                ->setDirection($priceDTO->routeDirection);

            $this->priceRepository->add($price);
        }
    }
}

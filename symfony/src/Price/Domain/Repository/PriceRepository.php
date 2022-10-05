<?php

declare(strict_types=1);

namespace App\Price\Domain\Repository;

use App\Price\Entity\Price;
use App\Route\Entity\Route;
use App\Route\Enum\RouteDirection;
use DateTimeImmutable;

interface PriceRepository
{
    public function add(Price $price) : void;

    public function findForRouteAndDeparture(
        Route $route,
        RouteDirection $routeDirection,
        DateTimeImmutable $departure
    ) : Price|null;
}

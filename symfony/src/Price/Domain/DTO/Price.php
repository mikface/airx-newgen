<?php

declare(strict_types=1);

namespace App\Price\Domain\DTO;

use App\Route\Entity\Route;
use App\Route\Enum\RouteDirection;
use DateTimeImmutable;

final class Price
{
    public function __construct(
        public Route $route,
        public string $currencyCode,
        public float $price,
        public RouteDirection $routeDirection,
        public DateTimeImmutable $departure,
        public DateTimeImmutable $arrival
    ) {
    }
}

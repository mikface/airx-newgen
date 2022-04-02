<?php

declare(strict_types=1);

namespace App\Price\Domain\Repository;

use App\Price\Entity\Price;
use App\Route\Entity\Route;

interface PriceRepository
{
    public function add(Price $price) : void;

    public function deleteForRoute(Route $route) : void;
}

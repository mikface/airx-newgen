<?php

declare(strict_types=1);

namespace App\Price\Domain\Repository;

use App\Price\Entity\Rate;

interface RateRepository
{
    public function add(Rate $rate) : void;

    public function findByCode(string $code) : Rate|null;
}
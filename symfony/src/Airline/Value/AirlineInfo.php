<?php

declare(strict_types=1);

namespace App\Airline\Value;

final class AirlineInfo
{
    public function __construct(public string $fullName, public string $iata, public string $icao)
    {
    }
}

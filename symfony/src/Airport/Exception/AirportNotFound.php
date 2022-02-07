<?php

declare(strict_types=1);

namespace App\Airport\Exception;

use DomainException;

final class AirportNotFound extends DomainException
{
    public static function forIata(string $iata) : self
    {
        return new self('Airport not found for IATA code: ' . $iata);
    }
}

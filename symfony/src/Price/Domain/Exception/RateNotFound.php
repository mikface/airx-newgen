<?php

declare(strict_types=1);

namespace App\Price\Domain\Exception;

use DomainException;

use function sprintf;

final class RateNotFound extends DomainException
{
    public static function forCurrencyCode(string $currencyCode) : self
    {
        return new self(sprintf('Rate for currency with code %s was not found', $currencyCode));
    }
}

<?php

declare(strict_types=1);

namespace App\Route\Exception;

use DomainException;

use function sprintf;

final class RouteNotFound extends DomainException
{
    public static function forId(string $id) : self
    {
        return new self(sprintf('Route with id %s was not found', $id));
    }
}

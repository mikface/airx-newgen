<?php

declare(strict_types=1);

namespace App\Airline\Repository\Domain;

use App\Airline\Entity\Airline;

interface AirlineRepository
{
    public function add(Airline $airline) : void;

    public function findByIcao(string $icao) : Airline;
}

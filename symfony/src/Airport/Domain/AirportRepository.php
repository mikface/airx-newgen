<?php

declare(strict_types=1);

namespace App\Airport\Domain;

use App\Airport\Entity\Airport;

interface AirportRepository
{
    public function add(Airport $airport) : void;

    public function findByIata(string $iata) : Airport|null;

    /** @return list<Airport> */
    public function getAll() : array;
}

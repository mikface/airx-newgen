<?php

declare(strict_types=1);

namespace App\Route\Repository\Domain;

use App\Airline\Entity\Airline;
use App\Airport\Entity\Airport;
use App\Route\Entity\Route;

interface RouteRepository
{
    public function add(Route $route) : void;

    public function findByAirlineAndAirports(Airline $airline, Airport $airportA, Airport $airportB) : Route|null;

    public function addIfNotExists(Airline $airline, Airport $airportA, Airport $airportB) : void;
}

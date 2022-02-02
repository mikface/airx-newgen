<?php

declare(strict_types=1);

namespace App\Route\Entity;

use App\Airline\Entity\Airline;
use App\Airport\Entity\Airport;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_route', columns: ['airline_id', 'airport_a_id', 'airport_b_id'])]
class Route
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Airline::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Airline $airline;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Airport $airportA;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Airport $airportB;

    public function __construct(Airline $airline, Airport $airportA, Airport $airportB)
    {
        $this->id = Uuid::uuid4();
        $this->airline = $airline;
        $this->airportA = $airportA;
        $this->airportB = $airportB;
    }

    public function getAirline() : ?Airline
    {
        return $this->airline;
    }

    public function setAirline(?Airline $airline) : self
    {
        $this->airline = $airline;

        return $this;
    }

    public function getAirportA() : ?Airport
    {
        return $this->airportA;
    }

    public function setAirportA(?Airport $airportA) : self
    {
        $this->airportA = $airportA;

        return $this;
    }

    public function getAirportB() : ?Airport
    {
        return $this->airportB;
    }

    public function setAirportB(?Airport $airportB) : self
    {
        $this->airportB = $airportB;

        return $this;
    }
}

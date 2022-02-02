<?php

declare(strict_types=1);

namespace App\Airline\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'iata', columns: ['iata'])]
#[ORM\UniqueConstraint(name: 'icao', columns: ['icao'])]
class Airline
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 2)]
    private string $iata;

    #[ORM\Column(type: 'string', length: 3)]
    private string $icao;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fullName;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getIata() : ?string
    {
        return $this->iata;
    }

    public function setIata(string $iata) : self
    {
        $this->iata = $iata;

        return $this;
    }

    public function getIcao() : ?string
    {
        return $this->icao;
    }

    public function setIcao(string $icao) : self
    {
        $this->icao = $icao;

        return $this;
    }

    public function getFullName() : ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName) : self
    {
        $this->fullName = $fullName;

        return $this;
    }
}

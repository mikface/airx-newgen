<?php

declare(strict_types=1);

namespace App\Airport\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'icao', columns: ['icao'])]
#[ORM\UniqueConstraint(name: 'iata', columns: ['iata'])]
class Airport
{
    public const MULTI_IATA_MAP = [
        'MLH' => 'BSL',
        'EAP' => 'BSL',
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 3)]
    private string $iata;

    #[ORM\Column(type: 'string', length: 4)]
    private string $icao;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 17)]
    private string $latitude;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 17)]
    private string $longtitude;

    #[ORM\Column(type: 'string', length: 2)]
    private string $country;

    #[ORM\Column(type: 'string', length: 255)]
    private string $municipality;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
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

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getLatitude() : ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude) : self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongtitude() : ?string
    {
        return $this->longtitude;
    }

    public function setLongtitude(string $longtitude) : self
    {
        $this->longtitude = $longtitude;

        return $this;
    }

    public function getCountry() : ?string
    {
        return $this->country;
    }

    public function setCountry(string $country) : self
    {
        $this->country = $country;

        return $this;
    }

    public function getMunicipality() : ?string
    {
        return $this->municipality;
    }

    public function setMunicipality(string $municipality) : self
    {
        $this->municipality = $municipality;

        return $this;
    }
}

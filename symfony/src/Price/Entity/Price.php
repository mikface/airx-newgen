<?php

declare(strict_types=1);

namespace App\Price\Entity;

use App\Route\Entity\Route;
use App\Route\Enum\RouteDirection;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class Price
{
    private const DEFAULT_COUNT = 2;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Route::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Route $route;

    #[ORM\Column(type: 'float')]
    private float $price;

    #[ORM\Column(type: 'string', length: 1, enumType: RouteDirection::class)]
    private RouteDirection $direction;

    #[ORM\Column(type: 'float')]
    private float $priceOriginal;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currencyOriginal;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $departure;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $arrival;

    #[ORM\Column(type: 'integer')]
    private int $count = self::DEFAULT_COUNT;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $created;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = new DateTimeImmutable();
    }

    public function setRoute(Route $route) : self
    {
        $this->route = $route;

        return $this;
    }

    public function getPrice() : ?float
    {
        return $this->price;
    }

    public function setPrice(float $price) : self
    {
        $this->price = $price;

        return $this;
    }

    public function getDirection() : RouteDirection
    {
        return $this->direction;
    }

    public function setDirection(RouteDirection $direction) : self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getPriceOriginal() : ?float
    {
        return $this->priceOriginal;
    }

    public function setPriceOriginal(float $priceOriginal) : self
    {
        $this->priceOriginal = $priceOriginal;

        return $this;
    }

    public function getCurrencyOriginal() : ?string
    {
        return $this->currencyOriginal;
    }

    public function setCurrencyOriginal(string $currencyOriginal) : self
    {
        $this->currencyOriginal = $currencyOriginal;

        return $this;
    }

    public function getDeparture() : ?DateTimeImmutable
    {
        return $this->departure;
    }

    public function setDeparture(DateTimeImmutable $departure) : self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getArrival() : ?DateTimeImmutable
    {
        return $this->arrival;
    }

    public function setArrival(DateTimeImmutable $arrival) : self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getCount() : ?int
    {
        return $this->count;
    }

    public function setCount(int $count) : self
    {
        $this->count = $count;

        return $this;
    }
}

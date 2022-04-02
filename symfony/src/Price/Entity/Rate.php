<?php

declare(strict_types=1);

namespace App\Price\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_code', columns: ['currency_code'])]
class Rate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currencyCode;

    #[ORM\Column(type: 'float')]
    private float $rate;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}

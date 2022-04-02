<?php

declare(strict_types=1);

namespace App\Price\Infrastructure\Repository;

use App\Core\Service\EntityManagerConstructor;
use App\Price\Domain\Repository\PriceRepository;
use App\Price\Entity\Price;
use App\Route\Entity\Route;

final class DoctrinePriceRepository implements PriceRepository
{
    use EntityManagerConstructor;

    public function add(Price $price) : void
    {
        $this->entityManager->persist($price);
        $this->entityManager->flush();
    }

    public function deleteForRoute(Route $route) : void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Price::class, 'price')
            ->where('price.route = :route')
            ->setParameter('route', $route)
            ->getQuery()->execute();
    }
}

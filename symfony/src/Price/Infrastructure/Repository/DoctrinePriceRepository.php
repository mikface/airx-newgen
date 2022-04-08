<?php

declare(strict_types=1);

namespace App\Price\Infrastructure\Repository;

use App\Core\Service\EntityManagerConstructor;
use App\Price\Domain\Repository\PriceRepository;
use App\Price\Entity\Price;
use App\Route\Entity\Route;
use DateTimeImmutable;

final class DoctrinePriceRepository implements PriceRepository
{
    use EntityManagerConstructor;

    public function add(Price $price) : void
    {
        $this->entityManager->persist($price);
        $this->entityManager->flush();
    }

    public function findForRouteAndDeparture(Route $route, DateTimeImmutable $departure) : Price|null
    {
        return $this->entityManager->createQueryBuilder()
            ->select('price')
            ->from(Price::class, 'price')
            ->where('price.route = :routeId')
            ->setParameter('routeId', $route->getId()->toString())
            ->getQuery()->getOneOrNullResult();
    }
}

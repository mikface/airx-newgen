<?php

declare(strict_types=1);

namespace App\Airport\Infrastructure;

use App\Airport\Domain\AirportRepository;
use App\Airport\Entity\Airport;
use App\Airport\Exception\AirportNotFound;
use App\Core\Service\EntityManagerConstructor;

final class DoctrineAirportRepository implements AirportRepository
{
    use EntityManagerConstructor;

    public function add(Airport $airport) : void
    {
        $this->entityManager->persist($airport);
        $this->entityManager->flush();
    }

    public function findByIata(string $iata) : ?Airport
    {
        $iata = Airport::MULTI_IATA_MAP[$iata] ?? $iata;

        return $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Airport::class, 'a')
            ->where('a.iata = :iata')
            ->setParameter('iata', $iata)
            ->getQuery()->getOneOrNullResult();
    }

    public function getByIata(string $iata) : Airport
    {
        return $this->findByIata($iata) ?? throw AirportNotFound::forIata($iata);
    }

    /** @inheritDoc */
    public function getAll() : array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Airport::class, 'a')
            ->getQuery()->getResult();
    }
}

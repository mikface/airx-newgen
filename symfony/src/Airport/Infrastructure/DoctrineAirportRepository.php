<?php

declare(strict_types=1);

namespace App\Airport\Infrastructure;

use App\Airport\Domain\AirportRepository;
use App\Airport\Entity\Airport;
use App\Airport\Exception\AirportNotFound;
use App\Core\Service\EntityManagerConstructor;
use Webmozart\Assert\Assert;

use function var_dump;

final class DoctrineAirportRepository implements AirportRepository
{
    use EntityManagerConstructor;

    public function add(Airport $airport) : void
    {
        $this->entityManager->persist($airport);
        $this->entityManager->flush();
    }

    public function findByIata(string $iata) : Airport
    {
        $iata = Airport::MULTI_IATA_MAP[$iata] ?? $iata;

        $result = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Airport::class, 'a')
            ->where('a.iata = :iata')
            ->setParameter('iata', $iata)
            ->getQuery()->getOneOrNullResult();
        if ($result === null) {
            throw AirportNotFound::forIata($iata);
        }

        Assert::notNull($result);

        return $result;
    }

    public function getAll() : array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Airport::class, 'a')
            ->getQuery()->getResult();
    }
}

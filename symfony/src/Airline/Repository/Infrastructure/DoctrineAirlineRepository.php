<?php

declare(strict_types=1);

namespace App\Airline\Repository\Infrastructure;

use App\Airline\Entity\Airline;
use App\Airline\Repository\Domain\AirlineRepository;
use App\Core\Service\EntityManagerConstructor;
use Webmozart\Assert\Assert;

final class DoctrineAirlineRepository implements AirlineRepository
{
    use EntityManagerConstructor;

    public function add(Airline $airline) : void
    {
        $this->entityManager->persist($airline);
        $this->entityManager->flush();
    }

    public function findByIcao(string $icao) : Airline
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Airline::class, 'a')
            ->where('a.icao = :icao')
            ->setParameter('icao', $icao)
            ->getQuery()->getOneOrNullResult();
        Assert::notNull($result);

        return $result;
    }
}

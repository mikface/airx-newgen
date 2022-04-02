<?php

declare(strict_types=1);

namespace App\Price\Infrastructure\Repository;

use App\Core\Service\EntityManagerConstructor;
use App\Price\Domain\Repository\RateRepository;
use App\Price\Entity\Rate;

final class DoctrineRateRepository implements RateRepository
{
    use EntityManagerConstructor;

    public function add(Rate $rate): void
    {
        $this->entityManager->persist($rate);
        $this->entityManager->flush();
    }

    public function findByCode(string $code): Rate|null
    {
        return $this->entityManager->createQueryBuilder()
            ->select('rate')
            ->from(Rate::class, 'rate')
            ->where('rate.currencyCode = :code')
            ->setParameter('code', $code)
            ->getQuery()->getOneOrNullResult();
    }
}
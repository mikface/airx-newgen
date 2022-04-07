<?php

declare(strict_types=1);

namespace App\Route\Repository\Infrastructure;

use App\Airline\Entity\Airline;
use App\Airport\Entity\Airport;
use App\Core\Service\EntityManagerConstructor;
use App\Route\Entity\Route;
use App\Route\Exception\RouteNotFound;
use App\Route\Repository\Domain\RouteRepository;

final class DoctrineRouteRepository implements RouteRepository
{
    use EntityManagerConstructor;

    public function add(Route $route) : void
    {
        $this->entityManager->persist($route);
        $this->entityManager->flush();
    }

    /** @inheritDoc */
    public function findByAirline(Airline $airline, int|null $limit = null, int|null $offset = null) : array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Route::class, 'r')
            ->where('r.airline = :airline')
            ->setParameter('airline', $airline)
            ->orderBy('r.id ASC');
        if ($offset !== null) {
            $queryBuilder->setFirstResult($offset);
        }

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByAirlineAndAirports(Airline $airline, Airport $airportA, Airport $airportB) : Route|null
    {
        $queryBuilderInner = $this->entityManager->createQueryBuilder();

        $airportQuery = $queryBuilderInner->expr()->orX(
            $queryBuilderInner->expr()->andX(
                $queryBuilderInner->expr()->eq('r.airportA', ':airportA'),
                $queryBuilderInner->expr()->eq('r.airportB', ':airportB')
            ),
            $queryBuilderInner->expr()->andX(
                $queryBuilderInner->expr()->eq('r.airportA', ':airportB'),
                $queryBuilderInner->expr()->eq('r.airportB', ':airportA')
            )
        );

        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Route::class, 'r')
            ->where($airportQuery)
            ->andWhere('r.airline = :airline')
            ->setParameter('airportA', $airportA)
            ->setParameter('airportB', $airportB)
            ->setParameter('airline', $airline)
            ->getQuery()->getOneOrNullResult();
    }

    public function addIfNotExists(Airline $airline, Airport $airportA, Airport $airportB) : void
    {
        $existingRoute = $this->findByAirlineAndAirports($airline, $airportA, $airportB);
        if ($existingRoute !== null) {
            return;
        }

        $this->add(new Route($airline, $airportA, $airportB));
    }

    public function get(string $id) : Route
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('route')
            ->from(Route::class, 'route')
            ->where('route.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        return $result ?? throw RouteNotFound::forId($id);
    }
}

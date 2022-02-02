<?php

declare(strict_types=1);

namespace App\Core\Service;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerConstructor
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }
}

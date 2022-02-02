<?php

declare(strict_types=1);

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepo;
    private UserPasswordHasherInterface $hasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher
    ) {
        $this->entityManager = $entityManager;
        $this->userRepo = $userRepo;
        $this->hasher = $hasher;
    }

    public function create(string $email, string $password, string $role = User::ROLE_RESTRICTED) : bool
    {
        if ($this->userRepo->findBy(['email' => $email])) {
            return false;
        }

        $newUser = new User();
        $newUser->setEmail($email);
        $newUser->setPassword($this->hasher->hashPassword($newUser, $password));
        $newUser->setRoles([$role]);
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        return true;
    }
}

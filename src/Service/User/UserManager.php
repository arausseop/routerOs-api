<?php

namespace App\Service\User;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{

    private $em;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getRepository(): UserRepository
    {
        return $this->userRepository;
    }

    public function create(): User
    {
        $user = new User();
        return $user;
    }

    public function persist(User $user): User
    {
        $this->em->persist($user);
        return $user;
    }

    public function save(User $user): User
    {
        // die(var_dump($user->getPassword()));
        $this->em->persist($user);

        $this->em->flush();
        return $user;
    }

    public function reload(User $user): User
    {
        $this->em->refresh($user);
        return $user;
    }

    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function getEntityReference($entityNameEspace, $entityId)
    {
        return $this->em->getReference($entityNameEspace, $entityId);
    }
}

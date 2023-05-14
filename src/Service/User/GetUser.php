<?php

namespace App\Service\User;

use App\Entity\Main\User;
use App\Model\Exception\User\UserNotFound;
use App\Repository\Main\UserRepository;


class GetUser
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(string $uuid): User
    {
        // $user = $this->userRepository->find($id);
        $user = $this->userRepository->findOneByUuid($uuid);

        if (!$user) {
            UserNotFound::throwException();
        }
        return $user;
    }
}

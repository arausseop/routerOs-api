<?php

namespace App\Service\User;


class DeleteUser
{
    private GetUser $getUser;
    private UserManager $userManager;

    public function __construct(GetUser $getUser, UserManager $userManager)
    {
        $this->getUser = $getUser;
        $this->userManager = $userManager;
    }

    public function __invoke(string $uuid)
    {
        $user = ($this->getUser)($uuid);
        $this->userManager->delete($user);
    }
}

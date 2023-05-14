<?php

namespace App\Form\Model\User;

use App\Entity\Main\User;
use DateTimeInterface as DateTimeInterface;

class UserResetPasswordDto
{
    public ?string $uuid = null;
    public ?string $email = "";
    public ?string $password = null;
    public ?string $plainPassword = null;
    public ?string $token = null;

    public function __construct()
    {
        // $this->plainPassword = [];
    }


    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromUser(User $user): self
    {
        $dto = new self();
        $dto->uuid = $user->getUuid();
        $dto->password = $user->getPassword();
        $dto->email = $user->getEmail();
        $dto->token = $user->getResetPasswordRequests()->last()->getToken();

        return $dto;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }
}

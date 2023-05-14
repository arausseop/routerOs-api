<?php

namespace App\Form\Model\User;

use App\Entity\Main\User;
use DateTimeInterface as DateTimeInterface;

class UserChangePasswordDto
{
    public ?string $plainPassword = null;
    public ?string $password = null;
    // public ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromUser(User $user): self
    {
        $dto = new self();
        $dto->password = $user->getPassword();

        return $dto;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function getPassword()
    {
        return $this->password;
    }
}

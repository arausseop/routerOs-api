<?php

namespace App\Form\Model\User;

use App\Entity\Main\User;
use DateTimeInterface as DateTimeInterface;

class UserDto
{
    public ?string $uuid = null;
    public ?string $email = "";
    public ?string $password = null;
    public ?array $roles = [];
    public ?array $roleGroups = [];
    public ?string $firstName = "";
    public ?string $lastName = "";
    public ?string $dni = "";
    public ?string $base64File = null;
    public ?string $avatar = "";
    public ?bool $deleted = false;
    public ?bool $active =  true;
    public ?bool $expired = false;
    public ?bool $isVerified = false;
    public ?DateTimeInterface $createdAt = null;
    public ?DateTimeInterface $updatedAt = null;
    public ?string $expiredAt = null;

    // public ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->roleGroups = [];
        $this->roles = [];
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
        $dto->roles = $user->getRoles();
        // $dto->roleGroups = $user->getRoleGroups();
        $dto->firstName = $user->getFirstName();
        $dto->lastName = $user->getLastName();
        $dto->dni = $user->getDni();
        $dto->avatar = $user->getAvatar();
        $dto->deleted = $user->isDeleted();
        $dto->active = $user->isActive();
        $dto->expired = $user->isExpired();
        $dto->isVerified = $user->isVerified();
        $dto->createdAt = $user->getCreatedAt();
        $dto->updatedAt = $user->getUpdatedAt();
        $dto->expiredAt = $user->getExpiredAt();

        // $dto->createdAt = $user->getCreatedAt();

        return $dto;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getRoleGroups()
    {
        return $this->roleGroups;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getDni()
    {
        return $this->dni;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isExpired()
    {
        return $this->expired;
    }

    public function isVerified()
    {
        return $this->isVerified;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    public function getBase64File()
    {
        return $this->base64File;
    }
}

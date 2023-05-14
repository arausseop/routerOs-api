<?php

namespace App\Form\Model\Router;

use App\Entity\Main\Router;
use DateTimeImmutable;
use DateTimeInterface as DateTimeInterface;

class RouterDto
{

    public ?string $uuid = null;
    public ?string $name = '';
    public ?string $description = '';
    public ?string $ipAddress = '';
    public ?string $login = '';
    public ?string $password = '';
    public ?bool $connect = false;



    public function __construct()
    {
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromRouter(Router $router): self
    {
        $dto = new self();

        $dto->uuid = $router->getUuid();
        $dto->name = $router->getName();
        $dto->description = $router->getDescription();
        $dto->ipAddress = $router->getIpAddress();
        $dto->login = $router->getLogin();
        $dto->password = $router->getPassword();
        $dto->connect = $router->isConnect();

        return $dto;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isConnect(): bool
    {
        return $this->connect;
    }
}

<?php

namespace App\Form\Model\Module;

use App\Entity\Main\Module;
use DateTimeInterface as DateTimeInterface;

class ModuleDto
{
    public ?string $uuid = '';
    public string $name = '';
    public string $code = '';
    public ?bool $active = null;
    public ?bool $deleted = null;
    public ?DateTimeInterface $createdAt = null;
    public ?DateTimeInterface $updatedAt = null;

    // public ?DateTimeInterface $createdAt = null;


    public function __construct()
    {
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromModule(Module $module): self
    {
        $dto = new self();
        $dto->name = $module->getName();
        $dto->code = $module->getCode();
        $dto->active = $module->isActive();
        $dto->deleted = $module->isDeleted();
        $dto->createdAt = $module->getCreatedAt();
        $dto->updatedAt = $module->getUpdatedAt();
        $dto->uuid = $module->getUuid();

        // $dto->createdAt = $module->getCreatedAt();

        return $dto;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isDeleted()
    {
        return $this->active;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getUuid()
    {
        return $this->uuid;
    }
}

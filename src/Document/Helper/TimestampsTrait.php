<?php

namespace App\Document\Helper;

use DateTime;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Types\Type;

trait TimestampsTrait
{
    #[MongoDB\Field(name: 'created_at', type: Type::DATE, nullable: true)]
    private $createdAt;

    #[MongoDB\Field(name: 'updated_at', type: Type::DATE, nullable: true)]
    private $updatedAt;

    #[MongoDB\Field(name: 'deleted_at', type: Type::DATE, nullable: true)]
    private $deletedAt;

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $timestamp): self
    {
        $this->createdAt = $timestamp;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $timestamp): self
    {
        $this->updatedAt = $timestamp;
        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $timestamp): self
    {
        $this->deletedAt = $timestamp;
        return $this;
    }

    #[MongoDB\PrePersist]
    public function setCreatedAtAutomatically()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    #[MongoDB\PreUpdate]
    public function setUpdatedAtAutomatically()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}

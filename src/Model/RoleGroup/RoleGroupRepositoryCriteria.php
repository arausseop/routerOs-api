<?php

namespace App\Model\RoleGroup;

use Ramsey\Uuid\UuidInterface;

class RoleGroupRepositoryCriteria
{
    public function __construct(
        public readonly ?string $searchText = null,
        public readonly ?int $page = 1,
        public readonly ?int $itemsPerPage = 10,
    ) {
    }
}

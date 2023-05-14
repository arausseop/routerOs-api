<?php

namespace App\Model\User;

use Ramsey\Uuid\UuidInterface;

class UserRepositoryCriteria
{
    public function __construct(
        public readonly ?string $searchText = null,
        public readonly ?int $page = 1,
        public readonly ?int $itemsPerPage = 10,
    ) {
    }
}

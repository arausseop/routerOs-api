<?php

namespace App\Model\Router;

use Ramsey\Uuid\UuidInterface;

class RouterRepositoryCriteria
{
    public function __construct(
        public readonly ?string $searchText = null,
        public readonly ?int $page = 1,
        public readonly ?int $itemsPerPage = 10,
    ) {
    }
}

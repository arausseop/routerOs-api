<?php

namespace App\Model\Module;

class ModuleRepositoryCriteria
{
    public function __construct(
        public readonly ?string $searchText = null,
        public readonly ?int $page = 1,
        public readonly ?int $itemsPerPage = 10,
    ) {
    }
}

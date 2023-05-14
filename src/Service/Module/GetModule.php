<?php

namespace App\Service\Module;

use App\Entity\Main\Module;
use App\Model\Exception\Module\ModuleNotFound;
use App\Repository\Main\ModuleRepository;


class GetModule
{
    private ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function __invoke(string $uuid): Module
    {
        $module = $this->moduleRepository->findOneByUuid($uuid);

        if (!$module) {
            ModuleNotFound::throwException();
        }
        return $module;
    }
}

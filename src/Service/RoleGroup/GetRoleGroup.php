<?php

namespace App\Service\RoleGroup;

use App\Entity\Main\RoleGroup;
use App\Model\Exception\RoleGroup\RoleGroupNotFound;
use App\Repository\Main\RoleGroupRepository;


class GetRoleGroup
{
    private RoleGroupRepository $roleGroupRepository;

    public function __construct(RoleGroupRepository $roleGroupRepository)
    {
        $this->roleGroupRepository = $roleGroupRepository;
    }

    public function __invoke(string $uuid): RoleGroup
    {
        // $roleGroup = $this->roleGroupRepository->find($id);
        $roleGroup = $this->roleGroupRepository->findOneByUuid($uuid);

        if (!$roleGroup) {
            RoleGroupNotFound::throwException();
        }
        return $roleGroup;
    }
}

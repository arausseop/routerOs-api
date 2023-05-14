<?php

namespace App\Service\RoleGroup;


class DeleteRoleGroup
{
    private GetRoleGroup $getRoleGroup;
    private RoleGroupManager $roleGroupManager;

    public function __construct(GetRoleGroup $getRoleGroup, RoleGroupManager $roleGroupManager)
    {
        $this->getRoleGroup = $getRoleGroup;
        $this->roleGroupManager = $roleGroupManager;
    }

    public function __invoke(string $uuid, ?int $customerId = null)
    {
        $RoleGroup = ($this->getRoleGroup)($uuid, $customerId);
        $this->roleGroupManager->delete($RoleGroup);
    }
}

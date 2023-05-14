<?php

namespace App\Service\RoleGroup;

use App\Entity\Main\RoleGroup;
use App\Repository\Main\RoleGroupRepository;
use App\Service\TelnetRoleGroupApi\RoleGroupApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RoleGroupManager
{

    public function __construct(
        private EntityManagerInterface $em,
        private RoleGroupRepository $roleGroupRepository,
        private HttpClientInterface $httpClient,
    ) {
        $this->em = $em;
        $this->roleGroupRepository = $roleGroupRepository;
    }

    public function find(int $id): ?RoleGroup
    {
        return $this->roleGroupRepository->find($id);
    }

    public function getRepository(): RoleGroupRepository
    {
        return $this->roleGroupRepository;
    }

    public function create(): RoleGroup
    {
        $roleGroup = new RoleGroup();
        return $roleGroup;
    }

    public function persist(RoleGroup $roleGroup): RoleGroup
    {
        $this->em->persist($roleGroup);
        return $roleGroup;
    }

    public function save(RoleGroup $roleGroup): RoleGroup
    {
        $this->em->persist($roleGroup);
        $this->em->flush();
        return $roleGroup;
    }

    public function reload(RoleGroup $roleGroup): RoleGroup
    {
        $this->em->refresh($roleGroup);
        return $roleGroup;
    }

    public function delete(RoleGroup $roleGroup)
    {
        $this->em->remove($roleGroup);
        $this->em->flush();
    }

    public function getEntityReference($entityNameEspace, $entityId)
    {
        return $this->em->getReference($entityNameEspace, $entityId);
    }
}

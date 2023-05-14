<?php

namespace App\Service\Module;

use App\Entity\Main\Module;
use App\Repository\Main\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;

class ModuleManager
{

    private $em;
    private $moduleRepository;

    public function __construct(
        EntityManagerInterface $em,
        ModuleRepository $moduleRepository
    ) {
        $this->em = $em;
        $this->moduleRepository = $moduleRepository;
    }

    public function find(int $id): ?Module
    {
        return $this->moduleRepository->find($id);
    }

    public function getRepository(): ModuleRepository
    {
        return $this->moduleRepository;
    }

    public function create(): Module
    {
        $module = new Module();
        return $module;
    }

    public function persist(Module $module): Module
    {
        $this->em->persist($module);
        return $module;
    }

    public function save(Module $module): Module
    {
        // die(var_dump($module->getPassword()));
        $this->em->persist($module);

        $this->em->flush();
        return $module;
    }

    public function reload(Module $module): Module
    {
        $this->em->refresh($module);
        return $module;
    }

    public function delete(Module $module)
    {
        $this->em->remove($module);
        $this->em->flush();
    }

    public function getEntityReference($entityNameEspace, $entityId)
    {
        return $this->em->getReference($entityNameEspace, $entityId);
    }
}

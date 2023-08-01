<?php

namespace App\Service\Router\RouterSetting\IpPool;

use App\Entity\Main\Router;
use App\Repository\Main\RouterRepository;
use App\Service\Mikrotic\MikroticConnectManager;
use App\Service\TelnetRouterApi\RouterApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RouterIpPoolManager
{

    public function __construct(
        private readonly MikroticConnectManager $mikroticConnectManager
    ) {
        // $this->em = $em;
        // $this->routerRepository = $routerRepository;
    }

    // public function find(int $id): ?Router
    public function find(int $id)
    {
        // return $this->routerRepository->find($id);
    }

    public function create(): Router
    {
        $router = new Router();
        return $router;
    }

    public function save(Router $router): Router
    {
        // $this->em->persist($router);
        // $this->em->flush();
        return $router;
    }

    public function delete(Router $router)
    {
        // $this->em->remove($router);
        // $this->em->flush();
    }
}

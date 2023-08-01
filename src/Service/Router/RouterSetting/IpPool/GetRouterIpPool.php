<?php

namespace App\Service\Router\RouterSetting\IpPool;

use App\Entity\Main\Router;
use App\Model\Exception\Router\RouterNotFound;
use App\Repository\Main\RouterRepository;
use App\Service\Mikrotic\MikroticConnectManager;

class GetRouterIpPool
{


    public function __construct(
        private readonly MikroticConnectManager $mikroticConnectManager
    ) {
    }

    // public function __invoke(string $uuid, ?int $customerId = null): Router
    public function __invoke(string $poolId, ?int $customerId = null)
    {
        // $router = $this->routerRepository->findOneByUuid($uuid);

        // if (!$router) {
        //     RouterNotFound::throwException();
        // }
        // return $router;
    }
}

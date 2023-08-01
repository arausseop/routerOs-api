<?php

namespace App\Service\Router\RouterSetting\IpPool;

use App\Service\Router\GetRouter;
use App\Service\Router\RouterManager;

class DeleteRouterIpPool
{
    private GetRouter $getRouter;
    private RouterManager $routerManager;

    public function __construct(GetRouter $getRouter, RouterManager $routerManager)
    {
        $this->getRouter = $getRouter;
        $this->routerManager = $routerManager;
    }

    public function __invoke(string $uuid, ?int $customerId = null)
    {
        $Router = ($this->getRouter)($uuid, $customerId);
        $this->routerManager->delete($Router);
    }
}

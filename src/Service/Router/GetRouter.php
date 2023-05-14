<?php

namespace App\Service\Router;

use App\Entity\Main\Router;
use App\Model\Exception\Router\RouterNotFound;
use App\Repository\Main\RouterRepository;


class GetRouter
{
    private RouterRepository $routerRepository;

    public function __construct(RouterRepository $routerRepository)
    {
        $this->routerRepository = $routerRepository;
    }

    public function __invoke(string $uuid, ?int $customerId = null): Router
    {
        $router = $this->routerRepository->findOneByUuid($uuid);

        if (!$router) {
            RouterNotFound::throwException();
        }
        return $router;
    }
}

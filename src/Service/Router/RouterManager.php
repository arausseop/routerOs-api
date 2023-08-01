<?php

namespace App\Service\Router;

use App\Entity\Main\Router;
use App\Repository\Main\RouterRepository;
use App\Service\TelnetRouterApi\RouterApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RouterManager
{

    public function __construct(
        private EntityManagerInterface $em,
        private RouterRepository $routerRepository,
        private HttpClientInterface $httpClient,
    ) {
        $this->em = $em;
        $this->routerRepository = $routerRepository;
    }

    public function find(int $id): ?Router
    {
        return $this->routerRepository->find($id);
    }

    public function getRepository(): RouterRepository
    {
        return $this->routerRepository;
    }

    public function create(): Router
    {
        $router = new Router();
        return $router;
    }

    public function persist(Router $router): Router
    {
        $this->em->persist($router);
        return $router;
    }

    public function save(Router $router): Router
    {
        $this->em->persist($router);
        $this->em->flush();
        return $router;
    }

    public function reload(Router $router): Router
    {
        $this->em->refresh($router);
        return $router;
    }

    public function delete(Router $router)
    {
        $this->em->remove($router);
        $this->em->flush();
    }

    public function getEntityReference($entityNameEspace, $entityId)
    {
        return $this->em->getReference($entityNameEspace, $entityId);
    }

    public function checkRouterConnection(string $ipAddress, string $login, string $password)
    {
        $API = new RouterApi;
        $connection = $API->connect(
            $ipAddress, //'186.4.186.97:8728', 
            $login, // admin 
            $password //password
        );
        if (!$connection) {
            return $connection;
        }
        $identity = $API->comm('/system/identity/print')[0]['name'];
        $API->disconnect();
        return $identity;
    }

    public function checkApiRestRouterConnection(string $ipAddress, string $login, string $password)
    {
        try {
            $base64AuthorizationWhitPassword = base64_encode(sprintf('%s:%s', $login, $password));
            // $base64AuthorizationWhitoutPassword = base64_encode(sprintf('%s:%s', $login, ''));

            $response = $this->httpClient->request(
                'GET',
                "https://$ipAddress:16969/rest/system/identity",
                [
                    'verify_peer' => false,
                    'headers' => [
                        // 'Authorization' => sprintf('Basic %s', $base64AuthorizationWhitoutPassword),
                        'Authorization' => sprintf('Basic %s', $base64AuthorizationWhitPassword),
                    ]
                ]
            );
            // dd($response->getStatusCode());
            return ['statusCode' => $response->getStatusCode(), 'content' => json_decode($response->getContent(), true)];
        } catch (\Throwable $th) {
            return ['statusCode' => $th->getCode(), 'error' => $th->getMessage()];
        }
    }
}

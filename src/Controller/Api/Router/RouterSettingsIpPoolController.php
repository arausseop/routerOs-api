<?php


namespace App\Controller\Api\Router;

use App\Model\Exception\Router\RouterConnectionError;
use App\Service\Mikrotic\CustomMikroticConnect;
use App\Service\Mikrotic\MikroticConnectManager;
use App\Service\Mikrotic\MikroticHttpClientInterface;
use App\Service\Mikrotic\MikroticOptions;
use App\Service\Router\RouterFormProcessor;
use App\Service\Router\RouterManager;
use App\Service\Router\DeleteRouter;
use App\Service\Router\GetRouter;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[Route(path: "/api/routers-settings/ip/pools", name: "routers_settings_ip_pools")]
class RouterSettingsIpPoolController extends AbstractFOSRestController
{

  public function __construct(
    private readonly MikroticHttpClientInterface $customHttpClient,
    private readonly CustomMikroticConnect $mikroticConnect,
    private readonly MikroticOptions $mikroticOptions,
    private readonly MikroticConnectManager $mikroticConnectManager
  ) {
  }

  #[Rest\Get('', name: 'ip_pool_list')]
  #[QueryParam(name: 'routerUuid', strict: false, nullable: true, allowBlank: true, description: 'Router Uuid')]

  public function getAction(

    string $routerUuid,
    GetRouter $getRouter,
    ParamFetcherInterface $paramFetcher,
  ) {
    $params = $paramFetcher->all();

    try {
      $router = ($getRouter)($routerUuid);

      $mikroticClient = $this->mikroticConnectManager->getMikroticClientConnect(
        [
          'username'  => $router->getLogin(),
          // 'password' => 'asd',
          'password' => $router->getPassword(),
          'ipAddress' => $router->getIpAddress(),
          'verificationPeer'   => false
        ]
      );

      $mikroticIpPool = $mikroticClient->Ip;
      $responseIpPools = $mikroticIpPool->pool([])->json;

      if ($responseIpPools['statusCode'] !== 200) {
        RouterConnectionError::throwException($mikroticClient);
      }

      return new JsonResponse([
        "items" => $responseIpPools['body'],
        "totalCount" => count($responseIpPools['body'])
      ]);
    } catch (\Throwable $exception) {
      return new JsonResponse(
        [
          'code' => $exception->getCode(),
          'message' => $exception->getMessage()
        ],
        Response::HTTP_BAD_REQUEST
      );
    }
  }

  #[Rest\Get('/{ipPoolId}', name: '_show')]
  #[QueryParam(name: 'routerUuid', strict: false, nullable: true, allowBlank: true, description: 'Router Uuid')]
  public function getSingleAction(
    string $ipPoolId,
    string $routerUuid,
    GetRouter $getRouter,
    SerializerInterface $serializer
  ) {
    try {
      $router = ($getRouter)($routerUuid);

      $mikroticClient = $this->mikroticConnectManager->getMikroticClientConnect(
        [
          'username'  => $router->getLogin(),
          // 'password' => 'asd',
          'password' => $router->getPassword(),
          'ipAddress' => $router->getIpAddress(),
          'verificationPeer'   => false
        ]
      );
      $mikroticIpPool = $mikroticClient->Ip;

      $responseIpPools = $mikroticIpPool->getIpPool(['id' => $ipPoolId])->json;
    } catch (Exception $exception) {
      return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }
    $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]);
    return JsonResponse::fromJsonString($data);
    return $router;
  }

  // #[Rest\Post('', name: '_create')]
  // public function postAction(
  //   RouterFormProcessor $routerFormProcessor,
  //   Request $request,
  //   SerializerInterface $serializer,
  // ) {
  //   try {
  //     [$router, $error] = ($routerFormProcessor)($request);
  //     $statusCode = $router ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
  //     $data = $router ?? $error;

  //     if ($statusCode !== 201) {
  //       return View::create($data, $statusCode);
  //     } else {
  //       $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]) ?? $error;
  //       return JsonResponse::fromJsonString($data, $statusCode);
  //     }
  //   } catch (\Throwable $exception) {

  //     return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
  //   }
  // }

  // #[Rest\Put('/{uuid}', name: '_full_update')]
  // public function editAction(
  //   string $uuid,
  //   RouterFormProcessor $router,
  //   Request $request,
  //   SerializerInterface $serializer
  // ) {
  //   try {
  //     [$router, $error] = ($router)($request, $uuid);
  //     $statusCode = $router ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
  //     $data = $router ?? $error;

  //     if ($statusCode !== 201) {
  //       return View::create($data, $statusCode);
  //     } else {
  //       $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]) ?? $error;
  //       return JsonResponse::fromJsonString($data, $statusCode);
  //     }
  //   } catch (Throwable $exception) {
  //     return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
  //   }
  // }

  // #[Route('/check-router-connection/{uuid}',  name: 'app_check_router_connection')]
  // public function checkRouterConnectionAction(
  //   string $uuid,
  //   GetRouter $getRouter,
  //   RouterManager $routerManager,
  //   SerializerInterface $serializer
  // ) {
  //   try {

  //     $router = ($getRouter)($uuid);

  //     $connection  = $routerManager->checkApiRestRouterConnection($router->getIpAddress(), $router->getLogin(), $router->getPassword());

  //     if ($connection['statusCode'] !== 200) {
  //       RouterConnectionError::throwException($connection);
  //     }

  //     return JsonResponse::fromJsonString(true);
  //   } catch (\Throwable $exception) {
  //     return JsonResponse::fromJsonString($exception->getMessage(), Response::HTTP_BAD_REQUEST);
  //   }
  // }

  // #[Rest\Delete(path: '/{uuid}', name: '_delete')]
  // public function deleteAction(string $uuid, DeleteRouter $deleteRouter)
  // {
  //   try {
  //     ($deleteRouter)($uuid);
  //   } catch (Throwable $exception) {
  //     return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
  //   }
  //   return View::create(null, Response::HTTP_NO_CONTENT);
  // }
}

<?php


namespace App\Controller\Api\Router;

use App\Model\Exception\Router\RouterConnectionError;
use App\Model\Router\RouterRepositoryCriteria;
use App\Security\EmailVerifier;
use App\Service\Router\RouterFormProcessor;
use App\Service\Router\RouterManager;
use App\Service\Router\DeleteRouter;
use App\Service\Router\GetRouter;
use App\Service\Paginator\PaginatorLink;
use App\Service\Shared\Paginator\PaginatorLinks;
use App\Service\TelnetRouterApi\RouterApi;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Throwable;



#[Route(path: "/api/routers", name: "routers")]
class RouterController extends AbstractFOSRestController
{
    // /**
    //  * @Rest\Get(path="/", name="_list")
    //  * @QueryParam(name="page", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="page number")
    //  * @QueryParam(name="itemsPerPage", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="items Per Page")
    //  * @QueryParam(name="searchText", strict=false, nullable=true, allowBlank=true, description="Text to search")
    //  * @param ParamFetcherInterface $paramFetcher
    //  * @param $page
    //  */
    #[Rest\Get('', name: '_list')]
    #[QueryParam(name: 'page', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'page number')]
    #[QueryParam(name: 'itemsPerPage', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'items Per Page')]
    #[QueryParam(name: 'searchText', strict: false, nullable: true, allowBlank: true, description: 'Text to search')]

    public function getAction(
        $page,
        $itemsPerPage,
        $searchText,
        RouterManager $routerManager,
        SerializerInterface $serializer,
        PaginatorInterface $pager,
        Request $request,
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $entityManager,
        PaginatorLinks $pagintaorLinks,
    ) {
        $params = $paramFetcher->all();

        $criteria = new RouterRepositoryCriteria(
            $searchText,
            $page ? \intval($page) : 1,
            $itemsPerPage ? \intval($itemsPerPage) : 10
        );
        $paginatedData = $routerManager->getRepository()->findByCriteria($criteria, $pager);

        $data = $serializer->serialize($paginatedData['items'], 'json', ['groups' => ['routers']]);

        $routeName = $request->attributes->get('_route');

        return new JsonResponse([
            "items" => json_decode($data),
            "totalCount" => $paginatedData['totalCount'],
            "rels" => $pagintaorLinks->generateRefPaginatorLinks(
                $routeName,
                $page ? $page : 1,
                $itemsPerPage ? $itemsPerPage : 10,
                $searchText ? $searchText : null
            )
        ]);
    }

    #[Rest\Get('/{uuid}', name: '_show')]
    public function getSingleAction(
        string $uuid,
        GetRouter $getRouter,
        SerializerInterface $serializer
    ) {
        try {
            $router = ($getRouter)($uuid);
        } catch (Exception $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]);
        return JsonResponse::fromJsonString($data);
        return $router;
    }

    #[Rest\Post('', name: '_create')]
    public function postAction(
        RouterFormProcessor $routerFormProcessor,
        Request $request,
        SerializerInterface $serializer,
    ) {
        try {
            [$router, $error] = ($routerFormProcessor)($request);
            $statusCode = $router ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $router ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (\Throwable $exception) {

            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Put('/{uuid}', name: '_full_update')]
    public function editAction(
        string $uuid,
        RouterFormProcessor $router,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {
            [$router, $error] = ($router)($request, $uuid);
            $statusCode = $router ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $router ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $data = $serializer->serialize($router, 'json', ['groups' => ['routers']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/check-router-connection/{uuid}',  name: 'app_check_router_connection')]
    public function checkRouterConnectionAction(
        string $uuid,
        GetRouter $getRouter,
        RouterManager $routerManager,
        SerializerInterface $serializer
    ) {
        try {

            $router = ($getRouter)($uuid);

            $connection  = $routerManager->checkApiRestRouterConnection($router->getIpAddress(), $router->getLogin(), $router->getPassword());

            if ($connection['statusCode'] !== 200) {
                RouterConnectionError::throwException($connection);
            }

            return JsonResponse::fromJsonString(true);
        } catch (\Throwable $exception) {
            return JsonResponse::fromJsonString($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Delete(path: '/{uuid}', name: '_delete')]
    public function deleteAction(string $uuid, DeleteRouter $deleteRouter)
    {
        try {
            ($deleteRouter)($uuid);
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}

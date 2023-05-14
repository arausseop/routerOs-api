<?php


namespace App\Controller\Api\Module;

use App\Model\Module\ModuleRepositoryCriteria;
use App\Service\Module\ModuleFormProcessor;
use App\Service\Module\ModuleManager;
use App\Service\Module\DeleteModule;
use App\Service\Module\GetModule;
use App\Service\Paginator\PaginatorLink;
use App\Service\Shared\Paginator\PaginatorLinks;
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
use Symfony\Component\HttpFoundation\Response;
use Throwable;



#[Route(path: "/api/modules", name: "modules")]
class ModuleController extends AbstractFOSRestController
{
    // /**
    //  * @Rest\Get(path="/", name="_list")
    //  * @QueryParam(name="page", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="page number")
    //  * @QueryParam(name="itemsPerPage", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="items Per Page")
    //  * @QueryParam(name="searchText", strict=false, nullable=true, allowBlank=true, description="Text to search")
    //  * @param ParamFetcherInterface $paramFetcher
    //  * @param $page
    //  */
    #[Rest\Get('/', name: '-list')]
    #[QueryParam(name: 'page', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'page number')]
    #[QueryParam(name: 'itemsPerPage', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'items Per Page')]
    #[QueryParam(name: 'searchText', strict: false, nullable: true, allowBlank: true, description: 'Text to search')]

    public function getAction(
        $page,
        $itemsPerPage,
        $searchText,
        ModuleManager $moduleManager,
        SerializerInterface $serializer,
        PaginatorInterface $pager,
        Request $request,
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $entityManager,
        PaginatorLinks $pagintaorLinks,
    ) {
        $params = $paramFetcher->all();

        $criteria = new ModuleRepositoryCriteria(
            $searchText,
            $page ? \intval($page) : 1,
            $itemsPerPage ? \intval($itemsPerPage) : 10
        );
        $paginatedData = $moduleManager->getRepository()->findByCriteria($criteria, $pager);

        $data = $serializer->serialize($paginatedData['items'], 'json', ['groups' => ['module']]);

        $routeName = $request->attributes->get('_route');
        // $routeParams = array();
        // $test = $pagintaorLinks->generateRefPaginatorLinks(
        //     'modules_list',
        //     $page,
        //     $itemsPerPage,
        //     $searchText
        // );

        //dd($test);

        // $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
        //     return $this->generateUrl($route, array_merge(
        //         $routeParams,
        //         array('page' => $targetPage, "limit" => "3")
        //     ));
        // };

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

    #[Rest\Get('/{uuid}')]
    public function getSingleAction(
        string $uuid,
        GetModule $getModule,
        SerializerInterface $serializer
    ) {
        try {
            $module = ($getModule)($uuid);
        } catch (Exception $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        $data = $serializer->serialize($module, 'json', ['groups' => ['module']]);
        return JsonResponse::fromJsonString($data);
        return $module;
    }

    #[Rest\Post('/')]
    public function postAction(
        ModuleFormProcessor $module,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {
            [$module, $error] = ($module)($request);
            $statusCode = $module ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $module ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $data = $serializer->serialize($module, 'json', ['groups' => ['module']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (\Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Put('/{uuid}')]
    public function editAction(
        string $uuid,
        ModuleFormProcessor $module,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {
            [$module, $error] = ($module)($request, $uuid);
            $statusCode = $module ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $module ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $data = $serializer->serialize($module, 'json', ['groups' => ['module']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Delete(path: '/{uuid}')]
    public function deleteAction(string $uuid, DeleteModule $deleteModule)
    {
        try {
            ($deleteModule)($uuid);
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}

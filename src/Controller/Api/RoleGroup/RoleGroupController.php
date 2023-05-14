<?php


namespace App\Controller\Api\RoleGroup;

use App\Model\Exception\RoleGroup\RoleGroupConnectionError;
use App\Model\RoleGroup\RoleGroupRepositoryCriteria;
use App\Security\EmailVerifier;
use App\Service\RoleGroup\RoleGroupFormProcessor;
use App\Service\RoleGroup\RoleGroupManager;
use App\Service\RoleGroup\DeleteRoleGroup;
use App\Service\RoleGroup\GetRoleGroup;
use App\Service\Paginator\PaginatorLink;
use App\Service\Shared\Paginator\PaginatorLinks;
use App\Service\TelnetRoleGroupApi\RoleGroupApi;
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



#[Route(path: "/api/role-groups", name: "roleGroups")]
class RoleGroupController extends AbstractFOSRestController
{
    // /**
    //  * @Rest\Get(path="/", name="_list")
    //  * @QueryParam(name="page", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="page number")
    //  * @QueryParam(name="itemsPerPage", requirements="\d+", strict=false, nullable=true, allowBlank=true, description="items Per Page")
    //  * @QueryParam(name="searchText", strict=false, nullable=true, allowBlank=true, description="Text to search")
    //  * @param ParamFetcherInterface $paramFetcher
    //  * @param $page
    //  */
    #[Rest\Get('/', name: '_list')]
    #[QueryParam(name: 'page', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'page number')]
    #[QueryParam(name: 'itemsPerPage', requirements: '\d+', strict: false, nullable: true, allowBlank: true, description: 'items Per Page')]
    #[QueryParam(name: 'searchText', strict: false, nullable: true, allowBlank: true, description: 'Text to search')]

    public function getAction(
        $page,
        $itemsPerPage,
        $searchText,
        RoleGroupManager $roleGroupManager,
        SerializerInterface $serializer,
        PaginatorInterface $pager,
        Request $request,
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $entityManager,
        PaginatorLinks $pagintaorLinks,
    ) {
        $params = $paramFetcher->all();

        $criteria = new RoleGroupRepositoryCriteria(
            $searchText,
            $page ? \intval($page) : 1,
            $itemsPerPage ? \intval($itemsPerPage) : 10
        );
        $paginatedData = $roleGroupManager->getRepository()->findByCriteria($criteria, $pager);

        $data = $serializer->serialize($paginatedData['items'], 'json', ['groups' => ['roleGroups']]);

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
        GetRoleGroup $getRoleGroup,
        SerializerInterface $serializer
    ) {
        try {
            $roleGroup = ($getRoleGroup)($uuid);
        } catch (Exception $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        $data = $serializer->serialize($roleGroup, 'json', ['groups' => ['roleGroups']]);
        return JsonResponse::fromJsonString($data);
        return $roleGroup;
    }

    // #[Rest\Post('', name: '_create')]
    // public function postAction(
    //     RoleGroupFormProcessor $roleGroupFormProcessor,
    //     Request $request,
    //     SerializerInterface $serializer,
    // ) {
    //     try {
    //         [$roleGroup, $error] = ($roleGroupFormProcessor)($request);
    //         $statusCode = $roleGroup ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
    //         $data = $roleGroup ?? $error;

    //         if ($statusCode !== 201) {
    //             return View::create($data, $statusCode);
    //         } else {
    //             $data = $serializer->serialize($roleGroup, 'json', ['groups' => ['roleGroups']]) ?? $error;
    //             return JsonResponse::fromJsonString($data, $statusCode);
    //         }
    //     } catch (\Throwable $exception) {

    //         return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }

    // #[Rest\Put('/{uuid}', name: '_full_update')]
    // public function editAction(
    //     string $uuid,
    //     RoleGroupFormProcessor $roleGroup,
    //     Request $request,
    //     SerializerInterface $serializer
    // ) {
    //     try {
    //         [$roleGroup, $error] = ($roleGroup)($request, $uuid);
    //         $statusCode = $roleGroup ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
    //         $data = $roleGroup ?? $error;

    //         if ($statusCode !== 201) {
    //             return View::create($data, $statusCode);
    //         } else {
    //             $data = $serializer->serialize($roleGroup, 'json', ['groups' => ['roleGroups']]) ?? $error;
    //             return JsonResponse::fromJsonString($data, $statusCode);
    //         }
    //     } catch (Throwable $exception) {
    //         return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }

    #[Rest\Delete(path: '/{uuid}', name: '_delete')]
    public function deleteAction(string $uuid, DeleteRoleGroup $deleteRoleGroup)
    {
        try {
            ($deleteRoleGroup)($uuid);
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}

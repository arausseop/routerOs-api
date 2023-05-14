<?php


namespace App\Controller\Api\User;

use App\Model\User\UserRepositoryCriteria;
use App\Security\EmailVerifier;
use App\Service\User\UserFormProcessor;
use App\Service\User\UserManager;
use App\Service\User\DeleteUser;
use App\Service\User\GetUser;
use App\Service\Paginator\PaginatorLink;
use App\Service\Shared\Paginator\PaginatorLinks;
use App\Service\User\UserChangePasswordFormProcessor;
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



#[Route(path: "/api/users", name: "users")]
class UserController extends AbstractFOSRestController
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
        UserManager $userManager,
        SerializerInterface $serializer,
        PaginatorInterface $pager,
        Request $request,
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $entityManager,
        PaginatorLinks $pagintaorLinks,
    ) {
        $params = $paramFetcher->all();

        $criteria = new UserRepositoryCriteria(
            $searchText,
            $page ? \intval($page) : 1,
            $itemsPerPage ? \intval($itemsPerPage) : 10
        );
        $paginatedData = $userManager->getRepository()->findByCriteria($criteria, $pager);

        $data = $serializer->serialize($paginatedData['items'], 'json', ['groups' => ['users']]);

        $routeName = $request->attributes->get('_route');
        // $routeParams = array();
        // $test = $pagintaorLinks->generateRefPaginatorLinks(
        //     'users_list',
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

    #[Rest\Get('/{uuid}', name: '_show')]
    public function getSingleAction(
        string $uuid,
        GetUser $getUser,
        SerializerInterface $serializer
    ) {
        try {
            $user = ($getUser)($uuid);
        } catch (Exception $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
        $data = $serializer->serialize($user, 'json', ['groups' => ['users']]);
        return JsonResponse::fromJsonString($data);
        return $user;
    }

    #[Rest\Post('', name: '_create')]
    public function postAction(
        UserFormProcessor $user,
        Request $request,
        SerializerInterface $serializer,
        EmailVerifier $emailVerifier
    ) {
        try {
            [$user, $error] = ($user)($request);
            $statusCode = $user ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $user ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $emailVerifier->sendEmailConfirmation(
                    'api_user_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@mikrotic.cl', 'Soporte Mikrotic'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $data = $serializer->serialize($user, 'json', ['groups' => ['users']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (\Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Put('/{uuid}', name: '_full_update')]
    public function editAction(
        string $uuid,
        UserFormProcessor $user,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {
            [$user, $error] = ($user)($request, $uuid);
            $statusCode = $user ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $user ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                $data = $serializer->serialize($user, 'json', ['groups' => ['users']]) ?? $error;
                return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Post('/user_auth/who_am_i', name: '_who_im_i')]
    public function whoImIAction(
        GetUser $getUser,
        SerializerInterface $serializer
    ) {
        try {
            $user = $this->getUser();
        } catch (Exception $exception) {
            return View::create(
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
        $data = $serializer->serialize($user, 'json', ['groups' => ['whoimi']]);
        return JsonResponse::fromJsonString($data);
        return $user;
    }

    #[Rest\Put('/change-password/{uuid}', name: 'change_password')]
    public function changePasswordAction(
        string $uuid,
        UserChangePasswordFormProcessor $userChangePasswordFormProcessor,
        Request $request,
        SerializerInterface $serializer
    ) {
        try {
            [$user, $error] = ($userChangePasswordFormProcessor)($request, $uuid);
            $statusCode = $user ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $user ?? $error;

            if ($statusCode !== 201) {
                return View::create($data, $statusCode);
            } else {
                return View::create(null, Response::HTTP_NO_CONTENT);
                // $data = $serializer->serialize($user, 'json', ['groups' => ['users']]) ?? $error;
                // return JsonResponse::fromJsonString($data, $statusCode);
            }
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Rest\Delete(path: '/{uuid}', name: '_delete')]
    public function deleteAction(
        string $uuid,
        DeleteUser $deleteUser
    ) {
        try {
            ($deleteUser)($uuid);
            return View::create(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $exception) {
            return View::create($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

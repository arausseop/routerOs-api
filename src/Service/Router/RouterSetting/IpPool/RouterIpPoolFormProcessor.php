<?php

namespace App\Service\Router\RouterSetting\IpPool;

use App\Form\Model\Router\RouterDto;
use App\Form\Type\Router\RouterFormType;
use App\Model\Exception\Router\RouterConnectionError;
use App\Service\Router\GetRouter;
use App\Service\Router\RouterManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RouterIpPoolFormProcessor
{

    public function __construct(
        private GetRouter $getRouter,
        private RouterManager $routerManager,
        private FormFactoryInterface $formFactory,
        private HttpClientInterface $httpClient,

    ) {
        $this->getRouter = $getRouter;
        $this->routerManager = $routerManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request, ?string $routerUuid = null): array
    {

        $router = null;
        $routerDto = null;

        if ($routerUuid === null) {
            $routerDto = RouterDto::createEmpty();
        } else {
            $router = ($this->getRouter)($routerUuid);
            $routerDto = RouterDto::createFromRouter($router);
        }

        $content = json_decode($request->getContent(), true);
        $form = $this->formFactory->create(RouterFormType::class, $routerDto);
        $form->submit($content);


        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }

        if (!$form->isValid()) {
            return [null, $form];
        }

        if ($router === null) {

            $routerIdentity = $this->checkConnectionRouter($routerDto);

            if ($routerIdentity['statusCode'] !== 200) {
                RouterConnectionError::throwException($routerIdentity);
            }

            $router = $this->routerManager->create();
            $router->setIdentity($routerIdentity['content']['name']);
            $router->setConnect(true);
            $router->setCreatedAtAutomatically();
        } else {

            if (
                $routerDto->getIpAddress() !== $router->getIpAddress() ||
                $routerDto->getLogin() !== $router->getLogin() ||
                $routerDto->getPassword() !== $router->getPassword()
            ) {
                $routerIdentity = $this->checkConnectionRouter($routerDto);

                if ($routerIdentity['statusCode'] !== 200) {
                    RouterConnectionError::throwException($routerIdentity);
                }
                $router->setConnect(true);
                $router->setUpdatedAtAutomatically();
            }
        }

        $router->setName($routerDto->getName());
        $router->setDescription($routerDto->getDescription());
        $router->setIpAddress($routerDto->getIpAddress());
        $router->setLogin($routerDto->getLogin());
        $router->setPassword($routerDto->getPassword());


        $this->routerManager->save($router);
        $this->routerManager->reload($router);
        return [$router, null];
    }

    private function checkConnectionRouter(RouterDto $routerDto)
    {
        $routerIdentity = $this->routerManager->checkApiRestRouterConnection($routerDto->getIpAddress(), $routerDto->getLogin(), $routerDto->getPassword());

        if ($routerIdentity['statusCode'] !== 200) {
            RouterConnectionError::throwException($routerIdentity);
        }
        return $routerIdentity;
    }
}

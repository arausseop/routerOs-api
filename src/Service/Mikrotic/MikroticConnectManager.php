<?php

namespace App\Service\Mikrotic;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TomTom\Telematics\MikroticOptions;

class MikroticConnectManager
{

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MikroticHttpClientInterface $customHttpClient,
        private readonly CustomMikroticConnect $mikroticConnect,
        private readonly MikroticOptions $mikroticOptions,
    ) {
    }

    public function getMikroticClientConnect(array $customerMikroticParamsConnect)
    {
        // $mikroticApiUrl = $this->container->getParameter('mikrotic.api.base');

        $mikroticOptions = new $this->mikroticOptions([
            'account'  => $customerMikroticParamsConnect['account'],
            'username' => $customerMikroticParamsConnect['username'],
            'password' => $customerMikroticParamsConnect['password'],
            'apikey'   => $customerMikroticParamsConnect['apikey'],
            'lang'     => 'en',
        ]);

        return (new $this->mikroticConnect($this->customHttpClient, $mikroticOptions));
    }
}

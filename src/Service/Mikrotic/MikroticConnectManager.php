<?php

namespace App\Service\Mikrotic;

use Symfony\Component\DependencyInjection\ContainerInterface;


class MikroticConnectManager
{

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MikroticHttpClientInterface $customHttpClient,
        private readonly CustomMikroticConnect $mikroticConnect,
        private readonly MikroticOptions $mikroticOptions,
        private readonly MikroticHeaderOptions $mikroticHeaderOptions,
    ) {
    }

    public function getMikroticClientConnect(array $customerMikroticParamsConnect)
    {
        // $mikroticApiUrl = $this->container->getParameter('mikrotic.api.base');

        $mikroticOptions = new $this->mikroticOptions([
            'username' => $customerMikroticParamsConnect['username'],
            'password' => $customerMikroticParamsConnect['password'],
            'ipAddress' => $customerMikroticParamsConnect['ipAddress'],
            'verificationPeer' => $customerMikroticParamsConnect['verificationPeer'],
        ]);

        $microticCustomerHeaders = new $this->mikroticHeaderOptions([
            'customerHeaders' => [
                'username' => $customerMikroticParamsConnect['username'],
                'password' => $customerMikroticParamsConnect['password'],
                'ipAddress' => $customerMikroticParamsConnect['ipAddress'],
                'verificationPeer' => $customerMikroticParamsConnect['verificationPeer'],
            ]
        ]);


        return (new $this->mikroticConnect($this->customHttpClient, $mikroticOptions, $microticCustomerHeaders));
    }
}

<?php

namespace App\Service\Mikrotic;

use chillerlan\TinyCurl\RequestOptions;
use TomTom\Telematics\HTTP\HTTPClientInterface;
use TomTom\Telematics\HTTP\TinyCurlClient;

class HttpClientTest extends TinyCurlClient
{

    /**
     * @var \chillerlan\TinyCurl\Request
     */
    protected $request;

    /**
     * TinyCurlClient constructor.
     *
     * @param \chillerlan\TinyCurl\RequestOptions $requestOptions
     */
    public function __construct(RequestOptions $requestOptions)

    {
        parent::__construct($requestOptions);
    }
}

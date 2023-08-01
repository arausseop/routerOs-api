<?php

namespace App\Service\Mikrotic;

use App\Model\Exception\Mikrotic\MikroticException;
use ReflectionClass;


use App\Service\Mikrotic\Endpoints\{
    Ip,
};

/**
 * @property Ip $Ip
 */
class CustomMikroticConnect
{

    const INTERFACES = [
        Ip::class,
    ];

    /**
     * @var array
     */
    protected $method_map = [];

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var \App\Service\Mikrotic\MikroticHttpClientInterface
     */
    protected $http;

    /**
     * @var \App\Service\Mikrotic\MikroticOptions
     */
    protected $options;

    /**
     * @var \App\Service\Mikrotic\MikroticHeaderOptions
     */
    protected $customHeaders;

    /**
     * MikroticConnect constructor.
     *
     * @param \App\Service\Mikrotic\MikroticHttpClientInterface $http
     * @param \App\Service\Mikrotic\MikroticOptions             $options
     * @param \App\Service\Mikrotic\MikroticHeaderOptions       $customerHeaders
     */

    //TODO: Get CustomHeaderOptions
    public function __construct(MikroticHttpClientInterface $http, MikroticOptions $options = null, MikroticHeaderOptions $customHeaders = null)
    {

        $this->http    = $http;
        $this->options = $options ?? [];
        $this->customHeaders = $customHeaders ?? [];

        $this->mapApiMethods();
    }

    /**
     * @param string $interface
     *
     * @return \App\Service\Mikrotic\MikroticEndpoint
     * @throws \TomTom\Telematics\MikroticException
     */
    public function __get(string $interface): MikroticEndpoint
    {
        $interface = __NAMESPACE__ . '\\Endpoints\\' . $interface;


        if (array_key_exists($interface, $this->method_map)) {

            return new MikroticEndpoint($this->http, $this->options, $interface, $this->customHeaders);
        }
        throw new MikroticException('interface does not exist: ' . $interface);
    }

    /**
     * Maps the MikroticConnectInterface methods -> Interface name
     */
    protected function mapApiMethods()
    {

        foreach (self::INTERFACES as $interface) {
            $reflection_class = new ReflectionClass($interface);
            foreach ($reflection_class->getMethods() as $method) {
                $this->method_map[$interface][$method->name] = $reflection_class->getConstant($method->name);
                $this->method_map[$interface]['apiEndpoint'] = $reflection_class->getConstant('API_ENDPOINT');
            }
        }
        #		file_put_contents(__DIR__.'/../config/mikrotic_interface.json', json_encode($this->method_map, JSON_PRETTY_PRINT));
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->endpoint ? $this->method_map[$this->endpoint] : $this->method_map;
    }
}

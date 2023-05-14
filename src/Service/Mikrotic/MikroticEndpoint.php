<?php

/**
 *
 * @filesource   MikroticEndpoint.php
 * @created      19.03.2017
 * @package      TomTom\Telematics
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace App\Service\Mikrotic;

use App\Model\Exception\MikroticException;


// use TomTom\Telematics\HTTP\HTTPClientInterface;

/**
 * Class MikroticEndpoint
 */
class MikroticEndpoint extends CustomMikroticConnect
{


	/**
	 * __anonymous constructor.
	 *
	 * @param \App\Service\Mikrotic\MikroticHttpClientInterface $http
	 * @param \App\Service\Mikrotic\MikroticOptions $options
	 * @param string $interface
	 */
	public function __construct(MikroticHttpClientInterface $http, MikroticOptions $options, string $interface)
	{
		parent::__construct($http, $options);

		$this->endpoint = $interface;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 *
	 * @todo rate limits
	 *
	 * @return MikroticResponse
	 * @throws MikroticException
	 */
	// public function __call(string $method, array $arguments): MikroticResponse
	public function __call(string $method, array $arguments)
	{

		if (isset($this->method_map[$this->endpoint]) && array_key_exists($method, $this->method_map[$this->endpoint])) {

			$_method = $this->method_map[$this->endpoint][$method];

			// ...limiter

			// method parameters
			$params = isset($arguments[0]) && !empty($arguments[0]) ? $arguments[0] : [];

			if (isset($_method['required']) && !empty($_method['required']) && empty(array_intersect($_method['required'], array_keys($params)))) {
				throw new MikroticException('required parameter missing');
			}

			// date range
			if (isset($arguments[1]) && !empty($arguments[1])) {
				$params = array_merge($params, $this->dateRangeFilter($arguments[1]));
			}

			$params = array_merge($this->options->__toArray(), $params, ['action' => $method]);

			foreach ($params as $param => $value) {

				if (is_null($value)) {
					unset($params[$param]);
				} elseif (is_bool($value)) {
					$params[$param] = $value ? 'true' : 'false';
				}
			}

			return $this->http->request($params);
		}

		throw new MikroticException('method does not exist in class "' . $this->endpoint . '": ' . $method);
	}
}

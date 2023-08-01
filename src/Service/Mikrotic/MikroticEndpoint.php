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

use App\Model\Exception\Mikrotic\MikroticException;


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
  public function __construct(
    MikroticHttpClientInterface $http,
    MikroticOptions $options,
    string $interface,
    MikroticHeaderOptions $customHeaders
  ) {
    parent::__construct($http, $options, $customHeaders);

    $this->endpoint = $interface;
  }

  /**
   * @param string $apiMethod
   * @param array $arguments
   *
   * @todo rate limits
   *
   * @return MikroticResponse
   * @throws MikroticException
   */
  // public function __call(string $apiMethod, array $arguments): MikroticResponse
  public function __call(string $apiMethod, array $arguments)
  {

    if (isset($this->method_map[$this->endpoint]) && array_key_exists($apiMethod, $this->method_map[$this->endpoint])) {

      $_method = $this->method_map[$this->endpoint][$apiMethod];

      $_apiEndpoint = $this->method_map[$this->endpoint]['apiEndpoint'];
      $_httpMethod = $this->method_map[$this->endpoint][$apiMethod]['method'];

      // dd($_method, $_apiEndpoint);
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

      //! Resquest params
      $params = array_merge($this->options->__toArray(), $params);

      foreach ($params as $param => $value) {

        if (is_null($value) || in_array($param, ['username', 'password', 'ipAddress', 'verificationPeer'])) {
          unset($params[$param]);
        } elseif (is_bool($value)) {
          $params[$param] = $value ? 'true' : 'false';
        }
      }


      $headers = $this->getCustomRequestHeader($this->options->__toArray(), $this->customHeaders);

      //TODO: Settings headers request
      // dd([$apiMethod, $_apiEndpoint]);
      return $this->http->request(
        [
          'apiMethod' => $apiMethod,
          'apiEndpoint' => $_apiEndpoint,
          'ipAddress' => $this->options->ipAddress,
          'addressPort' => 16969, //TODO: setcustom address port
        ],
        $params,
        $_httpMethod,
        null,
        $headers
      );
    }

    throw new MikroticException('method does not exist in class "' . $this->endpoint . '": ' . $apiMethod);
  }

  private function getCustomRequestHeader($options, $headerOptions): array
  {
    // dd($options);
    $base64AuthorizationWhitPassword = base64_encode(sprintf('%s:%s', $options['username'], $options['password']));
    return [
      'Authorization' => sprintf('Basic %s', $base64AuthorizationWhitPassword)
    ];
  }
}

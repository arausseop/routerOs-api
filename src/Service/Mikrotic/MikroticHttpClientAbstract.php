<?php

/**
 * Class HTTPClientAbstract
 *
 * @filesource   HTTPClientAbstract.php
 * @created      14.03.2017
 * @package      TomTom\Telematics\HTTP
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace App\Service\Mikrotic;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 *
 */
abstract class MikroticHttpClientAbstract implements MikroticHttpClientInterface
{

    /**
     * @param array $headers
     *
     * @return array
     */
    public function normalizeHeaders(array $headers): array
    {
        $normalized_headers = [];

        foreach ($headers as $key => $val) {

            if (is_numeric($key)) {
                $header = explode(':', $val, 2);

                if (count($header) === 2) {
                    $key = $header[0];
                    $val = $header[1];
                } else {
                    continue;
                }
            }

            $key = ucfirst(strtolower($key));

            $normalized_headers[$key] = trim($key) . ': ' . trim($val);
        }

        return $normalized_headers;
    }

    public function getUrlApiBaseWithEndpointInterface($apiEndpointParams): string
    {
        return "https://" . $apiEndpointParams['ipAddress'] . ":" . $apiEndpointParams['addressPort'] . "/rest/" . $apiEndpointParams['apiEndpoint'] . "/" . $apiEndpointParams['apiMethod'];
        // return self::API_BASE . '' . $apiEndpoint;
    }
}

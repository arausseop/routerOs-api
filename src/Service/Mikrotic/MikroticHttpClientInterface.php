<?php

namespace App\Service\Mikrotic;

use App\Model\Exception\HttpRequestException;
use Symfony\Contracts\HttpClient\ResponseInterface;
use TomTom\Telematics\MikroticResponse;

interface MikroticHttpClientInterface
{

    const API_BASE = 'https://186.4.186.97:16969/rest/';

    /**
     * @param array  $params
     * @param string $method
     * @param mixed  $body
     * @param array  $headers
     *
     * @return App\Service\Mikrotic\MikroticResponse
     */
    // public function request(array $params = [], string $method = 'GET', $body = null, array $headers = []): MikroticResponse;
    public function request(array $apiEndpoint, array $params = [], string $method = 'GET', $body = null, array $headers = []);
    // public function request(string $method, string $url, array $options = []): ResponseInterface;
}

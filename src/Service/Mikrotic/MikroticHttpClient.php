<?php

namespace App\Service\Mikrotic;

use App\Service\Mikrotic\MikroticHttpClientInterface;
use chillerlan\TinyCurl\URL;
use chillerlan\TinyCurl\URLException;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use TomTom\Telematics\MikroticException;
use TomTom\Telematics\MikroticResponse;

class MikroticHttpClient extends MikroticHttpClientAbstract
{
    /**
     * @var Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private SymfonyHttpClientInterface $httpClient;

    /**
     * mikroticHttpClient constructor.
     *
     * @param Symfony\Contracts\HttpClient\HttpClientInterface $httpClient
     * @param array $requestOptions
     */
    public function __construct(SymfonyHttpClientInterface $httpClient, array $requestOptions = [])
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param array  $params
     * @param string $method
     * @param mixed  $body
     * @param array  $headers
     *
     * @return \TomTom\Telematics\MikroticResponse
     * @throws \TomTom\Telematics\MikroticException
     */
    public function request(array $params = [], string $method = 'GET', $body = null, array $headers = []): MikroticResponse
    {

        try {
            $url = new URL(self::API_BASE, $params, $method, $body, $this->normalizeHeaders($headers));
        } catch (URLException $e) {
            throw new MikroticException('invalid URL: ' . $e->getMessage());
        }


        $response = $this->httpClient->request(
            $url->method,
            $url->url,
            [
                'headers' => $url->headers,
                'query' => $url->params
            ]
        );


        // $response = $this->httpClient->fetch($url);

        return new MikroticResponse([
            'headers' => $response->getHeaders(),
            'body'    => $response->GetContent(),
        ]);
    }
}

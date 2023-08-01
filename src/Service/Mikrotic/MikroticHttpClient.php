<?php

namespace App\Service\Mikrotic;

use App\Model\Exception\Mikrotic\MikroticException;
use App\Model\Exception\System\UrlException;
use App\Service\Mikrotic\MikroticHttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;



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
     * @return \App\Service\Mikrotic\MikroticResponse
     * @throws \App\Model\Exception\Mikrotic\MikroticException
     */
    public function request(array $apiEndpointParams = [], array $params = [], string $method = 'GET', $body = null, array $headers = []): MikroticResponse
    {
        try {

            dd([$apiEndpointParams, $params]);
            $mikroticApiUrl = $this->getUrlApiBaseWithEndpointInterface($apiEndpointParams);

            $url = new Url($mikroticApiUrl, $params, $method, $body, $this->normalizeHeaders($headers));
        } catch (UrlException $e) {
            throw new MikroticException('invalid URL: ' . $e->getMessage());
        }

        // dd($url->headers);
        $headersMap = array_values(

            $url->headers
        );

        $response = $this->httpClient->request(
            $url->method,
            $url->url,
            [
                'verify_peer' => false,
                'headers' => $headersMap,
                'query' => $url->params
            ]
        );

        // $response = $this->httpClient->fetch($url);
        // dd(['devolverResponse', $response]);

        return new MikroticResponse([
            'statusCode' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body'    => $response->GetContent(),
        ]);
    }
}

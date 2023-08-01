<?php

namespace App\Service\Mikrotic;

use App\Model\Exception\System\UrlException;

/**
 * @todo -> Container
 *
 * @property string url
 * @property string method
 * @property string scheme
 * @property string host
 * @property string port
 * @property string path
 * @property string query
 * @property string fragment
 * @property array  params
 * @property array  parsedquery
 * @property mixed  body
 * @property array  headers
 */
class Url
{

    const URL_PARTS       = ['scheme', 'host', 'port', 'path', 'query', 'fragment'];
    const ALLOWED_SCHEMES = ['http', 'https'];
    const ALLOWED_METHODS = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT'];

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $fragment;

    /**
     * @var array
     */
    protected $parsedquery = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * URL constructor.
     *
     * @param string $url
     * @param array  $params
     * @param string $method
     * @param mixed  $body
     * @param array  $headers
     */
    public function __construct(string $url, array $params = [], string $method = 'GET', $body = null, array $headers = [])
    {
        $this->url     = $url;
        $this->params  = $params;
        $this->body    = $body;
        $this->headers = $headers;
        $this->method  = strtoupper($method);

        $this->parse();
    }

    /**
     * @throws App\Model\Exception\System;
     * @return void
     */
    protected function parse()
    {
        $url = parse_url($this->url);

        foreach (self::URL_PARTS as $part) {
            $this->{$part} = $url[$part] ?? null;
        }

        if ($this->scheme !== null && !in_array(strtolower($this->scheme), self::ALLOWED_SCHEMES, true)) {
            throw new UrlException('invalid scheme: ' . $this->scheme);
        }

        if (!$this->host) {
            throw new UrlException('no host given');
        }

        if (!in_array($this->method, self::ALLOWED_METHODS, true)) {
            throw new UrlException('invalid method: ' . $this->method);
        }

        if ($this->query !== null) {
            parse_str($this->query, $this->parsedquery);
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return false;
    }

    /**
     * @return string URL with merged params
     */
    public function __toString(): string
    {
        return $this->mergeParams();
    }

    /**
     * @return string
     */
    public function originalParams(): string
    {
        return $this->getURL($this->parsedquery);
    }

    /**
     * @return string
     */
    public function overrideParams(): string
    {
        return $this->getURL($this->params);
    }

    /**
     * @return string
     */
    public function mergeParams(): string
    {
        return $this->getURL(array_merge($this->parsedquery, $this->params));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function getURL(array $params): string
    {
        $url = '';

        if ($this->scheme) {
            $url .= $this->scheme . ':';
        }

        if ($this->host) {
            $url .= '//' . $this->host;

            if ($this->port) {
                $url .= ':' . $this->port;
            }
        }

        if ($this->path) {
            $url .= $this->path;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }
}

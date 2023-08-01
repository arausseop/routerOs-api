<?php

namespace App\Service\Mikrotic;

/**
 * @property array          $headers
 * @property string         $body
 * @property statusCode     $statusCode
 * @property bool|\stdClass $json
 */
class MikroticResponse extends Container
{

    /**
     * @var array
     */
    protected $headers = [];

    protected $request_time;
    protected $response_time;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $statusCode;


    public function __get(string $property)
    {
        if ($property === 'json') {
            // return json_decode($this->body);
            return [
                'body' => json_decode($this->body),
                'statusCode' => $this->statusCode
            ];
        } else if (property_exists($this, $property)) {
            return $this->{$property};
        }

        return false;
    }
}

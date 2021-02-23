<?php


namespace coreapi\Utilities\Http\Curl;


use coreapi\Utilities\Contracts\Curl\mixedF;
use coreapi\Utilities\Contracts\Curl\ResponseContract;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseContract
{
    /**
     * The HTTP Response implementation.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Create a new Response instance.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Check if the call is successful by the response code.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->response->getStatusCode() >= 200 && $this->response->getStatusCode() < 300;
    }

    /**
     * Get the response body.
     *
     * @param  bool $toArray
     * @return array|object|null
     */
    public function getBody($toArray = false)
    {
        return json_decode($this->response->getBody(), $toArray);
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getOriginBody() {
        return $this->response->getBody();
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name) {
        return $this->response->getHeaderLine($name);
    }

    /**
     * Handle dynamic method calls into the Response.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->response, $method], $parameters);
    }

    /**
     * Get the response status code
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }
}
<?php
namespace coreapi\Utilities\Http\Curl\Endpoints;

use coreapi\Utilities\Contracts\Curl\EndpointContract;
use coreapi\Utilities\Contracts\Curl\ServiceContract;

abstract class Endpoint implements EndpointContract
{
    /**
     * The endpoint URI.
     *
     * @var string
     */
    protected $uri;
    /**
     * The endpoint method.
     *
     * @var string
     */
    protected $method;
    /**
     * The endpoint's options.
     *
     * @var array
     */
    protected $options;
    /**
     * The Service implementation.
     *
     * @var ServiceContract
     */
    protected $service;
    /**
     * Create a new Endpoint instance.
     *
     * @param  ServiceContract  $service
     * @param  array  $options
     * @return void
     */
    public function __construct(ServiceContract $service, array $options = [])
    {
        $this->service = $service;
        $this->options = $options;
    }
    /**
     * Get the endpoint URI.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->normalize($this->service->uri()).'/'.$this->normalize($this->uri ?: '');
    }
    /**
     * Get the endpoint method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method ?: 'GET';
    }
    /**
     * Get the endpoint options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * Get the Service implementation in this endpoint.
     *
     * @return ServiceContract
     */
    public function getService()
    {
        return $this->service;
    }
    /**
     * Normalize the given string.
     *
     * @param  string  $string
     * @return string
     */
    protected function normalize($string)
    {
        return trim($string, '/');
    }
}
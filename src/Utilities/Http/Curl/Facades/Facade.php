<?php
namespace coreapi\Utilities\Http\Curl\Facades;

use coreapi\Utilities\Contracts\Curl\ServiceContract;
use RuntimeException;
use coreapi\Utilities\Contracts\Curl\HttpClientContract;

abstract class Facade
{
    /**
     * The service class name.
     *
     * @var string
     */
    protected $service;
    /**
     * The Http Client implementation.
     *
     * @var HttpClientContract
     */
    protected static $httpClient;
    /**
     * Set the HttpClient for the Facade.
     *
     * @param  HttpClientContract  $httpClient
     * @return void
     */
    public static function setHttpClient(HttpClientContract $httpClient)
    {
        static::$httpClient = $httpClient;
    }
    /**
     * Get the service instance.
     *
     * @return ServiceContract
     */
    protected function getService()
    {
        $service = $this->getServiceClassPath();
        return new $service;
    }
    /**
     * Get the service class name.
     *
     * @return string
     */
    protected function getServiceClassName()
    {
        return $this->service ?: last(explode('\\', static::class));
    }
    /**
     * Get the endpoint instance.
     *
     * @param  string  $class
     * @param  array  $parameters
     * @return \App\Contracts\Http\Curl\Endpoint
     */
    protected function getEndpoint($class, array $parameters = [])
    {
        $endpoint = $this->getEndpointClassName($class);
        return new $endpoint($this->getService(), ...$parameters);
    }
    /**
     * Get the full endpoint class name by given class name.
     *
     * @param  string  $class
     * @return string
     */
    protected function getEndpointClassName($class)
    {
        return 'App\Http\Curl\Endpoints\\'.$this->getServiceClassName().'\\'.studly_case($class).'Endpoint';
    }
    /**
     * Handle dynamic method calls into the Facade.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if (! static::$httpClient instanceof HttpClientContract) {
            throw new RuntimeException('httpClient is not an instance of '.HttpClientContract::class.'.');
        }
        $endpoint = $this->getEndpoint($method, $parameters);
        return static::$httpClient->call($endpoint);
    }
    /**
     * Handle dynamic static method calls into the Facade.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, array $parameters)
    {
        return call_user_func_array([new static, $method], $parameters);
    }

    private function getServiceClassPath()
    {
        return config('coreapi.microservices_folder').$this->getServiceClassName().'Service';
    }
}
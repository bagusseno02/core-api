<?php
namespace coreapi\Utilities\Http\Curl;

use Exception;
use GuzzleHttp\RequestOptions;
use coreapi\Utilities\Contracts\Curl\EndpointContract;
use coreapi\Utilities\Exceptions\CircuitBreakerException;
use GuzzleHttp\ClientInterface as GuzzleHttpClientContract;
use coreapi\Utilities\Http\CircuitBreaker\CircuitBreakerInterface;
use coreapi\Utilities\Contracts\Curl\HttpClientContract;

class HttpClient implements HttpClientContract
{
    /**
     * The Guzzle HTTP Client implementation.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;
    /**
     * The circuit breaker implementation.
     *
     * @var CircuitBreakerInterface
     */
    protected $circuitBreaker;

    /**
     * Create a new HttpClient instance.
     *
     * @param  \GuzzleHttp\ClientInterface $httpClient
     * @param  CircuitBreakerInterface     $circuitBreaker
     * @return void
     */
    public function __construct(GuzzleHttpClientContract $httpClient, CircuitBreakerInterface $circuitBreaker)
    {
        $this->httpClient = $httpClient;
        $this->circuitBreaker = $circuitBreaker;
    }

    /**
     * Call an API by the given Endpoint object.
     *
     * @param  EndpointContract $endpoint
     * @param  bool             $wait
     * @return mixed
     *
     * @throws CircuitBreakerException
     */
    public function call(EndpointContract $endpoint, $wait = true)
    {
        $this->checkEndpoint($endpoint);
        $method = $wait ? 'request' : 'requestAsync';
        $result = $this->getClient()->{$method}(
            $endpoint->getMethod(), $endpoint->getUri(), $this->options($endpoint->getOptions())
        );
        return $wait ? new Response($result) : $result;
    }

    /**
     * Call an API by the given Endpoint object asynchronously.
     *
     * @param  EndpointContract $endpoint
     * @return mixed
     *
     * @throws CircuitBreakerException
     */
    public function callAsync(EndpointContract $endpoint)
    {
        return $this->call($endpoint, false);
    }

    /**
     * Get the HTTP Client implementation.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient()
    {
        return $this->httpClient;
    }

    /**
     * Check if the given endpoint is unavailable.
     *
     * @param  EndpointContract $endpoint
     * @return void
     *
     * @throws CircuitBreakerException
     */
    protected function checkEndpoint(EndpointContract $endpoint)
    {
        $key = sha1($endpoint->getUri());
        if ($this->circuitBreaker->tooManyFailures($key, env('CIRCUIT_BREAKER_MAX', 10),
            env('CIRCUIT_BREAKER_DECAY', 1))) {
            throw new CircuitBreakerException('Currently, the server is unavailable. Please try again later.');
        }
    }

    /**
     * Returns the options when call an API.
     *
     * @param  array $options
     * @return array
     */
    protected function options(array $options = [])
    {
        $defaults = [
            RequestOptions::CONNECT_TIMEOUT => env('CONNECT_TIMEOUT', 3),
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => env('TIMEOUT', 5),
        ];
        return array_merge($defaults, $options);
    }

    /**
     * Handle dynamic method calls into the HttpClient.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->getClient(), $method], $parameters);
    }
}
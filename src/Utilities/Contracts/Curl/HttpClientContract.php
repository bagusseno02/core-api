<?php
namespace coreapi\Utilities\Contracts\Curl;


interface HttpClientContract
{
    /**
     * Get the HTTP Client implementation.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient(): \GuzzleHttp\ClientInterface;
    /**
     * Call an API by the given Endpoint object asynchronously.
     *
     * @param  EndpointContract  $endpoint
     * @return mixed
     */
    public function callAsync(EndpointContract $endpoint);

    /**
     * Call an API by the given Endpoint object.
     *
     * @param EndpointContract $endpoint
     * @param  bool            $wait
     * @return mixed
     */
    public function call(EndpointContract $endpoint, $wait = true);
}
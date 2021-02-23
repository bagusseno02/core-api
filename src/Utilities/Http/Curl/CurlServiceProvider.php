<?php

namespace coreapi\Utilities\Http\Curl;


use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use coreapi\Utilities\Http\Curl\Facades\Facade;

class CurlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Facade::setHttpClient($this->app['coreapi\Utilities\Http\Curl\HttpClient']);
        ExceptionHandler::setCircuitBreaker($this->app['coreapi\Utilities\Http\CircuitBreaker\CircuitBreaker']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HttpClient::class, function ($app) {
            return new HttpClient(new Client, $app['coreapi\Utilities\Http\CircuitBreaker\CircuitBreaker']);
        });
        $this->app->alias(HttpClient::class, ' coreapi\Utilities\Contracts\Curl\HttpClientContract');
    }
}
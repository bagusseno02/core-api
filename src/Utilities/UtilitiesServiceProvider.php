<?php

namespace coreapi\Utilities;


use Illuminate\Support\ServiceProvider;
use coreapi\Utilities\Console\Commands\JwtSecretKeyGeneratorCommand;
use coreapi\Utilities\Http\CircuitBreaker\CircuitBreakerServiceProvider;
use coreapi\Utilities\Http\Curl\CurlServiceProvider;
use coreapi\Utilities\Loggers\MonologProvider;

class UtilitiesServiceProvider extends ServiceProvider
{

    //indicate this service will be load in defer way
    protected $defer = true;

    protected $commands = [
        'JwtKeyGenerate' => 'command.jwt.generate',
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupConfig();
        //register rotating daily monolog provider
        $this->app->register(MonologProvider::class);
        $this->app->register(CircuitBreakerServiceProvider::class);
        $this->app->register(CurlServiceProvider::class);
    }

    /**
     * called when register the service provider
     */
    public function register()
    {
        $this->registerCommands($this->commands);
    }

    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            $method = "register{$command}Command";
            call_user_func_array([$this, $method], []);
        }
        $this->commands(array_values($commands));
    }

    protected function registerJwtKeyGenerateCommand()
    {
        $this->app->singleton('command.jwt.generate', function () {
            return new JwtSecretKeyGeneratorCommand();
        });
    }


    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../config/baseapilib.php');
        $this->app->configure('baseapilib');
        $this->mergeConfigFrom($source, 'baseapilib');
    }
}
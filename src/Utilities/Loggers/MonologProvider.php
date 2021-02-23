<?php

namespace coreapi\Utilities\Loggers;


use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

class MonologProvider extends ServiceProvider
{
    /**
     * Configure logging on boot.
     *
     * @return void
     */
    public function boot()
    {
        $maxFiles = config('coreapi.log_retention');

        // Allow the log path to be configurable
        $logPath = config('coreapi.log_path');

        $handlers[] = (new RotatingFileHandler($logPath . "/" . config('coreapi.log_file_name'), $maxFiles))
            ->setFormatter(new LineFormatter(null, null, true, true));

        $this->app['log']->setHandlers($handlers);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
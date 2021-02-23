<?php
namespace coreapi\Utilities\Http\CircuitBreaker;

use Illuminate\Support\Facades\Facade;
/**
 * @see \coreapi\Utilities\Http\CircuitBreaker\CircuitBreaker
 */
class CircuitBreakerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CircuitBreaker::class;
    }
}
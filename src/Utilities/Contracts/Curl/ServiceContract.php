<?php


namespace coreapi\Utilities\Contracts\Curl;


interface ServiceContract
{
    /**
     * Get the micro service's base URI.
     *
     * @return string
     */
    public function uri();

    /**
     * Get the micro service's name.
     *
     * @return string
     */
    public function name();
}
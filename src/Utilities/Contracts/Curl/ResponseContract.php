<?php

namespace coreapi\Utilities\Contracts\Curl;


interface ResponseContract
{
    /**
     * Check if the call is successful by the response code.
     *
     * @return bool
     */
    public function isSuccessful();

    /**
     * Get the response body.
     *
     * @param  bool $toArray
     * @return array|object
     */
    public function getBody($toArray = false);

    /**
     * Get the response status code
     * @return mixedF
     */
    public function getStatusCode();
}
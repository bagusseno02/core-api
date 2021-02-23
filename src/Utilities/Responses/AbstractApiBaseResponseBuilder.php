<?php


namespace coreapi\Utilities\Responses;

abstract class AbstractApiBaseResponseBuilder
{
    abstract function build();
    abstract function showResponse();
}
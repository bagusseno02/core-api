<?php


namespace coreapi\Utilities\Exceptions;


use Throwable;

class ValidationParameterException extends BaseException
{
    public function __construct(
        $errorMessage,
        $errorCode = "VALIDATION.0001",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($errorMessage, $errorCode, $code, $previous);
    }


}
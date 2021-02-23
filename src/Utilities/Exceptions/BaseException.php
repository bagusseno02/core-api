<?php


namespace coreapi\Utilities\Exceptions;


class BaseException extends \Exception
{
    protected $errorCode = "";
    protected $errorMessage = [];

    public function __construct(
        $errorMessage,
        $errorCode = "EXCEPTION.001",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($errorMessage, $code, $previous);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
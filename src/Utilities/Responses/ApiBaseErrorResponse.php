<?php

namespace coreapi\Utilities\Responses;


class ApiBaseErrorResponse extends BaseResponse
{
    protected $errorCode; // for showing the error code
    protected $errorMessage; // for showing the detail error message

    /**
     * ApiBaseErrorResponse constructor.
     * @param $errorCode
     * @param $errorMessage
     */
    public function __construct($errorCode, $errorMessage)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }


}
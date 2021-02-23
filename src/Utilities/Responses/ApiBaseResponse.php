<?php


namespace coreapi\Utilities\Responses;


/**
 * Class ApiBaseResponse
 * @package coreapi\Utilities\Responses
 */
class ApiBaseResponse extends BaseResponse
{

    /**
     * @var bool
     */
    protected $error;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var TransformerAbstract
     */
    protected $dataTransformers;

    /**
     * @var mixed
     */
    protected $dataPaginator;

    /**
     * @var mixed
     */
    protected $meta;
    /**
     * @var ApiBaseErrorResponse[]
     */
    protected $errors;
    /**
     * @var null
     */
    protected $warnings;
    /**
     * @var string[]
     */
    protected $messages;

    protected $message;

    protected $length;

    protected $lastUpdate;

    protected $code;

    protected $dataKey;

    /**
     * ApiBaseResponse constructor.
     * @param bool $error
     * @param string $message
     * @param $data
     * @param $meta
     * @param $errors
     * @param $warnings
     * @param $messages
     */
    public function __construct(
        $error = false,
        $message = "",
        $data = null,
        $meta = null,
        $errors = null,
        $warnings = null,
        $messages = null
    ) {
        $this->error = $error;
        $this->message = $message;
        $this->data = $data;
        $this->meta = $meta;
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->messages = $messages;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return ApiBaseErrorResponse[]|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return null
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return null|string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param bool $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @param null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param null $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @param null $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param null $warnings
     */
    public function setWarnings($warnings)
    {
        $this->warnings = $warnings;
    }

    /**
     * @param null $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return TransformerAbstract
     */
    public function getDataTransformers()
    {
        return $this->dataTransformers;
    }

    /**
     * @param TransformerAbstract $dataTransformers
     */
    public function setDataTransformers($dataTransformers)
    {
        $this->dataTransformers = $dataTransformers;
    }

    /**
     * @return mixed
     */
    public function getDataPaginator()
    {
        return $this->dataPaginator;
    }

    /**
     * @param mixed $dataPaginator
     */
    public function setDataPaginator($dataPaginator)
    {
        $this->dataPaginator = $dataPaginator;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param mixed $lastUpdate
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getDataKey()
    {
        return $this->dataKey;
    }

    /**
     * @param mixed $dataKey
     */
    public function setDataKey($dataKey)
    {
        $this->dataKey = $dataKey;
    }
}
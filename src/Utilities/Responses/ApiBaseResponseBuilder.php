<?php


namespace coreapi\Utilities\Responses;

use Illuminate\Database\Eloquent\Collection;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Serializers\JsonApiSerializer;
use coreapi\Utilities\Serializers\JsonPaginatorApiSerializer;
use coreapi\Utilities\Transformers\BaseApiErrorResponseTransformer;
use coreapi\Utilities\Transformers\BaseApiResponseTransformer;


/**
 * Class ApiBaseResponseBuilder
 * @package coreapi\Utilities\Responses
 */
class ApiBaseResponseBuilder extends AbstractApiBaseResponseBuilder
{
    /**
     * @var ApiBaseResponse|null
     */
    private $response = null;

    private $statusCode = HttpStatusCodes::HTTP_OK;

    /**
     * ApiBaseResponseBuilder constructor.
     */
    public function __construct()
    {
        $this->response = new ApiBaseResponse();
    }

    /**
     * @param $errors
     */
    public function withErrors($errors)
    {
        if ($errors != null && !empty($errors)) {
            $this->response->setError(true);
        }

        $errorData = fractal()
            ->collection($errors)
            ->serializeWith(new JsonApiSerializer())
            ->transformWith(new BaseApiErrorResponseTransformer())->toArray();
        $this->response->setErrors($errorData);
    }

    public function withStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param $data
     */
    public function withData($data)
    {
        $this->response->setData($data);
    }

    public function withDataTransformers($transformers)
    {
        $this->response->setDataTransformers($transformers);
    }

    public function withDataPaginator($paginator)
    {
        $this->response->setDataPaginator($paginator);
    }

    /**
     * @param $warnings
     */
    public function withWarnings($warnings)
    {
        $this->response->setWarnings($warnings);
    }

    /**
     * @param $meta
     */
    public function withMeta($meta)
    {
        $this->response->setMeta($meta);
    }

    /**
     * @param $messages
     */
    public function withMessages($messages)
    {
        $this->response->setMessages($messages);
    }

    public function withMessage($message) {
        $this->response->setMessage($message);
    }

    public function withLength($length) {
        $this->response->setLength($length);
    }

    public function withLastUpdate($lastUpdate) {
        $this->response->setLastUpdate($lastUpdate);
    }

    public function withError($error = false) {
        $this->response->setError($error);
    }

    public function withCode($code = '') {
        $this->response->setCode($code);
    }

    public function withDataKey($dataKey)
    {
        $this->response->setDataKey($dataKey);
    }

    public function withDefaultMessage($code, $message, $error = false, $httpStatusCode = HttpStatusCodes::HTTP_OK) {
        $this->response->setError($error);
        $this->response->setMessage($message);
        $this->response->setCode($code);
        $this->statusCode = $httpStatusCode;
    }

    /**
     * @return ApiBaseResponse|null
     */
    function build()
    {
        if ($this->response->getDataTransformers() != null) {
            $formattedData = $this->response->getData();

            if (is_array($this->response->getData()) || $this->response->getData() instanceof Countable || $this->response->getData() instanceof Collection) {
                if (count($this->response->getData()) > 1) {
                    if ($this->response->getDataPaginator() == null) {
                        $formattedData = fractal()
                            ->collection($formattedData)
                            ->serializeWith(new JsonApiSerializer())
                            ->transformWith($this->response->getDataTransformers())->toArray();
                    } else {
                        $formattedData = fractal()
                            ->collection($formattedData)
                            ->paginateWith($this->response->getDataPaginator())
                            ->serializeWith(new JsonPaginatorApiSerializer())
                            ->transformWith($this->response->getDataTransformers())->toArray();
                    }
                } else {
                    if ($formattedData instanceof Collection || is_array($formattedData)) {
                        if ($this->response->getDataPaginator() == null) {
                            $formattedData = fractal()
                                ->collection($formattedData)
                                ->serializeWith(new JsonPaginatorApiSerializer())
                                ->transformWith($this->response->getDataTransformers())->toArray();
                        } else {
                            $formattedData = fractal()
                                ->collection($formattedData)
                                ->paginateWith($this->response->getDataPaginator())
                                ->serializeWith(new JsonPaginatorApiSerializer())
                                ->transformWith($this->response->getDataTransformers())->toArray();
                        }
                    } else {
                        $formattedData = fractal()
                            ->item($formattedData)
                            ->serializeWith(new JsonApiSerializer())
                            ->transformWith($this->response->getDataTransformers())->toArray();
                    }
                }
            } else {
                $formattedData = fractal()
                    ->item($formattedData)
                    ->serializeWith(new JsonApiSerializer())
                    ->transformWith($this->response->getDataTransformers())->toArray();
            }

            if ((is_array($this->response->getData()) || $this->response->getData() instanceof Countable || $this->response->getData() instanceof Collection) && isset($formattedData['data'])) {
                $this->response->setData($formattedData['data']);
            } else {
                $this->response->setData($formattedData);
            }
            if (isset($formattedData['meta'])) {
                $meta = $formattedData['meta'];
                if (isset($formattedData['links'])) {
                    $meta['links'] = $formattedData['links'];
                }
                $this->response->setMeta($meta);
            }
        }
        return $this->response;
    }

    function showResponse()
    {
        return response()->json(fractal()
            ->item($this->build())
            ->serializeWith(new JsonApiSerializer())
            ->transformWith(new BaseApiResponseTransformer())
            ->toArray(), $this->statusCode);
    }
}
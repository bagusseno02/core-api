<?php


namespace coreapi\Utilities\Transformers;

use coreapi\Utilities\Responses\ApiBaseResponse;
use League\Fractal\TransformerAbstract;

class BaseApiResponseTransformer extends TransformerAbstract
{

    /**
     * Function to do a response mapping
     * @param ApiBaseResponse $response
     * @return array
     */
    public function transform(ApiBaseResponse $response)
    {
        $resp = [];
        $payloadDataKey = 'data';

        $resp['error'] = $response->isError();
        if ($response->getDataKey()) {
            $payloadDataKey = $response->getDataKey();
        }
        if ($response->getCode() != null) {
            $resp['code'] = $response->getCode();
        }
        if ($response->getMessage() != null) {
            $resp['message'] = $response->getMessage();
        }
        if (!is_null($response->getLength())) {
            $resp['length'] = $response->getLength();
        }
        if (!is_null($response->getLastUpdate())) {
            $resp['last_update'] = $response->getLastUpdate();
        }
        if (!is_null($response->getData())) {
            $resp[$payloadDataKey] = $response->getData();
        }
        if ($response->getMessages() != null) {
            $resp['messages'] = $response->getMessages();
        }
        if ($response->getWarnings() != null) {
            $resp['warnings'] = $response->getWarnings();
        }
        if ($response->getErrors() != null) {
            $resp['errors'] = $response->getErrors();
        }
        if ($response->getMeta() != null) {
            $resp['meta'] = $response->getMeta();
        }
        return $resp;
    }
}
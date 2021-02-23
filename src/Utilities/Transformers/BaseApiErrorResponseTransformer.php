<?php


namespace coreapi\Utilities\Transformers;

use coreapi\Utilities\Responses\ApiBaseErrorResponse;
use League\Fractal\TransformerAbstract;

class BaseApiErrorResponseTransformer extends TransformerAbstract
{

    /**
     * Function to do a response mapping
     * @param ApiBaseErrorResponse $response
     * @return array
     */
    public function transform(ApiBaseErrorResponse $response): array
    {
        $resp = [];
        $resp['code'] = $response->getErrorCode();
        $resp['message'] = $response->getErrorMessage();
        return $resp;
    }
}
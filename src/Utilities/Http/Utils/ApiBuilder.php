<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 18/12/18
 * Time: 16.11
 */
namespace coreapi\Utilities\Http\Utils;

use Illuminate\Http\Request;

class ApiBuilder {

    public static function build(Request $request, $data) {
        $requestBuilder = new ApiRequestBuilder();
        $requestBuilder->withAccessToken($request->header('authorization'));
        $requestBuilder->withData($data);
        return $requestBuilder;
    }
}
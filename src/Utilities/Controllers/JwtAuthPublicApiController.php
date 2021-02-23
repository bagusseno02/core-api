<?php


namespace coreapi\Utilities\Controllers;


use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Helpers\EncryptionHelper;
use coreapi\Utilities\Models\UserModel;
use Validator;

class JwtAuthPublicApiController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create a new token
     * @return string
     */
    protected function jwt($params)
    {
        //time return Return current Unix timestamp
        //unixtimestamp.com for show / convert to date

        /*  iat –   timestamp of token issuing.
            jti –   A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
            iss –   A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
            nbf –   Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case,
                    the token will begin to be valid 10 seconds after being issued.
            exp –   Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 3600 seconds after being issued.*/

        $payload = [
            'iss' => "coreapi-jwt-service", // Issuer of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => $params['exp_time'], // Expiration time 1 hour
            'sub' => $params['id'],
            'sess_id' => isset($params['sess_id']) ? $params['sess_id'] : 0,
            'user' => $params['user']
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.

        return JWT::encode($payload, config('coreapi.jwt_secret'));
    }

    public function convertJwtPublicToPrivate($publicParams) {

        $userJson = EncryptionHelper::decryptGeneral($publicParams->user);
        $userJson = \GuzzleHttp\json_decode($userJson);

        $payload = [
            'iss' => "coreapi-jwt-service", // Issuer of the token
            'iat' => $publicParams->iat,
            'exp' => $publicParams->exp,
            'sub' => $publicParams->sub,
            'sess_id' => isset($publicParams->sess_id) ? $publicParams->sess_id : 0,
            'user' => $userJson
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.

        return JWT::encode($payload, config('coreapi.jwt_secret'));
    }

}
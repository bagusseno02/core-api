<?php

namespace coreapi\Utilities\Middlewares;

use App\Models\SessionUserData;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Controllers\SessionSettingController;
use coreapi\Utilities\Http\Curl\Facades\User;

class JwtAuthMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->get('token');

        //check the header
        if (empty($token)) {
            $token = trim(str_replace('Bearer', '', $request->header('Authorization')));
        }

        if (!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => true,
                'message' => 'Token not provided.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        }

        try {
            //handle if token still using user.token
            if (strlen($token) <= 32) {
                //GET TOKEN JWT by OLD TOKEN
                $client = new Client(['base_uri' => env('MICROSERVICE_USER_URI')]);
                $resp = $client->request('GET', 'auth/by-token',[
                    'headers' => [
                        'Authorization' => $request->header('Authorization'),
                        'Content-Type' => 'application/json'
                    ]
                ])->getBody();
                $resp = json_decode($resp);
                $token = $resp->token;
                $request->headers->add(['Authorization' => $token]);
            }
            JWT::$leeway = 120; //add leeway 2 minute
            $credentials = JWT::decode($token, config('coreapi.jwt_secret'), ['HS256']);
            
            $language = config('coreapi.language');
            if($request->header('Accept-Language')){
                $language = $request->header('Accept-Language');
            }else{
                if(isset($credentials->lang) && $credentials->lang){
                    $language = $credentials->lang;
                }
            }
            app('translator')->setLocale($language);
        } catch (ExpiredException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Provided token is expired.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error while decoding token.'
            ], HttpStatusCodes::HTTP_BAD_REQUEST);
        }

        if (getenv('SESSION_SETTING') == 'true') {

            $checkSession = new SessionSettingController($request);
            $checkSession = $checkSession->checkSession($credentials);

            if ($checkSession['error']) {
                return response()->json([
                    'error' => true,
                    'message' => $checkSession['message']
                ], HttpStatusCodes::HTTP_NOT_ACCEPTABLE);
            }

        }

        $request->auth = $credentials;
        $request->Token = isset($checkSession) ? $checkSession['token'] : $token;

        return $next($request);
    }
}

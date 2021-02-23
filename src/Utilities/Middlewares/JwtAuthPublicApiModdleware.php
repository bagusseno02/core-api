<?php

namespace coreapi\Utilities\Middlewares;

use Closure;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Controllers\JwtAuthPublicApiController;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtAuthPublicApiMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $allowedAlgs = ['HS256'];

        $jwtAuthPublicApiController = new JwtAuthPublicApiController($request);

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
            $publicCredentials = JWT::decode($token, config('coreapi.jwt_secret'), $allowedAlgs);

            //generate / convert private token
            $newJwtPrivateToken = $jwtAuthPublicApiController->convertJwtPublicToPrivate($publicCredentials);

            //get credential
            $credentials = JWT::decode($newJwtPrivateToken, config('coreapi.jwt_secret'), $allowedAlgs);

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

        //set auth credential with data from new token
        $request->auth = $credentials;
        $request->Token = $newJwtPrivateToken;
        return $next($request);
    }
}
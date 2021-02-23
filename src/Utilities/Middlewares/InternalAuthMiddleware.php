<?php

namespace coreapi\Utilities\Middlewares;

use Closure;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Exceptions\UnathorizedException;

class InternalAuthMiddleware
{
    /**
     * @param \Illuminate\Http\Request  $request
     * @param Closure $next
     * @return mixed
     * @throws UnathorizedException
     */
    public function handle($request, Closure $next)
    {
        $token = $request->get('token');

        //check the header
        if (empty($token)) {
            $token = trim(str_replace('Basic', '', $request->header('Authorization')));
        }

        if (!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => true,
                'message' => 'Token not provided.'
            ], HttpStatusCodes::HTTP_UNAUTHORIZED);
        }

        if (env('M2M_TOKEN')) {
            if ($token == config('coreapi.m2m_token')) {
                return $next($request);
            } else {
                throw new UnathorizedException('unauthorized');
            }
        }

        if( $request->getUser() == null ||
            $request->getPassword() == null ||
            $request->getUser() != config('coreapi.internal_username') ||
            $request->getPassword() != config('coreapi.internal_password'))
        {
            throw new UnathorizedException('unauthorized');
        }

        return $next($request);
    }
}

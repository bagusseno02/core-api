<?php
/**
 * Created by PhpStorm.
 * User: Sabriyan
 * Date: 4/2/2019
 * Time: 2:50 PM
 */

namespace coreapi\Utilities\Middlewares;

use Closure;

class ResponseMiddleware
{

    public function handle($request, Closure $next)
    {

        $response = $next($request);

        return $response->header('Token', $request->Token)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', '86400')
            ->header('Access-Control-Allow-Headers', 'Content-Type,Authorization,X-Requested-With,DNT,User-Agent,If-Modified-Since,Cache-Control,Range,Token')
            ->header('Access-Control-Expose-Headers', 'Content-Length,Content-Range,Token');
    }

}
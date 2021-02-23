<?php

namespace coreapi\Utilities\Middlewares;

use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Controllers\SessionSettingController;
use coreapi\Utilities\Helpers\StringHelper;
use coreapi\Utilities\Http\Curl\Facades\User;
use coreapi\Utilities\Models\LogGatewayActivity;

class ApiDataLoggerActivityGatewayMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (env('API_DATALOGGER', false)) {
            //get credential
            $credentials = null;
            try {
                $token = $request->get('token');

                //check the header
                if (empty($token)) {
                    $token = trim(str_replace('Bearer', '', $request->header('Authorization')));
                }

                $credentials = JWT::decode($token, config('coreapi.jwt_secret'), ['HS256']);
            } catch (\Exception $ex) { }

            $this->writeDatabase($request, $credentials);
        }

        return $next($request);
    }

    private function writeDatabase(Request $request, $credentials) {
        $agent = new Agent();

        $log = new LogGatewayActivity();
        $log->method = (string) $request->method();

        if (sizeof($request->headers->all() > 0)) {
            $log->headers = json_encode($request->headers->all());
        }

        $log->body = $this->hashSecretData($request, $request->getContent());

        //$log->response = $response->getContent();
        //$log->response_code = $response->getStatusCode();

        $log->endpoint = $request->fullUrl();
        $log->created_date = \Carbon\Carbon::now('Asia/Jakarta');

        if ($request->hasHeader('TraceId')) {
            $log->traceId = $request->header('TraceId');
        }

        //handle other info user agent
        if ($request->hasHeader('user-agent')) {
            $log->user_agent = $request->header('user-agent');
        }

        if ($agent->isDesktop()) {
            $log->user_agent_type = Constant::USER_AGENT_DESKTOP;
        } else if ($agent->is('AndroidOS')) {
            $log->user_agent_type = Constant::USER_AGENT_ANDROID;
        } else if ($agent->is('OS X')) {
            $log->user_agent_type = Constant::USER_AGENT_IOS;
        } else if ($agent->isRobot()) {
            $log->user_agent_type = Constant::USER_AGENT_ROBOT;
        }

        $log->user_agent_device = $agent->device();
        $log->user_agent_browser = $agent->browser();
        $log->user_agent_platform = $agent->platform();

        //get data user is available
        if (!empty($credentials)) {
            $log->user_id = $credentials->user->id;
            $log->user_company_id = $credentials->user->user_company_id;
            $log->company_id = $credentials->user->company_id;
        }

        $log->save();

        return $log;
    }

    private function hashSecretData($request, $content) {

        $isJson = StringHelper::isJSON($content);
        if (!$isJson) { return $content; }

        //handle endpoint login
        $isEndpointLogin = StringHelper::contains($request->fullUrl(), '/login');
        if ($isEndpointLogin) {
            $json = json_decode($content);
            $json->password = app('hash')->make($json->password);
            $content = json_encode($json);
        }

        /*$isEndpointRegister = StringHelper::contains($request->fullUrl(), '/register');
        if ($isEndpointRegister) {
            $json = json_decode($content);
            $json->password = app('hash')->make($json->password);
            $content = json_encode($json);
        }*/

        return $content;
    }
}

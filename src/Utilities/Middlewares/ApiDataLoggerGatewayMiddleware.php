<?php
namespace coreapi\Utilities\Middlewares;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Jenssegers\Agent\Agent;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Helpers\DateHelper;
use coreapi\Utilities\Helpers\StringHelper;
use coreapi\Utilities\Models\LogGateway;
use coreapi\Utilities\Models\LogMicroService;

class ApiDataLoggerGatewayMiddleware
{
    private $startTime;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //generate new trace ID and set to header
        $request->headers->set('TraceId', $this->generateTraceId());

        $this->startTime = microtime(true);
        return $next($request);
    }

    public function terminate(Request $request, $response)
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

            $endTime = microtime(true);

            if (env('API_DATALOGGER_MODE', Constant::LOG_WRITE_FILE) == Constant::LOG_WRITE_FILE) {
                $this->writeFile($request, $response, $credentials, $endTime);
            } else {
                $this->writeDatabase($request, $response, $credentials, $endTime);
            }
        }
    }

    public function writeDatabase(Request $request, $response, $credentials, $endTime) {

        $agent = new Agent();

        $log = new LogGateway();
        $log->method = (string) $request->method();
        $log->body = $this->hashSecretData($request, $request->getContent());
        $log->response = $response->getContent();
        $log->response_code = $response->getStatusCode();
        $log->endpoint = $request->fullUrl();
        $log->duration = number_format($endTime - LUMEN_START, 3);
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

    public function writeFile(Request $request, JsonResponse $response, $credentials, $endTime) {

        $filename = 'api_datalogger_' . date('Y-m-d') . '.log';
        $dataToLog  = 'Time: '   . gmdate("F j, Y, g:i a") . "\n";
        $dataToLog .= 'Duration: ' . number_format($endTime - LUMEN_START, 3) . "\n";
        $dataToLog .= 'Endpoint: '    . $request->fullUrl() . "\n";
        $dataToLog .= 'Method: ' . $request->method() . "\n";
        $dataToLog .= 'Body: '  . $this->hashSecretData($request,$request->getContent()) . "\n";
        $dataToLog .= 'Response: ' . $response->getContent() . "\n";
        $dataToLog .= 'Response Code: ' . $response->getStatusCode() . "\n";

        if ($request->hasHeader('TraceId')) {
            $dataToLog .= 'Trace Id: ' . $request->header('TraceId') . "\n";
        }

        //get data user is available
        if (!empty($credentials)) {
            $dataToLog .= 'UserId: ' .$credentials->user->id . "\n";
            $dataToLog .= 'UserCompanyId: ' .$credentials->user->user_company_id . "\n";
            $dataToLog .= 'CompanyId: ' .$credentials->user->company_id . "\n";
        }

        File::append( storage_path('logs' . DIRECTORY_SEPARATOR . $filename), $dataToLog . "\n" . str_repeat("=", 100) . "\n\n");
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

    private function generateTraceId() {
        return uniqid() .'-'. (int) DateHelper::getMillisecond();
    }
}
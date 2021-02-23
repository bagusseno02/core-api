<?php
namespace coreapi\Utilities\Middlewares;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Helpers\DateHelper;
use coreapi\Utilities\Helpers\StringHelper;
use coreapi\Utilities\Models\LogMicroService;

class ApiDataLoggerMicroServiceMiddleware
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

        $log = new LogMicroService();
        $log->name = env('SERVICE_NAME', '');
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

        $filename = 'api_datalogger.log';

        $dataToLog['milisecond'] = (string) DateHelper::getMillisecond();
        $dataToLog['name'] = env('SERVICE_NAME', '');
        $dataToLog['time'] = \Carbon\Carbon::now('Asia/Jakarta');
        $dataToLog['duration'] = number_format($endTime - LUMEN_START, 3);
        $dataToLog['endpoint'] = $request->fullUrl();
        $dataToLog['method'] = $request->method();
        $dataToLog['response_code'] = $response->getStatusCode();

        //get data content from body json
        if (!empty($request->getContent())) {
            $dataToLog['body'] = json_decode($this->hashSecretData($request,$request->getContent()));
        }

        //get data response
        if (!empty($response->getContent())) {
            $dataToLog['response'] = json_decode($response->getContent());
        }

        //get all headers
        if (!empty($request->headers->all())) {
            $dataToLog['headers'] = $request->headers->all();
        }

        //get data trace ID, trace ID from api gateway
        if ($request->hasHeader('TraceId')) {
            $dataToLog['trace_id'] = $request->header('TraceId');
        }

        //get data user is available
        if (!empty($credentials)) {
            $dataToLog['user']['id'] = $credentials->user->id;
            $dataToLog['user']['user_company_id'] = $credentials->user->user_company_id;
            $dataToLog['user']['company_id'] = $credentials->user->company_id;
        }

        $dataToLog = json_encode($dataToLog);
        File::append( storage_path('logs' . DIRECTORY_SEPARATOR . $filename), $dataToLog ."\n");
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
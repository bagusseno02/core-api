<?php

namespace coreapi\Utilities\Helpers;

use Carbon\Carbon;
use coreapi\Utilities\Constants\Constant;
use coreapi\Utilities\Models\LogMicroService;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogHelper
{

    public static function writeLog(Request $request, $body): bool
    {

        if (!env('APP_DEBUG', false)) {
            return false;
        }

        //get credential
        $credentials = null;
        try {
            $token = $request->get('token');

            //check the header
            if (empty($token)) {
                $token = trim(str_replace('Bearer', '', $request->header('Authorization')));
            }

            $credentials = JWT::decode($token, config('coreapi.jwt_secret'), ['HS256']);
        } catch (Exception $ex) {
        }

        //write log
        if (env('API_DATALOGGER_MODE', Constant::LOG_WRITE_FILE) == Constant::LOG_WRITE_FILE) {
            self::writeToFile($request, $credentials, $body);
        } else {
            self::writeToDatabase($request, $credentials, $body);
        }
    }

    private static function writeToFile(Request $request, $credentials, $body)
    {

        $filename = 'api_datalogger_' . date('Y-m-d') . '.log';
        $dataToLog = 'Time: ' . gmdate("F j, Y, g:i a") . "\n";
        //$dataToLog .= 'Duration: ' . number_format($endTime - LUMEN_START, 3) . "\n";
        $dataToLog .= 'Endpoint: ' . $request->fullUrl() . "\n";
        $dataToLog .= 'Method: ' . $request->method() . "\n";
        $dataToLog .= 'Body: ' . $body . "\n";
        //$dataToLog .= 'Response: ' . $response->getContent() . "\n";
        //$dataToLog .= 'Response Code: ' . $response->getStatusCode() . "\n";

        if ($request->hasHeader('TraceId')) {
            $dataToLog .= 'Trace Id: ' . $request->header('TraceId') . "\n";
        }

        //get data user is available
        if (!empty($credentials)) {
            $dataToLog .= 'UserId: ' . $credentials->user->id . "\n";
            $dataToLog .= 'UserCompanyId: ' . $credentials->user->user_company_id . "\n";
            $dataToLog .= 'CompanyId: ' . $credentials->user->company_id . "\n";
        }

        File::append(storage_path('logs' . DIRECTORY_SEPARATOR . $filename), $dataToLog . "\n" . str_repeat("=", 100) . "\n\n");
    }

    private static function writeToDatabase(Request $request, $credentials, $body)
    {

        $log = new LogMicroService();
        $log->name = env('SERVICE_NAME', '');
        $log->method = (string)$request->method();
        $log->body = $body;
        //$log->response = $response->getContent();
        //$log->response_code = $response->getStatusCode();
        $log->endpoint = $request->fullUrl();
        //$log->duration = number_format($endTime - LUMEN_START, 3);
        $log->created_date = Carbon::now('Asia/Jakarta');

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
}
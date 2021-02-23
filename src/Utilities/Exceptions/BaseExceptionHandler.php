<?php


namespace coreapi\Utilities\Exceptions;


use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use coreapi\Utilities\Constants\HttpStatusCodes;
use coreapi\Utilities\Responses\ApiBaseErrorResponse;
use coreapi\Utilities\Responses\ApiBaseResponseBuilder;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseExceptionHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (!empty(env('SENTRY_LARAVEL_DSN')) && app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $statusCode = HttpStatusCodes::HTTP_BAD_REQUEST;

        if ($e instanceof BaseException) {
            $exceptions = $this->mapcoreapiExceptionToApiErrorResponse($e);
        } elseif ($e instanceof NotFoundHttpException) {
            $statusCode = HttpStatusCodes::HTTP_NOT_FOUND;
            $exceptions = $this->mapNotFoundHttpExceptionToApiErrorResponse($e);
        } else {
            $statusCode = HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR;
            $exceptions = $this->mapExceptionToApiErrorResponse($e);
        }

        if ($e instanceof UnathorizedException || $e instanceof AuthorizationException) {
            $statusCode = HttpStatusCodes::HTTP_UNAUTHORIZED;
        }

        $response = new ApiBaseResponseBuilder();
        $errors = $exceptions;

        //handle for message and code first
        if (sizeof($errors) > 0) {
            $error = reset($errors);
            if (!empty($error)) {
                $response->withCode($error->getErrorCode());
                $response->withMessage($error->getErrorMessage());
            }
        }

        //show all errors
        $response->withErrors($errors);
        $response->withStatusCode($statusCode);
        return $response->showResponse();
    }


    private function mapcoreapiExceptionToApiErrorResponse(BaseException $e)
    {
        $listOfErrors = array();
        if ($e instanceof UnathorizedException) {
            $error = new ApiBaseErrorResponse("AUTH.0001", "Username or Password is wrong.");
            array_push($listOfErrors, $error);
        } else {
            $error = new ApiBaseErrorResponse($e->getErrorCode(), $e->getErrorMessage());
            array_push($listOfErrors, $error);
        }

        return $listOfErrors;
    }

    private function mapExceptionToApiErrorResponse(Exception $e)
    {
        $listOfErrors = array();
        $error = new ApiBaseErrorResponse("EXCPT.500", $e->getMessage());
        array_push($listOfErrors, $error);

        return $listOfErrors;
    }

    public function mapNotFoundHttpExceptionToApiErrorResponse(Exception $e)
    {
        $listOfErrors = array();
        $error = new ApiBaseErrorResponse("EXCPT.404", "Url not found, please check request and log.");
        array_push($listOfErrors, $error);

        return $listOfErrors;
    }
}
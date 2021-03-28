<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception) {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception) {
        $request->headers->set('Accept', 'application/json');
        if ($exception instanceof HttpException) {
            return response([
                'code'=>$exception->getStatusCode(),
                'message'=>$exception->getMessage(),
            ], $exception->getStatusCode(), $exception->getHeaders());
        }
        if ($exception instanceof ValidationException) {
            return response(['code'=>400, 'message'=> $exception->getMessage()], 400);
        }
        if (env('APP_DEBUG'))
            return response(['message'=>$exception->getMessage(), 'code'=>500], 500);
        else return response(['message'=>'Internal Server Error', 'code'=>500], 500);
    }
}

<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof UnauthorizedHttpException) {
            return $this->failed($exception->getMessage(), 401);
        }

        if ($exception instanceof TokenInvalidException) {
            return $this->failed('用户未登录', 401);
        }

        if ($exception instanceof TokenBlacklistedException) {
            return $this->failed('Token已进入黑名单，请使用刷新Token', 401);
        }

        if ($exception instanceof TokenExpiredException) {
            return $this->failed('Token过期且不能刷新', 401);
        }

        if ($exception instanceof ValidationException) {
            return $this->failed($exception->validator->errors()->first(), 400);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->failed('非法请求', 404);
        }

        if ($exception instanceof ApiException) {
            return $this->failed($exception->getMessage(), $exception->getCode());
        }

        return parent::render($request, $exception);
    }
}

<?php

namespace Vanguard\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $e
     * @return mixed|void
     * @throws \Throwable
     */
    public function report(\Throwable $e)
    {
        //code==1 means that it is User's exception. We don't want to report them.
        if ($e->getCode() != 1) {
            parent::report($e);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable $e
     * @return Response
     */
    public function render($request, \Throwable $e)
    {
        if ($request->ajax() && $e->getCode() == 1) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        if ($e instanceof NotFoundHttpException) {
            return redirect('/');
        }
        return parent::render($request, $e);
    }

    private function getMessageFromStatusCode($code)
    {
        return Arr::get(Response::$statusTexts, $code);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json($exception->errors(), $exception->status);
    }
}

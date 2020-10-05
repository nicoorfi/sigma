<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Google\Cloud\ErrorReporting\Bootstrap;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        // Report to stackdriver on app engine
        if (isset($_SERVER['GAE_SERVICE']) && $this->shouldReport($exception)) {
            Bootstrap::exceptionHandler($exception);
        }

        parent::report($exception);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson() || $this->isProxyRequest($request)
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest($exception->redirectTo() ?? route('sign-in'));
    }

    public function isProxyRequest(Request $request): bool
    {
        return $request->getHost() === env('PROXY_DOMAIN');
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): Response
    {
        return parent::render($request, $exception);
    }
}

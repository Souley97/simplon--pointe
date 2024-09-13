<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

function jsonResponse($e, $status, $defaultMessage)
{
    return response()->json(
        ['error' => app()->environment('local') ? $e->getMessage() : $defaultMessage],
        $status
    );
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            // 'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,

        ]);
        $middleware->validateCsrfTokens(except: [
            env('APP_URL') . '/raesearchjspost'
        ]);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (RouteNotFoundException $e) {
            return jsonResponse($e, 404, "Route not found");
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return jsonResponse($e, 404, "Not found");
        });

        $exceptions->render(function (HttpException $e) {
            return jsonResponse($e, $e->getStatusCode(), "HttpException");
        });

        $exceptions->render(function (QueryException $e) {
            return jsonResponse($e, 500, "Query exception");
        });

        $exceptions->render(function (PDOException $e) {
            return jsonResponse($e, 500, "PDO exception");
        });

        $exceptions->render(function (BadMethodCallException $e) {
            return jsonResponse($e, 405, "Bad method call");
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return jsonResponse($e, 404, "Model not found");
        });

        $exceptions->render(function (InvalidArgumentException $e) {
            return jsonResponse($e, 500, "Invalid argument");
        });

        $exceptions->render(function (AuthorizationException $e) {
            return jsonResponse($e, 403, "Unauthorized");
        });

        $exceptions->render(function (AuthenticationException $e) {
            return jsonResponse($e, 401, "Authentication error");
        });
    })->create();

<?php

use App\Http\Middleware\CorsMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e){

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'Error de validación',
                    'error' => $e->errors(),
                ], 422);
            }
            $errorCode = $e->getCode();
            if(!is_int($errorCode) || $errorCode < 100 || $errorCode > 599){
                $errorCode = 500;
            }
            return new JsonResponse([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ],$errorCode
            );
        });
    })->create();

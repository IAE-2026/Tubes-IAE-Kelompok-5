<?php

error_reporting(E_ALL & ~E_DEPRECATED);


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    )
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'apikey' => \App\Http\Middleware\ApiKeyMiddleware::class,
        'jwt' => \App\Http\Middleware\JwtMiddleware::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

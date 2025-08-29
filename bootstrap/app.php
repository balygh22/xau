<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // مهم: ربط ملف API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // تسجيل وسطاء JWT كأسماء مستعارة
        $middleware->alias([
            'jwt.auth'    => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

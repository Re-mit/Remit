<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 내부망만 접근 허용 (Google OAuth 경로는 예외 처리: 방법 A)
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckInternalIp::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

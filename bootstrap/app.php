<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // 예약 내역은 최근 1달치만 유지 (config/reservation.php 참고)
        $schedule->command('reservations:purge-old')
            ->dailyAt('03:10')
            ->timezone('Asia/Seoul');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // 내부망만 접근 허용
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckInternalIp::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

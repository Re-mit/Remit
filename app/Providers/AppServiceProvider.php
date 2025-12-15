<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Windows 환경에서 SSL 인증서 검증 문제 해결 (개발 환경 전용)
        if (app()->environment('local')) {
            $this->app->resolving(Factory::class, function (Factory $factory) {
                $factory->extend('google', function ($app) use ($factory) {
                    $config = $app['config']['services.google'];
                    return $factory->buildProvider(
                        \Laravel\Socialite\Two\GoogleProvider::class,
                        $config
                    )->setHttpClient(new \GuzzleHttp\Client([
                        'verify' => false, // SSL 검증 비활성화 (개발 환경만)
                    ]));
                });
            });
        }
    }
}

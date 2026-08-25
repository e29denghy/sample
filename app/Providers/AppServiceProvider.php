<?php

namespace App\Providers;

use App\Contracts\MediaStorage;
use App\Services\Media\LocalMediaStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MediaStorage::class, LocalMediaStorage::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('password', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('media-uploads', fn (Request $request): Limit => Limit::perMinute(20)
            ->by((string) ($request->user()?->id ?: $request->ip())));
    }
}

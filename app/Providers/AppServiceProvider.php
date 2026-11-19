<?php

namespace App\Providers;

use App\Logic\LicenceVerification;
use App\Logic\NetifydCatalogRepository;
use App\Logic\NetifydLicenseRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LicenceVerification::class, function () {
            return new LicenceVerification(config('repositories.endpoints.enterprise'), config('repositories.endpoints.community'));
        });
        $this->app->singleton(NetifydLicenseRepository::class, function () {
            return new NetifydLicenseRepository(config('netifyd.endpoint'), config('netifyd.api-key'));
        });
        $this->app->singleton(NetifydCatalogRepository::class, function () {
            return new NetifydCatalogRepository(config('netifyd.endpoint'), config('netifyd.api-key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Needed by the OpenTelemetry Laravel instrumentation to trace redis commands
        Redis::enableEvents();

        RateLimiter::for('netifyd', function (Request $request) {
            $hour = now()->hour;

            if ($hour < config('netifyd.rate-limit-start-hour') || $hour >= config('netifyd.rate-limit-end-hour')) {
                return Limit::none()->by($request->ip());
            }

            return Limit::perMinute(config('netifyd.rate-limit'))->by($request->ip());
        });
    }
}

<?php

namespace App\Providers;

use App\Logic\LicenceVerification;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

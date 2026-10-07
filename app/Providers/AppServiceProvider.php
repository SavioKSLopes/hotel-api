<?php

namespace App\Providers;

use App\Services\ReserveService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReserveService::class, function ($app) {
            return new ReserveService;
        });
    }
}

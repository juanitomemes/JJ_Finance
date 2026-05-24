<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Movimiento;
use App\Observers\MovimientoObserver;

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
        Movimiento::observe(MovimientoObserver::class);
    }
}

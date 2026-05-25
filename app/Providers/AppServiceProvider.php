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
        // Forzar HTTPS en producción (Render) para que carguen los estilos
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Movimiento::observe(MovimientoObserver::class);
    }
}

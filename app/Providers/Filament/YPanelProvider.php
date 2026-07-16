<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class YPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Los widgets personalizados (StatsOverview, IngresosGastosChart, PresupuestosChart)
                // son descubiertos automáticamente por discoverWidgets().
                // Se eliminan los widgets genéricos de Filament para un dashboard 100% personalizado.
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // ── PWA: Inyectar manifest + Service Worker en el <head> ──────────
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render(<<<'BLADE'
                    {{-- Web App Manifest --}}
                    <link rel="manifest" href="{{ asset('manifest.json') }}">

                    {{-- Color de barra del sistema (Android Chrome / Edge) --}}
                    <meta name="theme-color" content="#09090b">

                    {{-- Compatibilidad con iOS (Safari) --}}
                    <meta name="apple-mobile-web-app-capable" content="yes">
                    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
                    <meta name="apple-mobile-web-app-title" content="JJ Finance">
                    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
                    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">

                    {{-- Microsoft Tiles (Edge / Windows) --}}
                    <meta name="msapplication-TileColor" content="#09090b">
                    <meta name="msapplication-TileImage" content="{{ asset('icons/icon-192x192.png') }}">

                    {{-- Registro del Service Worker --}}
                    <script>
                        if ('serviceWorker' in navigator) {
                            window.addEventListener('load', () => {
                                navigator.serviceWorker
                                    .register('/sw.js', { scope: '/' })
                                    .then(reg => {
                                        console.log('[PWA] Service Worker registrado. Scope:', reg.scope);

                                        // Detectar actualizaciones del SW y notificar al usuario
                                        reg.addEventListener('updatefound', () => {
                                            const newWorker = reg.installing;
                                            newWorker.addEventListener('statechange', () => {
                                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                                    console.log('[PWA] Nueva versión disponible. Actualizando…');
                                                    newWorker.postMessage({ type: 'SKIP_WAITING' });
                                                    window.location.reload();
                                                }
                                            });
                                        });
                                    })
                                    .catch(err => console.error('[PWA] Error al registrar el Service Worker:', err));
                            });
                        }
                    </script>
                BLADE)
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.hooks.fab-movimiento')->render(),
            );
    }
}

/**
 * Service Worker — Finanzas Personales PWA
 * 
 * Estrategia de caché híbrida:
 *  - Network-First  → Páginas HTML / Livewire (datos siempre frescos)
 *  - Stale-While-Revalidate → Assets estáticos de Vite (JS, CSS, fuentes, imágenes)
 *  - Offline Fallback → Muestra /offline.html si no hay red
 */

const CACHE_VERSION   = 'v1';
const CACHE_NAME      = `finanzas-pwa-${CACHE_VERSION}`;
const OFFLINE_URL     = '/offline.html';

// Recursos que se precargan en la instalación
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/favicon.ico',
    '/manifest.json',
];

// ─── Instalación ─────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    console.log('[SW] Instalando Service Worker…');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_URLS))
            .then(() => {
                console.log('[SW] Recursos iniciales cacheados.');
                return self.skipWaiting();
            })
    );
});

// ─── Activación ──────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    console.log('[SW] Activando Service Worker…');
    event.waitUntil(
        caches.keys().then(cacheNames => Promise.all(
            cacheNames
                .filter(name => name.startsWith('finanzas-pwa-') && name !== CACHE_NAME)
                .map(oldCache => {
                    console.log('[SW] Eliminando caché antigua:', oldCache);
                    return caches.delete(oldCache);
                })
        )).then(() => {
            console.log('[SW] Caché limpia. Service Worker listo.');
            return self.clients.claim();
        })
    );
});

// ─── Intercepción de Peticiones ───────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorar peticiones que no sean GET
    if (request.method !== 'GET') return;

    // Ignorar peticiones a dominios externos (CDN de Filament, Google Fonts, etc.)
    if (url.origin !== location.origin) return;

    // ── Regla 1: Bypass total para Livewire y llamadas API dinámicas ──────────
    // Los saldos financieros SIEMPRE deben venir de la red.
    if (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/sanctum/')
    ) {
        return; // Deja pasar sin interceptar
    }

    // ── Regla 2: Stale-While-Revalidate para assets estáticos compilados ──────
    // Filament y Vite sirven sus assets bajo /build/ y /css/, /js/ de vendor.
    const isStaticAsset = (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/vendor/filament/') ||
        url.pathname.startsWith('/icons/') ||
        /\.(js|css|woff2?|ttf|otf|eot|png|jpg|jpeg|gif|svg|webp|ico)$/i.test(url.pathname)
    );

    if (isStaticAsset) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // ── Regla 3: Network-First con fallback offline para navegación HTML ───────
    event.respondWith(networkFirstWithOfflineFallback(request));
});

// ─── Estrategia: Stale-While-Revalidate ──────────────────────────────────────
async function staleWhileRevalidate(request) {
    const cache  = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const networkFetch = fetch(request).then(response => {
        if (response && response.status === 200) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => null);

    return cached || await networkFetch;
}

// ─── Estrategia: Network-First con fallback a offline.html ───────────────────
async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);
        return response;
    } catch {
        // Si el request era una navegación, devolvemos la página offline
        if (request.mode === 'navigate') {
            const cache = await caches.open(CACHE_NAME);
            return await cache.match(OFFLINE_URL) || new Response(
                '<h1>Sin conexión</h1>',
                { headers: { 'Content-Type': 'text/html' } }
            );
        }
        return new Response('', { status: 503 });
    }
}

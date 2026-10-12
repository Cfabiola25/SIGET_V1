// SIGET Service Worker - PWA & Offline Support
const CACHE_NAME = 'siget-cache-v5';
const PRECACHE_ASSETS = [
  '/',
  '/manifest.json',
  '/build/assets/app-CzqfhJrN.css',
  '/build/assets/app-CMmoNCLt.js',
  '/images/stadium_hero.jpg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS).catch(() => {
        // Continue even if some optional precache items fail
      });
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Skip non-GET requests or streaming endpoints
  if (event.request.method !== 'GET' || url.pathname.includes('/stream') || url.pathname.includes('/_boost/')) {
    return;
  }

  // Network-First with Cache Fallback for HTML and dynamic views
  if (event.request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response.status === 200) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cached) => {
            if (cached) return cached;
            return new Response(
              `<!DOCTYPE html>
              <html lang="es">
              <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Modo Offline | SIGET</title>
                <style>
                  body { font-family: sans-serif; background: #0f172a; color: #f8fafc; text-align: center; padding: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh; }
                  .card { background: #1e293b; border: 1px solid #334155; padding: 2rem; border-radius: 1rem; max-width: 400px; }
                  .btn { display: inline-block; background: #057a55; color: white; padding: 0.75rem 1.5rem; border-radius: 9999px; text-decoration: none; font-weight: bold; margin-top: 1rem; }
                </style>
              </head>
              <body>
                <div class="card">
                  <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">📶 Sin Conexión</h1>
                  <p style="font-size: 0.875rem; color: #94a3b8;">Estás navegando en modo offline. Las acciones en la Consola Arbitral se sincronizarán al recuperar la red.</p>
                  <a href="javascript:location.reload()" class="btn">Reintentar Conexión</a>
                </div>
              </body>
              </html>`,
              { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            );
          });
        })
    );
    return;
  }

  // Cache-First for static assets (images, fonts, css, js)
  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request).then((response) => {
        if (response.status === 200 && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/'))) {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        }
        return response;
      });
    })
  );
});

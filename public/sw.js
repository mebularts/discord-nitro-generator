const CACHE_VERSION = 'solveclone-v2.1';
const CORE_ASSETS = [
  '/',
  '/offline.html',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => cache.addAll(CORE_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, copy));
          return response;
        })
        .catch(async () => {
          const cache = await caches.open(CACHE_VERSION);
          const cachedHome = await cache.match('/');
          return cachedHome || cache.match('/offline.html');
        })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) {
        return cached;
      }
      return fetch(event.request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, copy));
          return response;
        })
        .catch(async () => {
          if (event.request.destination === 'document') {
            const cache = await caches.open(CACHE_VERSION);
            return cache.match('/offline.html');
          }
          return new Response('', { status: 503, statusText: 'Service Unavailable' });
        });
    })
  );
});

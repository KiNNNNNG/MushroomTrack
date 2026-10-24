const CACHE_NAME = 'mushfung-cache-v3';
const STATIC_ASSETS = [
  '/',
  '/css/mushroomtrack.css',
  '/css/auth.css',
  '/js/mushroomtrack.js',
  '/images/logo-emblem.png',
  '/images/logo-full.png',
  '/images/loading-tl.png',
  '/images/loading-tr.png',
  '/images/loading-bl.png',
  '/images/loading-br.png',
  '/images/loading-center-trans.png',
  '/images/loading-screen-clean.png',
  '/images/favicon.png',
  '/images/icon-192.png',
  '/images/icon-512.png',
  '/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_ASSETS).catch(err => console.log('Asset cache error:', err));
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
      );
    })
  );
  return self.clients.claim();
});

self.addEventListener('fetch', event => {
  const req = event.request;
  
  // Don't intercept POST/PUT/DELETE requests in service worker cache directly
  if (req.method !== 'GET') {
    return;
  }

  // Static assets: cache-first with network fallback
  if (req.url.match(/\.(css|js|png|jpg|jpeg|svg|woff2?)$/)) {
    event.respondWith(
      caches.match(req).then(cached => {
        if (cached) return cached;
        return fetch(req).then(res => {
          if (res.status === 200) {
            const clone = res.clone();
            caches.open(CACHE_NAME).then(c => c.put(req, clone));
          }
          return res;
        }).catch(() => cached);
      })
    );
    return;
  }

  // HTML navigation: network-first, fallback to cache if offline
  event.respondWith(
    fetch(req).then(res => {
      if (res.status === 200) {
        const clone = res.clone();
        caches.open(CACHE_NAME).then(c => c.put(req, clone));
      }
      return res;
    }).catch(() => {
      return caches.match(req).then(cached => {
        if (cached) return cached;
        return caches.match('/');
      });
    })
  );
});


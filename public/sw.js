const CACHE_NAME = 'mushfung-cache-v5';

const STATIC_ASSETS = [
  '/offline',
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

const OFFLINE_HTML_FALLBACK = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>MushFungAI - Offline</title>
  <meta name="theme-color" content="#241c16">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #f9f6ee;
      color: #241c16;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px 20px;
      text-align: center;
    }
    .box {
      background: #ffffff;
      border: 1px solid #ece6dd;
      border-radius: 20px;
      padding: 32px 24px;
      max-width: 440px;
      width: 100%;
      box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fef3c7;
      color: #92400e;
      font-size: 11px;
      font-weight: 800;
      padding: 5px 12px;
      border-radius: 20px;
      margin-bottom: 16px;
    }
    .dot {
      width: 8px; height: 8px; border-radius: 50%; background: #d97706;
    }
    h2 { font-size: 22px; font-weight: 800; color: #241c16; margin-bottom: 8px; }
    p { font-size: 13.5px; color: #7c726a; line-height: 1.5; margin-bottom: 24px; }
    .btn {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; height: 46px; border-radius: 10px; font-size: 13px; font-weight: 700;
      text-decoration: none; cursor: pointer; margin-bottom: 10px; border: none;
    }
    .btn-green { background: #205e46; color: #ffffff; }
    .btn-outline { background: #ffffff; color: #241c16; border: 1px solid #dcd5c9; }
    .sync-card {
      margin-top: 18px; padding-top: 18px; border-top: 1px solid #ece6dd;
      font-size: 12px; color: #7c726a; display: none;
    }
  </style>
</head>
<body>
  <div class="box">
    <div class="badge"><div class="dot"></div>OFFLINE MODE ACTIVE</div>
    <h2>MushFungAI Farm Tracking</h2>
    <p>You are currently offline. You can access your cached fruiting batches and record daily checks locally.</p>
    <a href="/worker/monitoring" class="btn btn-green">OPEN FRUITING MONITORING</a>
    <a href="/worker/overview" class="btn btn-outline">OPERATIONS OVERVIEW</a>
    <button type="button" onclick="location.reload()" class="btn btn-outline">RELOAD / RETRY CONNECTION</button>
    <div id="offlineFallbackSync" class="sync-card"></div>
  </div>
  <script>
    try {
      var checks = JSON.parse(localStorage.getItem('mushfung_offline_checks') || '[]');
      if (checks.length > 0) {
        var sc = document.getElementById('offlineFallbackSync');
        sc.style.display = 'block';
        sc.innerHTML = '<strong>' + checks.length + ' check(s) stored on this device.</strong><br>They will sync automatically once an internet connection is restored.';
      }
    } catch(e) {}
    window.addEventListener('online', function() {
      document.body.style.background = '#e6f4ea';
      setTimeout(function() { location.href = '/worker/monitoring'; }, 1000);
    });
  </script>
</body>
</html>`;

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async cache => {
      await Promise.allSettled(
        STATIC_ASSETS.map(async url => {
          try {
            const res = await fetch(url, { redirect: 'follow' });
            if (res.ok || res.status === 200 || res.type === 'opaque') {
              await cache.put(url, res);
            }
          } catch(err) {
            console.warn('SW pre-cache skip for:', url, err);
          }
        })
      );
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

  // Don't intercept non-GET requests in Service Worker cache directly
  if (req.method !== 'GET') {
    return;
  }

  // Static assets (CSS, JS, images, fonts, icons, manifest): cache-first with network fallback
  if (req.url.match(/\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ico|json)$/)) {
    event.respondWith(
      caches.match(req).then(cached => {
        if (cached) return cached;
        return fetch(req)
          .then(res => {
            if (res.status === 200 || res.type === 'opaque') {
              const clone = res.clone();
              caches.open(CACHE_NAME).then(c => c.put(req, clone));
            }
            return res;
          })
          .catch(() => cached || new Response('', { status: 404 }));
      })
    );
    return;
  }

  // HTML Page Navigation: Network-first, with resilient multi-tier fallback
  const isNav = req.mode === 'navigate' ||
                req.destination === 'document' ||
                (req.headers.get('accept') && req.headers.get('accept').includes('text/html'));

  if (isNav) {
    event.respondWith(
      fetch(req)
        .then(networkRes => {
          if (networkRes && networkRes.status === 200) {
            const copy = networkRes.clone();
            caches.open(CACHE_NAME).then(c => {
              c.put(req, copy);
              try {
                const u = new URL(req.url);
                c.put(u.pathname, networkRes.clone());
              } catch(e) {}
            });
          }
          return networkRes;
        })
        .catch(async () => {
          // Device is offline!
          // 1. Try exact matched request
          let matched = await caches.match(req);
          if (matched) return matched;

          // 2. Try matched URL pathname
          try {
            const u = new URL(req.url);
            matched = await caches.match(u.pathname);
            if (matched) return matched;
          } catch(e) {}

          // 3. If navigating to root or home or monitoring, check worker cached pages
          const cachedMonitoring = await caches.match('/worker/monitoring');
          if (cachedMonitoring) return cachedMonitoring;

          const cachedOverview = await caches.match('/worker/overview');
          if (cachedOverview) return cachedOverview;

          // 4. Try dedicated /offline page
          const cachedOffline = await caches.match('/offline');
          if (cachedOffline) return cachedOffline;

          // 5. Hard-coded Embedded Fallback (100% guarantee no ERR_FAILED)
          return new Response(OFFLINE_HTML_FALLBACK, {
            status: 200,
            headers: {
              'Content-Type': 'text/html; charset=utf-8',
              'Cache-Control': 'no-store'
            }
          });
        })
    );
    return;
  }

  // Other GET requests: network-first, cache fallback
  event.respondWith(
    fetch(req)
      .then(res => {
        if (res.status === 200) {
          const clone = res.clone();
          caches.open(CACHE_NAME).then(c => c.put(req, clone));
        }
        return res;
      })
      .catch(() => caches.match(req))
  );
});

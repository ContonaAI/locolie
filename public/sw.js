// locolie service worker — app-shell cache (network-first) + web push.
const CACHE = 'golocal-v2';

self.addEventListener('install', (e) => {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(['/m', '/icon.svg'])));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  const { request } = e;
  // Never cache the API — always go to network so data is live.
  if (request.method !== 'GET' || new URL(request.url).pathname.startsWith('/api')) return;

  e.respondWith(
    fetch(request)
      .then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((c) => c.put(request, copy)).catch(() => {});
        return res;
      })
      .catch(() => caches.match(request).then((m) => m || caches.match('/m')))
  );
});

// ── Web push ────────────────────────────────────────────────────────────────
self.addEventListener('push', (e) => {
  let payload = { title: 'locolie', body: 'A new local offer near you.' };
  try { payload = e.data.json(); } catch (_) { if (e.data) payload.body = e.data.text(); }
  e.waitUntil(
    self.registration.showNotification(payload.title || 'locolie', {
      body: payload.body || '',
      icon: '/icon.svg',
      badge: '/icon.svg',
      data: payload.data || {},
    })
  );
});

self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || '/m';
  e.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
      for (const c of list) { if ('focus' in c) return c.focus(); }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});

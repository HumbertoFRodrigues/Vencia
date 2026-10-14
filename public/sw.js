// Vencia service worker: makes the app installable and opens instantly.
// Pages are always fetched from the network (they hold live, private data);
// only static files are cached, and a small offline page answers when there
// is no connection.
const CACHE = 'vencia-v2';
// Relative to this file, so the app also works from a subfolder (localhost/vencia).
const OFFLINE = new URL('offline.html', self.location).href;

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((c) => c.addAll([OFFLINE, new URL('assets/app-192.png', self.location).href])).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET' || new URL(req.url).origin !== self.location.origin) return;

  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match(OFFLINE)));
    return;
  }

  // Versioned static files (css ?v=…, icons, logos): cache-first.
  if (/\/(css|icons|assets)\//.test(new URL(req.url).pathname)) {
    event.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        if (res.ok) { const copia = res.clone(); caches.open(CACHE).then((c) => c.put(req, copia)); }
        return res;
      })),
    );
  }
});

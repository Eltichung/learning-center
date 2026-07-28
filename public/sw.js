// Service worker tối giản cho PWA LớpThêm.
// Chiến lược: cache-first cho file tĩnh (css/js/ảnh/font), network-first cho HTML
// để không bao giờ hiện dữ liệu cũ hay trang của người khác.
const CACHE = 'hocchua-v4';
// Chỉ precache asset tĩnh — manifest.json bây giờ do Laravel trả về động, để network-first
const PRECACHE = [
  '/favicon.svg',
  '/favicon-192.png',
  '/favicon-512.png',
  '/apple-touch-icon.png',
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;                 // bỏ qua POST/PUT/DELETE
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;  // chỉ xử lý same-origin

  // File tĩnh: cache-first (URL đã có ?v=filemtime nên đổi bản là tự lấy mới)
  if (/\.(css|js|png|svg|ico|jpe?g|woff2?)$/.test(url.pathname)) {
    e.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((resp) => {
        const copy = resp.clone();
        caches.open(CACHE).then((c) => c.put(req, copy));
        return resp;
      }))
    );
    return;
  }

  // HTML và phần còn lại: network-first, chỉ dùng cache khi mất mạng
  e.respondWith(fetch(req).catch(() => caches.match(req)));
});

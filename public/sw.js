/* Service worker minimal untuk PWA.
   Hanya menyimpan file statis (CSS, logo, ikon). Halaman aplikasi TIDAK di-cache karena berisi data dan sesi login. */
var CACHE = 'register-po-v1';
var STATIC = ['css/app.css', 'img/starion.png', 'img/icon-192.png', 'img/icon-512.png', 'img/favicon.png', 'offline.html'];

self.addEventListener('install', function (e) {
    e.waitUntil(caches.open(CACHE).then(function (c) { return c.addAll(STATIC); }).then(function () { return self.skipWaiting(); }));
});
self.addEventListener('activate', function (e) {
    e.waitUntil(caches.keys().then(function (keys) {
        return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
    }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
    var req = e.request;
    if (req.method !== 'GET') { return; }
    var url = new URL(req.url);
    if (url.origin !== location.origin) { return; }

    // Halaman (navigasi): selalu ke jaringan; kalau offline tampilkan halaman offline.
    if (req.mode === 'navigate') {
        e.respondWith(fetch(req).catch(function () { return caches.match('offline.html'); }));
        return;
    }
    // File statis: cache dulu, lalu perbarui di belakang layar.
    if (/\.(css|png|jpg|jpeg|svg|webp|woff2?)$/i.test(url.pathname) || url.pathname.indexOf('/vendor/') !== -1) {
        e.respondWith(caches.match(req).then(function (hit) {
            var net = fetch(req).then(function (res) {
                if (res && res.ok) { var copy = res.clone(); caches.open(CACHE).then(function (c) { c.put(req, copy); }); }
                return res;
            }).catch(function () { return hit; });
            return hit || net;
        }));
    }
});

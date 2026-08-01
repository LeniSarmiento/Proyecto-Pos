const CACHE_NAME = 'pos-arquitec-v1';
const ASSETS_TO_CACHE = [
    './',
    './index.php',
    './assets/css/styles.css',
    './assets/js/main.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(cache => cache.addAll(ASSETS_TO_CACHE))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
        .then(response => response || fetch(event.request))
    );
});
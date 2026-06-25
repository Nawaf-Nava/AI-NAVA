const CACHE_NAME = 'cyberflux-cache-v1';
const urlsToCache = [
  'Style.css', 
  'images/ooo.png',
  'images/gogo.png',
  'images/icons/nava-normal.png',
  'images/icons/nava-deep.png',
  'images/stardust.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
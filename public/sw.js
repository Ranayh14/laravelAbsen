const CACHE_NAME = 'absen-v1';
const STATIC_ASSETS = [
    '/',
    '/index.php',
    '/assets/css/style.css',
    '/assets/js/attendance.js',
    '/assets/js/performance-optimizer.js',
    // Face API models (cache the most important ones)
    '/assets/js/face-api-models/tiny_face_detector_model-weights_manifest.json',
    '/assets/js/face-api-models/tiny_face_detector_model-shard1',
    '/assets/js/face-api-models/face_landmark_68_model-weights_manifest.json',
    '/assets/js/face-api-models/face_landmark_68_model-shard1',
    '/assets/js/face-api-models/face_recognition_model-weights_manifest.json',
    '/assets/js/face-api-models/face_recognition_model-shard1',
    '/assets/js/face-api-models/face_recognition_model-shard2'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Use no-cors or ignore errors for individual files to prevent install failure
            return cache.addAll(STATIC_ASSETS).catch(err => console.warn('Some assets failed to cache during install', err));
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        })
    );
});

self.addEventListener('fetch', event => {
    // IMPORTANT: Cache API ONLY supports GET requests.
    // Skip any other methods (POST, PUT, DELETE, etc.)
    if (event.request.method !== 'GET') {
        return; 
    }

    const url = new URL(event.request.url);
    
    // Cache-first strategy for static assets and models
    const isModel = url.pathname.includes('face-api-models') || url.pathname.includes('face-models');
    const isStatic = STATIC_ASSETS.includes(url.pathname) || url.pathname.endsWith('.js') || url.pathname.endsWith('.css');

    if (isModel || isStatic) {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request).then(fetchResponse => {
                    // Only cache successful GET responses
                    if (!fetchResponse || fetchResponse.status !== 200 || fetchResponse.type !== 'basic') {
                        return fetchResponse;
                    }
                    const responseToCache = fetchResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                    return fetchResponse;
                });
            })
        );
    } else {
        // Network-first for everything else
        event.respondWith(
            fetch(event.request).catch(() => caches.match(event.request))
        );
    }
});

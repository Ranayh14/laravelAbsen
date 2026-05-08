/**
 * Performance Optimizer untuk Sistem Presensi
 * Mengoptimalkan performa aplikasi secara keseluruhan
 */

class PerformanceOptimizer {
    constructor() {
        this.isInitialized = false;
        this.performanceMetrics = {
            loadTime: 0,
            recognitionTime: 0,
            attendanceTime: 0
        };
        this.userMap = new Map(); // Initialize user map
        this.optimizationSettings = {
            enablePreloading: true,
            enableCaching: true,
            enableCompression: true,
            maxCacheSize: 50 // MB
        };
    }

    /**
     * Inisialisasi performance optimizer
     */
    async init() {
        try {
            console.log('Initializing Performance Optimizer...');
            
            // Setup performance monitoring
            this.setupPerformanceMonitoring();
            
            // Setup caching
            this.setupCaching();
            
            // Setup compression
            this.setupCompression();
            
            this.isInitialized = true;
            console.log('Performance Optimizer initialized successfully');
            // Auto-preload critical resources
            if (this.optimizationSettings.enablePreloading) {
                try { await this.preloadResources(); } catch(e) { /* noop */ }
            }
            
        } catch (error) {
            console.error('Error initializing Performance Optimizer:', error);
        }
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page load time
        window.addEventListener('load', () => {
            this.performanceMetrics.loadTime = performance.now();
            console.log(`Page loaded in ${this.performanceMetrics.loadTime.toFixed(2)}ms`);
        });

        // Monitor memory usage
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                if (memory.usedJSHeapSize > memory.jsHeapSizeLimit * 0.8) {
                    console.warn('High memory usage detected:', {
                        used: (memory.usedJSHeapSize / 1024 / 1024).toFixed(2) + 'MB',
                        limit: (memory.jsHeapSizeLimit / 1024 / 1024).toFixed(2) + 'MB'
                    });
                    this.optimizeMemory();
                }
            }, 5000);
        }
    }

    /**
     * Setup caching system
     */
    setupCaching() {
        if (!this.optimizationSettings.enableCaching) return;

        // Setup service worker for caching
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('Service Worker registered:', registration);
                })
                .catch(error => {
                    console.log('Service Worker registration failed:', error);
                });
        }
    }

    /**
     * Setup compression
     */
    setupCompression() {
        if (!this.optimizationSettings.enableCompression) return;

        // Compress images before processing
        this.setupImageCompression();
    }

    /**
     * Setup image compression
     */
    setupImageCompression() {
        // Override canvas toDataURL to compress images
        const originalToDataURL = HTMLCanvasElement.prototype.toDataURL;
        HTMLCanvasElement.prototype.toDataURL = function(type, quality) {
            if (type === 'image/jpeg' && quality === undefined) {
                quality = 0.8; // Default compression
            }
            return originalToDataURL.call(this, type, quality);
        };
    }

    /**
     * Optimize memory usage
     */
    optimizeMemory() {
        console.log('Optimizing memory usage...');
        
        // Clear unused caches
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                cacheNames.forEach(cacheName => {
                    caches.delete(cacheName);
                });
            });
        }

        // Force garbage collection if available
        if (window.gc) {
            window.gc();
        }
    }

    /**
     * Preload critical resources
     */
    async preloadResources() {
        try {
            console.log('Preloading critical resources...');
            
            // Preload Face API models
            await this.preloadFaceAPIModels();
            
            // Preload user data
            await this.preloadUserData();
            
            console.log('Critical resources preloaded');
            
        } catch (error) {
            console.error('Error preloading resources:', error);
        }
    }

    /**
     * Preload Face API models
     */
    async preloadFaceAPIModels() {
        if (typeof faceapi === 'undefined' || window.faceApiModelsLoaded) return;
        if (window.loadingFaceApiModels) return;

        try {
            window.loadingFaceApiModels = true;
            // Try WebGL first for performance, fallback to CPU
            try { await faceapi.tf.setBackend('webgl'); } catch(e) {
                try { await faceapi.tf.setBackend('cpu'); } catch(e2) {}
            }
            await faceapi.tf.ready();
            
            const MODEL_URL = (window.FACEAPI_MODEL_URL || 'assets/js/face-api-models');
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL)
            ]);
            window.faceApiModelsLoaded = true;
            console.log('Face API models preloaded (Backend:', faceapi.tf.getBackend(), ')');
        } catch (error) {
            console.error('Error preloading Face API models:', error);
        } finally {
            window.loadingFaceApiModels = false;
        }
    }

    /**
     * Preload user data
     */
    async preloadUserData() {
        // Optimization: Only preload all members if on attendance page or admin dashboard
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        const isAttendancePage = page && page.includes('presensi');
        const isAdmin = window.USER_ROLE === 'admin';
        
        // Employees don't need all members' face data on random pages
        if (!isAttendancePage && !isAdmin) return;

        try {
            // If on attendance page and mode is late_req, only load current user
            const isLateReq = urlParams.get('mode') === 'late_req';
            const action = (isLateReq && !isAdmin) ? 'get_current_user_descriptor' : 'get_members';
            
            const response = await fetch('/', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ ajax: action, light: 1 })
            });

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data && data.ok && Array.isArray(data.data)) {
                    this.userData = data.data;
                    console.log('User data preloaded:', this.userData.length, 'members');
                    
                    // Create map for quick lookup
                    this.userData.forEach(user => {
                        this.userMap.set(user.nama.toLowerCase(), user);
                    });
                }
            } catch (e) {
                console.error('Error parsing user data JSON:', e);
                console.log('Raw response:', text);
                // Don't throw, just log
            }
        } catch (error) {
            console.error('Error preloading user data:', error);
        }
    }

    /**
     * Get cached user data
     */
    getCachedUserData() {
        try {
            const cached = sessionStorage.getItem('members_cache');
            return cached ? JSON.parse(cached) : null;
        } catch (error) {
            console.error('Error getting cached user data:', error);
            return null;
        }
    }

    /**
     * Optimize for mobile devices
     */
    optimizeForMobile() {
        console.log('Optimizing for mobile devices...');
        
        // Reduce image quality for mobile
        this.optimizationSettings.imageQuality = 0.6;
        
        // Enable aggressive caching
        this.optimizationSettings.enableCaching = true;
        
        // Reduce processing frequency
        this.optimizationSettings.processingInterval = 1000; // 1 second
    }

    /**
     * Optimize for desktop
     */
    optimizeForDesktop() {
        console.log('Optimizing for desktop...');
        
        // Higher image quality for desktop
        this.optimizationSettings.imageQuality = 0.8;
        
        // Standard processing frequency
        this.optimizationSettings.processingInterval = 500; // 0.5 seconds
    }

    /**
     * Get performance metrics
     */
    getPerformanceMetrics() {
        return {
            ...this.performanceMetrics,
            memoryUsage: this.getMemoryUsage(),
            cacheSize: this.getCacheSize()
        };
    }

    /**
     * Get memory usage
     */
    getMemoryUsage() {
        if ('memory' in performance) {
            const memory = performance.memory;
            return {
                used: (memory.usedJSHeapSize / 1024 / 1024).toFixed(2) + 'MB',
                total: (memory.totalJSHeapSize / 1024 / 1024).toFixed(2) + 'MB',
                limit: (memory.jsHeapSizeLimit / 1024 / 1024).toFixed(2) + 'MB'
            };
        }
        return null;
    }

    /**
     * Get cache size
     */
    getCacheSize() {
        if ('caches' in window) {
            return caches.keys().then(cacheNames => {
                return cacheNames.length;
            });
        }
        return 0;
    }

    /**
     * Clear all caches
     */
    async clearCaches() {
        if ('caches' in window) {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
            console.log('All caches cleared');
        }
    }

    /**
     * Optimize image for processing
     */
    optimizeImage(canvas, quality = null) {
        if (quality === null) {
            quality = this.optimizationSettings.imageQuality || 0.8;
        }

        // Create optimized canvas
        const optimizedCanvas = document.createElement('canvas');
        const ctx = optimizedCanvas.getContext('2d');
        
        // Calculate optimal dimensions
        const maxWidth = 800;
        const maxHeight = 600;
        
        let { width, height } = canvas;
        
        if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width *= ratio;
            height *= ratio;
        }
        
        optimizedCanvas.width = width;
        optimizedCanvas.height = height;
        
        // Draw optimized image
        ctx.drawImage(canvas, 0, 0, width, height);
        
        return optimizedCanvas.toDataURL('image/jpeg', quality);
    }
}

// Initialize global performance optimizer
window.performanceOptimizer = new PerformanceOptimizer();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.performanceOptimizer.init();
    });
} else {
    window.performanceOptimizer.init();
}
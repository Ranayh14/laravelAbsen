/**
 * Recognition Optimizer untuk Sistem Presensi
 * Mengoptimalkan proses face recognition untuk mengurangi delay
 */

class RecognitionOptimizer {
    constructor() {
        this.isInitialized = false;
        this.faceDescriptors = new Map();
        this.recognitionSettings = {
            detectionConfidence: 0.5,
            recognitionThreshold: 0.6,
            maxFaceSize: 224,
            enablePreloading: true,
            enableCaching: true,
            processingInterval: 500
        };
        this.performanceMetrics = {
            detectionTime: 0,
            recognitionTime: 0,
            totalTime: 0
        };
    }

    /**
     * Inisialisasi recognition optimizer
     */
    async init() {
        try {
            console.log('Initializing Recognition Optimizer...');
            
            // Setup recognition settings
            this.setupRecognitionSettings();
            
            // Preload face descriptors if enabled
            if (this.recognitionSettings.enablePreloading) {
                await this.preloadFaceDescriptors();
            }
            
            this.isInitialized = true;
            console.log('Recognition Optimizer initialized successfully');
            
        } catch (error) {
            console.error('Error initializing Recognition Optimizer:', error);
        }
    }

    /**
     * Setup recognition settings
     */
    setupRecognitionSettings() {
        // Optimize detection settings
        if (typeof faceapi !== 'undefined') {
            // Set optimal detection options
            this.detectionOptions = new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: this.recognitionSettings.detectionConfidence
            });
        }
    }

    /**
     * Preload face descriptors dari database
     */
    async preloadFaceDescriptors(members = null) {
        try {
            console.log('Preloading face descriptors...');
            
            let membersData = members;
            if (!membersData) {
                membersData = await this.fetchMembers();
            }
            
            if (!membersData || membersData.length === 0) {
                console.log('No members to preload');
                return;
            }
            
            // Process each member's face descriptor
            for (const member of membersData) {
                if (member.foto_base64) {
                    try {
                        const descriptor = await this.extractFaceDescriptor(member.foto_base64);
                        if (descriptor) {
                            this.faceDescriptors.set(member.id, {
                                id: member.id,
                                nama: member.nama,
                                descriptor: descriptor,
                                timestamp: Date.now()
                            });
                        }
                    } catch (error) {
                        console.warn(`Failed to preload descriptor for ${member.nama}:`, error);
                    }
                }
            }
            
            console.log(`Preloaded ${this.faceDescriptors.size} face descriptors`);
            
        } catch (error) {
            console.error('Error preloading face descriptors:', error);
        }
    }

    /**
     * Fetch members dari server
     */
    async fetchMembers() {
        try {
            const response = await fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_members'
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.ok ? data.data : null;
            }
        } catch (error) {
            console.error('Error fetching members:', error);
        }
        return null;
    }

    /**
     * Extract face descriptor dari image
     */
    async extractFaceDescriptor(imageData) {
        try {
            const startTime = performance.now();
            
            // Create image element
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            return new Promise((resolve, reject) => {
                img.onload = async () => {
                    try {
                        // Detect faces
                        const detections = await faceapi
                            .detectAllFaces(img, this.detectionOptions)
                            .withFaceLandmarks()
                            .withFaceDescriptors();
                        
                        if (detections.length > 0) {
                            const descriptor = detections[0].descriptor;
                            const processingTime = performance.now() - startTime;
                            console.log(`Descriptor extracted in ${processingTime.toFixed(2)}ms`);
                            resolve(descriptor);
                        } else {
                            resolve(null);
                        }
                    } catch (error) {
                        reject(error);
                    }
                };
                
                img.onerror = () => reject(new Error('Failed to load image'));
                img.src = imageData;
            });
            
        } catch (error) {
            console.error('Error extracting face descriptor:', error);
            return null;
        }
    }

    /**
     * Optimized face recognition
     */
    async recognizeFaceOptimized(imageData) {
        try {
            const startTime = performance.now();
            
            // Extract face descriptor from input image
            const inputDescriptor = await this.extractFaceDescriptor(imageData);
            if (!inputDescriptor) {
                return null;
            }
            
            const recognitionStartTime = performance.now();
            
            // Find best match using preloaded descriptors
            let bestMatch = null;
            let bestDistance = Infinity;
            
            for (const [id, memberData] of this.faceDescriptors) {
                const distance = faceapi.euclideanDistance(inputDescriptor, memberData.descriptor);
                
                if (distance < this.recognitionSettings.recognitionThreshold && distance < bestDistance) {
                    bestDistance = distance;
                    bestMatch = {
                        id: memberData.id,
                        nama: memberData.nama,
                        confidence: 1 - distance,
                        distance: distance
                    };
                }
            }
            
            const recognitionTime = performance.now() - recognitionStartTime;
            const totalTime = performance.now() - startTime;
            
            // Update performance metrics
            this.performanceMetrics.detectionTime = recognitionStartTime - startTime;
            this.performanceMetrics.recognitionTime = recognitionTime;
            this.performanceMetrics.totalTime = totalTime;
            
            console.log(`Recognition completed in ${totalTime.toFixed(2)}ms (detection: ${this.performanceMetrics.detectionTime.toFixed(2)}ms, recognition: ${recognitionTime.toFixed(2)}ms)`);
            
            return bestMatch;
            
        } catch (error) {
            console.error('Error in optimized face recognition:', error);
            return null;
        }
    }

    /**
     * Process attendance dengan optimasi
     */
    async processAttendanceOptimized(imageData, mode) {
        try {
            const startTime = performance.now();
            
            // Optimize image before processing
            const optimizedImage = await this.optimizeImageForProcessing(imageData);
            
            // Perform recognition
            const recognizedUser = await this.recognizeFaceOptimized(optimizedImage);
            
            if (!recognizedUser) {
                return {
                    success: false,
                    message: 'Wajah tidak dikenali'
                };
            }
            
            // Submit attendance
            const attendanceResult = await this.submitAttendance(recognizedUser, mode, optimizedImage);
            
            const processingTime = performance.now() - startTime;
            
            return {
                success: attendanceResult.success,
                data: attendanceResult.data,
                message: attendanceResult.message,
                processingTime: processingTime,
                recognizedUser: recognizedUser
            };
            
        } catch (error) {
            console.error('Error processing attendance:', error);
            return {
                success: false,
                message: 'Terjadi kesalahan dalam proses presensi'
            };
        }
    }

    /**
     * Optimize image untuk processing
     */
    async optimizeImageForProcessing(imageData) {
        try {
            // Create canvas from image data
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            return new Promise((resolve, reject) => {
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Calculate optimal dimensions
                    const maxSize = this.recognitionSettings.maxFaceSize;
                    let { width, height } = img;
                    
                    if (width > maxSize || height > maxSize) {
                        const ratio = Math.min(maxSize / width, maxSize / height);
                        width *= ratio;
                        height *= ratio;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // Draw optimized image
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Return optimized image data
                    resolve(canvas.toDataURL('image/jpeg', 0.8));
                };
                
                img.onerror = () => reject(new Error('Failed to load image'));
                img.src = imageData;
            });
            
        } catch (error) {
            console.error('Error optimizing image:', error);
            return imageData; // Return original if optimization fails
        }
    }

    /**
     * Submit attendance ke server
     */
    async submitAttendance(recognizedUser, mode, imageData) {
        try {
            const formData = new FormData();
            formData.append('action', 'submit_attendance');
            formData.append('user_id', recognizedUser.id);
            formData.append('mode', mode);
            formData.append('image_data', imageData);
            formData.append('confidence', recognizedUser.confidence);
            
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const data = await response.json();
                return data;
            } else {
                throw new Error('Server error');
            }
            
        } catch (error) {
            console.error('Error submitting attendance:', error);
            return {
                success: false,
                message: 'Gagal mengirim presensi ke server'
            };
        }
    }

    /**
     * Optimize untuk mobile devices
     */
    optimizeForMobile() {
        console.log('Optimizing recognition for mobile...');
        
        // Reduce processing requirements for mobile
        this.recognitionSettings.detectionConfidence = 0.4;
        this.recognitionSettings.recognitionThreshold = 0.7;
        this.recognitionSettings.maxFaceSize = 160;
        this.recognitionSettings.processingInterval = 1000;
    }

    /**
     * Optimize untuk desktop
     */
    optimizeForDesktop() {
        console.log('Optimizing recognition for desktop...');
        
        // Higher accuracy settings for desktop
        this.recognitionSettings.detectionConfidence = 0.5;
        this.recognitionSettings.recognitionThreshold = 0.6;
        this.recognitionSettings.maxFaceSize = 224;
        this.recognitionSettings.processingInterval = 500;
    }

    /**
     * Get performance metrics
     */
    getPerformanceMetrics() {
        return {
            ...this.performanceMetrics,
            descriptorsLoaded: this.faceDescriptors.size,
            settings: this.recognitionSettings
        };
    }

    /**
     * Clear cached descriptors
     */
    clearCachedDescriptors() {
        this.faceDescriptors.clear();
        console.log('Cached face descriptors cleared');
    }

    /**
     * Update recognition settings
     */
    updateSettings(newSettings) {
        this.recognitionSettings = {
            ...this.recognitionSettings,
            ...newSettings
        };
        console.log('Recognition settings updated:', this.recognitionSettings);
    }
}

// Initialize global recognition optimizer
window.recognitionOptimizer = new RecognitionOptimizer();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.recognitionOptimizer.init();
    });
} else {
    window.recognitionOptimizer.init();
}
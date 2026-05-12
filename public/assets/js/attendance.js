/**
 * Attendance System Logic
 * Consolidated and Optimized
 */

// Global variables
let video = document.getElementById('video');
let canvas = document.getElementById('overlay'); // Ensure ID matches HTML
let videoInterval;
let labeledFaceDescriptors = [];
let faceMatcher = null; // Built once after descriptors load, NOT every detection cycle
let members = [];
let scanMode = null; // 'masuk' or 'pulang'
let isCameraActive = false;
let isPresensiSuccess = false;
// Global for speech synthesis
window.lastSpokenMessage = null;
let isDetectionPaused = false;
let isDetectionStopped = false;
let isProcessingRecognition = false;
let processedLabels = new Map();
let recognitionCompleted = false;
let logMasukData = [];
let logPulangData = [];
let currentRecognitionData = null;

// UI Elements (Lazy bound)
let loadingOverlay = document.getElementById('loading-overlay');
let presensiStatus = document.getElementById('presensi-status');
let scanButtonsContainer = document.getElementById('scan-buttons');
let videoContainer = document.getElementById('video-container');
let btnBackScan = document.getElementById('btn-back-scan');
let btnScanMasuk = document.getElementById('btn-scan-masuk');
let btnScanPulang = document.getElementById('btn-scan-pulang');

// Configuration
const detectionConfig = {
    faceMatcherThreshold: 0.4,
    recognitionThreshold: 0.4,
    qualityThreshold: 0.25,
    scoreThreshold: 0.5,
    inputSize: 320,
    minFaceSize: 50,
    maxFaces: 1,
    detectionThrottle: 100,
    strictMode: true,
    multiAttemptValidation: true,
    genderValidation: true
};

const performanceStats = {
    detectionCount: 0,
    totalDetectionTime: 0,
    averageDetectionTime: 0,
    lastDetectionTime: 0
};

// ---- Helpers ----
function qs(selector) { return document.querySelector(selector); }
function qsa(selector) { return document.querySelectorAll(selector); }

// Notification Wrapper
function statusMessage(msg, classes) {
    if (presensiStatus) {
        presensiStatus.textContent = msg;
        presensiStatus.className = `fixed bottom-10 left-1/2 -translate-x-1/2 bg-white text-gray-800 px-6 py-3 rounded-full font-medium shadow-xl z-70 animate-fade-in-up ${classes || ''}`;
        presensiStatus.classList.remove('hidden');
        
        // Speak if critical
        if (classes && (classes.includes('red') || classes.includes('green'))) {
             if (typeof speak === 'function') speak(msg);
        }
        
        // Auto hide after 5s
        setTimeout(() => {
            if (presensiStatus) presensiStatus.classList.add('hidden');
        }, 5000);
    } else {
        // Fallback
        if (typeof showNotif === 'function') showNotif(msg, classes.includes('green'));
    }
}

// Device Detection
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

function detectDevicePerformance() {
    const cores = navigator.hardwareConcurrency || 4;
    const memory = navigator.deviceMemory || 4;
    if (cores <= 4 && memory <= 4) return 'low';
    if (cores <= 8 && memory <= 8) return 'medium';
    return 'high';
}

function getAdjustedRecognitionThreshold() {
    const perf = detectDevicePerformance();
    const isMobile = isMobileDevice();
    let threshold = detectionConfig.recognitionThreshold;
    if (isMobile) threshold += 0.05;
    if (perf === 'low') threshold += 0.02;
    return Math.min(0.6, threshold);
}

function getAdjustedQualityThreshold() {
    const perf = detectDevicePerformance();
    const isMobile = isMobileDevice();
    let threshold = detectionConfig.qualityThreshold;
    if (isMobile) threshold -= 0.05;
    if (perf === 'low') threshold -= 0.05;
    return Math.max(0.1, threshold);
}

function getAdjustedFaceMatcherThreshold() { return detectionConfig.faceMatcherThreshold; }

// ---- Face Recognition Setup ----

async function initializeFaceRecognition() {
    try {
        // Try WebGL for maximum performance
        try {
            await faceapi.tf.setBackend('webgl');
            await faceapi.tf.ready();
            console.log('Using WebGL backend for face recognition');
        } catch (e) {
            console.warn('WebGL failed, using CPU:', e);
            await faceapi.tf.setBackend('cpu');
        }

        // Initialize listeners once
        initAttendanceListeners();
        
        // Don't await everything on load to keep page responsive
        // Set backend early for better performance
        if (faceapi.tf.getBackend() !== 'webgl') {
            faceapi.tf.setBackend('webgl').catch(() => faceapi.tf.setBackend('cpu'));
        }
        
        loadFaceApiModels().catch(e => console.error('Delayed model load failed:', e));
        
        console.log('Face recognition system initialized');
    } catch (error) {
        console.error('Failed to initialize face recognition:', error);
    }
}

function initAttendanceListeners() {
    // Listeners are now managed globally or in layout_footer.php for better reliability
}

async function loadFaceApiModels() {
    if (window.faceApiModelsLoaded) return;
    if (window.loadingFaceApiModels) {
        // Wait if already loading
        while (window.loadingFaceApiModels) {
            await new Promise(r => setTimeout(r, 100));
            if (window.faceApiModelsLoaded) return;
        }
    }
    
    window.loadingFaceApiModels = true;
    // Removed overlay toggle here to allow silent background loading on page load
    // if (loadingOverlay) loadingOverlay.classList.remove('hidden');
    
    const MODEL_URL = window.FACEAPI_MODEL_URL || 'assets/js/face-api-models';
    
    try {
        console.log('🚀 Loading face recognition models...');
        
        // Ensure backend is ready
        await faceapi.tf.ready();
        
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
        ]);
        window.faceApiModelsLoaded = true;
    } catch (e) {
        console.error('Error loading models', e);
        throw e;
    } finally {
        window.loadingFaceApiModels = false;
        // if (loadingOverlay) loadingOverlay.classList.add('hidden');
    }
}

async function loadLabeledFaceDescriptors() {
    if (typeof api !== 'function') return;
    
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');
    const isLateReq = mode === 'late_req';
    
    try {
        const startTime = performance.now();
        console.log(`Starting descriptor load (Mode: ${isLateReq ? 'Limited' : 'Full'})...`);
        
        let membersToProcess = [];
        
        if (isLateReq) {
            // Optimized: Only load current user for late requests
            const res = await api('?ajax=get_current_user_descriptor', {}, { cache: true });
            if (res.ok && res.data) {
                membersToProcess = [res.data];
                members = [res.data];
            }
        } else {
            // Standard: Load all members (Kiosk mode)
            // OPTIMIZED: Use 'light' mode to skip large base64 photos if not needed
            const res = await api('?ajax=get_members&light=1');
            members = res.data || [];
            membersToProcess = members;
        }

        if (membersToProcess.length === 0) return;

        // Try load from IndexedDB if available
        if (typeof idbGetDescriptors === 'function' && typeof computeMembersVersionKey === 'function') {
            const versionKey = await computeMembersVersionKey(membersToProcess);
            const cached = await idbGetDescriptors(versionKey);
            if (cached && Array.isArray(cached) && cached.length > 0) {
                labeledFaceDescriptors = cached.map(item => new faceapi.LabeledFaceDescriptors(
                    item.label,
                    item.descriptors.map(d => new Float32Array(d))
                ));
                console.log(`Loaded ${labeledFaceDescriptors.length} face descriptors from IDB cache in ${(performance.now() - startTime).toFixed(2)}ms`);
                return;
            }
        }

        labeledFaceDescriptors = [];
        const perfLevel = detectDevicePerformance();
        const batchSize = isLateReq ? 1 : (perfLevel === 'low' ? 3 : 10);
        
        for (let i = 0; i < membersToProcess.length; i += batchSize) {
            const batch = membersToProcess.slice(i, i + batchSize);
            const promises = batch.map(async m => {
                try {
                    // Handle both object and indexed array formats for robustness
                    const label = String(m.nim || m[3] || m.nama || m[4] || m.id || m[0]);
                    const embedding = m.face_embedding || m[8];
                    const foto = m.foto_base64 || m[7];
                    const name = m.nama || m[4] || 'User';

                    // OPTIMIZED: Use pre-computed embedding from server if available
                    if (embedding) {
                        try {
                            const desc = new Float32Array(JSON.parse(embedding));
                            if (desc.length === 128) {
                                return new faceapi.LabeledFaceDescriptors(label, [desc]);
                            }
                        } catch (e) { console.error('Error parsing embedding for', name); }
                    }
                    
                    if (!foto) return null;
                    const img = await faceapi.fetchImage(foto);
                    const det = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
                        .withFaceLandmarks().withFaceDescriptor();
                    if (det) {
                        try {
                            // Auto-save the computed embedding to the database so future loads are instant
                            const userId = m.id || m[0];
                            if (userId) {
                                const formData = new FormData();
                                formData.append('action', 'save_computed_face_embedding');
                                formData.append('user_id', userId);
                                formData.append('embedding', JSON.stringify(Array.from(det.descriptor)));
                                fetch('?ajax=save_computed_face_embedding', { method: 'POST', body: formData }).catch(e => console.warn('Silently failed to save embedding'));
                            }
                        } catch (err) {}
                        
                        return new faceapi.LabeledFaceDescriptors(label, [det.descriptor]);
                    }
                } catch (e) { console.warn('Failed to load face data', e); }
                return null;
            });
            const results = await Promise.all(promises);
            labeledFaceDescriptors.push(...results.filter(r => r !== null));
            if (i + batchSize < membersToProcess.length) await new Promise(r => setTimeout(r, 20));
        }

        // Save to cache
        if (typeof idbSetDescriptors === 'function' && typeof computeMembersVersionKey === 'function') {
            const versionKey = await computeMembersVersionKey(membersToProcess);
            const toStore = labeledFaceDescriptors.map(ld => ({
                label: ld.label,
                descriptors: ld.descriptors.map(arr => Array.from(arr))
            }));
            await idbSetDescriptors(versionKey, toStore);
        }
        
        console.log(`Loaded ${labeledFaceDescriptors.length} face descriptors in ${(performance.now() - startTime).toFixed(2)}ms`);
    } catch (e) {
        console.error('Failed to load descriptors', e);
    }
}


// ---- Camera & Recognition Logic ----

async function startScan(mode) {
    scanMode = mode;
    isPresensiSuccess = false;
    isDetectionStopped = false;
    isDetectionPaused = false;
    isProcessingRecognition = false;
    currentRecognitionData = null;
    faceMatcher = null;
    
    // Show UI immediately
    if (scanButtonsContainer) scanButtonsContainer.classList.add('hidden');
    if (videoContainer) videoContainer.classList.remove('hidden');
    if (btnBackScan) btnBackScan.classList.remove('hidden');
    const stopBtn = qs('#btn-stop-detection');
    if (stopBtn) stopBtn.classList.remove('hidden');

    // Show appropriate log table
    const logMasuk = qs('#log-masuk-container');
    const logPulang = qs('#log-pulang-container');
    if (mode === 'masuk') {
        if (logMasuk) logMasuk.classList.remove('hidden');
        if (logPulang) logPulang.classList.add('hidden');
        loadLogMasuk();
    } else {
        if (logPulang) logPulang.classList.remove('hidden');
        if (logMasuk) logMasuk.classList.add('hidden');
        loadLogPulang();
    }

    statusMessage('Menghubungkan kamera...', 'bg-blue-100 text-blue-700');

    statusMessage('Menginisialisasi sistem...', 'bg-blue-100 text-blue-700');

    // PARALLEL: Start camera, load models, and fetch descriptors at the same time
    const preWarmPromise = window._preWarmReady || Promise.resolve();
    
    await Promise.allSettled([
        startVideo(),
        preWarmPromise,
        (async () => {
            if (!window.faceApiModelsLoaded) {
                console.log('Loading Face API models...');
                await loadFaceApiModels();
            }
        })(),
        (async () => {
            if (labeledFaceDescriptors.length === 0) {
                console.log('Loading face descriptors...');
                await loadLabeledFaceDescriptors();
            }
        })()
    ]);

    // Build FaceMatcher ONCE — reused every detection frame
    if (labeledFaceDescriptors.length > 0 && !faceMatcher) {
        try {
            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, getAdjustedFaceMatcherThreshold());
            console.log('✅ FaceMatcher ready with', labeledFaceDescriptors.length, 'descriptors');
        } catch(e) {
            console.error('Failed to build FaceMatcher:', e);
        }
    }

    statusMessage('Sistem siap! Arahkan wajah ke kamera.', 'bg-green-100 text-green-700');
    startVideoInterval();
}

async function startVideo() {
    if (!video) return;
    try {
        const perfLevel = detectDevicePerformance();
        // Use low resolution on low-end devices for faster camera start
        const constraints = {
            video: {
                facingMode: 'user',
                width:  { ideal: perfLevel === 'low' ? 320 : 640 },
                height: { ideal: perfLevel === 'low' ? 240 : 480 }
            }
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        isCameraActive = true;

        // Wait for camera to be actually ready (metadata loaded + playing)
        await new Promise((resolve) => {
            video.onloadedmetadata = () => {
                video.play().then(resolve).catch(resolve);
            };
            // Safety timeout in case onloadedmetadata never fires
            setTimeout(resolve, 5000);
        });

        console.log('✅ Camera ready:', video.videoWidth, 'x', video.videoHeight);
    } catch (err) {
        console.error('Camera error:', err);
        statusMessage('Gagal mengakses kamera: ' + err.message, 'bg-red-100 text-red-700');
    }
}

function stopVideo() {
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }
    isCameraActive = false;
    // Clear timeout-based loop (not setInterval)
    if (videoInterval) { clearTimeout(videoInterval); videoInterval = null; }
    _detectionRunning = false;
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function resetPresensiPage() {
    stopVideo();
    isPresensiSuccess = false;
    if (scanButtonsContainer) scanButtonsContainer.classList.remove('hidden');
    if (videoContainer) videoContainer.classList.add('hidden');
    if (btnBackScan) btnBackScan.classList.add('hidden');
    const stopBtn = qs('#btn-stop-detection');
    if (stopBtn) stopBtn.classList.add('hidden');
    
    // Hide next scan button
    const nextBtn = qs('#next-scan-container');
    if (nextBtn) nextBtn.classList.add('hidden');
    
    // Hide confirmation modal
    const confirmModal = qs('#confirm-presensi-modal');
    if (confirmModal) confirmModal.classList.add('hidden');
    
    // Go back logic
    if (window.history.length > 1) {
       // window.history.back(); // Optional: depend on UX
    }
}

let _detectionRunning = false; // Prevent concurrent async detection calls

function startVideoInterval() {
    if (!isCameraActive || videoInterval || !video) return;

    // Use recursive setTimeout instead of setInterval to prevent async stacking
    // This means: wait for one detection to FINISH before scheduling the next
    const detectionDelay = 250; // ms between detection cycles

    async function detectionLoop() {
        if (isDetectionStopped || !isCameraActive) return; // Stop loop
        if (videoInterval === null) return; // stopVideo was called

        if (!isPresensiSuccess && !isProcessingRecognition && !isDetectionPaused && !_detectionRunning) {
            _detectionRunning = true;
            try {
                if (video.readyState < 2) { // Camera not fully ready yet
                    _detectionRunning = false;
                    videoInterval = setTimeout(detectionLoop, detectionDelay);
                    return;
                }

                const displaySize = { width: video.clientWidth || 640, height: video.clientHeight || 480 };
                if (displaySize.width === 0) {
                    _detectionRunning = false;
                    videoInterval = setTimeout(detectionLoop, detectionDelay);
                    return;
                }

                faceapi.matchDimensions(canvas, displaySize);

                const detection = await faceapi.detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({
                        inputSize: 224, // Smaller = MUCH faster on low-end devices
                        scoreThreshold: 0.5
                    })
                ).withFaceLandmarks().withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    // Simpan deteksi terakhir untuk ekstraksi landmark saat presensi
                    window.lastDetectionForLandmark = detection;

                    const resized = faceapi.resizeResults(detection, displaySize);
                    const box = resized.detection.box;
                    const mirroredX = displaySize.width - box.x - box.width;

                    let labelText = 'Posisikan wajah...';
                    let boxColor = '#3b82f6'; // blue while searching

                    // Use pre-built faceMatcher — no need to rebuild every frame!
                    if (faceMatcher) {
                        const bestMatch = faceMatcher.findBestMatch(detection.descriptor);

                        if (bestMatch.label === 'unknown') {
                            labelText = 'Wajah tidak dikenal';
                            boxColor = '#ef4444'; // red
                        } else {
                            const searchLabel = String(bestMatch.label);
                            const member = members.find(m => {
                                return String(m.nim || '') === searchLabel ||
                                       String(m.nama || '') === searchLabel ||
                                       String(m.id || '') === searchLabel;
                            });
                            const memberName = member ? (member.nama || '') : bestMatch.label;
                            labelText = `${memberName} (${bestMatch.distance.toFixed(2)})`;
                            boxColor = '#22c55e'; // green
                            handleRecognition(bestMatch.label, 'neutral');
                        }
                    }

                    // Draw box
                    ctx.strokeStyle = boxColor;
                    ctx.lineWidth = 3;
                    ctx.strokeRect(mirroredX, box.y, box.width, box.height);

                    // Label
                    ctx.font = 'bold 14px Inter, sans-serif';
                    const textWidth = ctx.measureText(labelText).width;
                    ctx.fillStyle = boxColor;
                    ctx.fillRect(mirroredX, box.y - 26, textWidth + 10, 26);
                    ctx.fillStyle = 'white';
                    ctx.fillText(labelText, mirroredX + 5, box.y - 7);
                }
            } catch (e) {
                if (!e.message?.includes('disposed')) console.error('Detection error:', e);
            } finally {
                _detectionRunning = false;
            }
        }

        // Schedule next iteration ONLY after this one is done
        if (!isDetectionStopped && isCameraActive) {
            videoInterval = setTimeout(detectionLoop, detectionDelay);
        }
    }

    videoInterval = setTimeout(detectionLoop, 100); // Start first cycle soon
    console.log('✅ Detection loop started');
}

function shouldAcceptDetection(match, faceData) {
    if (match.distance > getAdjustedRecognitionThreshold()) return false;
    if (assessFaceQuality(faceData) < getAdjustedQualityThreshold()) return false;
    return true;
}

function assessFaceQuality(face) {
    if (!face || !face.detection) return 0;
    const box = face.detection.box;
    const area = box.width * box.height;
    let quality = 1.0;
    if (area < 15000) quality *= 0.5;
    const centerX = box.x + box.width / 2;
    const canvasCenterX = (canvas ? canvas.width : 640) / 2;
    const dist = Math.abs(centerX - canvasCenterX);
    if (dist > 150) quality *= 0.6;
    return quality;
}

function getTopExpression(expressions) {
    if (!expressions) return 'neutral';
    return Object.keys(expressions).reduce((a, b) => expressions[a] > expressions[b] ? a : b);
}

// ---- Attendance Submission ----

async function handleRecognition(nim, expression) {
    if (isProcessingRecognition || isDetectionPaused) return;
    
    // Pause detection to show confirmation
    isDetectionPaused = true;
    
    if (typeof speak === 'function') speak('Wajah dikenali. Mohon konfirmasi data Anda.');
    
    try {
        // Ambil landmark wajah (ringan) DAN screenshot terkompresi (visual)
        const landmarks = window.lastDetectionForLandmark ? extractFaceLandmarks(window.lastDetectionForLandmark) : null;
        const screenshot = captureCompressedScreenshot();
        const pos = await getPosition();
        
        let lokasi = 'Mencari lokasi...';
        let lat = null, lng = null;
        
        if (pos) {
            lat = pos.coords.latitude;
            lng = pos.coords.longitude;
            lokasi = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            
            try {
                const streetName = await getStreetNameFromCoordinates(lat, lng);
                if (streetName) lokasi = streetName;
            } catch(e) {}
        }

        // VALIDATION: Reject if coordinates are 0 or placeholder name
        if (!lat || !lng || Math.abs(lat) < 0.0001) {
            statusMessage('Gagal mendeteksi lokasi yang valid. Silakan coba lagi.', 'bg-red-100 text-red-700');
            isDetectionPaused = false;
            return;
        }
        if (lokasi.includes('Mencari lokasi')) {
             statusMessage('Sedang mencari lokasi... Tunggu sebentar.', 'bg-blue-100 text-blue-700');
             isDetectionPaused = false;
             return;
        }
        
        const searchLabel = String(nim);
        const member = members.find(m => {
            const idMatch = String(m.id || m[0] || '') === searchLabel;
            const nimMatch = String(m.nim || m[3] || '') === searchLabel;
            const nameMatch = String(m.nama || m[4] || '') === searchLabel;
            return idMatch || nimMatch || nameMatch;
        });

        currentRecognitionData = {
            nim: member ? (member.nim || member[3] || member.id || member[0]) : nim,
            nama: member ? (member.nama || member[4]) : 'Unknown',
            mode: scanMode,
            ekspresi: expression,
            screenshot, // Foto terkompresi (~10-20KB)
            landmarks,  // JSON landmarks (~1-2KB)
            lat,
            lng,
            lokasi
        };

        // NEW: Check for different clock-out location
        if (scanMode === 'pulang') {
            const clockin = await api('?ajax=get_clockin_location&nim=' + nim, {}, { suppressModal: true });
            if (clockin.ok && clockin.lat && clockin.lng) {
                const distance = calculateDistance(lat, lng, clockin.lat, clockin.lng);
                console.log('Distance from clock-in:', distance, 'meters');
                if (distance > 500) { // 500 meters threshold
                    showDiffLocationModal(currentRecognitionData);
                    return;
                }
            }
        }
        
        showConfirmationModal(currentRecognitionData);
    } catch (e) {
        console.error('Recognition handling error:', e);
        isDetectionPaused = false;
    }
}

function showConfirmationModal(data) {
    const modal = qs('#confirm-presensi-modal');
    if (!modal) return;
    
    qs('#confirm-nama').textContent = data.nama;
    qs('#confirm-nim').textContent = data.nim;
    qs('#confirm-lokasi').textContent = data.lokasi;
    
    // Tampilkan bukti visual di modal (Prioritas: Foto > Landmark)
    const screenshotSection = qs('#confirm-screenshot-section');
    if (screenshotSection) {
        const img = qs('#confirm-screenshot-img');
        let lmCanvas = screenshotSection.querySelector('canvas#confirm-landmark-canvas');
        
        if (data.screenshot) {
            // Tampilkan foto asli terkompresi
            if (lmCanvas) lmCanvas.classList.add('hidden');
            if (img) {
                img.src = data.screenshot;
                img.classList.remove('hidden');
            }
            screenshotSection.classList.remove('hidden');
        } else if (data.landmarks) {
            // Fallback ke landmark jika foto gagal
            if (img) img.classList.add('hidden');
            if (!lmCanvas) {
                lmCanvas = document.createElement('canvas');
                lmCanvas.id = 'confirm-landmark-canvas';
                lmCanvas.className = 'w-full h-48 rounded-xl border-2 border-indigo-100 shadow-sm bg-gray-900';
                screenshotSection.querySelector('.relative')?.appendChild(lmCanvas);
            }
            lmCanvas.classList.remove('hidden');
            renderLandmarkCanvas(lmCanvas, data.landmarks, { width: 300, height: 200 });
            screenshotSection.classList.remove('hidden');
        } else {
            screenshotSection.classList.add('hidden');
        }
    }
    
    modal.classList.remove('hidden');
    
    // Bind buttons
    qs('#btn-confirm-yes').onclick = async () => {
        modal.classList.add('hidden');
        await submitFinalAttendance(data);
    };
    
    qs('#btn-confirm-no').onclick = () => {
        modal.classList.add('hidden');
        resumeDetection();
    };
}

async function submitFinalAttendance(data) {
    isProcessingRecognition = true;
    statusMessage('Menyimpan data presensi...', 'bg-blue-100 text-blue-700');
    
    try {
        // Special Mode: Late Request (from Admin Help)
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');
        
        if (mode === 'late_req') {
            statusMessage('Wajah terverifikasi! Mengalihkan...', 'bg-green-100 text-green-700');
            
            // Simpan hasil verifikasi wajah di sessionStorage (landmark, bukan screenshot)
            sessionStorage.setItem('late_req_face_verified', JSON.stringify({
                landmarks: data.landmarks,
                lokasi: data.lokasi,
                timestamp: new Date().toISOString()
            }));
            
            // Redirect back to pegawai page where the modal will auto-open
            setTimeout(() => {
                window.location.href = '?page=pegawai';
            }, 1500);
            return;
        }

        const res = await api('?ajax=save_attendance', data, { suppressModal: true });
        
        if (res.ok) {
            statusMessage(`Berhasil: ${res.message}`, 'bg-green-100 text-green-700');
            isPresensiSuccess = true;
            if (typeof speak === 'function') speak('Presensi berhasil disimpan. Terima kasih.');
            
            // Show "Next Scan" button
            const nextScanContainer = qs('#next-scan-container');
            if (nextScanContainer) nextScanContainer.classList.remove('hidden');
            
            // Log entry
            updateLogAfterAttendance(data.nim, data.mode);
        } else {
            handleAttendanceError(res, data);
        }
    } catch (e) {
        console.error('Submit error:', e);
        statusMessage('Gagal menyimpan: ' + e.message, 'bg-red-100 text-red-700');
        isDetectionPaused = false;
    } finally {
        isProcessingRecognition = false;
    }
}

function resumeDetection() {
    isDetectionPaused = false;
    isPresensiSuccess = false;
    currentRecognitionData = null;
    const nextScanContainer = qs('#next-scan-container');
    if (nextScanContainer) nextScanContainer.classList.add('hidden');
    statusMessage('Mencari wajah...', 'bg-blue-100 text-blue-700');
}

function handleAttendanceError(res, pendingData) {
    window.pendingAttendanceData = pendingData;
    
    if (res.need_reason) { // WFA
        showWFAModal(res.message);
    } else if (res.need_overtime_reason) {
        showOvertimeModal(res.message);
    } else if (res.need_early_leave_reason) {
        showEarlyLeaveModal(res.message);
    } else {
        statusMessage(res.message, 'bg-red-100 text-red-700');
        if (typeof speak === 'function') speak('Gagal. ' + res.message);
        isProcessingRecognition = false;
    }
}

/**
 * Ekstrak 68 titik landmark wajah dari hasil deteksi face-api.js.
 * Output: JSON string ~1.5KB (vs screenshot JPEG ~50-100KB = hemat ~40x)
 * @param {object} detection - Hasil faceapi.detectSingleFace().withFaceLandmarks()
 * @returns {string|null} JSON string array [{x, y}, ...] 68 titik, ternormalisasi 0-1
 */
function extractFaceLandmarks(detection) {
    if (!detection || !detection.landmarks) return null;
    try {
        const box = detection.detection.box;
        const positions = detection.landmarks.positions;
        // Normalisasi koordinat relatif terhadap bounding box (0-1)
        const normalized = positions.map(p => ({
            x: parseFloat(((p.x - box.x) / box.width).toFixed(4)),
            y: parseFloat(((p.y - box.y) / box.height).toFixed(4))
        }));
        return JSON.stringify(normalized);
    } catch (e) {
        console.warn('extractFaceLandmarks error:', e);
        return null;
    }
}

/**
 * Capture frame dari video dan kompres menjadi JPEG resolusi rendah.
 * @returns {string|null} DataURL image/jpeg
 */
function captureCompressedScreenshot() {
    if (!video || video.readyState < 2) return null;
    try {
        const canvas = document.createElement('canvas');
        
        // Maintain original video aspect ratio
        const videoWidth = video.videoWidth || video.clientWidth || 640;
        const videoHeight = video.videoHeight || video.clientHeight || 480;
        const aspectRatio = videoWidth / videoHeight;
        
        // Target width 320, calculate height to maintain ratio
        canvas.width = 320;
        canvas.height = 320 / aspectRatio;
        
        const ctx = canvas.getContext('2d');
        
        // Draw video frame ke canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Kompresi kualitas 0.6 (60%) for better balance of size and clarity
        return canvas.toDataURL('image/jpeg', 0.6);
    } catch (e) {
        console.warn('Capture error:', e);
        return null;
    }
}

/**
 * Ekstrak 68 titik landmark dari objek deteksi face-api.js.
 * Koordinat dinormalisasi (0.0 - 1.0) relatif terhadap bounding box wajah.
 * @param {object} detection - Objek deteksi dari face-api.js
 * @returns {Array|null} Array [{x,y},...] atau null
 */
function extractFaceLandmarks(detection) {
    if (!detection || !detection.landmarks) return null;
    const landmarks = detection.landmarks.positions;
    const box = detection.detection.box;
    // Normalisasi agar landmark bisa dirender di canvas dengan ukuran apapun
    return landmarks.map(p => ({
        x: (p.x - box.x) / box.width,
        y: (p.y - box.y) / box.height
    }));
}

/**
 * Cek apakah sebuah tanggal masih dalam kurun waktu 10 hari kerja terakhir.
 * @param {string} dateString - Format YYYY-MM-DD atau ISO string
 * @returns {boolean}
 */
function isWithin10WorkingDays(dateString) {
    if (!dateString) return false;
    const recordDate = new Date(dateString);
    recordDate.setHours(0, 0, 0, 0);
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Jika tanggal di masa depan (tidak mungkin tapi jaga-jaga), anggap valid
    if (recordDate > today) return true;
    
    let workingDaysCount = 0;
    let tempDate = new Date(recordDate);
    
    // Hitung hari kerja dari recordDate sampai hari ini
    while (tempDate <= today) {
        const day = tempDate.getDay();
        // 0 = Sunday, 6 = Saturday. Skip weekends.
        if (day !== 0 && day !== 6) {
            workingDaysCount++;
        }
        tempDate.setDate(tempDate.getDate() + 1);
    }
    
    // "10 hari kerja kebelakang" berarti selisihnya max 10 (termasuk hari ini)
    return workingDaysCount <= 11; // 11 agar mencakup "10 hari kebelakang" + hari ini
}

function showExpiredModal() {
    if (typeof showModalNotif === 'function') {
        showModalNotif('Bukti Kadaluarsa', 'Maaf, foto bukti presensi ini sudah dihapus dari sistem karena sudah melewati batas penyimpanan 10 hari kerja.', 'info');
    } else {
        alert('Foto bukti presensi sudah expired (melebihi 10 hari kerja).');
    }
}

/**
 * Render visualisasi 68 titik landmark wajah ke elemen canvas.
 * Digunakan oleh admin sebagai "bukti presensi" pengganti foto.
 * @param {HTMLCanvasElement} canvasEl - Element canvas target
 * @param {string|Array} landmarkData - JSON string atau array [{x,y},...]
 * @param {object} opts - Opsi tampilan {width, height, dotColor, lineColor, bgColor}
 */
function renderLandmarkCanvas(canvasEl, landmarkData, opts = {}) {
    if (!canvasEl) return;
    const {
        width    = 160,
        height   = 120,
        dotColor = '#60a5fa',  // biru
        lineColor = '#1e40af', // biru tua
        bgColor  = '#0f172a'  // hitam gelap
    } = opts;

    canvasEl.width  = width;
    canvasEl.height = height;
    const ctx = canvasEl.getContext('2d');
    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, width, height);

    let pts = null;
    try {
        pts = typeof landmarkData === 'string' ? JSON.parse(landmarkData) : landmarkData;
    } catch (e) {
        ctx.fillStyle = '#94a3b8';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Data landmark tidak valid', width / 2, height / 2);
        return;
    }

    if (!Array.isArray(pts) || pts.length === 0) {
        ctx.fillStyle = '#94a3b8';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data landmark', width / 2, height / 2);
        return;
    }

    // Segment landmark wajah (berdasarkan indeks face-api.js 68-point model)
    const segments = {
        jaw:       { range: [0,  16],  color: '#64748b' },  // abu-abu
        leftBrow:  { range: [17, 21],  color: '#fbbf24' },  // kuning
        rightBrow: { range: [22, 26],  color: '#fbbf24' },
        nose:      { range: [27, 35],  color: '#f97316' },  // oranye
        leftEye:   { range: [36, 41],  color: '#60a5fa' },  // biru
        rightEye:  { range: [42, 47],  color: '#60a5fa' },
        mouth:     { range: [48, 67],  color: '#f472b6' },  // pink
    };

    // Padding agar tidak terlalu mepet tepi
    const pad = 8;
    const scaleX = width  - pad * 2;
    const scaleY = height - pad * 2;

    function toCanvas(p) {
        return { x: pad + p.x * scaleX, y: pad + p.y * scaleY };
    }

    // Gambar garis per segmen
    for (const seg of Object.values(segments)) {
        const [start, end] = seg.range;
        ctx.strokeStyle = seg.color + '66'; // semi-transparan
        ctx.lineWidth = 1;
        ctx.beginPath();
        const first = toCanvas(pts[start]);
        ctx.moveTo(first.x, first.y);
        for (let i = start + 1; i <= end && i < pts.length; i++) {
            const p = toCanvas(pts[i]);
            ctx.lineTo(p.x, p.y);
        }
        // Tutup loop untuk mata dan mulut
        if (seg === segments.leftEye || seg === segments.rightEye || seg === segments.mouth) {
            ctx.closePath();
        }
        ctx.stroke();
    }

    // Gambar titik untuk semua landmark
    pts.forEach((pt, i) => {
        const p = toCanvas(pt);
        ctx.beginPath();
        ctx.arc(p.x, p.y, i < 17 ? 1.5 : 2, 0, Math.PI * 2);
        // Warna berbeda per area
        if (i <= 16)      ctx.fillStyle = '#64748b';
        else if (i <= 26) ctx.fillStyle = '#fbbf24';
        else if (i <= 35) ctx.fillStyle = '#f97316';
        else if (i <= 47) ctx.fillStyle = '#60a5fa';
        else              ctx.fillStyle = '#f472b6';
        ctx.fill();
    });

    // Label di pojok
    ctx.fillStyle = '#94a3b8';
    ctx.font = '9px monospace';
    ctx.textAlign = 'left';
    ctx.fillText('68-pt landmark', 3, height - 3);
}

function getPosition() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) return resolve(null);
        // Stricter options to enforce real-time, high-accuracy GPS
        const options = { 
            timeout: 20000, 
            enableHighAccuracy: true,
            maximumAge: 0 // Force device to get fresh coordinates, no cache
        };
        
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                // Reject extremely low accuracy (e.g., > 2000m) as it's often a sign of cell tower spoofing or lack of actual GPS lock
                if (pos.coords.accuracy > 2000) {
                    console.warn(`Location discarded due to terrible accuracy: ${pos.coords.accuracy}m`);
                    statusMessage('Akurasi lokasi buruk. Mohon cari area terbuka.', 'bg-yellow-100 text-yellow-700');
                    resolve(null);
                    return;
                }
                resolve(pos);
            }, 
            (err) => {
                console.warn('Geolocation error:', err);
                resolve(null);
            }, 
            options
        );
    });
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // metres
    const φ1 = lat1 * Math.PI/180;
    const φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180;
    const Δλ = (lon2-lon1) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // in metres
}

function showDiffLocationModal(data) {
    const modal = qs('#diff-location-modal');
    if (!modal) return showConfirmationModal(data);
    
    modal.classList.remove('hidden');
    
    qs('#diff-location-submit').onclick = () => {
        const reason = qs('#diff-location-reason-input').value.trim();
        if (!reason) {
            statusMessage('Harap isi alasan lokasi berbeda', 'bg-red-100 text-red-700');
            return;
        }
        data.diff_location_reason = reason;
        modal.classList.add('hidden');
        showConfirmationModal(data);
    };
    
    qs('#diff-location-cancel').onclick = () => {
        modal.classList.add('hidden');
        resumeDetection();
    };
}

// ---- Additional Helpers (from footer) ----

async function getStreetNameFromCoordinates(lat, lng) {
    try {
        const result = await api('?ajax=reverse_geocode', { action: 'reverse_geocode', lat: lat, lng: lng }, { suppressModal: true });
        if (result.ok && result.data && result.data.address) {
            // Simplified for brevity, assume result logic is similar to footer
             return result.data.display_name || `Lat: ${lat}, Lng: ${lng}`;
        }
    } catch (e) {}
    return null;
}

// ---- Modals ----

function showEarlyLeaveModal(message) {
    const modal = qs('#early-leave-modal');
    if (!modal) {
        // Fallback for other pages if needed
        const reason = prompt('Masukkan alasan pulang awal:\n' + message);
        if (reason) submitAttendanceWithReason({ ...window.pendingAttendanceData, early_leave_reason: reason });
        else isProcessingRecognition = false;
        return;
    }
    
    const msgEl = modal.querySelector('p');
    if (msgEl) msgEl.textContent = message || 'Anda melakukan presensi pulang sebelum waktunya. Harap isi alasan.';
    
    const inputEl = qs('#early-leave-input');
    if (inputEl) inputEl.value = '';
    
    modal.classList.remove('hidden');
    
    const submitBtn = qs('#early-leave-submit');
    const cancelBtn = qs('#early-leave-cancel');
    
    if (submitBtn) {
        submitBtn.onclick = () => {
            const reason = qs('#early-leave-input')?.value?.trim();
            if (!reason) {
                showNotif('Alasan pulang awal wajib diisi!', false);
                return;
            }
            modal.classList.add('hidden');
            submitAttendanceWithReason({ ...window.pendingAttendanceData, early_leave_reason: reason });
        };
    }
    if (cancelBtn) {
        cancelBtn.onclick = () => {
            modal.classList.add('hidden');
            isProcessingRecognition = false;
            resumeDetection();
        };
    }
}

function showWFAModal(message) {
    // FIX: Use the proper WFA reason modal instead of browser prompt()
    // Browser prompt() doesn't work on many mobile browsers and is bad UX
    const modal = qs('#wfa-reason-modal');
    if (!modal) {
        // Fallback if modal doesn't exist
        const reason = prompt('Masukkan alasan WFA:\n' + message);
        if (reason) submitAttendanceWithReason({ ...window.pendingAttendanceData, alasan_wfa: reason });
        else isProcessingRecognition = false;
        return;
    }
    
    // Show contextual message
    const msgEl = modal.querySelector('p');
    if (msgEl) msgEl.textContent = message || 'Anda berada di luar area WFO. Silakan isi alasan bekerja di luar kantor.';
    
    // Clear input
    const inputEl = qs('#wfa-reason-input');
    if (inputEl) inputEl.value = '';
    
    modal.classList.remove('hidden');
    
    const submitBtn = qs('#wfa-reason-submit');
    const cancelBtn = qs('#wfa-reason-cancel');
    
    // Remove old listeners to avoid stacking
    const newSubmit = submitBtn ? submitBtn.cloneNode(true) : null;
    const newCancel = cancelBtn ? cancelBtn.cloneNode(true) : null;
    if (submitBtn && newSubmit) submitBtn.parentNode.replaceChild(newSubmit, submitBtn);
    if (cancelBtn && newCancel) cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);
    
    if (newSubmit) {
        newSubmit.addEventListener('click', () => {
            const reason = qs('#wfa-reason-input')?.value?.trim();
            if (!reason) {
                showNotif('Alasan WFA wajib diisi!', false);
                return;
            }
            modal.classList.add('hidden');
            submitAttendanceWithReason({ ...window.pendingAttendanceData, alasan_wfa: reason });
        });
    }
    if (newCancel) {
        newCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
            isProcessingRecognition = false;
            resumeDetection();
        });
    }
}

function showOvertimeModal(message) {
    const modal = qs('#overtime-modal');
    if (!modal) {
        const reason = prompt('Masukkan alasan overtime:\n' + message);
        if (reason) submitAttendanceWithReason({ ...window.pendingAttendanceData, overtime_reason: reason });
        else isProcessingRecognition = false;
        return;
    }
    
    const msgEl = modal.querySelector('p');
    if (msgEl) msgEl.textContent = message || 'Presensi di hari libur dianggap overtime. Harap isi alasan.';
    
    modal.classList.remove('hidden');
    
    const submitBtn = qs('#overtime-submit');
    const cancelBtn = qs('#overtime-cancel');
    
    if (submitBtn) {
        submitBtn.onclick = () => {
            const reason = qs('#overtime-reason-input')?.value?.trim();
            const location = qs('#overtime-location-input')?.value?.trim();
            if (!reason) {
                showNotif('Alasan overtime wajib diisi!', false);
                return;
            }
            modal.classList.add('hidden');
            submitAttendanceWithReason({ ...window.pendingAttendanceData, overtime_reason: reason, overtime_location: location });
        };
    }
    if (cancelBtn) {
        cancelBtn.onclick = () => {
            modal.classList.add('hidden');
            isProcessingRecognition = false;
            resumeDetection();
        };
    }
}

function submitAttendanceWithReason(data) {
    isProcessingRecognition = true;
    statusMessage('Menyimpan presensi...', 'bg-blue-100 text-blue-700');

    const payload = {
        nim: data.nim || window.pendingAttendanceData.nim,
        mode: data.mode || window.pendingAttendanceData.mode || scanMode || 'masuk',
        ekspresi: data.ekspresi || window.pendingAttendanceData.ekspresi,
        screenshot: data.screenshot || window.pendingAttendanceData.screenshot,
        landmarks: JSON.stringify(data.landmarks || window.pendingAttendanceData.landmarks),
        lokasi: data.lokasi || window.pendingAttendanceData.lokasi,
        lat: data.lat || window.pendingAttendanceData.lat,
        lng: data.lng || window.pendingAttendanceData.lng,
        wfa_reason: data.alasan_wfa || window.pendingAttendanceData.alasan_wfa,
        early_leave_reason: data.early_leave_reason || window.pendingAttendanceData.early_leave_reason,
        overtime_reason: data.overtime_reason || window.pendingAttendanceData.overtime_reason,
        overtime_location: data.overtime_location || window.pendingAttendanceData.overtime_location,
        diff_location_reason: data.diff_location_reason || window.pendingAttendanceData.diff_location_reason
    };

    api('?ajax=save_attendance', payload, { suppressModal: true }).then(res => {
        if (res.ok) {
            const mode = payload.mode;
            const nim  = payload.nim;

            // 1. Show success message
            statusMessage(res.message || `Presensi ${mode} berhasil disimpan!`, 'bg-green-100 text-green-700');

            // 2. Mark as success & unpause detection flags
            isPresensiSuccess  = true;
            isDetectionPaused  = false;

            // 3. Play voice
            if (typeof speak === 'function') {
                speak('Presensi berhasil disimpan. Terima kasih.');
            }

            // 4. Show "Scan Berikutnya" button (kiosk mode — multiple employees on one device)
            const nextScanContainer = document.getElementById('next-scan-container');
            if (nextScanContainer) nextScanContainer.classList.remove('hidden');

            // 5. Refresh the attendance log immediately (no page reload needed)
            if (typeof updateLogAfterAttendance === 'function') {
                updateLogAfterAttendance(nim, mode);
            }

            // Clear pending data
            window.pendingAttendanceData = null;
        } else {
            statusMessage(res.message || 'Gagal menyimpan presensi. Silakan coba lagi.', 'bg-red-100 text-red-700');
            if (typeof speak === 'function') speak('Gagal. ' + (res.message || 'Silakan coba lagi.'));
            resumeDetection(); // Allow retry
        }
    }).catch(e => {
        console.error('submitAttendanceWithReason error:', e);
        statusMessage('Terjadi kesalahan saat menyimpan presensi.', 'bg-red-100 text-red-700');
        resumeDetection();
    }).finally(() => {
        isProcessingRecognition = false;
    });
}


// ---- Log Management (from footer for presensi.php) ----

async function loadLogMasuk() {
    try {
        const result = await api('?ajax=get_today_attendance', { type: 'masuk' }, { suppressModal: true, cache: false });
        if (result.ok) {
            console.log('Result from get_today_attendance (masuk):', result);
            logMasukData = result.data || [];
            renderLogMasuk();
        }
    } catch (error) { console.error('Error loading log masuk:', error); }
}

async function loadLogPulang() {
    try {
        const result = await api('?ajax=get_today_attendance', { type: 'pulang' }, { suppressModal: true, cache: false });
        if (result.ok) {
            console.log('Result from get_today_attendance (pulang):', result);
            logPulangData = result.data || [];
            renderLogPulang();
        }
    } catch (error) { console.error('Error loading log pulang:', error); }
}

function renderLogMasuk() {
    const body = qs('#log-masuk-body');
    if (!body) return;
    body.innerHTML = '';
    if (logMasukData.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Belum ada presensi masuk hari ini</td></tr>';
        return;
    }
    logMasukData.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';
        const jamMasuk = item.jam_masuk ? item.jam_masuk.substring(0, 5) : '-';
        const isExpired = !isWithin10WorkingDays(item.jam_masuk_iso);
        
        // Robust photo detection: prioritize foto_masuk if not truncated, fallback to screenshot_masuk
        const photoData = (item.foto_masuk && (item.foto_masuk.startsWith('data:image/') ? item.foto_masuk.length > 500 : true) ? item.foto_masuk : item.screenshot_masuk);
        
        let buktiHtml = '<span class="text-gray-400 text-xs">-</span>';
        
        if (isExpired && (photoData || item.landmark_masuk || item.ekspresi_masuk)) {
            const label = item.ekspresi_masuk_label || item.ekspresi_masuk || 'EXP';
            buktiHtml = `<button onclick="showExpiredModal()" class="px-2 py-1 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-bold uppercase hover:bg-gray-200 transition-colors mx-auto block shadow-sm border border-gray-200" title="Foto sudah expired">${label}</button>`;
        } else if (photoData) {
            let imgSrc = photoData;
            if (!imgSrc.startsWith('data:image/') && !imgSrc.startsWith('storage/') && !imgSrc.startsWith('attendance/')) {
                imgSrc = '/storage/attendance/' + photoData;
            } else if (imgSrc.startsWith('attendance/')) {
                imgSrc = '/storage/' + photoData;
            }
            
            buktiHtml = `<div class="flex justify-center">
                <img src="${imgSrc}" 
                     class="w-12 h-10 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:scale-110 transition-transform"
                     onclick="if(window.showImageModal) window.showImageModal('${imgSrc}', 'Bukti Masuk - ${item.nama}'); else if(window.showScreenshotModal) window.showScreenshotModal('${imgSrc}', 'Bukti Masuk')"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Err&background=fee2e2&color=ef4444';">
            </div>`;
        } else if (item.landmark_masuk) {
            const canvasId = `lm-masuk-${index}`;
            buktiHtml = `<canvas id="${canvasId}" class="rounded border border-gray-200 cursor-pointer hover:border-blue-400 transition-colors mx-auto block" width="80" height="60" title="Klik untuk lihat detail landmark" onclick="showLandmarkModal(this, 'Bukti Masuk: ${item.nama || ''}')"></canvas>`;
        } else if (item.ekspresi_masuk) {
            const label = item.ekspresi_masuk_label || item.ekspresi_masuk;
            const cls = item.ekspresi_masuk_class || 'bg-gray-100 text-gray-600';
            buktiHtml = `<span class="px-2 py-1 rounded-full text-[10px] font-bold ${cls} uppercase tracking-wider mx-auto block w-fit shadow-sm">${label}</span>`;
        }
        
        tr.innerHTML = `<td class="py-2 px-4 text-center">${index + 1}</td><td class="py-2 px-4">${item.nama || '-'}</td><td class="py-2 px-4 text-center">${jamMasuk}</td><td class="py-2 px-4 text-sm">${item.lokasi_masuk || '-'}</td><td class="py-2 px-4 text-center">${buktiHtml}</td>`;
        body.appendChild(tr);
        
        if (!photoData && item.landmark_masuk) {
            const canvas = document.getElementById(`lm-masuk-${index}`);
            if (canvas) {
                canvas._landmarkData = item.landmark_masuk;
                renderLandmarkCanvas(canvas, item.landmark_masuk, { width: 80, height: 60 });
            }
        }
    });
}

function renderLogPulang() {
    const body = qs('#log-pulang-body');
    if (!body) return;
    body.innerHTML = '';
    if (logPulangData.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Belum ada presensi pulang hari ini</td></tr>';
        return;
    }
    logPulangData.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';
        const jamPulang = item.jam_pulang ? item.jam_pulang.substring(0, 5) : '-';
        const isExpired = !isWithin10WorkingDays(item.jam_pulang_iso);
        
        // Robust photo detection: prioritize foto_pulang if not truncated, fallback to screenshot_pulang
        const photoData = (item.foto_pulang && (item.foto_pulang.startsWith('data:image/') ? item.foto_pulang.length > 500 : true) ? item.foto_pulang : item.screenshot_pulang);
        
        let buktiHtml = '<span class="text-gray-400 text-xs">-</span>';
        
        if (isExpired && (photoData || item.landmark_pulang || item.ekspresi_pulang)) {
            const label = item.ekspresi_pulang_label || item.ekspresi_pulang || 'EXP';
            buktiHtml = `<button onclick="showExpiredModal()" class="px-2 py-1 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-bold uppercase hover:bg-gray-200 transition-colors mx-auto block shadow-sm border border-gray-200" title="Foto sudah expired">${label}</button>`;
        } else if (photoData) {
            let imgSrc = photoData;
            if (!imgSrc.startsWith('data:image/') && !imgSrc.startsWith('storage/') && !imgSrc.startsWith('attendance/')) {
                imgSrc = '/storage/attendance/' + photoData;
            } else if (imgSrc.startsWith('attendance/')) {
                imgSrc = '/storage/' + photoData;
            }
            
            buktiHtml = `<div class="flex justify-center">
                <img src="${imgSrc}" 
                     class="w-12 h-10 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:scale-110 transition-transform"
                     onclick="if(window.showImageModal) window.showImageModal('${imgSrc}', 'Bukti Pulang - ${item.nama}'); else if(window.showScreenshotModal) window.showScreenshotModal('${imgSrc}', 'Bukti Pulang')"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Err&background=fee2e2&color=ef4444';">
            </div>`;
        } else if (item.landmark_pulang) {
            const canvasId = `lm-pulang-${index}`;
            buktiHtml = `<canvas id="${canvasId}" class="rounded border border-gray-200 cursor-pointer hover:border-green-400 transition-colors mx-auto block" width="80" height="60" title="Klik untuk lihat detail landmark" onclick="showLandmarkModal(this, 'Bukti Pulang: ${item.nama || ''}')"></canvas>`;
        } else if (item.ekspresi_pulang) {
            const label = item.ekspresi_pulang_label || item.ekspresi_pulang;
            const cls = item.ekspresi_pulang_class || 'bg-gray-100 text-gray-600';
            buktiHtml = `<span class="px-2 py-1 rounded-full text-[10px] font-bold ${cls} uppercase tracking-wider mx-auto block w-fit shadow-sm">${label}</span>`;
        }
        
        tr.innerHTML = `<td class="py-2 px-4 text-center">${index + 1}</td><td class="py-2 px-4">${item.nama || '-'}</td><td class="py-2 px-4 text-center">${jamPulang}</td><td class="py-2 px-4 text-sm">${item.lokasi_pulang || '-'}</td><td class="py-2 px-4 text-center">${buktiHtml}</td>`;
        body.appendChild(tr);
        
        if (!photoData && item.landmark_pulang) {
            const canvas = document.getElementById(`lm-pulang-${index}`);
            if (canvas) {
                canvas._landmarkData = item.landmark_pulang;
                renderLandmarkCanvas(canvas, item.landmark_pulang, { width: 80, height: 60 });
            }
        }
    });
}

function updateLogAfterAttendance(nim, mode) {
    // Clear API Cache to force fresh logs
    if (typeof apiCache !== 'undefined' && apiCache.clear) {
        apiCache.clear();
    }
    
    if (mode === 'masuk') loadLogMasuk();
    else loadLogPulang();
}

function checkAndResetLogDaily() {
    const today = new Date().toDateString();
    const lastReset = localStorage.getItem('lastLogReset');
    if (lastReset !== today) {
        logMasukData = [];
        logPulangData = [];
        localStorage.setItem('lastLogReset', today);
    }
}

function resetRecognitionSystem() {
    detectionHistory = []; // Ensure globals exist
    recognitionCompleted = false;
    isProcessingRecognition = false;
    lastSuccessfulDetection = null;
}

function stopDetection() {
    isDetectionStopped = true;
    if(videoInterval) { clearInterval(videoInterval); videoInterval = null; }
    resetRecognitionSystem();
}

/**
 * Modal untuk melihat detail landmark wajah (bukti presensi) dalam ukuran besar.
 * Dipanggil saat admin klik canvas kecil di tabel presensi.
 * @param {HTMLCanvasElement} sourceCanvas - Canvas kecil yang diklik
 * @param {string} title - Judul modal
 */
function showLandmarkModal(sourceCanvas, title) {
    const landmarkData = sourceCanvas._landmarkData;
    if (!landmarkData) return;

    // Buat modal jika belum ada
    let modal = document.getElementById('landmark-detail-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'landmark-detail-modal';
        modal.className = 'fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="lm-modal-title" class="text-lg font-bold text-gray-800"></h3>
                    <button onclick="document.getElementById('landmark-detail-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <div class="bg-gray-900 rounded-xl overflow-hidden mb-4">
                    <canvas id="lm-modal-canvas" class="w-full" style="display:block"></canvas>
                </div>
                <div class="text-xs text-gray-500 space-y-1">
                    <p>&#9679; <span style="color:#64748b">Abu-abu</span>: Garis rahang (17 titik)</p>
                    <p>&#9679; <span style="color:#fbbf24">Kuning</span>: Alis kiri &amp; kanan (10 titik)</p>
                    <p>&#9679; <span style="color:#f97316">Oranye</span>: Hidung (9 titik)</p>
                    <p>&#9679; <span style="color:#60a5fa">Biru</span>: Mata kiri &amp; kanan (12 titik)</p>
                    <p>&#9679; <span style="color:#f472b6">Pink</span>: Mulut &amp; bibir (20 titik)</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        // Klik luar untuk tutup
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    }

    document.getElementById('lm-modal-title').textContent = title || 'Bukti Presensi (68-pt Landmark)';
    const bigCanvas = document.getElementById('lm-modal-canvas');
    renderLandmarkCanvas(bigCanvas, landmarkData, { width: 400, height: 300 });
    modal.classList.remove('hidden');
}

// Ensure detectionHistory is declared
let detectionHistory = [];
let lastSuccessfulDetection = null;

// ---- Initialization ----

document.addEventListener('DOMContentLoaded', () => {
    // Re-bind variables
    video = document.getElementById('video');
    canvas = document.getElementById('overlay') || document.getElementById('canvas');
    loadingOverlay = document.getElementById('loading-overlay');
    presensiStatus = document.getElementById('presensi-status');
    scanButtonsContainer = document.getElementById('scan-buttons');
    videoContainer = document.getElementById('video-container');
    btnBackScan = document.getElementById('btn-back-scan');
    btnScanMasuk = document.getElementById('btn-scan-masuk');
    btnScanPulang = document.getElementById('btn-scan-pulang');
    
    // Auto hook buttons
    if (btnScanMasuk) btnScanMasuk.addEventListener('click', () => startScan('masuk'));
    if (btnScanPulang) btnScanPulang.addEventListener('click', () => startScan('pulang'));
    if (btnBackScan) btnBackScan.addEventListener('click', resetPresensiPage);
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('page') === 'presensi-masuk') {
        // Special case: late_req mode from admin help
        if (urlParams.get('mode') === 'late_req') {
            setTimeout(() => {
                startScan('masuk');
                statusMessage('Mode Request Terlambat: Silakan verifikasi wajah Anda', 'bg-indigo-100 text-indigo-700');
            }, 500);
        } else {
            setTimeout(() => startScan('masuk'), 500);
        }
    }
    if (urlParams.get('page') === 'presensi-pulang') {
        setTimeout(() => startScan('pulang'), 500);
    }
});

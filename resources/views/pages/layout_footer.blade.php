<!-- Loading Overlay for model -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-75 flex flex-col items-center justify-center z-60 hidden">
    <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-24 w-24 mb-4"></div>
    <h2 class="text-center text-white text-xl font-semibold">Memuat Sistem Presensi...</h2>
    <p class="w-1/3 text-center text-white text-sm">Memuat model AI dan database wajah. Mohon tunggu sebentar.</p>
    <div class="mt-4 text-white text-xs opacity-75">
        <div id="loading-progress">Memulai...</div>
    </div>
</div>

<div id="notif-bar" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-lg z-70 hidden"></div>

<!-- Global Notification Modal -->
<div id="global-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] hidden">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-0 overflow-hidden animate-fade-in-up">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4">
            <div id="global-modal-title" class="text-lg font-bold text-white">Notifikasi</div>
        </div>
        <div class="p-6">
            <div id="global-modal-message" class="text-gray-700 text-base leading-relaxed"></div>
        </div>
        <div id="global-modal-actions" class="px-6 pb-6 flex gap-3 justify-end">
            <button id="global-modal-cancel" class="hidden px-5 py-2.5 rounded-xl font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all">
                Batal
            </button>
            <button id="global-modal-ok" class="px-5 py-2.5 rounded-xl font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white transition-all shadow-md hover:shadow-lg">
                OK
            </button>
        </div>
    </div>
</div>

<script>
// Global state logic
if (typeof dashboardCharts === 'undefined') {
    window.dashboardCharts = {}; // Global chart instance holder
}
if (typeof isInitRekapRunning === 'undefined') {
    window.isInitRekapRunning = false;
}
// SPBW_SYSTEM_FIX_MARKER: POLICY_SYNC_V4
if (typeof currentRekapData === 'undefined') {
    window.currentRekapData = null; // Store rekap data for week filtering
}

// --- RESTORED CORE JS LOGIC ---

/**
 * Cek apakah sebuah tanggal masih dalam kurun waktu 10 hari kerja terakhir.
 * Digunakan untuk validasi tampilan bukti presensi (screenshot/landmark).
 */
window.isWithin10WorkingDays = function(dateString) {
    if (!dateString) return false;
    const recordDate = new Date(dateString);
    recordDate.setHours(0, 0, 0, 0);
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (recordDate > today) return true;
    
    let workingDaysCount = 0;
    let tempDate = new Date(recordDate);
    
    while (tempDate <= today) {
        const day = tempDate.getDay();
        if (day !== 0 && day !== 6) {
            workingDaysCount++;
        }
        tempDate.setDate(tempDate.getDate() + 1);
    }
    
    return workingDaysCount <= 11;
};

window.showExpiredModal = function() {
    if (typeof showModalNotif === 'function') {
        showModalNotif('Bukti Kadaluarsa', 'Maaf, foto bukti presensi ini sudah dihapus dari sistem karena sudah melewati batas penyimpanan 10 hari kerja.', 'info');
    } else {
        alert('Foto bukti presensi sudah expired (melebihi 10 hari kerja).');
    }
};

window.translateExpression = function(exp) {
    if (!exp) return 'Netral';
    const dict = {
        'neutral': 'Netral',
        'happy': 'Bahagia',
        'sad': 'Sedih',
        'angry': 'Marah',
        'fearful': 'Takut',
        'disgusted': 'Jijik',
        'surprised': 'Terkejut'
    };
    return dict[exp.toLowerCase()] || exp;
};

// Global Lazy Loading for Member Photos
window.lazyLoadMemberPhoto = async function(id, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    try {
        const res = await api(`?ajax=get_member_photo&id=${id}`);
        if (res.ok && res.image) {
            container.innerHTML = `<img src="${res.image}" class="h-full w-full object-cover transition-transform duration-200 hover:scale-110" onclick="showScreenshotModal('${res.image}', 'Foto Member')">`;
        } else {
            container.innerHTML = `<span class="text-[10px] text-red-500">Gagal</span>`;
        }
    } catch (e) {
        console.error('Error loading member photo:', e);
        container.innerHTML = `<span class="text-[10px] text-red-500">Error</span>`;
    }
};

// Global Intersection Observer for Member Photos
window.memberPhotoObserver = window.IntersectionObserver ? new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const id = el.dataset.id;
            const containerId = el.id;
            if (id && !el.dataset.loaded) {
                el.dataset.loaded = "true";
                if (window.lazyLoadMemberPhoto) window.lazyLoadMemberPhoto(id, containerId);
            }
        }
    });
}, { rootMargin: '50px' }) : null;


// Initialize speech synthesis for offline use
window.initializeSpeechSynthesis = function() {
    try {
        if ('speechSynthesis' in window) {
            // Pre-load voices for offline use
            const loadVoices = () => {
                const voices = speechSynthesis.getVoices();
                console.log('Available voices:', voices.length);
                
                const indonesianVoices = voices.filter(voice => 
                    voice.lang.startsWith('id') || 
                    voice.lang.includes('Indonesian') ||
                    voice.name.includes('Indonesian')
                );
                
                if (indonesianVoices.length > 0) {
                    console.log('Indonesian voices found:', indonesianVoices.map(v => v.name));
                } else {
                    console.log('No Indonesian voices found, will use default voice');
                }
            };

            if (speechSynthesis.getVoices().length > 0) {
                loadVoices();
            } else {
                speechSynthesis.addEventListener('voiceschanged', loadVoices, { once: true });
            }
            
            console.log('Speech synthesis initialized for offline use');
        } else {
            console.warn('Speech synthesis not supported in this browser');
        }
    } catch (error) {
        console.error('Failed to initialize speech synthesis:', error);
    }
};

/**
 * Render landmark titik wajah ke canvas
 */
window.renderLandmarkOnCanvas = function(canvas, landmarkData, width, height) {
    if (!canvas || !landmarkData) return;
    const ctx = canvas.getContext('2d');
    let points = [];
    try {
        points = typeof landmarkData === 'string' ? JSON.parse(landmarkData) : landmarkData;
    } catch (e) { return; }
    
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#6366f1'; // Indigo-500
    points.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x * width, p.y * height, 1, 0, 2 * Math.PI);
        ctx.fill();
    });
};

// Speak helper function — robust version that handles Chrome speechSynthesis quirks
window.speak = function(text, rate = 1.0) {
    if (!('speechSynthesis' in window)) return;
    if (!text) return;

    // Cancel any ongoing speech first
    window.speechSynthesis.cancel();

    // Chrome has a bug: speaking immediately after cancel() silently fails.
    // A small delay (50ms) fixes this reliably.
    setTimeout(() => {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = rate;
        utterance.volume = 1.0;

        // Try to use Indonesian voice, fallback to first available
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => v.lang.startsWith('id'));
        if (idVoice) {
            utterance.voice = idVoice;
            utterance.lang = idVoice.lang;
        } else {
            utterance.lang = 'id-ID'; // Still set lang even without matching voice
        }

        utterance.onerror = (e) => {
            if (e.error !== 'interrupted') console.warn('Speech error:', e.error);
        };

        window.speechSynthesis.speak(utterance);
    }, 50);
};

// Initialize face recognition system
window.initializeFaceRecognition = async function() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const isLandingPage = urlParams.get('page') === 'landing';
        const isPresensiPage = document.getElementById('page-presensi') !== null;
        
        if (!isLandingPage && !isPresensiPage) {
            console.log('Skipping face recognition initialization on non-attendance page');
            return;
        }

        console.log('Initializing face recognition system...');
        if (typeof loadFaceRecognitionSettings === 'function') {
            await loadFaceRecognitionSettings();
        }
        
        // Pre-warm: store the promise so startScan can await it instead of double-loading
        if (!window._preWarmReady) {
            window._preWarmReady = (async () => {
                if (typeof loadPresensiFaceModels === 'function') {
                    await loadPresensiFaceModels();
                }
            })().catch(e => console.warn('Pre-warm failed silently:', e));
        }
        // Don't await — let it run in background while user reads the page
        
        console.log('✅ Face recognition system pre-warm started');
    } catch (error) {
        console.error('❌ Failed to initialize face recognition:', error);
    }
};

// Supporting functions for face recognition
window.fetchMembers = async function() {
    try {
        const res = await api('?ajax=get_members&light=1');
        return res.data || [];
    } catch (e) {
        console.error('Failed to fetch members:', e);
        return [];
    }
};

window.loadLabeledFaceDescriptors = async function() {
    const membersList = await fetchMembers();

    // CRITICAL: Always populate the global members array for name lookup
    // The attendance.js detection loop uses `members` to find m.nama from the matched label
    if (typeof members !== 'undefined') {
        members = membersList;
    }

    // --- Fast path: Use IndexedDB cache if available ---
    const versionKey = typeof computeMembersVersionKey === 'function' ? await computeMembersVersionKey(membersList) : null;
    if (versionKey && typeof idbGetDescriptors === 'function') {
        const cached = await idbGetDescriptors(versionKey);
        if (cached && Array.isArray(cached) && cached.length > 0) {
            labeledFaceDescriptors = cached.map(item => new faceapi.LabeledFaceDescriptors(
                item.label,
                item.descriptors.map(d => new Float32Array(d))
            ));
            console.log('✅ Loaded face descriptors from IDB cache:', labeledFaceDescriptors.length, '| members:', membersList.length);
            return;
        }
    }

    // --- Medium path: Use pre-computed face_embedding from server DB (fast, no image loading) ---
    labeledFaceDescriptors = [];
    const membersWithEmbedding = membersList.filter(m => m.face_embedding);
    const membersNeedingCompute = membersList.filter(m => !m.face_embedding && (m.foto_base64 || m.has_foto));

    if (membersWithEmbedding.length > 0) {
        console.log(`⚡ Loading ${membersWithEmbedding.length} pre-computed embeddings from server...`);
        for (const m of membersWithEmbedding) {
            try {
                const desc = new Float32Array(JSON.parse(m.face_embedding));
                if (desc.length === 128) {
                    const label = String(m.nim || m.nama || m.id);
                    labeledFaceDescriptors.push(new faceapi.LabeledFaceDescriptors(label, [desc]));
                }
            } catch (e) { console.warn('Failed to parse embedding for', m.nama); }
        }
        console.log(`✅ Loaded ${labeledFaceDescriptors.length} embeddings instantly from server.`);
    }

    // --- Slow path: Only compute from image for members missing an embedding ---
    if (membersNeedingCompute.length > 0) {
        console.log(`🐢 Computing ${membersNeedingCompute.length} missing embeddings from photos (fallback)...`);
        const loadingProgress = qs('#loading-progress');
        for (const m of membersNeedingCompute) {
            if (loadingProgress) loadingProgress.textContent = `Menghitung vektor wajah: ${m.nama}...`;
            try {
                let photo = m.foto_base64;
                if (!photo && m.has_foto && typeof api === 'function') {
                    const photoRes = await api(`?ajax=get_member_photo&id=${m.id}`);
                    if (photoRes && photoRes.ok) photo = photoRes.image;
                }
                
                if (!photo) continue;
                
                const img = await faceapi.fetchImage(photo);
                const det = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.3 }))
                    .withFaceLandmarks().withFaceDescriptor();
                if (det) {
                    const label = String(m.nim || m.nama || m.id);
                    labeledFaceDescriptors.push(new faceapi.LabeledFaceDescriptors(label, [det.descriptor]));
                }
            } catch (err) { console.warn('Detection failed for', m.nama, err); }
        }
    }

    // Save to IDB for next time
    if (versionKey && typeof idbSetDescriptors === 'function' && labeledFaceDescriptors.length > 0) {
        const toStore = labeledFaceDescriptors.map(ld => ({
            label: ld.label,
            descriptors: ld.descriptors.map(arr => Array.from(arr))
        }));
        idbSetDescriptors(versionKey, toStore).catch(() => {});
    }

    console.log('✅ Total face descriptors loaded:', labeledFaceDescriptors.length);
};


function showNotif(msg, success=true){
    const bar = qs('#notif-bar');
    if (!bar) return;
    bar.textContent = msg;
    bar.className = `fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-lg shadow-lg z-[9999] ${success?'bg-green-600':'bg-red-600'} text-white font-bold`;
    bar.classList.remove('hidden');
    setTimeout(()=> bar.classList.add('hidden'), 2000); 
}
function showModalNotif(message, success=true, title='Notifikasi'){
    const m = qs('#global-modal');
    const t = qs('#global-modal-title');
    const c = qs('#global-modal-message');
    const okBtn = qs('#global-modal-ok');
    const cancelBtn = qs('#global-modal-cancel');
    
    if(!m||!t||!c) return showNotif(message, success);
    
    t.textContent = title;
    c.textContent = message;
    
    const handleOk = () => {
        m.classList.add('hidden');
        okBtn.removeEventListener('click', handleOk);
    };
    
    // Show only OK button for alerts
    if(okBtn) {
        okBtn.classList.remove('hidden');
        okBtn.addEventListener('click', handleOk);
    }
    if(cancelBtn) cancelBtn.classList.add('hidden');
    
    m.classList.remove('hidden');
}

// Custom Alert (replaces alert())
function customAlert(message, title = 'Pemberitahuan') {
    return new Promise((resolve) => {
        const m = qs('#global-modal');
        const t = qs('#global-modal-title');
        const c = qs('#global-modal-message');
        const okBtn = qs('#global-modal-ok');
        const cancelBtn = qs('#global-modal-cancel');
        
        if(!m||!t||!c) {
            alert(message);
            resolve();
            return;
        }
        
        t.textContent = title;
        c.textContent = message;
        
        // Show only OK button
        if(okBtn) okBtn.classList.remove('hidden');
        if(cancelBtn) cancelBtn.classList.add('hidden');
        
        m.classList.remove('hidden');
        
        const handleOk = () => {
            m.classList.add('hidden');
            okBtn.removeEventListener('click', handleOk);
            resolve();
        };
        
        okBtn.addEventListener('click', handleOk);
    });
}

// Custom Confirm (replaces confirm())
function customConfirm(message, title = 'Konfirmasi') {
    return new Promise((resolve) => {
        const m = qs('#global-modal');
        const t = qs('#global-modal-title');
        const c = qs('#global-modal-message');
        const okBtn = qs('#global-modal-ok');
        const cancelBtn = qs('#global-modal-cancel');
        
        if(!m||!t||!c) {
            resolve(confirm(message));
            return;
        }
        
        t.textContent = title;
        c.textContent = message;
        
        // Show both buttons
        if(okBtn) okBtn.classList.remove('hidden');
        if(cancelBtn) cancelBtn.classList.remove('hidden');
        
        m.classList.remove('hidden');
        
        const handleOk = () => {
            m.classList.add('hidden');
            cleanup();
            resolve(true);
        };
        
        const handleCancel = () => {
            m.classList.add('hidden');
            cleanup();
            resolve(false);
        };
        
        const cleanup = () => {
            okBtn.removeEventListener('click', handleOk);
            cancelBtn.removeEventListener('click', handleCancel);
        };
        
        okBtn.addEventListener('click', handleOk);
        cancelBtn.addEventListener('click', handleCancel);
    });
}

// Close modal when clicking outside (for alerts only)
document.addEventListener('click', (e)=>{
    const m = qs('#global-modal');
    const cancelBtn = qs('#global-modal-cancel');
    
    // Only allow backdrop close for alerts (when cancel button is hidden)
    if(e.target.id==='global-modal' && cancelBtn && cancelBtn.classList.contains('hidden')){
        m.classList.add('hidden');
    }
});
function qs(sel){ return document.querySelector(sel); }
function qsa(sel){ return Array.from(document.querySelectorAll(sel)); }

// KPI Filters (Global) - Re-initialized here after utility functions are defined
let kpiFilterType, kpiFilterMonth, kpiFilterYear;
function initKpiGlobals() {
    kpiFilterType = qs('#kpi-filter-type');
    kpiFilterMonth = qs('#kpi-filter-month');
    kpiFilterYear = qs('#kpi-filter-year');
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKpiGlobals);
} else {
    initKpiGlobals();
}

// Screenshot modal functions
async function loadAndShowEvidence(id, type, title) {
    try {
        const modal = qs('#screenshot-modal');
        const modalImage = qs('#screenshot-modal-image');
        const modalTitle = qs('#screenshot-modal-title');
        
        if (modal && modalTitle) {
            modalTitle.textContent = 'Memuat ' + title + '...';
            if (modalImage) { modalImage.src = ''; }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        const r = await api('?ajax=get_attendance_evidence&id=' + id + '&type=' + type, {}, { suppressModal: true });
        
        // Handle landmark response (face geometry proof)
        if (r && r.ok && (r.landmark || r.type === 'landmark')) {
            if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
            if (typeof showAdminLandmarkModal === 'function') {
                showAdminLandmarkModal(r.landmark, title);
            } else {
                showNotif('Landmark wajah tersedia tapi visualisasi tidak tersedia di halaman ini.', true);
            }
            return;
        }
        
        // Handle image response
        const imgData = r.data || r.image || r.evidence;
        if (r && r.ok && imgData) {
            showScreenshotModal(imgData, title);
        } else {
            if (modal) modal.classList.add('hidden');
            showNotif('Tidak ada bukti tersedia untuk record ini', false);
        }
    } catch (e) {
        console.error('Error loading evidence:', e);
        showNotif('Terjadi kesalahan saat memuat bukti', false);
    }
}

async function loadLazyProof(id, type, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    try {
        const r = await api('?ajax=get_attendance_evidence&id=' + id + '&type=' + type, {}, { suppressModal: true });
        
        // Handle image data (Prioritas Utama: Foto Wajah / Bukti Izin)
        const imgData = r.data || r.image || r.evidence;
        if (r && r.ok && imgData) {
            container.innerHTML = `<img src="${imgData}" class="w-full h-full object-contain rounded border shadow-sm hover:scale-110 transition-transform duration-200" onclick="showScreenshotModal('${imgData}', 'Bukti presensi')">` ;
            return;
        }

        // Handle landmark data (Fallback: Geometri Wajah)
        if (r && r.ok && r.has_landmark && r.landmark) {
            container.innerHTML = '';
            const c = document.createElement('canvas');
            c.style.cssText = 'border-radius:.5rem;cursor:pointer;width:100%;background:#0f172a';
            c.title = 'Klik untuk memperbesar visualisasi wajah';
            if (typeof renderLandmarkCanvas === 'function') {
                renderLandmarkCanvas(c, r.landmark, { width: 320, height: 240 });
                c.onclick = () => { if(typeof showAdminLandmarkModal === 'function') showAdminLandmarkModal(r.landmark, 'Bukti Presensi Wajah'); };
            }
            container.appendChild(c);
            return;
        }

        container.innerHTML = `<div class="text-xs text-gray-400 text-center">Tidak ada bukti</div>`;
    } catch (e) {
        container.innerHTML = `<div class="text-xs text-red-500 text-center">Error</div>`;
    }
}

// Setup lazy loading observer
let evidenceObserver = null;
if (window.IntersectionObserver) {
    evidenceObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const id = el.dataset.id;
                const type = el.dataset.type;
                const containerId = el.id;
                if (id && type && !el.dataset.loaded) {
                    el.dataset.loaded = "true";
                    loadLazyProof(id, type, containerId);
                }
            }
        });
    }, { rootMargin: '50px' });
}

function showScreenshotModal(imageSrc, title) {
    const modal = qs('#screenshot-modal');
    const modalTitle = qs('#screenshot-modal-title');
    const modalImage = qs('#screenshot-modal-image');
    
    if (modal && modalTitle && modalImage) {
        modalTitle.textContent = title;
        modalImage.src = imageSrc;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeScreenshotModal() {
    const modal = qs('#screenshot-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

window.showAdminLandmarkModal = function(landmarkData, title) {
    const modal = document.getElementById('landmark-modal');
    const canvas = document.getElementById('landmark-modal-canvas');
    const titleEl = document.getElementById('landmark-modal-title');
    
    if (modal && canvas) {
        if (titleEl) titleEl.innerHTML = `<i class="fi fi-sr-face-recognition text-blue-400"></i> ${title}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Wait for modal to be visible before rendering
        setTimeout(() => {
            if (typeof renderLandmarkCanvas === 'function') {
                renderLandmarkCanvas(canvas, landmarkData, { width: 640, height: 480 });
            }
        }, 50);
    }
};

window.closeLandmarkModal = function() {
    const modal = document.getElementById('landmark-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

// Close screenshot modal when clicking outside
document.addEventListener('click', (e) => {
    const modal = qs('#screenshot-modal');
    if (modal && !modal.contains(e.target) && !e.target.closest('img[onclick*="showScreenshotModal"]')) {
        closeScreenshotModal();
    }
});
// Add global variables to manage speech synthesis
let currentSpeech = null;
let speechQueue = [];
let isSpeaking = false;
let speechInterval = null;

function speak(text) {
    try {
        // Check if speech synthesis is available
        if (!('speechSynthesis' in window)) {
            console.warn('Speech synthesis not supported');
            return;
        }

        // Add to queue instead of canceling immediately
        if (text && text.trim() && text !== lastSpokenMessage) {
            speechQueue.push(text);
            lastSpokenMessage = text;
        }

        // Start speech processing if not already running
        if (!isSpeaking) {
            processSpeechQueue();
        }
        return;

    } catch (e) {
        console.error('Speech synthesis error:', e);
        isSpeaking = false;
        speechQueue = [];
    }
}

function processSpeechQueue() {
    if (isSpeaking || speechQueue.length === 0) return;
    
    isSpeaking = true;
    const text = speechQueue.shift();
    
    try {
        // Cancel any ongoing speech
        speechSynthesis.cancel();
        
        // Wait for voices to be loaded
        const speakWithVoice = () => {
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'id-ID';
            u.rate = 0.9; // Faster rate for speed
            u.pitch = 1.0;
            u.volume = 1.0;

            // Try to use a local voice if available
            const voices = speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => 
                voice.lang.startsWith('id') || 
                voice.lang.includes('Indonesian') ||
                voice.name.includes('Indonesian')
            );
            
            if (indonesianVoice) {
                u.voice = indonesianVoice;
            } else if (voices.length > 0) {
                // Use any available voice as fallback
                u.voice = voices[0];
            }

            u.onstart = () => {
                console.log('Speech started:', text);
            };

            u.onend = () => {
                console.log('Speech ended:', text);
                isSpeaking = false;
                
                // Process next in queue after a short delay
                setTimeout(() => {
                    if (speechQueue.length > 0) {
                        processSpeechQueue();
                    } else if (isCameraActive && !videoInterval && !isDetectionStopped) {
                        startVideoInterval();
                    }
                }, 200); // 200ms interval between speeches
            };

            u.onerror = (e) => {
                console.error('Speech error:', e);
                isSpeaking = false;
                
                // Skip this speech and continue with queue
                setTimeout(() => {
                    if (speechQueue.length > 0) {
                        processSpeechQueue();
                    } else if (isCameraActive && !videoInterval && !isDetectionStopped) {
                        startVideoInterval();
                    }
                }, 100);
            };

            speechSynthesis.speak(u);
            currentSpeech = u;
        };

        // If voices are already loaded, speak immediately
        if (speechSynthesis.getVoices().length > 0) {
            speakWithVoice();
        } else {
            // Wait for voices to load
            speechSynthesis.addEventListener('voiceschanged', speakWithVoice, { once: true });
            
            // Fallback if no voices
            if (speechSynthesis.getVoices().length === 0) {
                console.warn('No voices available, speaking with default settings');
                speakWithVoice();
            }
        }

    } catch (e) {
        console.error('Speech processing error:', e);
        isSpeaking = false;
        
        // Continue with queue
        setTimeout(() => {
            if (speechQueue.length > 0) {
                processSpeechQueue();
            }
        }, 100);
    }
}

// Modify the `statusMessage` function to use the improved `speak` function
let notifLockUntil = 0;
function statusMessage(text, cls) {
    if (!presensiStatus) return;
    
    // Show the text notification
    presensiStatus.textContent = text;
    presensiStatus.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md ' + cls;
    presensiStatus.classList.remove('hidden');

    // Hindari interupsi suara untuk pesan non-kritis
    const now = Date.now();
    const isCritical = /bg-(green|yellow|red)-100/.test(cls || '');
    if (isCritical || now > notifLockUntil) {
        // Hitung durasi lock berdasarkan panjang teks agar tidak terpotong
        const dur = Math.max(2500, Math.min(7000, text.length * 60));
        notifLockUntil = now + dur;
        speak(text);
    }
}



// ===== IndexedDB caching for face descriptors =====
function simpleHash(str){
    let h = 5381; for (let i=0;i<str.length;i++){ h = ((h<<5)+h) + str.charCodeAt(i); h |= 0; }
    return 'v' + (h >>> 0).toString(16);
}

async function computeMembersVersionKey(membersList){
    try{
        const basis = membersList.map(m=>[m.nim, m.foto||m.photo||m.image||'', m.nama||'']).sort((a,b)=>String(a[0]).localeCompare(String(b[0])));
        return simpleHash(JSON.stringify(basis)) + "-v3-members-fix"; // v3: force cache bust for members array fix
    }catch(e){ return 'v-default'; }
}

function idbOpen(){
    return new Promise((resolve,reject)=>{
        const req = indexedDB.open('presensi-cache', 1);
        req.onupgradeneeded = (e)=>{
            const db = e.target.result;
            if (!db.objectStoreNames.contains('descriptors')) {
                db.createObjectStore('descriptors');
            }
        };
        req.onsuccess = ()=> resolve(req.result);
        req.onerror = ()=> reject(req.error);
    });
}

async function idbGetDescriptors(versionKey){
    try{
        const db = await idbOpen();
        return await new Promise((resolve,reject)=>{
            const tx = db.transaction('descriptors','readonly');
            const store = tx.objectStore('descriptors');
            const getReq = store.get(versionKey);
            getReq.onsuccess = ()=> resolve(getReq.result||null);
            getReq.onerror = ()=> resolve(null);
        });
    }catch(e){ return null; }
}

async function idbSetDescriptors(versionKey, data){
    try{
        const db = await idbOpen();
        return await new Promise((resolve,reject)=>{
            const tx = db.transaction('descriptors','readwrite');
            const store = tx.objectStore('descriptors');
            const putReq = store.put(data, versionKey);
            putReq.onsuccess = ()=> resolve(true);
            putReq.onerror = ()=> resolve(false);
        });
    }catch(e){ return false; }
}

// ===== Dashboard Refresh Helper =====
async function refreshDashboardComponents() {
    console.log('[Dashboard] Refreshing all components and clearing cache...');
    
    // 1. Clear API Cache to force fresh data
    if (typeof apiCache !== 'undefined' && apiCache.clear) {
        apiCache.clear();
    }
    
    // 2. Refresh Rekap Page if on that page
    if (typeof initRekapPage === 'function') {
        initRekapPage();
    }
    
    // 3. Refresh Missing Reports Shortcut
    if (typeof loadMissingDailyReports === 'function') {
        await loadMissingDailyReports();
    }
    
    // 4. Refresh Robot Cat Character (with small delay for consistency)
    setTimeout(() => {
        if (typeof loadRobotCatCharacter === 'function') {
            loadRobotCatCharacter();
        }
    }, 500);
}

// ===== API Caching =====
const apiCache = {
    data: {},
    get: function(url, params) {
        const key = url + JSON.stringify(params || {});
        const entry = this.data[key];
        if (entry && Date.now() < entry.expiry) {
            return entry.response;
        }
        return null;
    },
    set: function(url, params, response, ttl = 60000) {
        const key = url + JSON.stringify(params || {});
        this.data[key] = {
            response: response,
            expiry: Date.now() + ttl
        };
    },
    clear: function() {
        this.data = {};
    }
};

async function api(url, data, opts){
    const options = opts || {};
    try {
        // Log the data being sent (but not the full screenshot to avoid console spam)
        const logData = { ...data };
        if (logData.screenshot) {
            logData.screenshot = logData.screenshot.substring(0, 50) + '... (truncated)';
        }
        // ULTRA-FAST: Skip logging for maximum speed
        
        // Ensure URL is correct - use relative URL to avoid port issues
        if (url.startsWith('http')) {
            // If it's already a full URL, use it as is
        } else if (url.startsWith('/')) {
            // If it starts with /, it's already a proper relative URL
        } else if (url.startsWith('?')) {
            // If it starts with ?, it's a query string, use current page
            url = window.location.pathname + url;
        } else {
            // If it's a relative URL, make it start with /api/
            url = '/api/' + url.replace(/^\//, '');
        }
        
        // Fallback: if URL contains localhost:3000, replace with current host
        if (url.includes('localhost:3000')) {
            url = url.replace('localhost:3000', window.location.host);
        }
        
        // Additional fallback: if URL still contains localhost:3000, force use current origin
        if (url.includes('localhost:3000')) {
            url = window.location.origin + url.replace(/^https?:\/\/[^\/]+/, '');
        }
        
        // ULTRA-FAST: Skip all logging for maximum speed
        
        // Check cache for GET-like requests (ajax queries) - NEVER cache POST/PUT/DELETE
        const method = (options.method || 'POST').toUpperCase();
        if (options.cache !== false && method === 'GET' && !(data instanceof FormData)) {
            const cachedResponse = apiCache.get(url, data);
            if (cachedResponse) {
                return cachedResponse;
            }
        }

        const res = await fetch(url, { 
            method: 'POST', 
            body: data instanceof FormData ? data : new URLSearchParams(data),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            // ULTRA-FAST: No timeout, let browser handle it for maximum speed
        });
        
        // Check if response is JSON based on header
        const contentType = res.headers.get('content-type');
        const isJson = contentType && contentType.includes('application/json');
        
        if (!res.ok) {
            const errorText = await res.text();
            console.error('API Error Response:', errorText);
            
            if (isJson) {
                try {
                    const errorJson = JSON.parse(errorText);
                    if (errorJson.need_reason || errorJson.need_overtime_reason || errorJson.need_early_leave_reason) {
                        return errorJson;
                    }
                    if (!options.suppressModal && errorJson.message) {
                        showModalNotif(errorJson.message, false, 'Gagal');
                    }
                } catch (e) {}
            } else {
                if (!options.suppressModal) {
                    showModalNotif(`Terjadi kesalahan server (${res.status}). Silakan coba lagi nanti.`, false, 'Gagal');
                }
            }
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const responseText = await res.text();
        let json;
        if (isJson) {
            try {
                json = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON Parse Error. Response text:', responseText);
                if (!options.suppressModal) showModalNotif('Respon server tidak valid (Bukan JSON).', false, 'Gagal');
                throw new Error('Invalid JSON response');
            }
        } else {
            console.error('Expected JSON but received:', contentType, responseText.substring(0, 500));
            if (!options.suppressModal) showModalNotif('Server tidak mengembalikan data JSON.', false, 'Gagal');
            throw new Error('Non-JSON response');
        }
        
        // Cache successful non-FormData responses
        if (options.cache !== false && !(data instanceof FormData) && json && json.ok !== false) {
            apiCache.set(url, data, json, options.ttl || 60000);
        }

        // Return the JSON response regardless of HTTP status code
        // Let the calling function handle the business logic (ok: false, etc.)
        if (!options.suppressModal) {
            if(json && json.ok===true && json.message){
                showModalNotif(json.message, true, 'Berhasil');
            } else if(json && json.ok===false && json.message){
                showModalNotif(json.message, false, 'Gagal');
            }
        }
        return json;
    } catch (error) {
        console.error('API call failed:', error);
        
        // FIX: Show user-visible notification for server errors so users don't need to check console
        let userErrMsg = null;
        
        // Perbaikan: Handle specific error types
        if (error.name === 'TypeError' && (error.message.includes('fetch') || error.message.includes('Failed to fetch'))) {
            userErrMsg = 'Koneksi ke server gagal. Pastikan XAMPP/server sudah berjalan.';
        } else if (error.message.includes('ERR_CONNECTION_REFUSED')) {
            userErrMsg = 'Server tidak merespons. Silakan coba lagi.';
        } else if (error.message.includes('HTTP error! status: 400')) {
            if (error.message.includes('Presensi masuk hanya tersedia') || error.message.includes('Presensi masuk tersedia')) {
                userErrMsg = 'Waktu presensi tidak sesuai. Silakan coba pada jam yang tepat.';
            } else {
                userErrMsg = 'Data yang dikirim tidak valid. Silakan coba lagi.';
            }
        } else if (error.message.includes('HTTP error! status: 500')) {
            userErrMsg = 'Terjadi kesalahan pada server (500). Silakan coba lagi atau hubungi administrator.';
        } else if (error.message.includes('HTTP error! status: 503')) {
            userErrMsg = 'Server sedang sibuk (503). Silakan coba beberapa saat lagi.';
        } else if (error.message && !error.message.includes('HTTP error!')) {
            // For non-HTTP errors (e.g. invalid JSON, timeout), show the message
            userErrMsg = error.message;
        }
        
        // Show notification only if opts.suppressModal is not set and we have a message
        if (userErrMsg && !(opts && opts.suppressModal)) {
            // Use showNotif (toast) for non-blocking user notification
            if (typeof showNotif === 'function') {
                showNotif('⚠️ ' + userErrMsg, false);
            }
        }
        
        throw error;
    }
}

// Port Detection and Fix
(function() {
    // Check if we're on the wrong port (Local development only)
    if (window.location.port === '3000' && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
        console.warn('Detected port 3000, redirecting to correct XAMPP port...');
        // Try common XAMPP ports
        const xamppPorts = ['80', '8080', '8000'];
        let redirectAttempted = false;
        
        for (const port of xamppPorts) {
            if (!redirectAttempted) {
                const testUrl = `${window.location.protocol}//${window.location.hostname}:${port}${window.location.pathname}${window.location.search}`;
                fetch(testUrl, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok && !redirectAttempted) {
                            redirectAttempted = true;
                            console.log(`Redirecting to port ${port}`);
                            window.location.href = testUrl;
                        }
                    })
                    .catch(() => {
                        // Port not available, try next
                    });
            }
        }
    }
})();



// Profile dropdown
(function(){
    const btn = qs('#btn-profile');
    const dd = qs('#dropdown-profile');
    if(btn && dd){
        btn.addEventListener('click', ()=> dd.classList.toggle('hidden'));
        document.addEventListener('click', (e)=>{ if(!btn.contains(e.target) && !dd.contains(e.target)) dd.classList.add('hidden'); });
    }
})();

<?php if ($page === 'login'): ?>
// Login
const loginForm = qs('#form-login');
if (loginForm) {
    loginForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const r = await api('?ajax=login', fd);
        const msg = qs('#login-msg');
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = 'Login berhasil. Mengalihkan...';
            setTimeout(()=> location.href='?page=dashboard', 200); // Redirect to dashboard to trigger auth check
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Gagal login';
        }
    });
}
<?php elseif ($page === 'register'): ?>
// Register camera
const regStart = qs('#reg-start-camera');
const regTake = qs('#reg-take-photo');
const regUpload = qs('#reg-upload-photo');
const regRemove = qs('#reg-remove-photo');
const regVideo = qs('#reg-video');
const regCanvas = qs('#reg-canvas');
const regPreview = qs('#reg-foto-preview');
const regVidContainer = qs('#reg-video-container');
const regFotoData = qs('#reg-foto-data');
const regPhotoFileInput = qs('#reg-photo-file-input');
let regStream = null;

// Camera action containers
const regPhotoActions = qs('#photo-actions');
const regCameraActions = qs('#camera-actions');

if (regStart) {
    regStart.addEventListener('click', async ()=>{
        try{
            regStream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } });
            regVideo.srcObject = regStream;
            regVidContainer.classList.remove('hidden');
            // FIX: Show #camera-actions container (which holds 'Ambil Foto' button) and hide #photo-actions
            if (regCameraActions) regCameraActions.classList.remove('hidden');
            if (regPhotoActions) regPhotoActions.classList.add('hidden');
            // Also show the take button itself (in case it's also toggled)
            if (regTake) regTake.classList.remove('hidden');
        }catch(err){ showNotif('Tidak bisa mengakses kamera: ' + err.message, false); console.error(err); }
    });
}

if (regTake) {
    regTake.addEventListener('click', ()=>{
        const ctx = regCanvas.getContext('2d');
        regCanvas.width = regVideo.videoWidth;
        regCanvas.height = regVideo.videoHeight;
        // Mirror the photo to match camera preview
        ctx.translate(regCanvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(regVideo, 0, 0, regCanvas.width, regCanvas.height);
        const dataUrl = regCanvas.toDataURL('image/jpeg', 0.9);
        regPreview.src = dataUrl;
        regPreview.classList.remove('hidden');
        regFotoData.value = dataUrl;
        // Stop stream
        if(regStream){ regStream.getTracks().forEach(t=>t.stop()); regStream=null; }
        regVidContainer.classList.add('hidden');
        // Hide camera-actions, show photo-actions again with updated text
        if (regCameraActions) regCameraActions.classList.add('hidden');
        if (regPhotoActions) regPhotoActions.classList.remove('hidden');
        // Show remove button, update start button text
        const regRemoveLocal = qs('#reg-remove-photo');
        if (regRemoveLocal) regRemoveLocal.classList.remove('hidden');
        if (regStart) { regStart.textContent = 'Ambil Ulang Foto'; }
    });
}

// Upload photo functionality
if (regUpload) {
    regUpload.addEventListener('click', ()=>{
        regPhotoFileInput.click();
    });
}

if (regPhotoFileInput) {
    regPhotoFileInput.addEventListener('change', (e)=>{
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showNotif('File harus berupa gambar', false);
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showNotif('Ukuran file maksimal 5MB', false);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                regPreview.src = dataUrl;
                regPreview.classList.remove('hidden');
                regFotoData.value = dataUrl;
                const regRemoveLocal = qs('#reg-remove-photo');
                if (regRemoveLocal) regRemoveLocal.classList.remove('hidden');
                if (regStart) regStart.textContent = 'Buka Kamera';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove photo functionality
if (regRemove) {
    regRemove.addEventListener('click', ()=>{
        regPreview.src = '';
        regPreview.classList.add('hidden');
        regFotoData.value = '';
        regRemove.classList.add('hidden');
        regPhotoFileInput.value = '';
        if (regStart) regStart.textContent = 'Buka Kamera';
        
        // Stop camera if running
        if(regStream){ 
            regStream.getTracks().forEach(t=>t.stop()); 
            regStream=null; 
        }
        regVidContainer.classList.add('hidden');
        // Show photo-actions, hide camera-actions
        if (regPhotoActions) regPhotoActions.classList.remove('hidden');
        if (regCameraActions) regCameraActions.classList.add('hidden');
    });
}

const registerForm = qs('#form-register');
if (registerForm) {
    registerForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#register-msg');
        // FIX: Use suppressModal=true to control modal display ourselves
        // This prevents false error modal when registration is actually successful
        const r = await api('?ajax=register', fd, { suppressModal: true });
        if(r && r.ok){ 
            msg.className='text-green-600 font-semibold';
            msg.textContent='✅ Registrasi berhasil! Mengalihkan ke halaman login...';
            showNotif('Registrasi berhasil!', true);
            setTimeout(()=>location.href='?page=login', 1500);
        } else {
            // Show clear, user-friendly error message
            const errMsg = (r && r.message) ? r.message : 'Gagal registrasi. Periksa data Anda dan coba lagi.';
            msg.className='text-red-600 font-semibold';
            msg.textContent='❌ ' + errMsg;
            // Also show a toast notification for visibility
            showNotif(errMsg, false);
        }
    });
}
<?php elseif ($page === 'forgot-password'): ?>
// Forgot Password
const forgotPasswordForm = qs('#form-forgot-password');
if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#forgot-password-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Mengirim permintaan...';
        
        try {
            const r = await api('?ajax=forgot_password', fd);
            if(r.ok){
                // Direct redirect to verify-otp without showing message
                if (r.token) {
                    window.location.href = '?page=verify-otp&token=' + encodeURIComponent(r.token);
                } else if (r.reset_url) {
                    window.location.href = r.reset_url;
                }
            } else {
                msg.className = 'text-red-600';
                msg.textContent = r.message || 'Email tidak ditemukan atau belum memiliki Google Authenticator';
            }
        } catch (error) {
            msg.className = 'text-red-600';
            msg.textContent = 'Email tidak ditemukan atau belum memiliki Google Authenticator';
            console.error('Forgot password error:', error);
        }
    });
}

// Check for token in URL and redirect to verify-otp
const urlParams = new URLSearchParams(window.location.search);
const tokenParam = urlParams.get('token');
if (tokenParam) {
    window.location.href = '?page=verify-otp&token=' + encodeURIComponent(tokenParam);
}
<?php elseif ($page === 'verify-otp'): ?>
// Verify OTP
const verifyOtpForm = qs('#form-verify-otp');
if (verifyOtpForm) {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tokenFromUrl = urlParams.get('token');
    
    if (tokenFromUrl) {
        qs('#reset-token').value = tokenFromUrl;
    }
    
    verifyOtpForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#verify-otp-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Memverifikasi OTP...';
        
        const r = await api('?ajax=verify_otp', fd);
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = r.message || 'OTP berhasil diverifikasi.';
            setTimeout(()=>{
                window.location.href = '?page=reset-password&token=' + encodeURIComponent(r.token || fd.get('token'));
            }, 1500);
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Kode OTP tidak valid';
        }
    });
    
    // Auto-focus OTP input
    const otpInput = verifyOtpForm.querySelector('input[name="otp"]');
    if (otpInput) {
        otpInput.focus();
    }
}
<?php elseif ($page === 'reset-password'): ?>
// Reset Password
const resetPasswordForm = qs('#form-reset-password');
if (resetPasswordForm) {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tokenFromUrl = urlParams.get('token');
    
    if (tokenFromUrl) {
        qs('#reset-token-final').value = tokenFromUrl;
    }
    
    resetPasswordForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#reset-password-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Mereset password...';
        
        const r = await api('?ajax=reset_password', fd);
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = r.message || 'Password berhasil direset.';
            setTimeout(()=>{
                window.location.href = '?page=login';
            }, 2000);
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Gagal mereset password';
        }
    });
}
<?php elseif ($page === 'landing'): ?>
// Browser compatibility polyfills
(function() {
    // Polyfill for getUserMedia for older browsers
    if (!navigator.mediaDevices) {
        navigator.mediaDevices = {};
    }
    if (!navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia = function(constraints) {
            const getUserMedia = navigator.getUserMedia || 
                                 navigator.webkitGetUserMedia || 
                                 navigator.mozGetUserMedia || 
                                 navigator.msGetUserMedia;
            
            if (!getUserMedia) {
                return Promise.reject(new Error('getUserMedia is not supported in this browser'));
            }
            
            return new Promise(function(resolve, reject) {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        };
    }
    
    // Polyfill for Promise if needed (for very old browsers)
    if (typeof Promise === 'undefined') {
        window.Promise = function(executor) {
            // Simple Promise polyfill
            const self = this;
            self.state = 'pending';
            self.value = undefined;
            self.handlers = [];
            
            function resolve(result) {
                if (self.state === 'pending') {
                    self.state = 'fulfilled';
                    self.value = result;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }
            
            function reject(error) {
                if (self.state === 'pending') {
                    self.state = 'rejected';
                    self.value = error;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }
            
            function handle(handler) {
                if (self.state === 'pending') {
                    self.handlers.push(handler);
                } else {
                    if (self.state === 'fulfilled' && typeof handler.onFulfilled === 'function') {
                        handler.onFulfilled(self.value);
                    }
                    if (self.state === 'rejected' && typeof handler.onRejected === 'function') {
                        handler.onRejected(self.value);
                    }
                }
            }
            
            self.then = function(onFulfilled, onRejected) {
                return new Promise(function(resolve, reject) {
                    handle({
                        onFulfilled: function(result) {
                            try {
                                resolve(onFulfilled ? onFulfilled(result) : result);
                            } catch (ex) {
                                reject(ex);
                            }
                        },
                        onRejected: function(error) {
                            try {
                                resolve(onRejected ? onRejected(error) : error);
                            } catch (ex) {
                                reject(ex);
                            }
                        }
                    });
                });
            };
            
            executor(resolve, reject);
        };
    }
    
    // Performance optimization: RequestIdleCallback polyfill
    if (!window.requestIdleCallback) {
        window.requestIdleCallback = function(callback, options) {
            const start = Date.now();
            return setTimeout(function() {
                callback({
                    didTimeout: false,
                    timeRemaining: function() {
                        return Math.max(0, 50 - (Date.now() - start));
                    }
                });
            }, 1);
        };
    }
    
    if (!window.cancelIdleCallback) {
        window.cancelIdleCallback = function(id) {
            clearTimeout(id);
        };
    }
    
    // Browser-specific fixes
    const ua = navigator.userAgent.toLowerCase();
    const isSafari = /safari/.test(ua) && !/chrome/.test(ua) && !/chromium/.test(ua);
    const isFirefox = /firefox/.test(ua);
    const isChrome = /chrome/.test(ua) && !/edge/.test(ua);
    const isMIBrowser = /miui/.test(ua) || /xiaomi/.test(ua);
    const isEdge = /edge/.test(ua);
    
    // Safari-specific fixes
    if (isSafari) {
        // Safari has issues with video autoplay - ensure video plays
        if (HTMLVideoElement.prototype.play) {
            const originalPlay = HTMLVideoElement.prototype.play;
            HTMLVideoElement.prototype.play = function() {
                const promise = originalPlay.call(this);
                if (promise && promise.catch) {
                    promise.catch(() => {
                        // Ignore autoplay errors in Safari
                    });
                }
                return promise;
            };
        }
        
        // Safari canvas fix for better performance
        if (HTMLCanvasElement.prototype.getContext) {
            const originalGetContext = HTMLCanvasElement.prototype.getContext;
            HTMLCanvasElement.prototype.getContext = function(contextType, attributes) {
                if (contextType === '2d' && attributes) {
                    attributes.willReadFrequently = false; // Better performance in Safari
                }
                return originalGetContext.call(this, contextType, attributes);
            };
        }
    }
    
    // Firefox-specific fixes
    if (isFirefox) {
        // Firefox may need explicit video play
        if (HTMLVideoElement.prototype.play) {
            const originalPlay = HTMLVideoElement.prototype.play;
            HTMLVideoElement.prototype.play = function() {
                const promise = originalPlay.call(this);
                if (promise && promise.catch) {
                    promise.catch(() => {
                        // Try to play with user interaction
                        this.muted = true;
                        return originalPlay.call(this);
                    });
                }
                return promise;
            };
        }
    }
    
    // MI Browser / Xiaomi Browser fixes
    if (isMIBrowser) {
        // MI Browser may have issues with getUserMedia - add extra fallback
        if (!navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia = function(constraints) {
                const getUserMedia = navigator.getUserMedia || 
                                   navigator.webkitGetUserMedia || 
                                   navigator.mozGetUserMedia || 
                                   navigator.msGetUserMedia;
                
                if (!getUserMedia) {
                    return Promise.reject(new Error('getUserMedia is not supported'));
                }
                
                return new Promise(function(resolve, reject) {
                    getUserMedia.call(navigator, constraints, resolve, reject);
                });
            };
        }
    }
    
    // Edge-specific fixes
    if (isEdge) {
        // Edge may need specific handling
        if (HTMLVideoElement.prototype.srcObject === undefined) {
            Object.defineProperty(HTMLVideoElement.prototype, 'srcObject', {
                get: function() {
                    return this.mozSrcObject || this.webkitSrcObject || null;
                },
                set: function(stream) {
                    if (this.mozSrcObject !== undefined) {
                        this.mozSrcObject = stream;
                    } else if (this.webkitSrcObject !== undefined) {
                        this.webkitSrcObject = stream;
                    } else {
                        this.src = window.URL.createObjectURL(stream);
                    }
                }
            });
        }
    }
    
    // Cross-browser canvas optimization
    if (HTMLCanvasElement.prototype.getContext) {
        const originalGetContext = HTMLCanvasElement.prototype.getContext;
        HTMLCanvasElement.prototype.getContext = function(contextType, attributes) {
            if (contextType === '2d') {
                // Optimize canvas for better performance across all browsers
                const optimizedAttributes = attributes || {};
                optimizedAttributes.alpha = true;
                optimizedAttributes.desynchronized = false;
                optimizedAttributes.willReadFrequently = false; // Better performance
                return originalGetContext.call(this, contextType, optimizedAttributes);
            }
            return originalGetContext.call(this, contextType, attributes);
        };
    }
    
    // Log browser detection
    console.log(`Browser detected: ${isSafari ? 'Safari' : isFirefox ? 'Firefox' : isChrome ? 'Chrome' : isMIBrowser ? 'MI Browser' : isEdge ? 'Edge' : 'Other'}`);
})();

// Landing page - Face recognition attendance
const videoContainer = qs('#video-container');
const video = qs('#video');
const canvas = qs('#canvas');
const presensiStatus = qs('#presensi-status');
const scanButtonsContainer = qs('#scan-buttons-container');
const btnScanMasuk = qs('#btn-scan-masuk');
const btnScanPulang = qs('#btn-scan-pulang');
const btnBackScan = qs('#btn-back-scan');
const loadingOverlay = qs('#loading-overlay');

let labeledFaceDescriptors = [];
let isCameraActive = false;
let videoInterval = null;
let scanMode = '';
let lastSpokenMessage = '';
let videoPlayListenerAdded = false;
let isPresensiSuccess = false; // Flag untuk menandai presensi sudah berhasil
let isDetectionStopped = false; // Flag untuk menandai detection dihentikan manual

// Optimasi: Performance monitoring variables
let performanceStats = {
    detectionCount: 0,
    totalDetectionTime: 0,
    averageDetectionTime: 0,
    lastDetectionTime: 0
};

// BALANCED ACCURACY: Detection config optimized for good accuracy while still detecting faces reliably
// Will be loaded from settings on page load
let detectionConfig = {
    faceMatcherThreshold: 0.38, // Will be loaded from settings
    recognitionThreshold: 0.38, // Will be loaded from settings
    inputSize: 416, // Will be loaded from settings
    scoreThreshold: 0.35, // Will be loaded from settings
    minFaceSize: 70, // Slightly smaller for easier detection (was 80)
    maxFaces: 1, // Limit to 1 face for processing
    confidenceThreshold: 0.7, // Balanced confidence requirement (was 0.75)
    detectionThrottle: 2, // Slightly slower but more accurate detection
    qualityThreshold: 0.55, // Will be loaded from settings
    landmarkThreshold: 0.55, // More lenient landmark threshold - easier detection while maintaining accuracy (was 0.65)
    expressionThreshold: 0.55, // Balanced expression threshold (was 0.6)
    landmarkWeight: 0.5, // Balanced weight
    descriptorWeight: 0.5, // Balanced weight
    genderValidation: true, // Enable gender validation for better accuracy (prevents cross-gender misdetection)
    multiAttemptValidation: true, // Enable multi-attempt validation for accuracy
    strictMode: true // Enable strict mode for maximum accuracy
};

// Load face recognition settings from backend
async function loadFaceRecognitionSettings() {
    try {
        const settingsJson = await api('?ajax=get_settings', {}, { suppressModal: true, cache: true, ttl: 300000 });
        if (settingsJson.ok && settingsJson.data) {
            const settings = settingsJson.data;
            if (settings.face_recognition_threshold?.value) {
                detectionConfig.faceMatcherThreshold = parseFloat(settings.face_recognition_threshold.value) || 0.38;
                detectionConfig.recognitionThreshold = parseFloat(settings.face_recognition_threshold.value) || 0.38;
            }
            if (settings.face_recognition_input_size?.value) {
                detectionConfig.inputSize = parseInt(settings.face_recognition_input_size.value) || 416;
            }
            if (settings.face_recognition_score_threshold?.value) {
                detectionConfig.scoreThreshold = parseFloat(settings.face_recognition_score_threshold.value) || 0.35;
            }
            if (settings.face_recognition_quality_threshold?.value) {
                detectionConfig.qualityThreshold = parseFloat(settings.face_recognition_quality_threshold.value) || 0.55;
            }
        }
    } catch (e) {
        console.warn('Failed to load face recognition settings, using defaults:', e);
    }
}

// Detect if device is mobile/phone (including mobile simulators)
function isMobileDevice() {
    const ua = navigator.userAgent.toLowerCase();
    const isMobileUA = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(ua);
    const isMobileViewport = window.innerWidth <= 768;
    const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    // Check for mobile simulator extensions (common patterns)
    // More aggressive detection for simulators
    const isSimulator = ua.includes('mobile') || 
                       ua.includes('simulator') || 
                       ua.includes('phone') ||
                       window.screen.width <= 768 || 
                       (isMobileViewport && hasTouch) ||
                       (window.innerWidth <= 768 && window.innerHeight <= 1024);
    
    return isMobileUA || (isMobileViewport && hasTouch) || isSimulator;
}

// Detect device performance level (for optimization)
let devicePerformanceLevel = 'unknown'; // 'high', 'medium', 'low'
let devicePerformanceDetected = false;

function detectDevicePerformance() {
    if (devicePerformanceDetected) return devicePerformanceLevel;
    
    devicePerformanceDetected = true;
    const ua = navigator.userAgent.toLowerCase();
    
    // Detect low-end devices
    const isLowEndDevice = 
        // Android low-end indicators
        (ua.includes('android') && (
            ua.includes('samsung') && (ua.includes('sm-a') || ua.includes('sm-j') || ua.includes('sm-g')) ||
            ua.includes('xiaomi') && (ua.includes('redmi') || ua.includes('mi a')) ||
            ua.includes('oppo') && ua.includes('a') ||
            ua.includes('vivo') && ua.includes('y')
        )) ||
        // Old laptop indicators
        (ua.includes('windows') && (
            ua.includes('nt 10.0') && !ua.includes('edge') && !ua.includes('chrome') // Old Windows 10
        )) ||
        // Low memory/CPU indicators
        (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2) ||
        (navigator.deviceMemory && navigator.deviceMemory <= 2);
    
    // Detect high-end devices
    const isHighEndDevice = 
        ua.includes('iphone') && (ua.includes('iphone15') || ua.includes('iphone14') || ua.includes('iphone13')) ||
        (navigator.hardwareConcurrency && navigator.hardwareConcurrency >= 8) ||
        (navigator.deviceMemory && navigator.deviceMemory >= 8);
    
    // Performance test
    const start = performance.now();
    for (let i = 0; i < 100000; i++) {
        Math.sqrt(i);
    }
    const testTime = performance.now() - start;
    
    if (isLowEndDevice || testTime > 5) {
        devicePerformanceLevel = 'low';
    } else if (isHighEndDevice || testTime < 1) {
        devicePerformanceLevel = 'high';
    } else {
        devicePerformanceLevel = 'medium';
    }
    
    console.log(`Device Performance: ${devicePerformanceLevel} (test: ${testTime.toFixed(2)}ms, cores: ${navigator.hardwareConcurrency || 'unknown'}, memory: ${navigator.deviceMemory || 'unknown'}GB)`);
    
    return devicePerformanceLevel;
}

// Get adjusted threshold based on device type
function getAdjustedRecognitionThreshold() {
    if (isMobileDevice()) {
        // Much more lenient threshold for mobile devices (0.55 instead of 0.38)
        // This allows distance up to 0.55 for mobile devices for easier detection
        return 0.55;
    }
    return detectionConfig.recognitionThreshold;
}

// Get adjusted face matcher threshold based on device type
function getAdjustedFaceMatcherThreshold() {
    if (isMobileDevice()) {
        // Much more lenient threshold for mobile devices (0.55 instead of 0.38)
        return 0.55;
    }
    return detectionConfig.faceMatcherThreshold;
}

// Get adjusted quality threshold based on device type
function getAdjustedQualityThreshold() {
    if (isMobileDevice()) {
        // Much more lenient quality threshold for mobile devices
        return 0.45; // Lowered from 0.50 to 0.45 for easier detection on mobile
    }
    return detectionConfig.qualityThreshold;
}

// Get adjusted landmark threshold based on device type
function getAdjustedLandmarkThreshold() {
    if (isMobileDevice()) {
        // Much more lenient landmark threshold for mobile devices
        return 0.45; // Lowered from 0.50 to 0.45 for easier detection on mobile
    }
    return detectionConfig.landmarkThreshold;
}
let logMasukData = [];
let logPulangData = [];
let members = []; // Global members array for gender validation

// WFA Modal functions for landing page

function showOvertimeModal(message) {
    // Create Overtime modal if it doesn't exist
    let overtimeModal = document.getElementById('overtimeModal');
    if (!overtimeModal) {
        overtimeModal = document.createElement('div');
        overtimeModal.id = 'overtimeModal';
        overtimeModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overtimeModal.style.display = 'flex';
        overtimeModal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-lg font-semibold mb-4">Overtime</h3>
                <p class="text-gray-600 mb-4">${message}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Overtime:</label>
                    <input type="text" id="overtimeLocation" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Masukkan lokasi overtime..." required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Overtime:</label>
                    <textarea id="overtimeReason" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="3" placeholder="Masukkan alasan overtime..." required></textarea>
                </div>
                <div class="flex space-x-3">
                    <button id="overtimeSubmit" class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        Submit
                    </button>
                    <button id="overtimeCancel" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(overtimeModal);
        
        // Add event listeners
        document.getElementById('overtimeSubmit').addEventListener('click', () => {
            const location = document.getElementById('overtimeLocation').value.trim();
            const reason = document.getElementById('overtimeReason').value.trim();
            if (location && reason) {
                overtimeModal.style.display = 'none';
                overtimeModal.classList.add('hidden');
                // Store Overtime reason and location for next attendance submission
                window.pendingOvertimeReason = reason;
                window.pendingOvertimeLocation = location;
                // Retry attendance submission
                if (window.pendingAttendanceData) {
                    submitAttendanceWithOvertime(window.pendingAttendanceData, reason, location);
                }
            } else {
                showNotif('Harap isi lokasi dan alasan overtime terlebih dahulu.', false);
            }
        });
        
        document.getElementById('overtimeCancel').addEventListener('click', () => {
            overtimeModal.style.display = 'none';
            overtimeModal.classList.add('hidden');
            isProcessingRecognition = false;
            // Clear pending data
            window.pendingOvertimeReason = null;
            window.pendingOvertimeLocation = null;
            window.pendingAttendanceData = null;
        });
    } else {
        // Modal exists, just show it
        overtimeModal.style.display = 'flex';
        overtimeModal.classList.remove('hidden');
        // Update message if modal exists
        const messageEl = overtimeModal.querySelector('p.text-gray-600');
        if (messageEl && message) {
            messageEl.textContent = message;
        }
    }
    
    // Show modal and populate location from pending data if available
    const locationInput = document.getElementById('overtimeLocation');
    const reasonInput = document.getElementById('overtimeReason');
    if (locationInput && window.pendingAttendanceData && window.pendingAttendanceData.lokasi) {
        locationInput.value = window.pendingAttendanceData.lokasi;
    }
    // Clear reason input when showing modal
    if (reasonInput) {
        reasonInput.value = '';
    }
    if (locationInput) {
        setTimeout(() => locationInput.focus(), 100);
    }
}

function showWFAModal(message) {
    // Create WFA modal if it doesn't exist
    let wfaModal = document.getElementById('wfaModal');
    if (!wfaModal) {
        wfaModal = document.createElement('div');
        wfaModal.id = 'wfaModal';
        wfaModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        wfaModal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Work From Anywhere (WFA)</h3>
                <p class="text-gray-600 mb-4">${message}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan WFA:</label>
                    <textarea id="wfaReason" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Masukkan alasan kerja di luar kantor..."></textarea>
                </div>
                <div class="flex space-x-3">
                    <button id="wfaSubmit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Submit
                    </button>
                    <button id="wfaCancel" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(wfaModal);
        
        // Add event listeners
        document.getElementById('wfaSubmit').addEventListener('click', () => {
            const reason = document.getElementById('wfaReason').value.trim();
            if (reason) {
                wfaModal.classList.add('hidden');
                // Store WFA reason for next attendance submission
                window.pendingWFAReson = reason;
                // Retry attendance submission
                if (window.pendingAttendanceData) {
                    submitAttendanceWithWFA(window.pendingAttendanceData, reason);
                }
            } else {
                showNotif('Harap isi alasan WFA terlebih dahulu.', false);
            }
        });
        
        document.getElementById('wfaCancel').addEventListener('click', () => {
            wfaModal.classList.add('hidden');
            isProcessingRecognition = false;
            // Clear pending data
            window.pendingWFAReson = null;
            window.pendingAttendanceData = null;
        });
    }
    
    // Show modal
    wfaModal.classList.remove('hidden');
    document.getElementById('wfaReason').focus();
}

// Show location confirmation modal
function showLocationConfirmation(lokasi, lat, lng, onRecheck = null) {
    return new Promise((resolve) => {
        // Create location confirmation modal if it doesn't exist
        let locationModal = document.getElementById('locationConfirmationModal');
        if (!locationModal) {
            locationModal = document.createElement('div');
            locationModal.id = 'locationConfirmationModal';
            locationModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            locationModal.style.display = 'flex';
            locationModal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
                    <h3 class="text-lg font-semibold mb-4">Konfirmasi Lokasi</h3>
                    <p class="text-gray-600 mb-4">Apakah lokasi berikut benar?</p>
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-1">Lokasi Saat Ini:</p>
                        <p class="text-sm text-gray-900" id="location-confirmation-text">${lokasi}</p>
                        <p class="text-xs text-gray-500 mt-2" id="location-confirmation-coords">Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}</p>
                    </div>
                    <div id="location-checking-indicator" class="hidden mb-2 text-sm text-blue-600">
                        <i class="fi fi-sr-spinner animate-spin mr-1"></i> Memeriksa lokasi ulang...
                    </div>
                    <div class="flex space-x-3">
                        <button id="locationConfirmYes" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Ya, Benar
                        </button>
                        <button id="locationConfirmNo" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Periksa Ulang
                        </button>
                        <button id="locationConfirmCancel" class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Batal
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(locationModal);
        }
        
        // Update location text
        const locationText = document.getElementById('location-confirmation-text');
        const coordText = document.getElementById('location-confirmation-coords');
        const checkingIndicator = document.getElementById('location-checking-indicator');
        if (locationText) {
            locationText.textContent = lokasi;
        }
        if (coordText) {
            coordText.textContent = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        if (checkingIndicator) {
            checkingIndicator.classList.add('hidden');
        }
        locationModal.style.display = 'flex';
        locationModal.classList.remove('hidden');
        
        // Return promise that resolves when user clicks
        const yesBtn = document.getElementById('locationConfirmYes');
        const noBtn = document.getElementById('locationConfirmNo');
        const cancelBtn = document.getElementById('locationConfirmCancel');
        
        // Remove old listeners and add new ones
        const newYesBtn = yesBtn.cloneNode(true);
        const newNoBtn = noBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
        noBtn.parentNode.replaceChild(newNoBtn, noBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        
        // Store current values that can be updated
        let currentValues = { lokasi, lat, lng };
        
        newYesBtn.addEventListener('click', () => {
            locationModal.style.display = 'none';
            locationModal.classList.add('hidden');
            // Return updated values
            resolve({ confirmed: true, ...currentValues });
        });
        
        newCancelBtn.addEventListener('click', () => {
            locationModal.style.display = 'none';
            locationModal.classList.add('hidden');
            resolve({ confirmed: false });
        });
        
        newNoBtn.addEventListener('click', async () => {
            // If recheck callback is provided, call it to re-check location
            if (onRecheck && typeof onRecheck === 'function') {
                if (checkingIndicator) {
                    checkingIndicator.classList.remove('hidden');
                }
                newNoBtn.disabled = true;
                newYesBtn.disabled = true;
                newCancelBtn.disabled = true;
                
                try {
                    // Call recheck function - it should return new {lokasi, lat, lng}
                    const newLocation = await onRecheck();
                    if (newLocation && newLocation.lokasi && newLocation.lat && newLocation.lng) {
                        // Update modal with new location
                        if (locationText) {
                            locationText.textContent = newLocation.lokasi;
                        }
                        if (coordText) {
                            coordText.textContent = `Koordinat: ${newLocation.lat.toFixed(6)}, ${newLocation.lng.toFixed(6)}`;
                        }
                        // Update current values
                        currentValues = { lokasi: newLocation.lokasi, lat: newLocation.lat, lng: newLocation.lng };
                    } else {
                        // Recheck failed - show error
                        if (locationText) {
                            locationText.textContent = 'Gagal mendapatkan lokasi. Silakan coba lagi atau klik Batal.';
                        }
                    }
                } catch (error) {
                    console.error('Error rechecking location:', error);
                    if (locationText) {
                        locationText.textContent = 'Error: ' + (error.message || 'Gagal memeriksa lokasi');
                    }
                } finally {
                    if (checkingIndicator) {
                        checkingIndicator.classList.add('hidden');
                    }
                    newNoBtn.disabled = false;
                    newYesBtn.disabled = false;
                    newCancelBtn.disabled = false;
                }
                // Don't resolve - keep modal open for user to confirm new location
            } else {
                // No recheck function - just cancel
                locationModal.style.display = 'none';
                locationModal.classList.add('hidden');
                resolve({ confirmed: false });
            }
        });
    });
}

function submitAttendanceWithOvertime(attendanceData, overtimeReason, overtimeLocation) {
    // Add Overtime reason and location to attendance data
    const dataWithOvertime = {
        ...attendanceData,
        overtime_reason: overtimeReason,
        overtime_location: overtimeLocation,
        is_overtime: true
    };
    
    // Submit attendance with Overtime reason and location
    api('?ajax=save_attendance', dataWithOvertime, { suppressModal: true })
        .then(response => {
            if (response.ok) {
                statusMessage('Presensi overtime berhasil!', 'bg-purple-100 text-purple-700');
                // Clear pending data
                window.pendingOvertimeReason = null;
                window.pendingOvertimeLocation = null;
                window.pendingAttendanceData = null;
                isProcessingRecognition = false;
            } else {
                const errorMsg = response.message || 'Presensi gagal. Silakan coba lagi.';
                statusMessage('Gagal menyimpan presensi: ' + errorMsg, 'bg-red-100 text-red-700');
                isProcessingRecognition = false;
            }
        })
        .catch(error => {
            console.error('Error submitting overtime attendance:', error);
            statusMessage('Terjadi kesalahan saat menyimpan presensi overtime.', 'bg-red-100 text-red-700');
            isProcessingRecognition = false;
        });
}

function submitAttendanceWithWFA(attendanceData, wfaReason) {
    // Add WFA reason to attendance data
    const dataWithWFA = {
        ...attendanceData,
        wfa_reason: wfaReason,
        is_wfa: true
    };
    
    // Submit attendance with WFA reason
    api('?ajax=save_attendance', dataWithWFA, { suppressModal: true })
        .then(response => {
            if (response.ok) {
                statusMessage('Presensi berhasil dengan alasan WFA!', 'bg-green-100 text-green-700');
                // Clear pending data
                window.pendingWFAReson = null;
                window.pendingAttendanceData = null;
                isProcessingRecognition = false;
            } else {
                const errorMsg = response.message || 'Presensi gagal. Silakan coba lagi.';
                statusMessage('Gagal menyimpan presensi: ' + errorMsg, 'bg-red-100 text-red-700');
                isProcessingRecognition = false;
            }
        })
        .catch(error => {
            console.error('Error submitting attendance with WFA:', error);
            statusMessage('Terjadi kesalahan saat menyimpan presensi.', 'bg-red-100 text-red-700');
            isProcessingRecognition = false;
        });
}

function showEarlyLeaveModal(message) {
    // Try to find existing modal from HTML first
    let earlyLeaveModal = document.getElementById('early-leave-reason-modal');
    
    if (!earlyLeaveModal) {
        // Create modal dynamically if not found in HTML
        earlyLeaveModal = document.createElement('div');
        earlyLeaveModal.id = 'early-leave-reason-modal';
        earlyLeaveModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        earlyLeaveModal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-lg font-semibold mb-4">Alasan Pulang Awal</h3>
                <p class="text-gray-600 mb-4">${message || 'Anda pulang sebelum jam yang ditentukan. Silakan isi alasan pulang awal untuk melanjutkan presensi pulang.'}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pulang Awal:</label>
                    <textarea id="earlyLeaveReason" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" rows="4" placeholder="Masukkan alasan pulang awal..."></textarea>
                </div>
                <div class="flex space-x-3">
                    <button id="earlyLeaveSubmit" class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        Kirim
                    </button>
                    <button id="earlyLeaveCancel" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(earlyLeaveModal);
        
        // Add event listeners for dynamically created modal
        document.getElementById('earlyLeaveSubmit').addEventListener('click', () => {
            const reason = document.getElementById('earlyLeaveReason').value.trim();
            if (reason) {
                earlyLeaveModal.classList.add('hidden');
                // Store early leave reason for next attendance submission
                window.pendingEarlyLeaveReason = reason;
                // Retry attendance submission
                if (window.pendingAttendanceData) {
                    submitAttendanceWithEarlyLeave(window.pendingAttendanceData, reason);
                }
            } else {
                showNotif('Harap isi alasan pulang awal terlebih dahulu.', false);
            }
        });
        
        document.getElementById('earlyLeaveCancel').addEventListener('click', () => {
            earlyLeaveModal.classList.add('hidden');
            isProcessingRecognition = false;
            // Clear pending data
            window.pendingEarlyLeaveReason = null;
            window.pendingAttendanceData = null;
        });
    } else {
        // Modal exists in HTML, use it and set up event listeners
        const reasonInput = document.getElementById('early-leave-reason-input');
        const submitBtn = document.getElementById('early-leave-reason-submit');
        const cancelBtn = document.getElementById('early-leave-reason-cancel');
        
        // Clear previous input
        if (reasonInput) {
            reasonInput.value = '';
        }
        
        // Update message if there's a message element (for dynamic modal)
        const messageEl = earlyLeaveModal.querySelector('p.text-gray-600');
        if (messageEl && message) {
            messageEl.textContent = message;
        }
        
        if (submitBtn && cancelBtn) {
            // Remove old event listeners by cloning
            const newSubmitBtn = submitBtn.cloneNode(true);
            const newCancelBtn = cancelBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            // Add event listeners
            newSubmitBtn.addEventListener('click', () => {
                const reason = reasonInput ? reasonInput.value.trim() : '';
                if (reason) {
                    earlyLeaveModal.classList.add('hidden');
                    // Store early leave reason for next attendance submission
                    window.pendingEarlyLeaveReason = reason;
                    // Retry attendance submission
                    if (window.pendingAttendanceData) {
                        submitAttendanceWithEarlyLeave(window.pendingAttendanceData, reason);
                    }
                } else {
                    showNotif('Harap isi alasan pulang awal terlebih dahulu.', false);
                }
            });
            
            newCancelBtn.addEventListener('click', () => {
                earlyLeaveModal.classList.add('hidden');
                isProcessingRecognition = false;
                // Clear pending data
                window.pendingEarlyLeaveReason = null;
                window.pendingAttendanceData = null;
            });
        }
    }
    
    // Show modal
    earlyLeaveModal.classList.remove('hidden');
    const reasonInput = document.getElementById('earlyLeaveReason') || document.getElementById('early-leave-reason-input');
    if (reasonInput) {
        setTimeout(() => reasonInput.focus(), 100);
    }
}

function submitAttendanceWithEarlyLeave(attendanceData, earlyLeaveReason) {
    // Add early leave reason to attendance data
    const dataWithEarlyLeave = {
        ...attendanceData,
        alasan_pulang_awal: earlyLeaveReason,
        early_leave_reason: earlyLeaveReason
    };
    
    isProcessingRecognition = true;
    statusMessage('Menyimpan presensi pulang awal...', 'bg-blue-100 text-blue-700');

    // Submit attendance with early leave reason
    api('?ajax=save_attendance', dataWithEarlyLeave, { suppressModal: true })
        .then(response => {
            if (response.ok) {
                // --- Mirror exactly what submitFinalAttendance does on success ---
                statusMessage('Presensi pulang berhasil dengan alasan pulang awal!', 'bg-green-100 text-green-700');

                // 1. Mark as success so detection loop stops and camera stays visible
                isPresensiSuccess = true;
                isDetectionPaused = false;

                // 2. Play success voice
                if (typeof speak === 'function') {
                    speak('Presensi pulang berhasil disimpan. Terima kasih.');
                }

                // 3. Show "Next Scan" button
                const nextScanContainer = document.getElementById('next-scan-container');
                if (nextScanContainer) nextScanContainer.classList.remove('hidden');

                // 4. Refresh the attendance log
                const nim = attendanceData.nim || attendanceData.id || '';
                const mode = attendanceData.mode || 'pulang';
                if (typeof updateLogAfterAttendance === 'function') {
                    updateLogAfterAttendance(nim, mode);
                } else if (typeof loadLogPulang === 'function') {
                    setTimeout(loadLogPulang, 500); // Fallback: reload log table
                }

                // 5. Clear pending data
                window.pendingEarlyLeaveReason = null;
                window.pendingAttendanceData = null;
            } else {
                const errorMsg = response.message || 'Presensi gagal. Silakan coba lagi.';
                statusMessage('Gagal menyimpan presensi: ' + errorMsg, 'bg-red-100 text-red-700');
                if (typeof speak === 'function') speak('Gagal. ' + errorMsg);
            }
        })
        .catch(error => {
            console.error('Error submitting attendance with early leave:', error);
            statusMessage('Terjadi kesalahan saat menyimpan presensi.', 'bg-red-100 text-red-700');
        })
        .finally(() => {
            isProcessingRecognition = false;
        });
}

// Enhanced location detection with reverse geocoding - ALWAYS shows actual device location
async function getStreetNameFromCoordinates(lat, lng) {
    // ALWAYS use reverse geocoding to get actual location - never assume WFO location
    // This ensures the modal shows the real device location, not a preset location
    try {
        // Use PHP proxy to avoid CORS issues
        const result = await api('?ajax=reverse_geocode', { action: 'reverse_geocode', lat: lat, lng: lng }, { suppressModal: true });
        
        if (!result.ok || !result.data) {
            console.error('[GEOCODE] Invalid result format:', result);
            throw new Error('Reverse geocoding failed');
        }
        
        const data = result.data;
        
        if (data && data.address) {
            const address = data.address;
            const parts = [];
            
            // 1. Building name or house name (most specific) - prioritize this for places like malls, universities
            if (address.building) {
                parts.push(address.building);
            } else if (address.house_name) {
                parts.push(address.house_name);
            }
            
            // Check for known places in display_name (like Trans Studio Mall, Telkom University, etc.)
            if (data.display_name) {
                const displayName = data.display_name.toLowerCase();
                // Check for common place names
                if (displayName.includes('trans studio') || displayName.includes('transstudio')) {
                    parts.push('Trans Studio Mall Bandung');
                } else if (displayName.includes('telkom university') || displayName.includes('telkom university')) {
                    parts.push('Telkom University');
                } else if (displayName.includes('fakultas ilmu terapan')) {
                    parts.push('Fakultas Ilmu Terapan Telkom University');
                }
            }
            
            // 2. Road/Street with house number if available
            const roadParts = [];
            if (address.house_number) roadParts.push(address.house_number);
            if (address.road) roadParts.push(address.road);
            else if (address.pedestrian) roadParts.push(address.pedestrian);
            else if (address.footway) roadParts.push(address.footway);
            if (roadParts.length > 0) {
                parts.push('Jl. ' + roadParts.join(' '));
            }
            
            // 3. Suburb/Neighbourhood
            if (address.suburb) parts.push(address.suburb);
            else if (address.neighbourhood) parts.push(address.neighbourhood);
            
            // 4. City/Town/Village
            if (address.city) parts.push(address.city);
            else if (address.town) parts.push(address.town);
            else if (address.village) parts.push(address.village);
            
            // 5. State/Province
            if (address.state) parts.push(address.state);
            
            // 6. Postal code
            if (address.postcode) parts.push(address.postcode);
            
            if (parts.length > 0) {
                return parts.join(', ');
            }
            
            // Fallback to display_name with postal code
            if (data.display_name) {
                let cleanName = data.display_name.replace(/, Indonesia$/, '');
                // Remove redundant "Bandung" if already in parts
                if (address.postcode) {
                    cleanName += ', ' + address.postcode;
                }
                return cleanName;
            }
        }
        
        // If address parsing failed but display_name exists, use it
        if (data && data.display_name) {
            let cleanName = data.display_name.replace(/, Indonesia$/, '');
            return cleanName;
        }
    } catch (error) {
        // Silently fail - will use coordinates fallback
        console.warn('Reverse geocoding failed:', error);
    }
    
    // Final fallback: coordinates only (no distance info to avoid confusion)
    return `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

// Helper function to calculate distance between two coordinates
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Earth's radius in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Helper functions for image variations to improve recognition accuracy
function createRotatedImage(img, degrees) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Set canvas size to accommodate rotation
        const size = Math.max(img.width, img.height) * 1.5;
        canvas.width = size;
        canvas.height = size;
        
        // Center the image
        ctx.translate(size / 2, size / 2);
        ctx.rotate((degrees * Math.PI) / 180);
        ctx.drawImage(img, -img.width / 2, -img.height / 2);
        
        // Convert back to image
        const rotatedImg = new Image();
        rotatedImg.onload = () => resolve(rotatedImg);
        rotatedImg.src = canvas.toDataURL();
    });
}

function createScaledImage(img, scale) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = img.width * scale;
        canvas.height = img.height * scale;
        
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        const scaledImg = new Image();
        scaledImg.onload = () => resolve(scaledImg);
        scaledImg.src = canvas.toDataURL();
    });
}


// Legacy attendance logic removed. Now using assets/js/attendance.js

if (btnScanMasuk) {
    btnScanMasuk.addEventListener('click', ()=> startScan('masuk'));
}
if (btnScanPulang) {
    btnScanPulang.addEventListener('click', ()=> startScan('pulang'));
}
if (btnBackScan) {
    btnBackScan.addEventListener('click', ()=>{ resetPresensiPage(); });
}

// Force request permissions on page load (for all devices)
document.addEventListener('DOMContentLoaded', async () => {
    // Request camera permission immediately on page load
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        // Stop immediately - we just want to trigger permission prompt
        stream.getTracks().forEach(track => track.stop());
    } catch (err) {
        // Permission denied or error - will be handled when user clicks button
        console.log('Camera permission request on load:', err.name);
    }
    
    // Request location permission immediately on page load
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            () => {}, // Success - permission granted
            () => {}, // Error - will be handled when needed
            { timeout: 3000, enableHighAccuracy: true }
        );
    }
    
    // Auto-start presensi if mode parameter is provided (from employee page)
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');
    if (mode === 'masuk' || mode === 'pulang') {
        // Wait a bit for page to fully load, then auto-start
        setTimeout(() => {
            startScan(mode);
        }, 500);
    }
});

// Add event listener for stop detection button
const btnStopDetection = qs('#btn-stop-detection');
if (btnStopDetection) {
    btnStopDetection.addEventListener('click', ()=>{ 
        stopDetection();
        const btnStart = qs('#btn-start-detection');
        if (btnStart) btnStart.classList.remove('hidden');
        btnStopDetection.classList.add('hidden');
        statusMessage('Deteksi dihentikan. Klik "Mulai Deteksi" untuk melanjutkan.', 'bg-yellow-100 text-yellow-700');
    });
}

function resetPresensiPage(){
    stopVideo();
    resetRecognitionSystem(); // Reset recognition system
    isPresensiSuccess = false; // Reset presensi success flag
    isDetectionStopped = false; // Reset stop detection flag
    processedLabels.clear(); // Clear processed labels
    scanButtonsContainer.classList.remove('hidden');
    videoContainer.classList.add('hidden');
    btnBackScan.classList.add('hidden');
    qs('#btn-stop-detection').classList.add('hidden');
    const btnStart = qs('#btn-start-detection');
    if (btnStart) btnStart.classList.add('hidden');
    
    // Check if we have return parameter - redirect to employee page
    const urlParams = new URLSearchParams(window.location.search);
    const returnParam = urlParams.get('return');
    if (returnParam === 'app') {
        // Redirect back to employee page (app)
        window.location.href = '?page=app';
        return;
    }
    
    // Show the two panel layout (text and image sections) again
    const twoPanelLayout = qs('#two-panel-layout');
    if (twoPanelLayout) {
        twoPanelLayout.classList.remove('hidden');
    }
    
    qs('#log-masuk-container').classList.add('hidden');
    qs('#log-pulang-container').classList.add('hidden');
    if (presensiStatus) {
        presensiStatus.classList.add('hidden');
        presensiStatus.textContent='';
    }
    videoPlayListenerAdded = false;
    if (window.presensiTimeout) {
        clearTimeout(window.presensiTimeout);
        window.presensiTimeout = null;
    }
    if (window.speechTimeout) {
        clearTimeout(window.speechTimeout);
        window.speechTimeout = null;
    }
    speechSynthesis.cancel();
    speechQueue = [];
    isSpeaking = false;
    
    // Advanced: Reset detection history for fresh start
    detectionHistory = [];
    lastSuccessfulDetection = null;
    detectionAttempts = 0;
    recognitionCompleted = false; // Reset recognition completion flag
}

function startVideo(){
    if (!video) return;
    
    // Browser compatibility: Try modern API first, then fallback
    const getUserMedia = navigator.mediaDevices?.getUserMedia || 
                        navigator.getUserMedia || 
                        navigator.webkitGetUserMedia || 
                        navigator.mozGetUserMedia || 
                        navigator.msGetUserMedia;
    
    if (!getUserMedia) {
        statusMessage('Browser tidak mendukung akses kamera. Silakan gunakan browser modern (Chrome, Firefox, Safari, Edge).', 'bg-red-100 text-red-700');
        return;
    }
    
    // Detect device performance and adjust video constraints
    const perfLevel = detectDevicePerformance();
    let videoConstraints = {
        video: {
            width: { ideal: detectDevicePerformance() === 'low' ? 320 : (detectDevicePerformance() === 'medium' ? 480 : 640), max: detectDevicePerformance() === 'low' ? 640 : 1280 },
            height: { ideal: detectDevicePerformance() === 'low' ? 240 : (detectDevicePerformance() === 'medium' ? 360 : 480), max: detectDevicePerformance() === 'low' ? 480 : 720 },
            frameRate: detectDevicePerformance() === 'low' ? { ideal: 10, max: 15 } : (detectDevicePerformance() === 'medium' ? { ideal: 12, max: 20 } : { ideal: 15, max: 30 }),
            facingMode: 'user'
        }
    };
    
    // Optimize video constraints for low-end devices - MORE AGGRESSIVE
    if (perfLevel === 'low') {
        videoConstraints = {
            video: {
                width: { ideal: 240, max: 480 }, // Reduced from 320 to 240
                height: { ideal: 180, max: 360 }, // Reduced from 240 to 180
                frameRate: { ideal: 8, max: 12 }, // Reduced from 10-15 to 8-12
                facingMode: 'user'
            }
        };
        console.log('Low-end device detected - using very low video resolution for better performance');
    } else if (perfLevel === 'medium') {
        videoConstraints = {
            video: {
                width: { ideal: 360, max: 720 }, // Reduced from 480 to 360
                height: { ideal: 270, max: 405 }, // Reduced from 360 to 270
                frameRate: { ideal: 10, max: 15 }, // Reduced from 12-20 to 10-15
                facingMode: 'user'
            }
        };
    }
    
    const constraints = videoConstraints;
    
    // Handle both modern and legacy APIs
    const handleStream = (stream) => {
        // Modern API uses srcObject
        if (video.srcObject !== undefined) {
            video.srcObject = stream;
        } else if (video.mozSrcObject !== undefined) {
            // Firefox legacy
            video.mozSrcObject = stream;
        } else if (video.src !== undefined) {
            // Very old browsers
            video.src = window.URL.createObjectURL(stream);
        }
        
        isCameraActive = true;
        // Mirror hanya video supaya tombol dan teks tidak terbalik
        if (video) video.classList.add('mirror-video');
        video.addEventListener('loadedmetadata', () => {
            video.play().catch(err => {
                console.warn('Video play error:', err);
            });
        });
    };
    
    const handleError = (err) => {
        console.error('Error camera', err);
        let errorMsg = 'Tidak dapat mengakses kamera.';
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMsg = 'Izin kamera ditolak. Silakan aktifkan izin kamera di pengaturan browser.';
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            errorMsg = 'Kamera tidak ditemukan. Pastikan kamera terhubung.';
        } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            errorMsg = 'Kamera sedang digunakan oleh aplikasi lain.';
        }
        statusMessage('Error: ' + errorMsg, 'bg-red-100 text-red-700');
    };
    
    // Try modern API first with browser-specific handling
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(constraints)
            .then(handleStream)
            .catch(err => {
                // Browser-specific error handling
                const ua = navigator.userAgent.toLowerCase();
                const isSafari = /safari/.test(ua) && !/chrome/.test(ua) && !/chromium/.test(ua);
                const isFirefox = /firefox/.test(ua);
                const isMIBrowser = /miui/.test(ua) || /xiaomi/.test(ua);
                
                // Safari may need different constraints
                if (isSafari && err.name === 'OverconstrainedError') {
                    // Try with simpler constraints for Safari
                    const simpleConstraints = { video: true };
                    navigator.mediaDevices.getUserMedia(simpleConstraints)
                        .then(handleStream)
                        .catch(handleError);
                } else if (isFirefox && err.name === 'NotReadableError') {
                    // Firefox may need explicit permission
                    handleError(new Error('Kamera sedang digunakan oleh aplikasi lain atau tidak dapat diakses.'));
                } else if (isMIBrowser && err.name === 'NotAllowedError') {
                    // MI Browser may need explicit permission request
                    handleError(new Error('Izin kamera diperlukan. Silakan aktifkan di pengaturan browser.'));
                } else {
                    handleError(err);
                }
            });
    } else {
        // Fallback to legacy API
        getUserMedia.call(navigator, constraints, handleStream, handleError);
    }
}

function stopVideo(){
    if(video && video.srcObject){ video.srcObject.getTracks().forEach(t=>t.stop()); video.srcObject=null; }
    isCameraActive=false; if(videoInterval) clearInterval(videoInterval); 
    
    // Clear speech queue and cancel any ongoing speech
    speechSynthesis.cancel();
    speechQueue = [];
    isSpeaking = false;
    
    if(canvas){ const ctx = canvas.getContext('2d'); ctx.clearRect(0,0,canvas.width,canvas.height); }
}

function startVideoInterval(){
    if(!isCameraActive || videoInterval || !video || isDetectionStopped) return;
    if (!faceapi.nets.tinyFaceDetector.isLoaded) {
        console.error('Face detection models not loaded');
        statusMessage('Model AI belum dimuat. Silakan refresh halaman.', 'bg-red-100 text-red-700');
        return;
    }
    const displaySize = { width: video.clientWidth, height: video.clientHeight };
    faceapi.matchDimensions(canvas, displaySize);
    
    // Ensure canvas size matches video display size exactly
    if (canvas.width !== displaySize.width || canvas.height !== displaySize.height) {
        canvas.width = displaySize.width;
        canvas.height = displaySize.height;
    }
    // Advanced: Optimized interval for maximum performance and accuracy
    let lastDetectionTime = 0;
    let detectionThrottle = detectionConfig.detectionThrottle; // Use config value
    
    // Fallback for detectDevicePerformance if attendance.js is not loaded
    const getPerfLevel = () => {
        if (typeof detectDevicePerformance === 'function') return detectDevicePerformance();
        const cores = navigator.hardwareConcurrency || 4;
        const memory = navigator.deviceMemory || 4;
        if (cores <= 4 && memory <= 4) return 'low';
        if (cores <= 8 && memory <= 8) return 'medium';
        return 'high';
    };

    const perfLevel = getPerfLevel();
    let optimizedInputSize = detectionConfig.inputSize;
    let optimizedThrottle = detectionThrottle;
    
    // Adjust based on device performance - MORE AGGRESSIVE for low-end devices
    if (perfLevel === 'low') {
        // Low-end devices: reduce resolution and increase throttle significantly
        optimizedInputSize = Math.min(224, detectionConfig.inputSize); // Reduced from 320 to 224
        optimizedThrottle = Math.max(10, detectionThrottle * 3); // Increased from 2x to 3x, minimum 10ms
        console.log('Low-end device detected - using aggressive optimized settings (inputSize: ' + optimizedInputSize + ', throttle: ' + optimizedThrottle + 'ms)');
    } else if (perfLevel === 'medium') {
        // Medium devices: moderate settings
        optimizedInputSize = Math.min(320, detectionConfig.inputSize); // Reduced from 416 to 320
        optimizedThrottle = Math.max(5, detectionThrottle * 2); // Increased from 1.5x to 2x
    } else {
        // High-end devices: use full settings
        optimizedInputSize = detectionConfig.inputSize;
        optimizedThrottle = detectionThrottle;
    }
    
    videoInterval = setInterval(async ()=>{
        // Check if detection is stopped manually
        if (isDetectionStopped || !isCameraActive || isPresensiSuccess) {
            return;
        }
        
        const now = Date.now();
        if (now - lastDetectionTime < optimizedThrottle) {
            return; // Skip detection jika terlalu cepat
        }
        
        // Continue detection for multi-person support
        // Only stop if explicitly requested
        lastDetectionTime = now;
        
        try {
            // Optimasi: Performance monitoring
            const detectionStartTime = performance.now();
            
            // ENHANCED: Optimized detection with adaptive resolution based on device performance
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({
                inputSize: optimizedInputSize, // Use optimized size based on device performance
                scoreThreshold: detectionConfig.scoreThreshold
            })).withFaceLandmarks().withFaceDescriptors();
            
            // Get current display size in every frame to ensure accuracy
            const currentDisplaySize = { width: video.clientWidth, height: video.clientHeight };
            
            // Ensure canvas dimensions match display size
            if (canvas.width !== currentDisplaySize.width || canvas.height !== currentDisplaySize.height) {
                canvas.width = currentDisplaySize.width;
                canvas.height = currentDisplaySize.height;
                faceapi.matchDimensions(canvas, currentDisplaySize);
            }
            
            // BALANCED: Smart filtering for accuracy + speed (using adjusted threshold for mobile)
            const adjustedQualityThreshold = getAdjustedQualityThreshold();
            const qualityDetections = detections.filter(detection => {
                const quality = assessFaceQuality(detection);
                const box = detection.detection.box;
                const area = box.width * box.height;
                // More lenient filtering for mobile devices - allows detection but maintains quality
                return quality >= adjustedQualityThreshold && area >= (detectionConfig.minFaceSize * detectionConfig.minFaceSize * 0.9);
            });
            
            // Sort by quality and take best detections
            qualityDetections.sort((a, b) => assessFaceQuality(b) - assessFaceQuality(a));
            const bestDetections = qualityDetections.slice(0, detectionConfig.maxFaces);
            
            // Optimasi: Update performance stats
            const detectionTime = performance.now() - detectionStartTime;
            performanceStats.detectionCount++;
            performanceStats.totalDetectionTime += detectionTime;
            performanceStats.averageDetectionTime = performanceStats.totalDetectionTime / performanceStats.detectionCount;
            performanceStats.lastDetectionTime = detectionTime;
            
            // ULTRA-FAST: Skip performance logging for maximum speed
            if (performanceStats.detectionCount % 50 === 0) {
                // Dynamic throttle adjustment based on average performance
                if (perfLevel === 'low') {
                    // Low-end devices: VERY aggressive throttling
                    if (performanceStats.averageDetectionTime > 200) {
                        optimizedThrottle = Math.min(50, optimizedThrottle + 5); // Increased max from 30 to 50
                    } else if (performanceStats.averageDetectionTime > 150) {
                        optimizedThrottle = Math.min(40, optimizedThrottle + 3);
                    } else if (performanceStats.averageDetectionTime < 100 && optimizedThrottle > 10) {
                        optimizedThrottle = Math.max(10, optimizedThrottle - 1); // Increased min from 5 to 10
                    }
                } else if (perfLevel === 'medium') {
                    // Medium devices: moderate throttling
                    if (performanceStats.averageDetectionTime > 100) {
                        optimizedThrottle = Math.min(25, optimizedThrottle + 2); // Increased max from 20 to 25
                    } else if (performanceStats.averageDetectionTime < 50 && optimizedThrottle > 5) {
                        optimizedThrottle = Math.max(5, optimizedThrottle - 1); // Increased min from 3 to 5
                    }
                } else {
                    // High-end devices: minimal throttling
                    if (performanceStats.averageDetectionTime > 100) {
                        optimizedThrottle = Math.min(15, optimizedThrottle + 2);
                    } else if (performanceStats.averageDetectionTime < 50 && optimizedThrottle > 1) {
                        optimizedThrottle = Math.max(1, optimizedThrottle - 1);
                    }
                }
                detectionThrottle = optimizedThrottle; // Update for next cycle
            }
            const resized = faceapi.resizeResults(bestDetections, currentDisplaySize);
            // Optimize canvas operations for better performance
            const ctx = canvas.getContext('2d', { 
                willReadFrequently: false, // Better performance
                alpha: true 
            });
            
            // Use requestAnimationFrame for smoother rendering on low-end devices
            if (perfLevel === 'low') {
                requestAnimationFrame(() => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                });
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            
            if (resized.length > 0) {
                if (labeledFaceDescriptors && labeledFaceDescriptors.length > 0) {
                    // Enhanced: Get best match with threshold
                    const adjustedThreshold = (typeof getAdjustedFaceMatcherThreshold === 'function') 
                        ? getAdjustedFaceMatcherThreshold() 
                        : (detectionConfig.faceMatcherThreshold || 0.45);
                    const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, adjustedThreshold);
                    
                    // Get results with both best and second best matches
                    const results = resized.map(d => {
                        // Validate descriptor exists and is valid
                        if (!d.descriptor || !d.descriptor.length || d.descriptor.length === 0) {
                            return {
                                label: 'unknown',
                                distance: Infinity,
                                secondBest: null,
                                confidenceGap: Infinity
                            };
                        }
                        
                        const bestMatch = faceMatcher.findBestMatch(d.descriptor);
                        
                        // Validate bestMatch structure
                        if (!bestMatch || typeof bestMatch !== 'object') {
                            return {
                                label: 'unknown',
                                distance: Infinity,
                                secondBest: null,
                                confidenceGap: Infinity
                            };
                        }
                        
                        // Ensure bestMatch has required properties
                        const validBestMatch = {
                            label: bestMatch.label || 'unknown',
                            distance: (typeof bestMatch.distance === 'number' && isFinite(bestMatch.distance)) ? bestMatch.distance : Infinity
                        };
                        
                        // Calculate second best match for confidence gap validation
                        let secondBestMatch = null;
                        let secondBestDistance = Infinity;
                        
                        // Find second best match (different person)
                        // labeledFaceDescriptors contains LabeledFaceDescriptors objects with:
                        // - label: string
                        // - descriptors: array of Float32Array (can have multiple descriptors per person)
                        for (const labeledDescriptor of labeledFaceDescriptors) {
                            if (labeledDescriptor && 
                                labeledDescriptor.label && 
                                labeledDescriptor.label !== validBestMatch.label && 
                                labeledDescriptor.descriptors && 
                                Array.isArray(labeledDescriptor.descriptors) && 
                                labeledDescriptor.descriptors.length > 0) {
                                
                                // Calculate distance to all descriptors for this person and take the best (smallest) one
                                for (const descriptor of labeledDescriptor.descriptors) {
                                    if (descriptor && 
                                        (descriptor instanceof Float32Array || Array.isArray(descriptor)) && 
                                        descriptor.length > 0 && 
                                        descriptor.length === d.descriptor.length) {
                                        try {
                                            const distance = faceapi.euclideanDistance(d.descriptor, descriptor);
                                            if (!isNaN(distance) && isFinite(distance) && distance < secondBestDistance) {
                                                secondBestDistance = distance;
                                                secondBestMatch = {
                                                    label: labeledDescriptor.label,
                                                    distance: distance
                                                };
                                            }
                                        } catch (err) {
                                            // Skip if descriptor is invalid or calculation fails
                                            console.warn('Error calculating distance for', labeledDescriptor.label, err);
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Calculate confidence gap safely
                        let confidenceGap = Infinity;
                        if (secondBestMatch && 
                            typeof secondBestMatch.distance === 'number' && 
                            isFinite(secondBestMatch.distance) &&
                            typeof validBestMatch.distance === 'number' && 
                            isFinite(validBestMatch.distance)) {
                            confidenceGap = secondBestMatch.distance - validBestMatch.distance;
                        }
                        
                        // Return validated result
                        return {
                            label: validBestMatch.label,
                            distance: validBestMatch.distance,
                            secondBest: secondBestMatch,
                            confidenceGap: confidenceGap
                        };
                    });
                    
                    // Reuse existing context for better performance
                    const ctx2 = ctx; // Use same context instead of creating new one
                    // Optimize canvas clearing for low-end devices
                    if (perfLevel === 'low') {
                        requestAnimationFrame(() => {
                            ctx2.clearRect(0, 0, canvas.width, canvas.height);
                        });
                    } else {
                        ctx2.clearRect(0, 0, canvas.width, canvas.height);
                    }
                    results.forEach((result, i) => {
                        const box = resized[i].detection.box;
                        const face = resized[i];
                        
                        // Karena video di-mirror dengan CSS scaleX(-1), tapi canvas tidak di-mirror,
                        // kita perlu membalik koordinat X agar kotak sesuai dengan posisi wajah di video yang terlihat
                        // Rumus: mirroredX = canvas.width - box.x - box.width
                        const mirroredX = canvas.width - box.x - box.width;
                        
                        // Gambar kotak dengan ukuran yang sesuai
                        ctx2.strokeStyle = '#22c55e';
                        ctx2.lineWidth = 2;
                        ctx2.strokeRect(mirroredX, box.y, box.width, box.height);
                        
                        // Label hasil (tidak terbalik)
                        // Safely get label from result
                        const resultLabel = (result && result.label) ? result.label : 'unknown';
                        const resultDistance = (result && typeof result.distance === 'number' && isFinite(result.distance)) 
                            ? result.distance.toFixed(2) 
                            : '?';
                        const shouldAccept = shouldAcceptDetection(result, face);
                        const label = `${resultLabel} (${resultDistance}) ${shouldAccept ? '✓' : '?'}`;
                        ctx2.font = '14px Inter, sans-serif';
                        ctx2.fillStyle = 'rgba(37, 99, 235, 0.9)';
                        const padding = 4;
                        const textWidth = ctx2.measureText(label).width;
                        ctx2.fillRect(mirroredX, Math.max(0, box.y - 20), textWidth + padding*2, 20);
                        ctx2.fillStyle = '#fff';
                        ctx2.fillText(label, mirroredX + padding, Math.max(12, box.y - 6));
                        
                        // Proses pengenalan
                        if (shouldAccept) {
                            // Recognition handled instantly in shouldAcceptDetection -> handleRecognition
                        }
                    });
                } else {
                    statusMessage('Database wajah kosong. Silakan tambah member.', 'bg-gray-200 text-gray-600');
                    console.warn('⚠️ No face descriptors available for recognition');
                }
            } else {
                if (presensiStatus && presensiStatus.textContent !== 'Arahkan wajah ke kamera') {
                    presensiStatus.textContent = 'Arahkan wajah ke kamera';
                    presensiStatus.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-blue-100 text-blue-700';
                    presensiStatus.classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Face detection error:', error);
            if (presensiStatus && presensiStatus.textContent !== 'Error deteksi wajah') {
                statusMessage('Error deteksi wajah. Coba refresh halaman.', 'bg-red-100 text-red-700');
            }
        }
    }, 10); // ULTRA-FAST interval for <2 second processing
}

if (video) {
    video.addEventListener('play', ()=>{
        if (!videoPlayListenerAdded) {
            startVideoInterval();
            videoPlayListenerAdded = true;
        }
    });
}

function getTopExpression(expressions){
    const map = { happy:'Senang', sad:'Sedih', neutral:'Biasa', angry:'Marah', disgusted:'Capek', surprised:'Ngantuk', fearful:'Laper' };
    let top='neutral', max=0; for(const [k,v] of Object.entries(expressions||{})){ if(v>max){ max=v; top=k; } }
    return map[top] || 'Biasa';
}

// Advanced: Enhanced face quality assessment with detailed analysis
function assessFaceQuality(face) {
    if (!face || !face.detection) return 0;
    
    const box = face.detection.box;
    const area = box.width * box.height;
    const aspectRatio = box.width / box.height;
    const isMobile = isMobileDevice();
    
    // Quality factors with detailed analysis
    let quality = 1.0;
    
    // 1. Size factor (prefer larger faces for better detail) - more lenient for mobile
    if (area < 15000) quality *= isMobile ? 0.4 : 0.3; // More lenient for mobile
    else if (area < 20000) quality *= isMobile ? 0.6 : 0.5; // More lenient for mobile
    else if (area < 30000) quality *= isMobile ? 0.85 : 0.75; // More lenient for mobile
    else if (area > 100000) quality *= 1.4; // Large and detailed - bonus
    else if (area > 60000) quality *= 1.2; // Good size - bonus
    
    // 2. Aspect ratio factor (prefer natural face proportions) - more lenient for mobile
    if (aspectRatio < 0.6 || aspectRatio > 1.6) quality *= isMobile ? 0.6 : 0.5; // More lenient for mobile
    else if (aspectRatio < 0.7 || aspectRatio > 1.4) quality *= isMobile ? 0.9 : 0.8; // More lenient for mobile
    else if (aspectRatio >= 0.8 && aspectRatio <= 1.2) quality *= 1.2; // Good proportions
    
    // 3. Position factor (prefer centered faces) - more lenient for mobile
    const centerX = box.x + box.width / 2;
    const centerY = box.y + box.height / 2;
    const canvasCenterX = 320; // Assuming 640px width
    const canvasCenterY = 240; // Assuming 480px height
    const distanceFromCenter = Math.sqrt(
        Math.pow(centerX - canvasCenterX, 2) + Math.pow(centerY - canvasCenterY, 2)
    );
    if (distanceFromCenter > 150) quality *= isMobile ? 0.5 : 0.4; // More lenient for mobile
    else if (distanceFromCenter > 100) quality *= isMobile ? 0.8 : 0.7; // More lenient for mobile
    else if (distanceFromCenter < 40) quality *= 1.3; // Well centered - bonus
    
    // 4. Enhanced landmark quality factor (if available) - more lenient for mobile
    if (face.landmarks) {
        const landmarkScore = assessEnhancedLandmarkQuality(face.landmarks);
        // For mobile, don't penalize landmark quality as much
        quality *= isMobile ? (0.75 + landmarkScore * 0.25) : (0.7 + landmarkScore * 0.3);
    }
    
    // 5. Expression quality factor (if available) - more lenient for mobile
    if (face.expressions) {
        const expressions = face.expressions;
        const maxExpression = Math.max(...Object.values(expressions));
        if (maxExpression > 0.8) quality *= 1.1; // Clear expression
        else if (maxExpression < 0.3) quality *= isMobile ? 0.95 : 0.9; // More lenient for mobile
    }
    
    // 6. Detection confidence factor - more lenient for mobile
    if (face.detection.score) {
        if (face.detection.score > 0.95) quality *= 1.4; // Very high confidence - bonus
        else if (face.detection.score > 0.85) quality *= 1.2; // High confidence - bonus
        else if (face.detection.score > 0.8) quality *= 1.1; // Good confidence
        else if (face.detection.score < 0.5) quality *= isMobile ? 0.7 : 0.6; // More lenient for mobile
        else if (face.detection.score < 0.6 && isMobile) quality *= 0.85; // Extra lenient for mobile
    }
    
    // 7. Face angle and symmetry factor (if landmarks available) - more lenient for mobile
    if (face.landmarks && face.landmarks.positions) {
        const landmarks = face.landmarks.positions;
        
        // Check eye symmetry
        if (landmarks[36] && landmarks[45]) {
            const leftEyeX = landmarks[36].x;
            const rightEyeX = landmarks[45].x;
            const eyeSymmetry = Math.abs(leftEyeX - rightEyeX);
            if (eyeSymmetry > 20) quality *= isMobile ? 0.8 : 0.7; // More lenient for mobile
            else if (eyeSymmetry < 10) quality *= 1.2; // Good symmetry - bonus
        }
        
        // Check nose position
        if (landmarks[30] && landmarks[36] && landmarks[45]) {
            const noseX = landmarks[30].x;
            const faceCenterX = (landmarks[36].x + landmarks[45].x) / 2;
            const noseOffset = Math.abs(noseX - faceCenterX);
            if (noseOffset > 15) quality *= isMobile ? 0.9 : 0.8; // More lenient for mobile
            else if (noseOffset < 5) quality *= 1.1; // Well centered nose - bonus
        }
    }
    
    return Math.max(0, Math.min(1.5, quality)); // Allow quality > 1 for excellent faces
}

// ENHANCED: Detailed facial feature assessment for better accuracy
function assessEnhancedLandmarkQuality(landmarks) {
    if (!landmarks || !landmarks.positions || landmarks.positions.length < 68) return 0;
    
    const positions = landmarks.positions;
    let featureScore = 0;
    
    // 1. Eye region analysis (points 36-47 for left eye, 42-47 for right eye)
    const leftEyePoints = positions.slice(36, 42);
    const rightEyePoints = positions.slice(42, 48);
    const eyeScore = assessEyeQuality(leftEyePoints, rightEyePoints);
    featureScore += eyeScore * 0.3; // 30% weight for eyes
    
    // 2. Nose analysis (points 27-35)
    const nosePoints = positions.slice(27, 36);
    const noseScore = assessNoseQuality(nosePoints);
    featureScore += noseScore * 0.25; // 25% weight for nose
    
    // 3. Eyebrow analysis (points 17-26)
    const leftEyebrow = positions.slice(17, 22);
    const rightEyebrow = positions.slice(22, 27);
    const eyebrowScore = assessEyebrowQuality(leftEyebrow, rightEyebrow);
    featureScore += eyebrowScore * 0.2; // 20% weight for eyebrows
    
    // 4. Mouth analysis (points 48-67)
    const mouthPoints = positions.slice(48, 68);
    const mouthScore = assessMouthQuality(mouthPoints);
    featureScore += mouthScore * 0.15; // 15% weight for mouth
    
    // 5. Face contour analysis (points 0-16)
    const contourPoints = positions.slice(0, 17);
    const contourScore = assessContourQuality(contourPoints);
    featureScore += contourScore * 0.1; // 10% weight for face shape
    
    return Math.min(1, featureScore);
}

function assessEyeQuality(leftEye, rightEye) {
    if (!leftEye || !rightEye || leftEye.length !== 6 || rightEye.length !== 6) return 0;
    
    let score = 1.0;
    
    // Check eye symmetry
    const leftEyeCenter = getCenterPoint(leftEye);
    const rightEyeCenter = getCenterPoint(rightEye);
    const eyeDistance = Math.abs(leftEyeCenter.x - rightEyeCenter.x);
    const eyeHeightDiff = Math.abs(leftEyeCenter.y - rightEyeCenter.y);
    
    // Good symmetry bonus
    if (eyeHeightDiff < eyeDistance * 0.05) score *= 1.2;
    else if (eyeHeightDiff > eyeDistance * 0.15) score *= 0.8;
    
    // Check eye shape consistency
    const leftEyeShape = getEyeShape(leftEye);
    const rightEyeShape = getEyeShape(rightEye);
    const shapeConsistency = 1 - Math.abs(leftEyeShape - rightEyeShape);
    score *= (0.5 + shapeConsistency * 0.5);
    
    return Math.min(1, score);
}

function assessNoseQuality(nosePoints) {
    if (!nosePoints || nosePoints.length !== 9) return 0;
    
    let score = 1.0;
    
    // Check nose alignment (should be roughly vertical)
    const noseTop = nosePoints[0];
    const noseBottom = nosePoints[6];
    const noseSlope = Math.abs((noseBottom.x - noseTop.x) / (noseBottom.y - noseTop.y));
    
    if (noseSlope < 0.1) score *= 1.2; // Very straight
    else if (noseSlope > 0.3) score *= 0.8; // Too tilted
    
    // Check nose width consistency
    const noseWidth = Math.abs(nosePoints[4].x - nosePoints[8].x);
    const noseHeight = Math.abs(noseBottom.y - noseTop.y);
    const noseRatio = noseWidth / noseHeight;
    
    if (noseRatio > 0.3 && noseRatio < 0.6) score *= 1.1; // Good proportions
    else if (noseRatio > 0.8 || noseRatio < 0.2) score *= 0.9; // Unusual proportions
    
    return Math.min(1, score);
}

function assessEyebrowQuality(leftEyebrow, rightEyebrow) {
    if (!leftEyebrow || !rightEyebrow || leftEyebrow.length !== 5 || rightEyebrow.length !== 5) return 0;
    
    let score = 1.0;
    
    // Check eyebrow symmetry
    const leftEyebrowCenter = getCenterPoint(leftEyebrow);
    const rightEyebrowCenter = getCenterPoint(rightEyebrow);
    const eyebrowHeightDiff = Math.abs(leftEyebrowCenter.y - rightEyebrowCenter.y);
    const eyebrowDistance = Math.abs(leftEyebrowCenter.x - rightEyebrowCenter.x);
    
    if (eyebrowHeightDiff < eyebrowDistance * 0.05) score *= 1.1;
    else if (eyebrowHeightDiff > eyebrowDistance * 0.15) score *= 0.9;
    
    // Check eyebrow shape consistency
    const leftShape = getEyebrowShape(leftEyebrow);
    const rightShape = getEyebrowShape(rightEyebrow);
    const shapeConsistency = 1 - Math.abs(leftShape - rightShape);
    score *= (0.7 + shapeConsistency * 0.3);
    
    return Math.min(1, score);
}

function assessMouthQuality(mouthPoints) {
    if (!mouthPoints || mouthPoints.length !== 20) return 0;
    
    let score = 1.0;
    
    // Check mouth symmetry
    const leftMouth = mouthPoints[0];
    const rightMouth = mouthPoints[6];
    const mouthCenter = mouthPoints[9];
    
    const leftDistance = Math.abs(leftMouth.x - mouthCenter.x);
    const rightDistance = Math.abs(rightMouth.x - mouthCenter.x);
    const symmetry = 1 - Math.abs(leftDistance - rightDistance) / Math.max(leftDistance, rightDistance);
    
    score *= (0.8 + symmetry * 0.2);
    
    return Math.min(1, score);
}

function assessContourQuality(contourPoints) {
    if (!contourPoints || contourPoints.length !== 17) return 0;
    
    let score = 1.0;
    
    // Check face shape consistency
    const chin = contourPoints[8];
    const leftJaw = contourPoints[4];
    const rightJaw = contourPoints[12];
    
    const jawWidth = Math.abs(rightJaw.x - leftJaw.x);
    const faceHeight = Math.abs(chin.y - contourPoints[0].y);
    const faceRatio = jawWidth / faceHeight;
    
    if (faceRatio > 0.6 && faceRatio < 0.9) score *= 1.1; // Good face proportions
    else if (faceRatio > 1.2 || faceRatio < 0.4) score *= 0.9; // Unusual proportions
    
    return Math.min(1, score);
}

// Helper functions
function getCenterPoint(points) {
    const x = points.reduce((sum, p) => sum + p.x, 0) / points.length;
    const y = points.reduce((sum, p) => sum + p.y, 0) / points.length;
    return { x, y };
}

function getEyeShape(eyePoints) {
    const width = Math.abs(eyePoints[3].x - eyePoints[0].x);
    const height = Math.abs(eyePoints[1].y - eyePoints[4].y);
    return width / height;
}

function getEyebrowShape(eyebrowPoints) {
    const start = eyebrowPoints[0];
    const end = eyebrowPoints[4];
    const middle = eyebrowPoints[2];
    const arch = Math.abs(middle.y - (start.y + end.y) / 2);
    const length = Math.abs(end.x - start.x);
    return arch / length;
}

// Advanced: Multiple detection attempts for better accuracy
let detectionAttempts = 0;
// Multi-person detection queue system
let detectionHistory = [];
let recognitionQueue = [];
let isProcessingQueue = false;
let lastSuccessfulDetection = null;

function shouldAcceptDetection(result, face) {
    // Comprehensive validation of result object
    if (!result || typeof result !== 'object') {
        console.warn('Invalid result: result is not an object', result);
        return false;
    }
    
    if (!result.label || result.label === 'unknown') {
        return false;
    }
    
    // Safe toFixed helper to prevent errors
    const safeToFixed = (value, decimals = 3) => {
        if (typeof value !== 'number' || isNaN(value) || !isFinite(value)) return 'N/A';
        return value.toFixed(decimals);
    };
    
    // Validate result.distance exists and is a valid number
    if (typeof result.distance !== 'number' || isNaN(result.distance) || !isFinite(result.distance)) {
        console.warn('Invalid result.distance:', result.distance, 'for label:', result.label);
        return false;
    }
    
    // Skip if this label recently processed
    const lastTs = processedLabels.get(result.label) || 0;
    if (Date.now() - lastTs < processedCooldownMs) return false;
    
    const isMobile = isMobileDevice();
    
    // ENHANCED: Adaptive threshold based on confidence gap and face quality
    const baseThreshold = getAdjustedRecognitionThreshold();
    const quality = assessFaceQuality(face);
    
    // Validate quality is a valid number
    if (typeof quality !== 'number' || isNaN(quality) || !isFinite(quality)) {
        console.warn('Invalid quality:', quality);
        return false;
    }
    
    // Calculate adaptive threshold based on confidence gap
    // If confidence gap is large (best match is much better than second best), we can be more lenient
    // If confidence gap is small (best and second best are close), we need to be stricter
    const confidenceGap = (typeof result.confidenceGap === 'number' && isFinite(result.confidenceGap)) ? result.confidenceGap : 0;
    let adaptiveThreshold = baseThreshold;
    
    if (confidenceGap > 0.15) {
        // Large gap: best match is clearly better - can be more lenient (up to 0.05 more lenient)
        adaptiveThreshold = Math.min(baseThreshold + 0.05, 0.60);
    } else if (confidenceGap > 0.08) {
        // Medium gap: slightly more lenient
        adaptiveThreshold = Math.min(baseThreshold + 0.02, 0.55);
    } else if (confidenceGap > 0.03) {
        // Small gap: use base threshold
        adaptiveThreshold = baseThreshold;
    } else {
        // Very small gap (< 0.03): be stricter to prevent false positive
        adaptiveThreshold = Math.max(baseThreshold - 0.05, 0.30);
    }
    
    // Adjust threshold based on face quality
    // Higher quality = can be slightly more lenient, lower quality = need to be stricter
    if (quality > 0.7) {
        adaptiveThreshold = Math.min(adaptiveThreshold + 0.02, 0.60);
    } else if (quality < 0.4) {
        adaptiveThreshold = Math.max(adaptiveThreshold - 0.03, 0.30);
    }
    
    // CRITICAL: Confidence gap validation to prevent false positive
    // If second best match is too close to best match, reject to prevent misidentification
    if (result.secondBest && confidenceGap < 0.05) {
        // Confidence gap too small - best and second best are very close
        // This is a red flag for potential false positive
        const secondBestDistance = safeToFixed(result.secondBest?.distance);
        const secondBestLabel = result.secondBest?.label || 'unknown';
        console.log(`🚫 Confidence gap too small (${safeToFixed(confidenceGap)} < 0.05) - best: ${result.label} (${safeToFixed(result.distance)}), second: ${secondBestLabel} (${secondBestDistance})`);
        return false;
    }
    
    // Check distance against adaptive threshold
    if (result.distance > adaptiveThreshold) {
        console.log(`🚫 Distance ${safeToFixed(result.distance)} exceeds adaptive threshold ${safeToFixed(adaptiveThreshold)} (base: ${safeToFixed(baseThreshold)}, gap: ${safeToFixed(confidenceGap)}, quality: ${safeToFixed(quality)}, device: ${isMobile ? 'mobile' : 'desktop'})`);
        return false;
    }
    
    // SPECIAL CASE: For excellent distance (< 0.35), be very lenient with other checks
    // This is because excellent distance means very high confidence in face match
    const isExcellentDistance = result.distance < 0.35;
    const isVeryGoodDistance = result.distance < 0.45;
    
    // Enhanced quality check with facial feature analysis
    // Quality already calculated above, reuse it
    const adjustedQualityThreshold = getAdjustedQualityThreshold();
    
    // For mobile, use distance-based quality thresholds to maintain accuracy
    // Also consider confidence gap - larger gap means we can be more lenient
    let effectiveQualityThreshold = adjustedQualityThreshold;
    if (isMobile) {
        if (isExcellentDistance && confidenceGap > 0.10) {
            // Excellent distance + large gap = very high confidence, allow very low quality
            effectiveQualityThreshold = 0.10;
        } else if (isExcellentDistance) {
            // Excellent distance but smaller gap - still allow low quality
            effectiveQualityThreshold = 0.15;
        } else if (isVeryGoodDistance && confidenceGap > 0.08) {
            // Very good distance + medium gap = high confidence, allow low quality
            effectiveQualityThreshold = 0.20;
        } else if (isVeryGoodDistance) {
            // Very good distance but smaller gap
            effectiveQualityThreshold = 0.25;
        } else if (result.distance < 0.50 && confidenceGap > 0.08) {
            // Good distance + medium gap = moderate confidence, allow moderate quality
            effectiveQualityThreshold = 0.30;
        } else if (result.distance < 0.50) {
            effectiveQualityThreshold = 0.35;
        }
    } else {
        // Desktop: stricter but still consider confidence gap
        if (isExcellentDistance && confidenceGap > 0.10) {
            effectiveQualityThreshold = 0.20;
        } else if (isExcellentDistance) {
            effectiveQualityThreshold = 0.30;
        } else if (isVeryGoodDistance && confidenceGap > 0.08) {
            effectiveQualityThreshold = 0.35;
        }
    }
    
    if (quality < effectiveQualityThreshold) {
        // For excellent distance with large gap, allow much lower quality threshold
        if (isExcellentDistance && confidenceGap > 0.10 && quality > 0.08) {
            console.log(`⚠️ Quality ${safeToFixed(quality)} below standard threshold ${safeToFixed(adjustedQualityThreshold)}, but allowing due to excellent distance < 0.35 and large gap ${safeToFixed(confidenceGap)} (effective threshold: ${safeToFixed(effectiveQualityThreshold)})`);
        } else if (isExcellentDistance && quality > 0.12) {
            console.log(`⚠️ Quality ${safeToFixed(quality)} below standard threshold ${safeToFixed(adjustedQualityThreshold)}, but allowing due to excellent distance < 0.35 (effective threshold: ${safeToFixed(effectiveQualityThreshold)})`);
        } else if (isVeryGoodDistance && confidenceGap > 0.08 && quality > 0.15) {
            console.log(`⚠️ Quality ${safeToFixed(quality)} below standard threshold ${safeToFixed(adjustedQualityThreshold)}, but allowing due to very good distance < 0.45 and medium gap ${safeToFixed(confidenceGap)} (effective threshold: ${safeToFixed(effectiveQualityThreshold)})`);
        } else if (isMobile && result.distance < 0.50 && confidenceGap > 0.08 && quality > 0.25) {
            console.log(`⚠️ Quality ${safeToFixed(quality)} below standard threshold ${safeToFixed(adjustedQualityThreshold)}, but allowing due to good distance < 0.50 and medium gap (mobile, effective threshold: ${safeToFixed(effectiveQualityThreshold)})`);
        } else {
            console.log(`🚫 Quality ${safeToFixed(quality)} below threshold ${safeToFixed(effectiveQualityThreshold)} (device: ${isMobile ? 'mobile' : 'desktop'}, distance: ${safeToFixed(result.distance)}, gap: ${safeToFixed(confidenceGap)})`);
            return false;
        }
    }
    
    // ENHANCED: Facial feature consistency check with confidence gap consideration
    // More lenient for mobile - skip if landmarks not available
    if (face.landmarks) {
        const landmarkScore = assessEnhancedLandmarkQuality(face.landmarks);
        const adjustedLandmarkThreshold = getAdjustedLandmarkThreshold();
        
        // Adjust landmark threshold based on distance AND confidence gap
        let effectiveLandmarkThreshold = adjustedLandmarkThreshold;
        if (isMobile) {
            if (isExcellentDistance && confidenceGap > 0.10) {
                effectiveLandmarkThreshold = 0.25; // Very low for excellent distance + large gap
            } else if (isExcellentDistance) {
                effectiveLandmarkThreshold = 0.30; // Low for excellent distance
            } else if (isVeryGoodDistance && confidenceGap > 0.08) {
                effectiveLandmarkThreshold = 0.35; // Low for very good distance + medium gap
            } else if (isVeryGoodDistance) {
                effectiveLandmarkThreshold = 0.40; // Moderate for very good distance
            } else if (result.distance < 0.50 && confidenceGap > 0.08) {
                effectiveLandmarkThreshold = 0.40; // Moderate for good distance + medium gap
            }
        } else {
            // Desktop: stricter but still consider confidence gap
            if (isExcellentDistance && confidenceGap > 0.10) {
                effectiveLandmarkThreshold = 0.35;
            } else if (isExcellentDistance) {
                effectiveLandmarkThreshold = 0.40;
            } else if (isVeryGoodDistance && confidenceGap > 0.08) {
                effectiveLandmarkThreshold = 0.45;
            }
        }
        
        if (landmarkScore < effectiveLandmarkThreshold) {
            // For excellent distance with large gap, allow much lower landmark score
            if (isExcellentDistance && confidenceGap > 0.10 && landmarkScore > 0.20) {
                console.log(`⚠️ Landmark score ${safeToFixed(landmarkScore)} below standard threshold ${safeToFixed(adjustedLandmarkThreshold)}, but allowing due to excellent distance < 0.35 and large gap ${safeToFixed(confidenceGap)} (effective threshold: ${safeToFixed(effectiveLandmarkThreshold)})`);
            } else if (isExcellentDistance && landmarkScore > 0.25) {
                console.log(`⚠️ Landmark score ${safeToFixed(landmarkScore)} below standard threshold ${safeToFixed(adjustedLandmarkThreshold)}, but allowing due to excellent distance < 0.35 (effective threshold: ${safeToFixed(effectiveLandmarkThreshold)})`);
            } else if (isVeryGoodDistance && confidenceGap > 0.08 && landmarkScore > 0.30) {
                console.log(`⚠️ Landmark score ${safeToFixed(landmarkScore)} below standard threshold ${safeToFixed(adjustedLandmarkThreshold)}, but allowing due to very good distance < 0.45 and medium gap ${safeToFixed(confidenceGap)} (effective threshold: ${safeToFixed(effectiveLandmarkThreshold)})`);
            } else if (isMobile && result.distance < 0.50 && confidenceGap > 0.08 && quality > 0.25 && landmarkScore > 0.35) {
                console.log(`⚠️ Landmark score ${safeToFixed(landmarkScore)} below standard threshold ${safeToFixed(adjustedLandmarkThreshold)}, but allowing due to good distance/quality/gap (mobile)`);
            } else {
                console.log(`🚫 Landmark score ${safeToFixed(landmarkScore)} below threshold ${safeToFixed(effectiveLandmarkThreshold)} (device: ${isMobile ? 'mobile' : 'desktop'}, distance: ${safeToFixed(result.distance)}, gap: ${safeToFixed(confidenceGap)})`);
                return false;
            }
        }
    }
    
    // NEW: Gender validation to prevent cross-gender misdetection (very lenient for excellent distance)
    // CRITICAL: Keep gender validation strict for accuracy, but allow excellent distance
    if (detectionConfig.genderValidation) {
        const genderMatch = validateGenderConsistency(result.label, face);
        if (!genderMatch) {
            // For excellent distance, be very lenient with gender validation
            if (isExcellentDistance) {
                console.log(`⚠️ Gender validation failed for ${result.label}, but allowing due to excellent distance < 0.35 (mobile)`);
            } else if (isVeryGoodDistance && quality > 0.20) {
                console.log(`⚠️ Gender validation failed for ${result.label}, but allowing due to very good distance < 0.45 (mobile)`);
            } else if (isMobile && result.distance < 0.50 && quality > 0.30) {
                console.log(`⚠️ Gender validation failed for ${result.label}, but allowing due to good distance/quality (mobile)`);
            } else {
                console.log(`🚫 Gender validation failed for ${result.label} (distance: ${safeToFixed(result.distance)}, quality: ${safeToFixed(quality)})`);
                return false;
            }
        }
    }
    
    // NEW: Multi-attempt validation for critical decisions (very lenient for excellent distance)
    // For excellent distance, skip strict validation entirely - distance is already strong indicator
    if (isExcellentDistance) {
        console.log(`✅ Excellent distance < 0.35 detected, using lenient multi-attempt validation for mobile`);
        // Still do basic validation but much more lenient
        const validationScore = performMultiAttemptValidation(result, face, isMobile);
        // Validate validationScore is a number
        if (typeof validationScore === 'number' && isFinite(validationScore)) {
            // For excellent distance, only reject if validation score is extremely low
            if (validationScore < 0.20) {
                console.log(`🚫 Multi-attempt validation score ${safeToFixed(validationScore)} extremely low (< 0.20), rejecting despite excellent distance`);
                return false;
            }
        }
    } else if (detectionConfig.multiAttemptValidation && detectionConfig.strictMode) {
        // For mobile, skip strict mode if distance and quality are good enough
        const shouldSkipStrictMode = isMobile && result.distance < 0.50 && quality > 0.20;
        
        if (shouldSkipStrictMode || detectionConfig.strictMode) {
            const validationScore = performMultiAttemptValidation(result, face, isMobile);
            // Validate validationScore is a number
            if (typeof validationScore === 'number' && isFinite(validationScore)) {
                // Much more lenient minimum score for mobile devices
                const minValidationScore = isMobile ? 0.30 : 0.5; // Lowered from 0.35 to 0.30 for mobile
                if (validationScore < minValidationScore) {
                    // For mobile, allow if distance is very good even if validation score is slightly lower
                    if (isMobile && result.distance < 0.40 && quality > 0.25) {
                        console.log(`⚠️ Multi-attempt validation score ${safeToFixed(validationScore)} below threshold ${minValidationScore}, but allowing due to excellent distance/quality (mobile)`);
                    } else if (isMobile && result.distance < 0.45 && quality > 0.20 && validationScore >= 0.25) {
                        // Additional fallback for mobile - allow if score is close to threshold
                        console.log(`⚠️ Multi-attempt validation score ${safeToFixed(validationScore)} below threshold ${minValidationScore}, but allowing due to good distance/quality (mobile, lenient mode)`);
                    } else {
                        console.log(`🚫 Multi-attempt validation failed for ${result.label} (score: ${safeToFixed(validationScore)}, min: ${minValidationScore}, device: ${isMobile ? 'mobile' : 'desktop'})`);
                        return false;
                    }
                }
            }
        }
    }
    
    // Check if this person is already being processed
    if (isProcessingRecognition) return false;
    
    // ENHANCED: Additional confidence gap validation for edge cases
    // Even if gap > 0.05, if gap is small and distance is borderline, be cautious
    if (result.secondBest && confidenceGap < 0.10 && result.distance > (adaptiveThreshold * 0.85)) {
        // Gap is small-medium and distance is close to threshold
        // Require higher quality or better distance for acceptance
        if (quality < 0.5 && result.distance > (adaptiveThreshold * 0.90)) {
            console.log(`🚫 Borderline detection rejected: distance ${safeToFixed(result.distance)} close to threshold ${safeToFixed(adaptiveThreshold)} with small gap ${safeToFixed(confidenceGap)} and low quality ${safeToFixed(quality)}`);
            return false;
        }
    }
    
    // ENHANCED: Log successful detection with confidence gap info
    const secondBestInfo = result.secondBest 
        ? `${result.secondBest.label || 'unknown'} ${safeToFixed(result.secondBest.distance)}`
        : 'N/A';
    const gapInfo = result.secondBest ? `gap: ${safeToFixed(confidenceGap)} (2nd: ${secondBestInfo})` : 'gap: N/A';
    console.log(`✅ Valid detection: ${result.label} (distance: ${safeToFixed(result.distance)}, ${gapInfo}, quality: ${safeToFixed(quality)}, adaptive threshold: ${safeToFixed(adaptiveThreshold)}, base: ${safeToFixed(baseThreshold)}, device: ${isMobile ? 'mobile' : 'desktop'}, excellent: ${isExcellentDistance ? 'YES' : 'NO'})`);
    console.log(`🎯 Processing attendance for: ${result.label}`);
    
    // INSTANT RECOGNITION: Process immediately on first valid detection
    addToRecognitionQueue(result.label, face);
    return true;
}

function addToRecognitionQueue(label, face) {
    // INSTANT PROCESSING: Always process immediately for maximum speed
    // console.log(`🚀 INSTANT PROCESSING for ${label}`);
    handleRecognition(label, 'Biasa'); // Use default expression for speed
}

// NEW: Gender validation function to prevent cross-gender misdetection
function validateGenderConsistency(label, face) {
    try {
        // Check if members array is available
        if (!members || !Array.isArray(members) || members.length === 0) {
            console.log('⚠️ Members array not available for gender validation, allowing detection');
            return true; // Allow detection if no member data
        }
        
        // Get employee data to check gender consistency
        const employee = members.find(m => m.nim === label);
        if (!employee) {
            console.log(`⚠️ Employee data not found for ${label}, allowing detection`);
            return true; // If no employee data, allow detection
        }
        
        // Simple gender detection based on facial features
        if (face.landmarks && face.landmarks.positions) {
            const landmarks = face.landmarks.positions;
            
            // Check if we have enough landmarks
            if (landmarks.length < 68) {
                console.log(`⚠️ Insufficient landmarks for gender validation (${landmarks.length}/68), allowing detection`);
                return true;
            }
            
            // Analyze jawline width (typically wider in males)
            const jawWidth = Math.abs(landmarks[16].x - landmarks[0].x);
            const faceHeight = Math.abs(landmarks[8].y - landmarks[19].y);
            const jawRatio = jawWidth / faceHeight;
            
            // Analyze eyebrow thickness and position
            const leftEyebrowThickness = Math.abs(landmarks[19].y - landmarks[20].y);
            const rightEyebrowThickness = Math.abs(landmarks[24].y - landmarks[25].y);
            const avgEyebrowThickness = (leftEyebrowThickness + rightEyebrowThickness) / 2;
            
            // More lenient heuristic: wider jaw and thicker eyebrows suggest male
            const isLikelyMale = jawRatio > 0.75 && avgEyebrowThickness > 4; // More strict criteria
            const isLikelyFemale = jawRatio < 0.6 && avgEyebrowThickness < 2; // More strict criteria
            
            // Check if employee name suggests gender (simple heuristic)
            const name = employee.nama.toLowerCase();
            const maleNames = ['budi', 'andi', 'joko', 'agus', 'doni', 'riko', 'tono', 'surya', 'rama', 'ahmad', 'muhammad', 'ali', 'umar', 'yusuf'];
            const femaleNames = ['sari', 'dewi', 'maya', 'lina', 'rina', 'siti', 'nina', 'dina', 'lisa', 'ana', 'sarah', 'fatimah', 'aisha', 'zainab'];
            
            const nameSuggestsMale = maleNames.some(maleName => name.includes(maleName));
            const nameSuggestsFemale = femaleNames.some(femaleName => name.includes(femaleName));
            
            // Only reject if we have VERY strong conflicting indicators
            if (isLikelyMale && nameSuggestsFemale && jawRatio > 0.8 && avgEyebrowThickness > 5) {
                console.log(`🚫 Strong gender mismatch: Face strongly suggests male but name suggests female for ${label}`);
                return false;
            }
            if (isLikelyFemale && nameSuggestsMale && jawRatio < 0.55 && avgEyebrowThickness < 1.5) {
                console.log(`🚫 Strong gender mismatch: Face strongly suggests female but name suggests male for ${label}`);
                return false;
            }
            
            console.log(`✅ Gender validation passed for ${label} (jawRatio: ${jawRatio.toFixed(3)}, eyebrowThickness: ${avgEyebrowThickness.toFixed(3)})`);
        }
        
        return true; // Allow detection if no clear gender mismatch
    } catch (error) {
        console.warn('Gender validation error:', error);
        return true; // Allow detection on error
    }
}

// BALANCED: Multi-attempt validation - balanced scoring for reliable detection
function performMultiAttemptValidation(result, face, isMobile = false) {
    try {
        let validationScore = 0;
        let maxPossibleScore = 0;
        
        // Score 1: Distance-based validation (40% weight)
        // Much more lenient scoring for mobile devices
        const distanceWeight = 0.4;
        maxPossibleScore += distanceWeight;
        const mobileDistanceThreshold = isMobile ? 0.55 : 0.38; // Increased from 0.50 to 0.55 for mobile
        const excellentThreshold = isMobile ? 0.40 : 0.30; // More lenient excellent threshold for mobile
        
        if (result.distance < excellentThreshold) {
            validationScore += distanceWeight * 1.0; // Excellent match
        } else if (result.distance < mobileDistanceThreshold) {
            validationScore += distanceWeight * 0.95; // Very good match (within threshold) - increased from 0.9
        } else if (result.distance < (isMobile ? 0.60 : 0.45)) {
            validationScore += distanceWeight * 0.85; // Good match - increased from 0.8 for mobile
        } else if (result.distance < (isMobile ? 0.70 : 0.55)) {
            validationScore += distanceWeight * 0.7; // Acceptable match - increased from 0.6 for mobile
        } else {
            validationScore += distanceWeight * 0.4; // Poor match - increased from 0.3 for mobile
        }
        
        // Score 2: Quality-based validation (35% weight)
        const qualityWeight = 0.35;
        const quality = assessFaceQuality(face);
        maxPossibleScore += qualityWeight;
        const adjustedQualityThreshold = getAdjustedQualityThreshold();
        if (quality > 0.75) {
            validationScore += qualityWeight * 1.0; // Excellent quality
        } else if (quality > adjustedQualityThreshold + 0.1) {
            validationScore += qualityWeight * 0.9; // Very good quality (above threshold)
        } else if (quality > adjustedQualityThreshold) {
            validationScore += qualityWeight * 0.85; // Good quality (within threshold)
        } else if (quality > adjustedQualityThreshold - 0.05) {
            validationScore += qualityWeight * 0.75; // Acceptable quality - increased for mobile
        } else if (quality > adjustedQualityThreshold - 0.1) {
            validationScore += qualityWeight * 0.6; // Marginally acceptable quality - increased for mobile
        } else if (quality > adjustedQualityThreshold - 0.15 && isMobile) {
            validationScore += qualityWeight * 0.5; // Still acceptable for mobile
        } else {
            validationScore += qualityWeight * 0.3; // Poor quality
        }
        
        // Score 3: Landmark-based validation (25% weight, optional)
        const landmarkWeight = 0.25;
        if (face.landmarks) {
            maxPossibleScore += landmarkWeight;
            const landmarkScore = assessEnhancedLandmarkQuality(face.landmarks);
            const adjustedLandmarkThreshold = getAdjustedLandmarkThreshold();
            if (landmarkScore > 0.7) {
                validationScore += landmarkWeight * 1.0; // Excellent landmarks
            } else if (landmarkScore > adjustedLandmarkThreshold + 0.1) {
                validationScore += landmarkWeight * 0.9; // Very good landmarks (above threshold)
            } else if (landmarkScore > adjustedLandmarkThreshold) {
                validationScore += landmarkWeight * 0.85; // Good landmarks (within threshold)
            } else if (landmarkScore > adjustedLandmarkThreshold - 0.05) {
                validationScore += landmarkWeight * 0.75; // Acceptable landmarks - increased for mobile
            } else if (landmarkScore > adjustedLandmarkThreshold - 0.1) {
                validationScore += landmarkWeight * 0.6; // Marginally acceptable landmarks - increased for mobile
            } else if (landmarkScore > adjustedLandmarkThreshold - 0.15 && isMobile) {
                validationScore += landmarkWeight * 0.5; // Still acceptable for mobile
            } else {
                validationScore += landmarkWeight * 0.3; // Poor landmarks
            }
        } else if (isMobile) {
            // For mobile, don't penalize too much if landmarks are missing
            maxPossibleScore += landmarkWeight;
            validationScore += landmarkWeight * 0.6; // Give partial credit for mobile
        }
        
        // Calculate normalized score (0-1 scale)
        const finalScore = maxPossibleScore > 0 ? validationScore / maxPossibleScore : 0.5;
        console.log(`Multi-attempt validation score: ${finalScore.toFixed(3)} (distance: ${result.distance.toFixed(3)}, quality: ${quality.toFixed(3)}, landmark: ${face.landmarks ? assessEnhancedLandmarkQuality(face.landmarks).toFixed(3) : 'N/A'}, device: ${isMobile ? 'mobile' : 'desktop'})`);
        return finalScore;
    } catch (error) {
        console.warn('Multi-attempt validation error:', error);
        return 0.6; // Balanced neutral score on error
    }
}

// Queue system removed for instant processing

let isProcessingRecognition = false;
// Track processed labels to prevent duplicate submissions while tetap melanjutkan deteksi
let processedLabels = new Map(); // nim -> timestamp ms
const processedCooldownMs = 30000; // 30 detik

async function handleRecognition(nim, topExpression){
    if(!scanMode || isProcessingRecognition) return;
    isProcessingRecognition = true;
    
        // Ultra-fast processing - minimal logging
        // console.log('Recognition triggered:', { nim, topExpression, scanMode });
    
    // Parallel processing with improved performance
    const [screenshot, position, membersList] = await Promise.all([
        // Screenshot attempt
        new Promise((resolve) => {
            // ... (keep existing screenshot logic)
            try {
                // Wait for video to be ready - check multiple times if needed
                const checkVideoReady = (attempts = 0) => {
                    if (attempts > 10) {
                        console.warn('Video not ready after multiple attempts');
                        resolve(null);
                        return;
                    }
                    
                    if (video && canvas && video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
                        try {
                            // Ensure video is playing and has valid frame
                            if (video.paused) {
                                video.play().catch(() => {});
                            }
                            
                            // Small delay to ensure frame is rendered
                            setTimeout(() => {
                                try {
                                    const ctx = canvas.getContext('2d');
                                    canvas.width = video.videoWidth;
                                    canvas.height = video.videoHeight;
                                    
                                    // Draw video frame to canvas - ensure video is visible
                                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                                    
                                    // Check if canvas has valid image data (not black)
                                    const imageData = ctx.getImageData(0, 0, Math.min(100, canvas.width), Math.min(100, canvas.height));
                                    const pixels = imageData.data;
                                    let hasNonBlackPixels = false;
                                    for (let i = 0; i < pixels.length; i += 4) {
                                        const r = pixels[i];
                                        const g = pixels[i + 1];
                                        const b = pixels[i + 2];
                                        // Check if pixel is not black (allow some tolerance)
                                        if (r > 10 || g > 10 || b > 10) {
                                            hasNonBlackPixels = true;
                                            break;
                                        }
                                    }
                                    
                                    if (!hasNonBlackPixels && attempts < 5) {
                                        // Canvas is black, wait a bit and retry
                                        setTimeout(() => checkVideoReady(attempts + 1), 100);
                                        return;
                                    }
                                    
                                    // Resize to speed up upload while keeping enough detail for verification
                                    const targetW = 240; const scale = targetW / canvas.width; const targetH = Math.round(canvas.height * scale);
                                    const tmp = document.createElement('canvas'); const tctx = tmp.getContext('2d');
                                    tmp.width = targetW; tmp.height = targetH;
                                    // Center-crop from the middle to avoid only-forehead issue on tall mobile cameras
                                    const srcW = video.videoWidth;
                                    const srcH = video.videoHeight;
                                    const aspect = targetW / targetH;
                                    let cropW = srcW;
                                    let cropH = Math.round(cropW / aspect);
                                    if (cropH > srcH) { cropH = srcH; cropW = Math.round(cropH * aspect); }
                                    const sx = Math.max(0, Math.floor((srcW - cropW) / 2));
                                    const sy = Math.max(0, Math.floor((srcH - cropH) / 2));
                                    tctx.drawImage(video, sx, sy, cropW, cropH, 0, 0, targetW, targetH);
                                    const screenshot = tmp.toDataURL('image/jpeg', 0.7); // Higher quality to avoid black screenshots
                                    resolve(screenshot);
                                } catch (drawError) {
                                    console.warn('Failed to draw video to canvas:', drawError);
                                    if (attempts < 5) {
                                        setTimeout(() => checkVideoReady(attempts + 1), 100);
                                    } else {
                                        resolve(null);
                                    }
                                }
                            }, 50); // Small delay to ensure frame is rendered
                        } catch (error) {
                            console.warn('Screenshot error:', error);
                            if (attempts < 5) {
                                setTimeout(() => checkVideoReady(attempts + 1), 100);
                            } else {
                                resolve(null);
                            }
                        }
                    } else {
                        // Video not ready, wait and retry
                        if (attempts < 10) {
                            setTimeout(() => checkVideoReady(attempts + 1), 100);
                        } else {
                            console.warn('Video not ready for screenshot after retries');
                            resolve(null);
                        }
                    }
                };
                
                checkVideoReady(0);
            } catch (screenshotError) {
                console.warn('Failed to take screenshot:', screenshotError);
                resolve(null);
            }
        }),
        
        // Geolocation - Accept GPS even with lower accuracy, but require permission
        new Promise((resolve) => {
            if (!navigator.geolocation) return resolve(null);
            navigator.geolocation.getCurrentPosition(
                pos => {
                    // Accept GPS position regardless of accuracy
                    resolve(pos);
                }, 
                err => {
                    console.warn('Geolocation error:', err);
                    // Check if permission was denied
                    if (navigator.permissions) {
                        navigator.permissions.query({ name: 'geolocation' }).then(result => {
                            if (result.state === 'denied') {
                                console.error('Location permission denied');
                            }
                        }).catch(() => {});
                    }
                    resolve(null);
                }, 
                { 
                    enableHighAccuracy: false, // Set to false for faster response on old devices
                    timeout: 4000, // Reduced to 4 seconds for faster response
                    maximumAge: 30000 // Allow 30 second cache for speed (reduced from 60s)
                }
            );
        })
    ]);
    
    // Validate screenshot before proceeding
    if (!screenshot || screenshot.length < 1000) {
        statusMessage('Gagal mengambil screenshot. Silakan coba lagi dengan posisi yang lebih baik.', 'bg-red-100 text-red-700');
        isProcessingRecognition = false;
        return;
    }
    
    // Use position from parallel processing with strict validation
    let lat=null, lng=null;
    if (position) {
        lat = position.coords.latitude;
        lng = position.coords.longitude;
        // Validate coordinates are valid numbers
        if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) {
            lat = null;
            lng = null;
            statusMessage('Koordinat GPS tidak valid. Pastikan GPS aktif dan akurat.', 'bg-red-100 text-red-700');
        }
        // GPS accuracy is accepted regardless of value (no warning shown)
    } else {
        // Check if permissions are already granted before showing error
        // Only show error if permission was denied, not if there's a timeout or other issue
        if (typeof navigator !== 'undefined' && navigator.permissions) {
            navigator.permissions.query({ name: 'geolocation' }).then(result => {
                if (result.state === 'denied') {
                    statusMessage('Izin lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.', 'bg-red-100 text-red-700');
                } else if (result.state === 'prompt') {
                    statusMessage('Silakan izinkan akses lokasi untuk melanjutkan presensi.', 'bg-yellow-100 text-yellow-700');
                } else {
                    // Permission granted but GPS still failed - might be timeout or GPS not available
                    statusMessage('Mendapatkan lokasi memakan waktu lama. Pastikan GPS aktif dan berada di area terbuka.', 'bg-yellow-100 text-yellow-700');
                }
                
                // Safe event listener binding
                if (result && typeof result.addEventListener === 'function') {
                    result.addEventListener('change', function() {
                        console.log('Permission state changed:', result.state);
                    });
                } else if (result) {
                    result.onchange = function() {
                        console.log('Permission state changed:', result.state);
                    };
                }
            }).catch(() => {
                // Fallback if permissions API not available
                statusMessage('Mendapatkan lokasi memakan waktu lama. Pastikan GPS aktif dan berada di area terbuka.', 'bg-yellow-100 text-yellow-700');
            });
        } else {
            // Fallback if permissions API not available
            statusMessage('Mendapatkan lokasi memakan waktu lama. Pastikan GPS aktif dan berada di area terbuka.', 'bg-yellow-100 text-yellow-700');
        }
        isProcessingRecognition = false;
        return;
    }
    
    // Validate location is required for attendance
    if (!lat || !lng) {
        statusMessage('Lokasi GPS wajib untuk presensi. Pastikan GPS aktif dan izin lokasi diberikan.', 'bg-red-100 text-red-700');
        isProcessingRecognition = false;
        return;
    }
    
    // FAST: Get location string immediately (don't wait, submit with coordinates if needed)
    // Start getting location string in parallel while processing other things
    let lokasi = '';
    const locationPromise = getStreetNameFromCoordinates(lat, lng).then(loc => {
        if (loc) return loc;
        return `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }).catch(() => {
        return `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    });
    
    // Get WiFi SSID if available (for WFO validation)
    // Note: Browser security prevents direct WiFi SSID access, but we can try multiple methods
    let wifiSSID = '';
    try {
        // Method 1: Check if we're on WiFi connection
        if (navigator.connection) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (connection && connection.type === 'wifi') {
                // We're on WiFi, try to get more info if available
                // For Chrome on Android, we might be able to get SSID in some cases
                if (connection.wifiSSID) {
                    wifiSSID = connection.wifiSSID;
                }
            }
        }
        
        // Method 2: Try Chrome-specific API (limited support)
        if (!wifiSSID && navigator.connection && 'getNetworkInformation' in navigator.connection) {
            try {
                const networkInfo = await navigator.connection.getNetworkInformation();
                if (networkInfo && networkInfo.wifiSSID) {
                    wifiSSID = networkInfo.wifiSSID;
                }
            } catch (e) {
                // Not available
            }
        }
        
        // If still empty and we're inside WFO area (by GPS), assume connected to Telkom WiFi
        // Backend will validate based on IP and location
        if (!wifiSSID && lat && lng) {
            // We'll let backend determine if WiFi is required based on location
            // This allows presensi if GPS indicates inside WFO area
        }
    } catch (e) {
        // WiFi detection not available on this platform - backend will handle validation
    }

    async function submitAttendance(extra={}){
        return api('?ajax=save_attendance', { 
            nim,
            mode: scanMode,
            ekspresi: topExpression,
            screenshot: screenshot,
            lat: lat ?? '',
            lng: lng ?? '',
            lokasi: lokasi ?? '',
            wifi_ssid: wifiSSID,
            gps_accuracy: position?.coords?.accuracy || '',
            ...extra
        }, { suppressModal: true });
    }

    try{
        // OPTIMIZED: Fetch public IP with aggressive timeout for better performance
        // Use cached IP if available (valid for 5 minutes)
        const ipCacheKey = 'cached_public_ip';
        const ipCacheTimeKey = 'cached_public_ip_time';
        const cachedIp = sessionStorage.getItem(ipCacheKey);
        const cachedIpTime = parseInt(sessionStorage.getItem(ipCacheTimeKey) || '0');
        const now = Date.now();
        const cacheValid = cachedIp && (now - cachedIpTime < 300000); // 5 minutes cache
        
        let publicIp = '';
        if (cacheValid) {
            // Use cached IP
            publicIp = cachedIp;
            window.__publicIp = publicIp;
        } else {
            // Fetch new IP with very short timeout for better performance
            const ipPromise = (async () => {
                try {
                    const ipFetch = fetch('https://api.ipify.org?format=json', { 
                        cache: 'no-store',
                        signal: AbortSignal.timeout(200) // Very short timeout: 200ms
                    });
                    const ipResp = await ipFetch;
                    if (ipResp && ipResp.ok) {
                        const ipJson = await ipResp.json();
                        const ip = ipJson?.ip || '';
                        // Cache the IP
                        if (ip) {
                            sessionStorage.setItem(ipCacheKey, ip);
                            sessionStorage.setItem(ipCacheTimeKey, now.toString());
                        }
                        return ip;
                    }
                } catch {}
                return '';
            })();
            
            // Don't wait for IP - get it asynchronously
            ipPromise.then(ip => {
                window.__publicIp = ip;
            });
            // OPTIMIZED: Get IP quickly or use empty string (backend can detect from server IP)
            // Very short wait time for better performance
            publicIp = await Promise.race([
                ipPromise,
                new Promise(resolve => setTimeout(() => resolve(''), 150)) // Reduced to 150ms for faster response
            ]);
            window.__publicIp = publicIp;
        }
        
        // Get location string with reasonable timeout to ensure we get full address
        // User needs to see full address, not just coordinates
        try {
            lokasi = await Promise.race([
                locationPromise,
                new Promise(resolve => setTimeout(() => {
                    // Fallback to coordinates only if timeout (increased timeout for better address retrieval)
                    resolve(`Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
                }, 8000)) // Increased to 8 seconds to allow reverse geocoding to complete
            ]);
            
            // If we got coordinates as fallback, try one more time with longer timeout
            if (lokasi && lokasi.startsWith('Koordinat:')) {
                console.log('First attempt returned coordinates, retrying with longer timeout...');
                try {
                    const retryLokasi = await Promise.race([
                        getStreetNameFromCoordinates(lat, lng),
                        new Promise(resolve => setTimeout(() => {
                            resolve(`Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
                        }, 6000)) // 6 seconds for retry
                    ]);
                    if (retryLokasi && !retryLokasi.startsWith('Koordinat:')) {
                        lokasi = retryLokasi; // Use the address if we got it
                    }
                } catch (retryError) {
                    console.warn('Retry reverse geocoding failed:', retryError);
                }
            }
        } catch (e) {
            // Fallback to coordinates on error
            console.warn('Error getting location string:', e);
            lokasi = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        
        // Ensure lokasi is never empty
        if (!lokasi || lokasi.trim() === '') {
            lokasi = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        
        // Store attendance data for potential WFA retry
        const attendanceData = { 
            nim,
            mode: scanMode,
            ekspresi: topExpression,
            screenshot: screenshot,
            lat: lat ?? '',
            lng: lng ?? '',
            lokasi: lokasi,
            public_ip: publicIp || '' // Use the IP we got (or empty if timeout)
        };
        window.pendingAttendanceData = attendanceData;
        
        // Recheck location function - called when user clicks "Tidak" on location confirmation
        const recheckLocation = async () => {
            return new Promise((resolve) => {
                // Re-fetch GPS location
                if (!navigator.geolocation) {
                    resolve(null);
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        const newLat = pos.coords.latitude;
                        const newLng = pos.coords.longitude;
                        
                        // Validate coordinates
                        if (isNaN(newLat) || isNaN(newLng) || newLat === 0 || newLng === 0) {
                            resolve(null);
                            return;
                        }
                        
                        // Get new location string with enhanced reverse geocoding
                        // Since user clicked "Periksa Ulang", they're willing to wait for accurate address
                        let newLokasi = '';
                        let retryCount = 0;
                        const maxRetries = 3;
                        
                        // Try to get address with retries and longer timeout
                        while (retryCount < maxRetries && (!newLokasi || newLokasi.startsWith('Koordinat:'))) {
                            try {
                                // Use longer timeout for recheck (user is willing to wait)
                                const controller = new AbortController();
                                const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout for recheck
                                
                                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${newLat}&lon=${newLng}&addressdetails=1&accept-language=id&zoom=18`, {
                                    signal: controller.signal
                                });
                                clearTimeout(timeoutId);
                                
                                if (response && response.ok) {
                                    const data = await response.json();
                                    
                                    if (data && data.address) {
                                        const address = data.address;
                                        const parts = [];
                                        
                                        // 1. Building name or house name (most specific)
                                        if (address.building) parts.push(address.building);
                                        else if (address.house_name) parts.push(address.house_name);
                                        
                                        // 2. Road/Street with house number if available
                                        const roadParts = [];
                                        if (address.house_number) roadParts.push(address.house_number);
                                        if (address.road) roadParts.push(address.road);
                                        else if (address.pedestrian) roadParts.push(address.pedestrian);
                                        else if (address.footway) roadParts.push(address.footway);
                                        if (roadParts.length > 0) {
                                            parts.push('Jl. ' + roadParts.join(' '));
                                        }
                                        
                                        // 3. Suburb/Neighbourhood
                                        if (address.suburb) parts.push(address.suburb);
                                        else if (address.neighbourhood) parts.push(address.neighbourhood);
                                        
                                        // 4. City/Town/Village
                                        if (address.city) parts.push(address.city);
                                        else if (address.town) parts.push(address.town);
                                        else if (address.village) parts.push(address.village);
                                        
                                        // 5. State/Province
                                        if (address.state) parts.push(address.state);
                                        
                                        // 6. Postal code
                                        if (address.postcode) parts.push(address.postcode);
                                        
                                        if (parts.length > 0) {
                                            newLokasi = parts.join(', ');
                                            break; // Success, exit retry loop
                                        }
                                        
                                        // Fallback to display_name
                                        if (data.display_name) {
                                            let cleanName = data.display_name.replace(/, Indonesia$/, '');
                                            if (address.postcode) {
                                                cleanName += ', ' + address.postcode;
                                            }
                                            newLokasi = cleanName;
                                            break; // Success, exit retry loop
                                        }
                                    }
                                    
                                    // If address parsing failed but display_name exists, use it
                                    if (data && data.display_name && !newLokasi) {
                                        newLokasi = data.display_name.replace(/, Indonesia$/, '');
                                        break; // Success, exit retry loop
                                    }
                                }
                            } catch (e) {
                                console.warn(`Reverse geocoding attempt ${retryCount + 1} failed:`, e);
                                retryCount++;
                                if (retryCount < maxRetries) {
                                    // Wait a bit before retry
                                    await new Promise(resolve => setTimeout(resolve, 1000));
                                }
                            }
                        }
                        
                        // If still no address after retries, use coordinates as last resort
                        if (!newLokasi || newLokasi.startsWith('Koordinat:')) {
                            newLokasi = `Koordinat: ${newLat.toFixed(6)}, ${newLng.toFixed(6)}`;
                        }
                        
                        // Update attendance data with new location
                        attendanceData.lat = newLat;
                        attendanceData.lng = newLng;
                        attendanceData.lokasi = newLokasi;
                        window.pendingAttendanceData = attendanceData;
                        
                        resolve({ lokasi: newLokasi, lat: newLat, lng: newLng });
                    },
                    (err) => {
                        console.warn('Geolocation recheck error:', err);
                        resolve(null);
                    },
                    {
                        enableHighAccuracy: true, // Use high accuracy for recheck
                        timeout: 6000, // Longer timeout for recheck
                        maximumAge: 0 // Force fresh location
                    }
                );
            });
        };
        
        // Show location confirmation modal before submitting - with recheck capability
        const locationResult = await showLocationConfirmation(lokasi, lat, lng, recheckLocation);
        if (!locationResult || !locationResult.confirmed) {
            // User cancelled
            isProcessingRecognition = false;
            return;
        }
        
        // Update with confirmed location (may have been rechecked)
        if (locationResult.lokasi && locationResult.lat && locationResult.lng) {
            lat = locationResult.lat;
            lng = locationResult.lng;
            lokasi = locationResult.lokasi;
            attendanceData.lat = lat;
            attendanceData.lng = lng;
            attendanceData.lokasi = lokasi;
            window.pendingAttendanceData = attendanceData;
        }
        
        // FAST: Submit after confirmation - location is guaranteed to be set
        let r = await submitAttendance();
        if(!r.ok && r.need_overtime_reason){
            // Show Overtime modal
            showOvertimeModal(r.message || 'Presensi di hari libur/weekend dianggap overtime. Harap isi alasan dan lokasi overtime.');
            isProcessingRecognition = false;
            return; // Exit early, Overtime modal will handle retry
        }
        if(!r.ok && r.need_reason){
            // Show WFA modal using new system
            showWFAModal(r.message || 'Di luar wilayah kantor. Harap isi alasan kerja di luar (WFA).');
            isProcessingRecognition = false;
            return; // Exit early, WFA modal will handle retry
        }
        if(!r.ok && r.need_early_leave_reason){
            // Show Early Leave modal
            showEarlyLeaveModal(r.message || 'Anda pulang sebelum jam yang ditentukan. Harap isi alasan pulang awal.');
            isProcessingRecognition = false;
            return; // Exit early, Early Leave modal will handle retry
        }
        // ULTRA-FAST: Skip logging for maximum speed
        
        // Auto stop detection after attendance submission (success or failed)
        isPresensiSuccess = true;
        isDetectionStopped = true;
        stopDetection();
        
        // Ubah tombol stop menjadi start
        const btnStop = qs('#btn-stop-detection');
        const btnStart = qs('#btn-start-detection');
        
        if (btnStop) btnStop.classList.add('hidden');
        if (btnStart) {
            btnStart.classList.remove('hidden');
            // Remove existing listeners and add new one
            const newBtnStart = btnStart.cloneNode(true);
            btnStart.parentNode.replaceChild(newBtnStart, btnStart);
            newBtnStart.addEventListener('click', () => {
                isPresensiSuccess = false;
                isDetectionStopped = false; // Reset stop flag
                processedLabels.delete(nim);
                startVideo();
                startVideoInterval();
                newBtnStart.classList.add('hidden');
                if (btnStop) btnStop.classList.remove('hidden');
            });
        }
        
        if(r.ok){
            statusMessage(r.message, r.statusClass || 'bg-green-100 text-green-700');
            // Update log after successful attendance
            updateLogAfterAttendance(nim, scanMode);
            // Tandai label sudah diproses agar tidak dobel
            processedLabels.set(nim, Date.now());
        } else {
            // Check if error is about WiFi requirement - show WFA modal
            const msg = (r.message || '').toLowerCase();
            if (msg.includes('wifi telkom university') || (msg.includes('wifi') && msg.includes('harus'))) {
                // Show WFA modal for WiFi-related errors
                showWFAModal(r.message || 'Untuk presensi WFO, Anda harus terhubung ke WiFi Telkom University. Silakan hubungkan ke WiFi Telkom University atau gunakan presensi WFA dengan alasan.');
                isProcessingRecognition = false;
                return; // Exit early, WFA modal will handle retry
            }
            
            statusMessage(r.message || 'Gagal menyimpan presensi', r.statusClass || 'bg-yellow-100 text-yellow-700');
            // Jika sudah presensi sebelumnya, hentikan deteksi dan berikan notifikasi jelas
            if (msg.includes('sudah presensi')) {
                processedLabels.set(nim, Date.now());
            }
        }
    }catch(err){
        console.error('Error in handleRecognition:', err);
        let errorMessage = 'Terjadi kesalahan server';
        if (err.message.includes('invalid JSON')) {
            errorMessage = 'Server mengalami masalah teknis. Silakan coba lagi.';
        } else if (err.message.includes('HTTP error')) {
            errorMessage = 'Koneksi ke server bermasalah. Silakan coba lagi.';
        } else if (err.message.includes('Data yang dikirim tidak valid')) {
            errorMessage = 'Data yang dikirim tidak valid. Silakan coba lagi.';
        } else if (err.message.includes('Server error')) {
            errorMessage = 'Server error. Silakan coba lagi.';
        } else if (err.message.includes('Presensi masuk hanya tersedia') || err.message.includes('Presensi masuk tersedia')) {
            errorMessage = 'Waktu presensi tidak sesuai. Silakan coba pada jam yang tepat.';
        } else if (err.message.includes('Waktu presensi tidak sesuai')) {
            errorMessage = 'Waktu presensi tidak sesuai. Silakan coba pada jam yang tepat.';
        } else if (err.message.includes('NIM tidak ditemukan')) {
            errorMessage = 'NIM tidak ditemukan. Silakan hubungi administrator.';
        } else if (err.message.includes('Database error')) {
            errorMessage = 'Database error. Silakan hubungi administrator.';
        } else if (err.message.includes('Screenshot tidak berhasil diambil')) {
            errorMessage = 'Screenshot tidak berhasil diambil. Silakan coba lagi dengan posisi yang lebih baik.';
        } else if (err.message.includes('Ukuran screenshot terlalu besar')) {
            errorMessage = 'Ukuran screenshot terlalu besar. Silakan coba lagi.';
        } else if (err.message.includes('Database structure error')) {
            errorMessage = 'Database structure error. Silakan hubungi administrator.';
        } else if (err.message.includes('Bad request')) {
            errorMessage = 'Bad request. Silakan coba lagi.';
        } else if (err.message.includes('Unauthorized')) {
            errorMessage = 'Unauthorized. Silakan login kembali.';
        } else if (err.message.includes('Forbidden')) {
            errorMessage = 'Forbidden. Silakan hubungi administrator.';
        } else if (err.message.includes('Tidak dapat terhubung ke server')) {
            errorMessage = 'Tidak dapat terhubung ke server. Pastikan XAMPP sudah berjalan.';
        } else if (err.message.includes('Server tidak merespons')) {
            errorMessage = 'Server tidak merespons. Silakan coba lagi.';
        } else if (err.message.includes('Network error')) {
            errorMessage = 'Network error. Silakan coba lagi.';
        } else if (err.message.includes('Connection refused')) {
            errorMessage = 'Connection refused. Silakan coba lagi.';
        }
        statusMessage(errorMessage, 'bg-red-100 text-red-700');
    } finally {
        // INSTANT: Immediate reset for maximum speed
        isProcessingRecognition = false;
    }
}

function stopVideoAfterRecognition(){
    if(videoInterval) {
        clearInterval(videoInterval);
        videoInterval = null;
    }
    // INSTANT: Much faster reset for better user experience
    let delayDuration = 3000; // Reduced from 10000 to 3000
    if (presensiStatus && presensiStatus.textContent) {
        const currentText = presensiStatus.textContent;
        const wordCount = currentText.split(' ').length;
        delayDuration = Math.max(2000, wordCount * 200 + 1000); // Much faster calculation
    }
    setTimeout(()=>{
        if(isCameraActive) resetPresensiPage();
    }, delayDuration);
}

// Function to reset recognition system for multi-person support
function resetRecognitionSystem() {
    // Clear detection history
    detectionHistory = [];
    
    // Clear recognition queue
    recognitionQueue = [];
    
    // Reset processing flags
    isProcessingRecognition = false;
    isProcessingQueue = false;
    recognitionCompleted = false;
    
    // Reset last successful detection
    lastSuccessfulDetection = null;
    
    console.log('Recognition system reset for multi-person support');
}

// Function to manually stop detection (for admin use)
function stopDetection() {
    isDetectionStopped = true; // Set flag to stop detection
    if(videoInterval) {
        clearInterval(videoInterval);
        videoInterval = null;
    }
    resetRecognitionSystem();
    console.log('Face detection stopped manually');
}

// Load daily report statistics for landing page
async function loadLandingDailyReportStats() {
    try {
        console.log('Fetching dashboard data for landing page...');
        const landingStatsDiv = document.getElementById('landing-daily-report-stats');
        
        // Check if section exists first
        if (!landingStatsDiv) {
            console.warn('Landing stats section not found in DOM - section may not be rendered');
            return;
        }
        
        // Show section immediately
        landingStatsDiv.style.display = 'block';
        
        const result = await api('?ajax=get_public_daily_report_stats', {}, { suppressModal: true, cache: true, ttl: 30000 });
        
        console.log('Public daily report stats response:', result);
        
        if (result.ok && result.data) {
            const stats = result.data;
            console.log('Daily report stats:', stats);
            
            // Show section if it exists
            if (landingStatsDiv) {
                landingStatsDiv.style.display = 'block';
                console.log('Landing stats section found and shown');
            } else {
                console.error('Landing stats section not found in DOM');
            }
            
            const employeeListContainer = document.getElementById('landing-employees-list-container');
            if (!employeeListContainer) {
                console.error('Employee list container not found');
                return;
            }
            if (stats.employee_details) {
                const employees = stats.employee_details;
                if (employees.length > 0) {
                    employeeListContainer.innerHTML = employees.map((emp, index) => {
                        const badgeClass = emp.missing_count > 0 
                            ? 'bg-gradient-to-r from-orange-500 to-amber-500' 
                            : 'bg-gradient-to-r from-green-500 to-green-600';
                        const badgeText = emp.missing_count > 0 
                            ? `${emp.missing_count} laporan` 
                            : 'Lengkap';
                        return `
                        <div class="flex items-center justify-between p-2 bg-gradient-to-r from-orange-50 to-amber-50 hover:from-orange-100 hover:to-amber-100 rounded-lg transition-all duration-200 border border-orange-200 hover:border-orange-300 hover:shadow-sm">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <div class="relative flex-shrink-0">
                                    <div id="landing-emp-photo-container-${emp.id}" data-id="${emp.id}" class="lazy-member-photo w-10 h-10 rounded-full border-2 border-orange-400 shadow-sm overflow-hidden bg-gray-200 flex items-center justify-center cursor-pointer">
                                        ${emp.has_foto ? 
                                            `<i class="fi fi-sr-spinner animate-spin text-gray-400 text-[10px]"></i>` : 
                                            `<i class="fi fi-sr-user text-gray-400 text-xs"></i>`
                                        }
                                    </div>
                                    <div class="absolute -top-1 -right-1 bg-gradient-to-br from-orange-500 to-orange-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-md" style="font-size: 0.65rem;">
                                        ${index + 1}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-900 truncate">${emp.nama}</p>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <span class="${badgeClass} text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                                    ${badgeText}
                                </span>
                            </div>
                        </div>
                    `;
                    }).join('');
                    
                    // Trigger lazy load observer for the new photos
                    setTimeout(() => {
                        employees.forEach(emp => {
                            if (emp.has_foto) {
                                const el = document.getElementById(`landing-emp-photo-container-${emp.id}`);
                                if (el && window.memberPhotoObserver) window.memberPhotoObserver.observe(el);
                                else if (el && !window.memberPhotoObserver && window.lazyLoadMemberPhoto) window.lazyLoadMemberPhoto(emp.id, `landing-emp-photo-container-${emp.id}`);
                            }
                        });
                    }, 100);
                } else {
                    employeeListContainer.innerHTML = `
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">Tidak ada data pegawai</p>
                        </div>
                    `;
                }
            }
        } else {
            console.warn('Daily report stats data not available');
            const employeeListContainer = document.getElementById('landing-employees-list-container');
            if (employeeListContainer) {
                employeeListContainer.innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <p class="text-sm">Tidak ada data yang tersedia</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading landing daily report stats:', error);
        const employeeListContainer = document.getElementById('landing-employees-list-container');
        if (employeeListContainer) {
            employeeListContainer.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Gagal memuat data. Silakan refresh halaman.</p>
                </div>
            `;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Initializing face recognition system...');
    
    // Load daily report statistics for landing page if admin
    // Check if we're on landing page
    const pagePresensi = document.getElementById('page-presensi');
    const isLandingPage = window.location.href.includes('page=landing') || (pagePresensi && pagePresensi.offsetParent !== null);
    const landingStatsDiv = document.getElementById('landing-daily-report-stats');
    
    console.log('DOMContentLoaded - Landing page check:', {
        isLandingPage: isLandingPage,
        hasPagePresensi: !!pagePresensi,
        hasStatsDiv: !!landingStatsDiv,
        url: window.location.href,
        statsDivDisplay: landingStatsDiv ? window.getComputedStyle(landingStatsDiv).display : 'N/A'
    });
    
    // Always try to load if section exists and we're on landing page
    if (isLandingPage) {
        console.log('Landing page detected');
        if (landingStatsDiv) {
            console.log('Stats section found, loading data...');
            // Show section immediately (in case it was hidden) and keep it visible
            landingStatsDiv.style.display = 'block';
            landingStatsDiv.style.visibility = 'visible';
            landingStatsDiv.style.opacity = '1';
            
            // Use MutationObserver to ensure section stays visible
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const currentDisplay = window.getComputedStyle(landingStatsDiv).display;
                        if (currentDisplay === 'none') {
                            console.log('Section was hidden, restoring visibility...');
                            landingStatsDiv.style.display = 'block';
                            landingStatsDiv.style.visibility = 'visible';
                            landingStatsDiv.style.opacity = '1';
                        }
                    }
                });
            });
            observer.observe(landingStatsDiv, { attributes: true, attributeFilter: ['style'] });
            
            // Load immediately
            loadLandingDailyReportStats();
            // Auto-refresh every 30 seconds (only if admin, will stop if 401)
            const refreshInterval = setInterval(() => {
                loadLandingDailyReportStats().catch(() => {
                    // Stop refreshing if consistently failing
                    clearInterval(refreshInterval);
                });
            }, 30000);
        } else {
            console.warn('Stats section not found in DOM - checking if it exists...');
            // Try to find it again after a short delay (in case DOM not fully loaded)
            setTimeout(() => {
                const retryDiv = document.getElementById('landing-daily-report-stats');
                if (retryDiv) {
                    console.log('Stats section found on retry, loading data...');
                    retryDiv.style.display = 'block';
                    loadLandingDailyReportStats();
                    setInterval(loadLandingDailyReportStats, 30000);
                } else {
                    console.error('Stats section still not found after retry');
                }
            }, 500);
        }
    } else {
        console.log('Not on landing page');
    }
    
    initializeSpeechSynthesis();
    initializeFaceRecognition();
    // OPTIMIZED: Lazy load models - only load when user clicks scan button (not on page load)
    // This significantly improves initial page load time, especially on low-end devices
    // Models will be loaded when btnScanMasuk or btnScanPulang is clicked
    
    // INSTANT: Immediate debug info display
    console.log('🔧 Face Recognition Debug Info:');
    console.log(`  - Face Matcher Threshold: ${detectionConfig.faceMatcherThreshold}`);
    console.log(`  - Recognition Threshold: ${detectionConfig.recognitionThreshold}`);
    console.log(`  - Quality Threshold: ${detectionConfig.qualityThreshold}`);
    console.log(`  - Score Threshold: ${detectionConfig.scoreThreshold}`);
    console.log(`  - Input Size: ${detectionConfig.inputSize}`);
    console.log(`  - Min Face Size: ${detectionConfig.minFaceSize}`);
    // Reset log data daily
    checkAndResetLogDaily();
});

// Load log presensi masuk
async function loadLogMasuk() {
    try {
        const result = await api('?ajax=get_today_attendance', { type: 'masuk' }, { suppressModal: true });
        console.log('Log masuk response:', result);
        
        if (result.ok) {
            logMasukData = result.data || [];
            console.log('Log masuk data:', logMasukData);
            renderLogMasuk();
        } else {
            console.error('API Error:', result.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading log masuk:', error);
    }
}

// Load log presensi pulang
async function loadLogPulang() {
    try {
        const result = await api('?ajax=get_today_attendance', { type: 'pulang' }, { suppressModal: true });
        console.log('Log pulang response:', result);
        
        if (result.ok) {
            logPulangData = result.data || [];
            console.log('Log pulang data:', logPulangData);
            renderLogPulang();
        } else {
            console.error('API Error:', result.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading log pulang:', error);
    }
}

// Render log presensi masuk
function renderLogMasuk() {
    const body = qs('#log-masuk-body');
    if (!body) return;
    
    console.log('Rendering log masuk with data:', logMasukData);
    
    body.innerHTML = '';
    if (logMasukData.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Belum ada presensi masuk hari ini</td></tr>';
        return;
    }
    
    logMasukData.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';
        
        const screenshot = item.has_sm ? 
            `<div class="text-center"><button type="button" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider hover:bg-blue-200 transition-colors" onclick="loadAndShowEvidence('${item.id}', 'masuk', 'Bukti Masuk')">Lihat Foto</button></div>` :
            '<span class="text-gray-400">-</span>';
        
        const jamMasuk = item.jam_masuk ? item.jam_masuk.substring(0, 5) : '-';
        const tanggal = item.jam_masuk_iso ? new Date(item.jam_masuk_iso).toLocaleDateString('id-ID') : '-';
        const lokasi = item.lokasi_masuk || '-';
        
        tr.innerHTML = `
            <td class="py-2 px-4 text-center">${index + 1}</td>
            <td class="py-2 px-4 text-center">${tanggal}</td>
            <td class="py-2 px-4">${item.nama || '-'}</td>
            <td class="py-2 px-4 text-center">${item.startup || '-'}</td>
            <td class="py-2 px-4 text-center">${jamMasuk}</td>
            <td class="py-2 px-4">${lokasi}</td>
            <td class="py-2 px-4 text-center">${screenshot}</td>
        `;
        body.appendChild(tr);
    });
}

// Render log presensi pulang
function renderLogPulang() {
    const body = qs('#log-pulang-body');
    if (!body) return;
    
    console.log('Rendering log pulang with data:', logPulangData);
    
    body.innerHTML = '';
    if (logPulangData.length === 0) {
        body.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Belum ada presensi pulang hari ini</td></tr>';
        return;
    }
    
    logPulangData.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';
        
        const screenshot = item.has_sp ? 
            `<div class="text-center"><button type="button" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider hover:bg-blue-200 transition-colors" onclick="loadAndShowEvidence('${item.id}', 'pulang', 'Bukti Pulang')">Lihat Foto</button></div>` :
            '<span class="text-gray-400">-</span>';
        
        const jamPulang = item.jam_pulang ? item.jam_pulang.substring(0, 5) : '-';
        const tanggal = item.jam_pulang_iso ? new Date(item.jam_pulang_iso).toLocaleDateString('id-ID') : '-';
        const lokasi = item.lokasi_pulang || '-';
        
        tr.innerHTML = `
            <td class="py-2 px-4 text-center">${index + 1}</td>
            <td class="py-2 px-4 text-center">${tanggal}</td>
            <td class="py-2 px-4">${item.nama || '-'}</td>
            <td class="py-2 px-4 text-center">${item.startup || '-'}</td>
            <td class="py-2 px-4 text-center">${jamPulang}</td>
            <td class="py-2 px-4">${lokasi}</td>
            <td class="py-2 px-4 text-center">${screenshot}</td>
        `;
        body.appendChild(tr);
    });
}

// Update log after successful attendance
function updateLogAfterAttendance(nim, mode) {
    // INSTANT: Immediate update for maximum speed
    if (mode === 'masuk') {
        loadLogMasuk();
    } else {
        loadLogPulang();
    }
}

// Check and reset log daily
function checkAndResetLogDaily() {
    const today = new Date().toDateString();
    const lastReset = localStorage.getItem('lastLogReset');
    
    if (lastReset !== today) {
        logMasukData = [];
        logPulangData = [];
        localStorage.setItem('lastLogReset', today);
    }
}

<?php else: ?>
// App (logged in)
document.addEventListener('click', (e) => {
    const target = e.target;
    
    // Refresh Handler
    const refreshBtn = target.closest('#refresh-kpi');
    if (refreshBtn) {
        const loading = qs('#kpi-loading');
        const empty = qs('#kpi-empty');
        if (loading) loading.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');
        loadKPIData();
        return;
    }
    
    // Export Handler
    const exportBtn = target.closest('#btn-export-kpi');
    if (exportBtn) {
        e.preventDefault();
        
        try {
            const fType = document.getElementById('kpi-filter-type');
            const fMonth = document.getElementById('kpi-filter-month');
            const fYear = document.getElementById('kpi-filter-year');
            
            const filterType = fType ? fType.value : 'period';
            const params = new URLSearchParams();
            params.append('filter_type', filterType);
            
            if (filterType === 'monthly' && fMonth && fYear) {
                params.append('month', fMonth.value);
                params.append('year', fYear.value);
            }
            
            const exportUrl = `/export/kpi?${params.toString()}`;
            window.location.href = exportUrl;
            
        } catch (err) {
            console.error('Export error:', err);
        }
    }
});
const pages = { rekap: qs('#page-rekap'), 'laporan-bulanan': qs('#page-laporan-bulanan'), members: qs('#page-members'), laporan: qs('#page-laporan'), 'admin-monthly': qs('#page-admin-monthly'), dashboard: qs('#page-dashboard'), settings: qs('#page-settings'), 'help-requests': qs('#tab-help-requests') };
qsa('.tab-link').forEach(btn=>{
    btn.addEventListener('click', ()=> showPage(btn.dataset.tab));
});

// Mobile sidebar tab links
qsa('.mobile-tab-link').forEach(btn=>{
    btn.addEventListener('click', ()=> {
        showPage(btn.dataset.tab);
        closeMobileSidebar(); // Close sidebar after clicking
    });
});

// Mobile sidebar functions
function openMobileSidebar() {
    const sidebar = qs('#mobile-sidebar');
    const overlay = qs('#mobile-sidebar-overlay');
    if (sidebar) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    }
    if (overlay) {
        overlay.classList.remove('hidden');
    }
    // Prevent body scroll when sidebar is open
    document.body.style.overflow = 'hidden';
}

function closeMobileSidebar() {
    const sidebar = qs('#mobile-sidebar');
    const overlay = qs('#mobile-sidebar-overlay');
    if (sidebar) {
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
    }
    if (overlay) {
        overlay.classList.add('hidden');
    }
    // Restore body scroll
    document.body.style.overflow = '';
}

// Auto clear cache when website is opened
(function() {
    'use strict';
    // Clear all caches
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
    // Clear service worker cache if exists
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        });
    }
    // Force reload if page is cached
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        window.location.reload();
    }
})();

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = qs('#mobile-menu-toggle');
    const sidebarClose = qs('#mobile-sidebar-close');
    const overlay = qs('#mobile-sidebar-overlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', openMobileSidebar);
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeMobileSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
    
    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });
});

function showPage(name, pushState = true){ 
    const isAlreadyActive = pages[name] && pages[name].style.display === 'block';

    Object.values(pages).forEach(p=> p && (p.style.display='none')); 
    
    if(pages[name]) {
        pages[name].style.display='block'; 
    } else {
        // Fallback to dashboard if page not found
        if(pages['dashboard']) pages['dashboard'].style.display='block';
        name = 'dashboard';
    }
    
    // Update active state for desktop tabs
    qsa('.tab-link').forEach(btn => {
        if (btn.dataset.tab === name) {
            btn.classList.add('active-tab');
            btn.classList.remove('text-gray-600', 'hover:bg-indigo-50', 'hover:text-indigo-600');
            // Ensure icons and spans inherit white from the CSS reset I added
        } else {
            btn.classList.remove('active-tab');
            btn.classList.add('text-gray-600', 'hover:bg-indigo-50', 'hover:text-indigo-600');
        }
    });
    
    // Update active state for mobile tabs
    qsa('.mobile-tab-link').forEach(btn => {
        if (btn.dataset.tab === name) {
            btn.classList.add('bg-indigo-600', 'text-white');
            btn.classList.remove('text-gray-700', 'hover:bg-indigo-50', 'hover:text-indigo-600');
        } else {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-gray-700', 'hover:bg-indigo-50', 'hover:text-indigo-600');
        }
    });

    // Update URL parameter without reload
    if (pushState) {
        const url = new URL(window.location.href);
        const currentPage = url.searchParams.get('page');
        
        // Only push state if the page param is different or doesn't exist for private pages
        // index.php defaults private pages to admin/pegawai based on auth, but we want the specific tab
        if (currentPage !== name) {
            url.searchParams.set('page', name);
            history.pushState({ tab: name }, "", url);
        }
    }
    
    if(name==='members') renderMembers(); 
    if(name==='laporan') { loadStartupOptions(); renderLaporan(); } 
    if(name==='rekap' && !isAlreadyActive) initRekapPage(); 
    if(name==='laporan-bulanan') renderMonthly(); 
    if(name==='admin-monthly') renderAdminMonthly(); 
    if(name==='dashboard') renderDashboard(); 
    if(name==='help-requests') loadAllHelpRequests();
    if(name==='settings' && !isAlreadyActive) { renderSettings(); initAddressSearch(); if(typeof loadBackupFiles === 'function') loadBackupFiles(); } 
}

// Handle Browser Back/Forward buttons
window.addEventListener('popstate', (e) => {
    const url = new URL(window.location.href);
    const pageParam = url.searchParams.get('page');
    
    if (pageParam && pages[pageParam]) {
        showPage(pageParam, false);
    } else if (e.state && e.state.tab && pages[e.state.tab]) {
        showPage(e.state.tab, false);
    }
});

// Ensure initial page sets based on URL or defaults
document.addEventListener('DOMContentLoaded', () => {
    const url = new URL(window.location.href);
    const pageParam = url.searchParams.get('page');
    
    // Standalone pages that are NOT part of the main SPA dashboard tabs
    const standalonePages = ['login', 'register', 'presensi-masuk', 'presensi-pulang', 'forgot-password', 'verify-otp', 'reset-password'];
    if (pageParam && standalonePages.includes(pageParam)) {
        console.log('Standalone page detected, skipping SPA routing: ' + pageParam);
        return; 
    }

    if (pageParam && pages[pageParam]) {
        showPage(pageParam, false);
    } else {
        <?php if (isAdmin()): ?>
        if (pages['dashboard']) showPage('dashboard', false);
        <?php else: ?>
        if (pages['rekap']) showPage('rekap', false);
        <?php endif; ?>
    }
});

// Header buttons for employees - navigate to landing page presensi with return parameter
document.addEventListener('DOMContentLoaded', () => {
    const btnHeaderMasuk = qs('#btn-header-presensi-masuk');
    const btnHeaderPulang = qs('#btn-header-presensi-pulang');
    
    if (btnHeaderMasuk) {
        btnHeaderMasuk.addEventListener('click', () => {
            window.location.href = '?page=landing&return=app&mode=masuk';
        });
    }
    
    if (btnHeaderPulang) {
        btnHeaderPulang.addEventListener('click', () => {
            window.location.href = '?page=landing&return=app&mode=pulang';
        });
    }

    // Initialize month/year selectors for rekap page
    const monthSel = qs('#rekap-month');
    const yearSel = qs('#rekap-year');
    
    if (monthSel) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        months.forEach((month, index) => {
            const option = document.createElement('option');
            option.value = String(index + 1);
            option.textContent = month;
            if (index === new Date().getMonth()) {
                option.selected = true;
            }
            monthSel.appendChild(option);
        });
    }
    
    if (yearSel) {
        const currentYear = new Date().getFullYear();
        for (let year = currentYear - 2; year <= currentYear + 1; year++) {
            const option = document.createElement('option');
            option.value = String(year);
            option.textContent = String(year);
            if (year === currentYear) {
                option.selected = true;
            }
            yearSel.appendChild(option);
        }
    }
    
    // Initialize rekap page only if on the rekap page and allowed
    const isPublicPage = ['presensi-masuk', 'presensi-pulang', 'landing'].includes(new URLSearchParams(window.location.search).get('page'));
    
    if (qs('#page-rekap') && !isPublicPage) {
        // Safe check for function existence
        if (typeof initRekapPage === 'function') {
            initRekapPage();
        }
    }
});

// Presensi page for logged-in employees
let presensiVideo = null;
let presensiCanvas = null;
let presensiIsCameraActive = false;
let presensiVideoInterval = null;
let presensiScanMode = '';
let presensiProcessedLabels = new Map();
let presensiIsProcessingRecognition = false;
let presensiLabeledFaceDescriptors = [];
let presensiIsPresensiSuccess = false;

function initPresensiPage() {
    presensiVideo = qs('#video-presensi');
    presensiCanvas = qs('#canvas-presensi');
    
    // Reset state
    presensiIsCameraActive = false;
    presensiVideoInterval = null;
    presensiScanMode = '';
    presensiProcessedLabels = new Map();
    presensiIsProcessingRecognition = false;
    presensiIsPresensiSuccess = false;
    
    // Hide video container initially
    const videoContainer = qs('#video-container-presensi');
    const statusDiv = qs('#presensi-status-presensi');
    const btnBack = qs('#btn-back-presensi');
    const btnStop = qs('#btn-stop-detection-presensi');
    const btnStart = qs('#btn-start-detection-presensi');
    
    if (videoContainer) videoContainer.classList.add('hidden');
    if (statusDiv) statusDiv.classList.add('hidden');
    if (btnBack) btnBack.classList.add('hidden');
    if (btnStop) btnStop.classList.add('hidden');
    if (btnStart) btnStart.classList.add('hidden');
    
    // Button handlers
    const btnMasuk = qs('#btn-presensi-masuk');
    const btnPulang = qs('#btn-presensi-pulang');
    
    if (btnMasuk) {
        btnMasuk.onclick = () => startPresensi('masuk');
    }
    if (btnPulang) {
        btnPulang.onclick = () => startPresensi('pulang');
    }
    if (btnBack) {
        btnBack.onclick = () => {
            stopPresensiCamera();
            videoContainer.classList.add('hidden');
            btnBack.classList.add('hidden');
            btnStop.classList.add('hidden');
            btnStart.classList.add('hidden');
            if (statusDiv) {
                statusDiv.classList.add('hidden');
                statusDiv.textContent = '';
            }
            // Return to employee presensi page (show the buttons again)
            // The page-presensi is already visible, we just need to ensure buttons are visible
            // The buttons are always visible when video container is hidden
        };
    }
    if (btnStop) {
        btnStop.onclick = () => {
            stopPresensiCamera();
            btnStop.classList.add('hidden');
            btnStart.classList.remove('hidden');
        };
    }
    if (btnStart) {
        btnStart.onclick = () => {
            if (!presensiScanMode) return;
            startPresensiCamera();
            btnStart.classList.add('hidden');
            btnStop.classList.remove('hidden');
        };
    }
}

async function startPresensi(mode) {
    presensiScanMode = mode;
    presensiIsPresensiSuccess = false;
    
    // Force request camera and location permissions BEFORE starting
    try {
        // Request camera permission explicitly
        const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
        // Stop it immediately - we just want to trigger the permission request
        cameraStream.getTracks().forEach(track => track.stop());
        
        // Request location permission explicitly  
        if (!navigator.geolocation) {
            showModalNotif('GPS tidak tersedia di perangkat Anda. Pastikan GPS aktif.', false, 'Izin Lokasi');
            return;
        }
        
        // Request location permission by trying to get position
        await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                () => resolve(true),
                (err) => {
                    if (err.code === err.PERMISSION_DENIED) {
                        showModalNotif('Izin lokasi diperlukan untuk presensi. Silakan aktifkan izin lokasi di pengaturan browser.', false, 'Izin Lokasi');
                        reject(new Error('Location permission denied'));
                    } else {
                        // Other errors are okay (timeout, etc) - we'll retry later
                        resolve(true);
                    }
                },
                { timeout: 5000, enableHighAccuracy: true }
            );
        });
    } catch (error) {
        if (error.name === 'NotAllowedError' || error.message === 'Location permission denied') {
            // Permission denied - user needs to enable it
            return; // Don't proceed
        } else if (error.name === 'NotFoundError') {
            showModalNotif('Kamera tidak ditemukan. Pastikan kamera terhubung.', false, 'Kamera Tidak Tersedia');
            return;
        } else {
            // Other errors - might be timeout, we'll proceed anyway
            console.warn('Permission check warning:', error);
        }
    }
    
    // Show video container
    const videoContainer = qs('#video-container-presensi');
    const btnBack = qs('#btn-back-presensi');
    const btnStop = qs('#btn-stop-detection-presensi');
    const btnStart = qs('#btn-start-detection-presensi');
    
    if (videoContainer) {
        videoContainer.classList.remove('hidden');
    }
    if (btnBack) btnBack.classList.remove('hidden');
    if (btnStop) btnStop.classList.remove('hidden');
    if (btnStart) btnStart.classList.add('hidden');
    
    // Load face recognition models and start camera
    await loadPresensiFaceModels();
    startPresensiCamera();
}

async function loadPresensiFaceModels() {
    if (window.faceApiModelsLoaded) return;
    
    const MODEL_PATH = window.FACEAPI_MODEL_URL || 'assets/face-models';
    
    try {
        console.log('🚀 Pre-warming: loading face models...');
        // Parallel load all 3 models
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_PATH),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_PATH),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_PATH)
        ]);
        window.faceApiModelsLoaded = true;
        console.log('✅ Face models loaded. Now loading member embeddings...');
        
        // Load face descriptors from database
        const res = await fetch('?ajax=get_members');
        const j = await res.json();
        const memberData = j.data || []; // Use memberData NOT members to avoid shadowing global!
        
        // Populate the GLOBAL members array for name lookup in attendance.js
        if (typeof members !== 'undefined') {
            members = memberData;
        }

        presensiLabeledFaceDescriptors = [];
        labeledFaceDescriptors = []; // ALSO fill attendance.js's descriptor array
        
        for (const m of memberData) {
            try {
                const nim = m.nim || m[3] || '';
                const nama = m.nama || m[4] || '';
                const embeddingStr = m.face_embedding || m[8] || null;
                const foto = m.foto_base64 || m[7] || null;
                const label = String(nim || nama || m.id || m[0] || '');

                if (embeddingStr) {
                    const desc = new Float32Array(JSON.parse(embeddingStr));
                    const ld = new faceapi.LabeledFaceDescriptors(label, [desc]);
                    presensiLabeledFaceDescriptors.push(ld);
                    labeledFaceDescriptors.push(ld); // Mirror to attendance.js global
                } else if (foto) {
                    const img = await faceapi.fetchImage(foto);
                    const detection = await faceapi.detectSingleFace(img,
                        new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.3 })
                    ).withFaceLandmarks().withFaceDescriptor();
                    if (detection) {
                        const ld = new faceapi.LabeledFaceDescriptors(label, [detection.descriptor]);
                        presensiLabeledFaceDescriptors.push(ld);
                        labeledFaceDescriptors.push(ld);
                    }
                }
            } catch (err) {
                console.warn('Error processing member for face sync:', err);
            }
        }
        console.log(`✅ Pre-warm done: ${labeledFaceDescriptors.length} face descriptors ready.`);
    } catch (error) {
        console.error('Error loading face models:', error);
    }
}
window.loadPresensiFaceModels = loadPresensiFaceModels;

function startPresensiCamera() {
    if (presensiIsCameraActive) return;
    
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            presensiVideo.srcObject = stream;
            presensiIsCameraActive = true;
            
            presensiVideo.addEventListener('loadedmetadata', () => {
                presensiCanvas.width = presensiVideo.videoWidth;
                presensiCanvas.height = presensiVideo.videoHeight;
                startPresensiDetection();
            });
        })
        .catch(err => {
            console.error('Error accessing camera:', err);
            showModalNotif('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.', false, 'Error Kamera');
        });
}

function stopPresensiCamera() {
    if (presensiVideo && presensiVideo.srcObject) {
        presensiVideo.srcObject.getTracks().forEach(track => track.stop());
        presensiVideo.srcObject = null;
    }
    presensiIsCameraActive = false;
    if (presensiVideoInterval) {
        clearInterval(presensiVideoInterval);
        presensiVideoInterval = null;
    }
}

function startPresensiDetection() {
    if (!presensiIsCameraActive || presensiIsPresensiSuccess) return;
    if (presensiVideoInterval) clearInterval(presensiVideoInterval);
    
    presensiVideoInterval = setInterval(async () => {
        if (presensiIsPresensiSuccess || presensiIsProcessingRecognition) return;
        
        try {
            const detections = await faceapi
                .detectAllFaces(presensiVideo, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors();
            
            if (detections.length === 0 || presensiLabeledFaceDescriptors.length === 0) {
                const ctx = presensiCanvas.getContext('2d');
                ctx.clearRect(0, 0, presensiCanvas.width, presensiCanvas.height);
                return;
            }
            
            // Use adjusted threshold based on device type (more lenient for mobile)
            const adjustedThreshold = getAdjustedFaceMatcherThreshold();
            const faceMatcher = new faceapi.FaceMatcher(presensiLabeledFaceDescriptors, adjustedThreshold);
            const resizedDetections = faceapi.resizeResults(detections, {
                width: presensiVideo.videoWidth,
                height: presensiVideo.videoHeight
            });
            
            const ctx = presensiCanvas.getContext('2d');
            ctx.clearRect(0, 0, presensiCanvas.width, presensiCanvas.height);
            
            resizedDetections.forEach(detection => {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                
                if (bestMatch.label !== 'unknown' && bestMatch.distance < 0.4) {
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#00ff00';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    ctx.fillStyle = '#00ff00';
                    ctx.font = '16px Arial';
                    ctx.fillText(bestMatch.label, box.x, box.y - 5);
                    
                    // Process recognition
                    if (!presensiProcessedLabels.has(bestMatch.label)) {
                        processPresensiRecognition(bestMatch.label);
                    }
                }
            });
        } catch (error) {
            console.error('Detection error:', error);
        }
    }, 100);
}

async function processPresensiRecognition(nim) {
    if (presensiIsProcessingRecognition || presensiIsPresensiSuccess) return;
    if (presensiProcessedLabels.has(nim)) return;
    
    presensiIsProcessingRecognition = true;
    presensiProcessedLabels.set(nim, Date.now());
    
    try {
        // Get GPS location with better error handling
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    if (pos.coords.accuracy <= 50) {
                        resolve(pos);
                    } else {
                        // GPS accuracy accepted regardless of value
                        resolve(pos);
                    }
                },
                (error) => {
                    // Check permission state before rejecting
                    if (navigator.permissions) {
                        navigator.permissions.query({ name: 'geolocation' }).then(result => {
                            if (result.state === 'denied') {
                                reject(new Error('Izin lokasi ditolak'));
                            } else {
                                reject(error);
                            }
                        }).catch(() => reject(error));
                    } else {
                        reject(error);
                    }
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
        
        // Take screenshot
        const screenshot = await new Promise((resolve) => {
            try {
                const tmp = document.createElement('canvas');
                tmp.width = 240;
                tmp.height = 240;
                const tctx = tmp.getContext('2d');
                tctx.drawImage(presensiVideo, 0, 0, tmp.width, tmp.height);
                resolve(tmp.toDataURL('image/jpeg', 0.5));
            } catch (e) {
                resolve(null);
            }
        });
        
        // Submit attendance
        const data = {
            nim: nim,
            mode: presensiScanMode,
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            gps_accuracy: position.coords.accuracy,
            screenshot: screenshot
        };
        
        const response = await api('?ajax=save_attendance', data, { suppressModal: true });
        
        if (response.ok) {
            presensiIsPresensiSuccess = true;
            stopPresensiCamera();
            
            const btnStop = qs('#btn-stop-detection-presensi');
            const btnStart = qs('#btn-start-detection-presensi');
            
            if (btnStop) {
                btnStop.classList.add('hidden');
            }
            if (btnStart) {
                btnStart.classList.remove('hidden');
                // Remove existing listeners and add new one
                const newBtnStart = btnStart.cloneNode(true);
                btnStart.parentNode.replaceChild(newBtnStart, btnStart);
                newBtnStart.addEventListener('click', () => {
                    presensiIsPresensiSuccess = false;
                    presensiProcessedLabels.delete(nim);
                    startPresensiCamera();
                    newBtnStart.classList.add('hidden');
                    if (btnStop) btnStop.classList.remove('hidden');
                });
            }
            
            const statusDiv = qs('#presensi-status-presensi');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-green-100 text-green-700';
                statusDiv.textContent = response.message || 'Presensi berhasil!';
            }
        } else {
            const statusDiv = qs('#presensi-status-presensi');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-red-100 text-red-700';
                statusDiv.textContent = response.message || 'Presensi gagal. Silakan coba lagi.';
            }
            presensiProcessedLabels.delete(nim);
        }
    } catch (error) {
        console.error('Presensi error:', error);
        const statusDiv = qs('#presensi-status-presensi');
        if (statusDiv) {
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-red-100 text-red-700';
            let errorMsg = 'Presensi gagal. Silakan coba lagi.';
            
            if (error.message.includes('Izin lokasi ditolak')) {
                errorMsg = 'Izin lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.';
            } else if (error.message.includes('GPS accuracy') || error.message.includes('GPS')) {
                // Check if permission is granted but GPS accuracy is low
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        if (result.state === 'granted') {
                            statusDiv.textContent = errorMsg;
                            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                        } else {
                            statusDiv.textContent = errorMsg;
                        }
                    }).catch(() => {
                        statusDiv.textContent = errorMsg;
                    });
                } else {
                    statusDiv.textContent = errorMsg;
                    statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                }
            } else if (error.message.includes('timeout')) {
                // Check if permission is granted before showing timeout error
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        if (result.state === 'granted') {
                            statusDiv.textContent = 'Mendapatkan lokasi memakan waktu lama. Pastikan GPS aktif dan berada di area terbuka.';
                            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                        } else {
                            statusDiv.textContent = 'Izin lokasi diperlukan. Silakan aktifkan izin lokasi.';
                        }
                    }).catch(() => {
                        statusDiv.textContent = errorMsg;
                    });
                } else {
                    statusDiv.textContent = errorMsg;
                }
            } else {
                statusDiv.textContent = errorMsg;
            }
        }
        presensiProcessedLabels.delete(nim);
    } finally {
        presensiIsProcessingRecognition = false;
    }
}

// Face recognition functions are handled in the landing page section
// The logged-in app focuses on admin/employee dashboard functionality

// Members (Admin)
async function renderMembers(){
    const j = await api('?ajax=get_members&light=1&no_embeddings=1', {}, { suppressModal: true, cache: true });
    const members = (j.data||[]);
    const term = (qs('#search-member')?.value||'').toLowerCase();
    const filtered = members.filter(m=> (m.nama||'').toLowerCase().includes(term) || (m.nim||'').toLowerCase().includes(term));
    const body = qs('#table-members-body'); if(!body) return; body.innerHTML='';
    if(filtered.length===0){ body.innerHTML = `<tr><td colspan="7" class="text-center py-4">Tidak ada data member.</td></tr>`; return; }
    filtered.forEach(m=>{
        const tr = document.createElement('tr'); tr.className='border-b hover:bg-gray-50';
        tr.innerHTML = `
            <td class="py-2 px-4">
                ${m.has_foto ? 
                    `<div id="member-photo-container-${m.id}" data-id="${m.id}" class="lazy-member-photo h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200 cursor-pointer" title="Klik untuk memperbesar">
                        <i class="fi fi-sr-spinner animate-spin text-gray-400 text-[10px]"></i>
                    </div>` : 
                    `<div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-[10px] border border-gray-200 leading-tight text-center">No Pic</div>`
                }
            </td>
            <td class="py-2 px-4">${m.nim||''}</td>
            <td class="py-2 px-4">${m.nama||''}</td>
            <td class="py-2 px-4">${m.prodi||''}</td>
            <td class="py-2 px-4">${m.startup||'-'}</td>
            <td class="py-2 px-4 text-center">
                <button class="btn-ga-qr bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition" data-id="${m.id}" data-email="${m.email || ''}" title="Lihat QR Code Google Authenticator">
                    <i class="fi fi-sr-qr-code mr-1"></i>QR Code
                </button>
            </td>
            <td class="py-2 px-4 text-center">
                <button class="btn-edit-member text-yellow-600 font-bold" data-id="${m.id}" data-json='${JSON.stringify(m).replace(/'/g,"&apos;")}' title="Edit"><i class="fi fi-sr-pen-square"></i></button>
                <button class="btn-work-schedule text-green-600 font-bold ml-2" data-id="${m.id}" data-name="${m.nama}" title="Kelola Jadwal Kerja"><i class="fi fi-sr-calendar"></i></button>
                <button class="btn-delete-member text-red-600 font-bold ml-2" data-id="${m.id}" title="Hapus"><i class="fi fi-ss-trash"></i></button>
            </td>`;
        body.appendChild(tr);
        if (m.has_foto) {
            const el = qs(`#member-photo-container-${m.id}`);
            if (el && window.memberPhotoObserver) window.memberPhotoObserver.observe(el);
            else if (el && !window.memberPhotoObserver && window.lazyLoadMemberPhoto) window.lazyLoadMemberPhoto(m.id, `member-photo-container-${m.id}`);
        }
    });
}

// End of member photo setup

qs('#search-member') && qs('#search-member').addEventListener('input', renderMembers);

const memberModal = qs('#member-modal');
const btnAddMember = qs('#btn-add-member');
const btnCancelModal = qs('#btn-cancel-modal');
const memberForm = qs('#member-form');

const modalVideoContainer = qs('#modal-video-container');
const modalVideo = qs('#modal-video');
const modalCanvas = qs('#modal-canvas');
const btnStartCamera = qs('#btn-start-camera');
const btnTakePhoto = qs('#btn-take-photo');
const btnUploadPhoto = qs('#btn-upload-photo');
const photoFileInput = qs('#photo-file-input');
const fotoPreview = qs('#foto-preview');
const fotoDataUrlInput = qs('#foto-data-url');
let modalStream = null;

function resetModalCamera(){ stopModalCamera(); modalVideoContainer.classList.add('hidden'); btnTakePhoto.classList.add('hidden'); btnStartCamera.classList.remove('hidden'); btnStartCamera.textContent='Buka Kamera untuk Foto'; fotoPreview.classList.add('hidden'); fotoDataUrlInput.value=''; }
function stopModalCamera(){ if(modalStream){ modalStream.getTracks().forEach(t=>t.stop()); modalStream=null; } }

btnStartCamera && btnStartCamera.addEventListener('click', async ()=>{
    try{ modalStream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } }); modalVideo.srcObject = modalStream; modalVideoContainer.classList.remove('hidden'); btnTakePhoto.classList.remove('hidden'); btnStartCamera.classList.add('hidden'); fotoPreview.classList.add('hidden'); }catch(err){ showNotif('Tidak bisa mengakses kamera.'); console.error(err); }
});

btnTakePhoto && btnTakePhoto.addEventListener('click', ()=>{
    const ctx = modalCanvas.getContext('2d'); modalCanvas.width = modalVideo.videoWidth; modalCanvas.height = modalVideo.videoHeight; ctx.drawImage(modalVideo,0,0,modalCanvas.width,modalCanvas.height);
    const dataUrl = modalCanvas.toDataURL('image/jpeg'); fotoPreview.src = dataUrl; fotoDataUrlInput.value = dataUrl; fotoPreview.classList.remove('hidden'); stopModalCamera(); modalVideoContainer.classList.add('hidden'); btnTakePhoto.classList.add('hidden'); btnStartCamera.classList.remove('hidden'); btnStartCamera.textContent='Ambil Ulang Foto';
});

btnUploadPhoto && btnUploadPhoto.addEventListener('click', ()=>{
    photoFileInput.click();
});

photoFileInput && photoFileInput.addEventListener('change', (e)=>{
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            fotoPreview.src = dataUrl;
            fotoDataUrlInput.value = dataUrl;
            fotoPreview.classList.remove('hidden');
            stopModalCamera();
            modalVideoContainer.classList.add('hidden');
            btnTakePhoto.classList.add('hidden');
            btnStartCamera.classList.remove('hidden');
            btnStartCamera.textContent='Ambil Ulang Foto';
        };
        reader.readAsDataURL(file);
    }
});

btnAddMember && btnAddMember.addEventListener('click', ()=>{
    memberForm.reset(); qs('#modal-title').textContent='Tambah Member Baru'; qs('#member-id').value=''; qs('#nim').readOnly=false; resetModalCamera(); btnStartCamera.textContent='Buka Kamera untuk Foto'; memberModal.classList.remove('hidden'); qs('#password-admin-wrapper').classList.remove('hidden');
});

btnCancelModal && btnCancelModal.addEventListener('click', ()=>{ stopModalCamera(); memberModal.classList.add('hidden'); });

// QR Code Modal
const gaQrModal = qs('#ga-qr-modal');
const btnCloseGaQr = qs('#btn-close-ga-qr');
if(btnCloseGaQr && gaQrModal){
    btnCloseGaQr.addEventListener('click', ()=>{
        gaQrModal.classList.add('hidden');
    });
    // Close modal when clicking outside
    gaQrModal.addEventListener('click', (e)=>{
        if(e.target === gaQrModal){
            gaQrModal.classList.add('hidden');
        }
    });
}

document.addEventListener('click', async (e)=>{
    const btnEdit = e.target.closest('.btn-edit-member');
    const btnDelete = e.target.closest('.btn-delete-member');
    const btnWorkSchedule = e.target.closest('.btn-work-schedule');
    const btnGaQr = e.target.closest('.btn-ga-qr');
    const btnViewDr = e.target.closest('.btn-view-dr-admin');
    const btnEditAtt = e.target.closest('.btn-edit-att');
    const btnDeleteLaporan = e.target.closest('.btn-delete-laporan');
    const btnViewMonth = e.target.closest('.btn-view-month');
    const btnAmApprove = e.target.closest('.btn-am-approve');
    const btnAmDisapprove = e.target.closest('.btn-am-disapprove');
    const btnViewMonthDetail = e.target.closest('.btn-view-month-detail');
    const btnViewKet = e.target.closest('.btn-view-ket');
    
    if(btnGaQr){
        const userId = btnGaQr.getAttribute('data-id');
        const email = btnGaQr.getAttribute('data-email');
        const qrModal = qs('#ga-qr-modal');
        const qrImage = qs('#ga-qr-image');
        const qrEmail = qs('#ga-qr-email');
        
        qrModal.classList.remove('hidden');
        qrEmail.textContent = 'Email: ' + email;
        qrImage.src = '';
        qrImage.alt = 'Loading QR Code...';
        
        try {
            const r = await api('?ajax=get_ga_qr&user_id=' + userId, {});
            if(r.ok && r.qr_url){
                qrImage.src = r.qr_url;
                qrImage.alt = 'QR Code Google Authenticator';
            } else {
                showNotif(r.message || 'Gagal memuat QR code', false);
                qrModal.classList.add('hidden');
            }
        } catch(err) {
            showNotif('Gagal memuat QR code', false);
            qrModal.classList.add('hidden');
        }
    }

    if(btnEdit){
        const data = JSON.parse(btnEdit.getAttribute('data-json').replace(/&apos;/g, "'"));
        resetModalCamera();
        qs('#modal-title').textContent='Edit Member';
        qs('#member-id').value = data.id;
        qs('#email').value = data.email || '';
        qs('#email').readOnly = false;
        qs('#nim').value = data.nim || '';
        qs('#nim').readOnly = true;
        qs('#nama').value = data.nama || '';
        qs('#prodi').value = data.prodi || '';
        qs('#startup').value = data.startup || '';
        fotoPreview.src = data.foto_base64 || '';
        if(data.foto_base64) fotoPreview.classList.remove('hidden');
        btnStartCamera.textContent='Ambil Ulang Foto';
        qs('#password-admin-wrapper').classList.add('hidden');
        memberModal.classList.remove('hidden');
    }

    if(btnDelete){
        const id = btnDelete.getAttribute('data-id');
        showConfirmModal('Apakah Anda yakin ingin menghapus member ini?', async ()=>{
            await api('?ajax=delete_member', { id });
            renderMembers(); 
            if (typeof loadLabeledFaceDescriptors === 'function') {
                loadLabeledFaceDescriptors();
            }
        });
    }

    if(btnWorkSchedule){
        const userId = btnWorkSchedule.getAttribute('data-id');
        const userName = btnWorkSchedule.getAttribute('data-name');
        await openWorkScheduleModal(userId, userName);
    }

    if(btnDeleteLaporan){
        const id = btnDeleteLaporan.getAttribute('data-id');
        showConfirmModal('Apakah Anda yakin ingin menghapus data kehadiran ini?', async ()=>{ await api('?ajax=delete_attendance', { id }); renderLaporan(); });
    }
    
        if(btnEditAtt){
        const att = JSON.parse(btnEditAtt.getAttribute('data-json').replace(/&apos;/g, "'"));
        qs('#edit-att-id').value = att.id;
        qs('#edit-att-user-id').value = att.user_id || '';
        qs('#edit-att-date').value = (att.jam_masuk_iso||att.date||'').slice(0,10);
        qs('#edit-att-nama').value = att.nama || '';
        qs('#edit-att-jam-masuk').value = att.jam_masuk ? att.jam_masuk.substring(0, 5) : '';
        qs('#edit-att-jam-pulang').value = att.jam_pulang ? att.jam_pulang.substring(0, 5) : '';
        qs('#edit-att-ket').value = att.ket || 'hadir';
        qs('#edit-att-status').value = att.status || 'ontime';
        
        // Handle WFA and Overtime fields
        const wfaForm = qs('#edit-att-wfa-form');
        const overtimeForm = qs('#edit-att-overtime-form');
        if (wfaForm) wfaForm.classList.add('hidden');
        if (overtimeForm) overtimeForm.classList.add('hidden');
        
        if (att.ket === 'wfa' && wfaForm) {
            wfaForm.classList.remove('hidden');
            qs('#edit-att-alasan-wfa').value = att.alasan_wfa || '';
        } else if (att.ket === 'overtime' && overtimeForm) {
            overtimeForm.classList.remove('hidden');
            qs('#edit-att-alasan-overtime').value = att.alasan_overtime || '';
            qs('#edit-att-lokasi-overtime').value = att.lokasi_overtime || '';
        }
        
        // Handle existing screenshots (LAZY LOADING)
        qs('#edit-att-screenshot-masuk-preview').classList.add('hidden');
        qs('#edit-att-screenshot-pulang-preview').classList.add('hidden');
        editAttScreenshotMasuk = null;
        editAttScreenshotPulang = null;
        
        if (att.has_sm) {
            api('?ajax=get_attendance_evidence&id=' + att.id + '&type=masuk', {}, { suppressModal: true }).then(r => {
                if (r && r.ok && r.data) {
                    editAttScreenshotMasuk = r.data;
                    qs('#edit-att-screenshot-masuk-data').value = r.data;
                    qs('#edit-att-screenshot-masuk-img').src = r.data;
                    qs('#edit-att-screenshot-masuk-preview').classList.remove('hidden');
                }
            });
        }
        if (att.has_sp) {
            api('?ajax=get_attendance_evidence&id=' + att.id + '&type=pulang', {}, { suppressModal: true }).then(r => {
                if (r && r.ok && r.data) {
                    editAttScreenshotPulang = r.data;
                    qs('#edit-att-screenshot-pulang-data').value = r.data;
                    qs('#edit-att-screenshot-pulang-img').src = r.data;
                    qs('#edit-att-screenshot-pulang-preview').classList.remove('hidden');
                }
            });
        }
        
        editAttModal.classList.remove('hidden');
    }

    if(btnViewDr){
        const userId = btnViewDr.getAttribute('data-user'); const date = btnViewDr.getAttribute('data-date');
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date });
        const modal = qs('#dr-modal'); const content=qs('#dr-content'); const evalEl=qs('#dr-evaluation');
        modal.dataset.reportId = r?.data?.id || '';
        content.textContent = r?.data?.content || '(Belum ada laporan)';
        evalEl.value = r?.data?.evaluation || '';
        modal.classList.remove('hidden');
    }
    
        if(btnViewMonthDetail){
        const id = btnViewMonthDetail.getAttribute('data-id');
        const r = await api('?ajax=get_monthly_report_detail', { id });
        if(!r.ok) { showNotif(r.message || 'Laporan tidak ditemukan', false); return; }
        const item = r.data;
        if(!item) { showNotif('Laporan tidak ditemukan', false); return; }
        
        // Create modal if it doesn't exist
        let modal = qs('#monthly-detail-modal');
        if(!modal) {
            modal = document.createElement('div');
            modal.id = 'monthly-detail-modal';
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="monthly-detail-title" class="text-xl font-bold"></h3>
                        <button onclick="this.closest('#monthly-detail-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ringkasan Pekerjaan:</h4>
                            <div class="bg-gray-50 p-3 rounded border">
                                <p id="monthly-detail-summary" class="text-gray-600 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Pencapaian dan Hasil Kerja:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white bordered">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Pencapaian</th>
                                            <th class="py-2 px-4">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-detail-achievements-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Kendala:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white bordered">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Kendala</th>
                                            <th class="py-2 px-4">Solusi</th>
                                            <th class="py-2 px-4">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-detail-obstacles-table"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        const titleElement = qs('#monthly-detail-title');
        const summaryElement = qs('#monthly-detail-summary');
        
        if (titleElement) {
            titleElement.textContent = `Laporan Bulanan ${item.nama} - ${monthName(parseInt(item.month))} ${item.year}`;
        }
        if (summaryElement) {
            summaryElement.textContent = item.summary || '(Tidak ada ringkasan)';
        }
        
        // Parse achievements properly and fill table
        let achievements = [];
        try {
            achievements = JSON.parse(item.achievements || '[]');
        } catch (e) {
            achievements = [];
        }
        
        const achievementsTable = qs('#monthly-detail-achievements-table');
        if (achievementsTable) {
            if (achievements.length > 0) {
                achievementsTable.innerHTML = achievements.map((a, index) => {
                    const achievement = typeof a === 'object' ? (a.achievement || '') : a;
                    const detail = typeof a === 'object' ? (a.detail || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${achievement}</td>
                            <td class="py-2 px-4">${detail}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                achievementsTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="3" class="py-2 px-4 text-center text-gray-500">Tidak ada data pencapaian</td>
                    </tr>
                `;
            }
        }
        
        // Parse obstacles properly and fill table
        let obstacles = [];
        try {
            obstacles = JSON.parse(item.obstacles || '[]');
        } catch (e) {
            obstacles = [];
        }
        
        const obstaclesTable = qs('#monthly-detail-obstacles-table');
        if (obstaclesTable) {
            if (obstacles.length > 0) {
                obstaclesTable.innerHTML = obstacles.map((o, index) => {
                    const obstacle = typeof o === 'object' ? (o.obstacle || '') : o;
                    const solution = typeof o === 'object' ? (o.solution || '') : '';
                    const note = typeof o === 'object' ? (o.note || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${obstacle}</td>
                            <td class="py-2 px-4">${solution}</td>
                            <td class="py-2 px-4">${note}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                obstaclesTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="4" class="py-2 px-4 text-center text-gray-500">Tidak ada data kendala</td>
                </tr>
            `;
            }
        }
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    // Handle view monthly report for pegawai
    if(btnViewMonth){
        const data = JSON.parse(btnViewMonth.getAttribute('data-json').replace(/&apos;/g, "'"));
        if(!data) { showNotif('Data laporan tidak ditemukan', false); return; }
        
        // Create modal if it doesn't exist
        let modal = qs('#monthly-pegawai-view-modal');
        if(!modal) {
            modal = document.createElement('div');
            modal.id = 'monthly-pegawai-view-modal';
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="monthly-pegawai-view-title" class="text-xl font-bold"></h3>
                        <button onclick="this.closest('#monthly-pegawai-view-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Status Laporan:</h4>
                                <div id="monthly-pegawai-view-status" class="text-sm"></div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Tanggal Dibuat:</h4>
                                <div id="monthly-pegawai-view-created" class="text-sm text-gray-600"></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ringkasan Pekerjaan:</h4>
                            <div class="bg-gray-50 p-3 rounded border">
                                <p id="monthly-pegawai-view-summary" class="text-gray-600 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Pencapaian dan Hasil Kerja:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white bordered">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Pencapaian</th>
                                            <th class="py-2 px-4">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-pegawai-view-achievements-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Kendala:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white bordered">
                                    <thead class="bg-gray-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Kendala</th>
                                            <th class="py-2 px-4">Solusi</th>
                                            <th class="py-2 px-4">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-pegawai-view-obstacles-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button onclick="this.closest('#monthly-pegawai-view-modal').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Tutup</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        const monthName = (m) => ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m-1];
        
        // Fill modal data
        const titleElement = qs('#monthly-pegawai-view-title');
        const statusElement = qs('#monthly-pegawai-view-status');
        const createdElement = qs('#monthly-pegawai-view-created');
        const summaryElement = qs('#monthly-pegawai-view-summary');
        
        if (titleElement) {
            titleElement.textContent = `Laporan Bulanan - ${monthName(parseInt(data.month))} ${data.year}`;
        }
        
        if (statusElement) {
            const statusMap = {
                'draft': '<span class="badge badge-gray">Draft</span>',
                'belum di approve': '<span class="badge badge-blue">Belum di Approve</span>',
                'approved': '<span class="badge badge-green">Di-approve</span>',
                'disapproved': '<span class="badge badge-red">Tidak di-approve</span>'
            };
            statusElement.innerHTML = statusMap[data.status] || '<span class="badge badge-gray">Unknown</span>';
        }
        
        if (createdElement) {
            const createdDate = new Date(data.created_at || data.updated_at);
            createdElement.textContent = createdDate.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        if (summaryElement) {
            summaryElement.textContent = data.summary || '(Tidak ada ringkasan)';
        }
        
        // Parse achievements and fill table
        let achievements = [];
        try {
            achievements = JSON.parse(data.achievements || '[]');
        } catch (e) {
            achievements = [];
        }
        
        const achievementsTable = qs('#monthly-pegawai-view-achievements-table');
        if (achievementsTable) {
            if (achievements.length > 0) {
                achievementsTable.innerHTML = achievements.map((a, index) => {
                    const achievement = typeof a === 'object' ? (a.achievement || '') : a;
                    const detail = typeof a === 'object' ? (a.detail || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${achievement}</td>
                            <td class="py-2 px-4">${detail}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                achievementsTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="3" class="py-2 px-4 text-center text-gray-500">Tidak ada data pencapaian</td>
                    </tr>
                `;
            }
        }
        
        // Parse obstacles and fill table
        let obstacles = [];
        try {
            obstacles = JSON.parse(data.obstacles || '[]');
        } catch (e) {
            obstacles = [];
        }
        
        const obstaclesTable = qs('#monthly-pegawai-view-obstacles-table');
        if (obstaclesTable) {
            if (obstacles.length > 0) {
                obstaclesTable.innerHTML = obstacles.map((o, index) => {
                    const obstacle = typeof o === 'object' ? (o.obstacle || '') : o;
                    const solution = typeof o === 'object' ? (o.solution || '') : '';
                    const note = typeof o === 'object' ? (o.note || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${obstacle}</td>
                            <td class="py-2 px-4">${solution}</td>
                            <td class="py-2 px-4">${note}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                obstaclesTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="4" class="py-2 px-4 text-center text-gray-500">Tidak ada data kendala</td>
                    </tr>
                `;
            }
        }
        
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    if(btnAmApprove){
        const id = btnAmApprove.getAttribute('data-id'); const status = 'approved';
        showConfirmModal('Yakin set status laporan bulanan?', async ()=>{ await api('?ajax=admin_set_monthly_status', { id, status }); renderAdminMonthly(); });
    }

    if(btnAmDisapprove){
        const id = btnAmDisapprove.getAttribute('data-id'); const status = 'disapproved';
        showConfirmModal('Yakin set status laporan bulanan?', async ()=>{ await api('?ajax=admin_set_monthly_status', { id, status }); renderAdminMonthly(); });
    }

    if(btnViewKet){
        const att = JSON.parse(btnViewKet.getAttribute('data-json').replace(/&apos;/g, "'"));
        const modal = qs('#ket-detail-modal');
        const title = qs('#ket-detail-title');
        const content = qs('#ket-detail-content');
        
        title.textContent = `Detail ${att.ket.toUpperCase()} - ${att.nama}`;
        
        if (att.ket === 'wfo' || att.ket === 'wfa') {
            // Show location map for WFO/WFA
            let mapContent = '';
            if (att.lat_masuk && att.lng_masuk && att.lokasi_masuk) {
                mapContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Presensi Masuk:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_masuk}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_masuk}, ${att.lng_masuk}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_masuk},${att.lng_masuk}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.lat_pulang && att.lng_pulang && att.lokasi_pulang) {
                mapContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Presensi Pulang:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_pulang}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_pulang}, ${att.lng_pulang}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_pulang},${att.lng_pulang}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.ket === 'wfa' && att.alasan_wfa) {
                mapContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2 text-indigo-700"><i class="fi fi-rr-comment-info mr-2"></i>Alasan WFA:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">${att.alasan_wfa}</p>
                    </div>
                `;
            }
            if (att.alasan_pulang_awal) {
                mapContent += `
                    <div class="mb-4 mt-4 pt-4 border-t border-gray-100">
                        <h4 class="font-semibold mb-2 text-rose-600"><i class="fi fi-rr-time-past mr-2"></i>Alasan Pulang Lebih Awal:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-rose-50/50 rounded-xl border border-rose-100 italic">"${att.alasan_pulang_awal}"</p>
                    </div>
                `;
            }
            if (att.alasan_lokasi_berbeda) {
                mapContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2 text-orange-600"><i class="fi fi-rr-map-marker-slash mr-2"></i>Alasan Lokasi Pulang Berbeda:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-orange-50/50 rounded-xl border border-orange-100 italic">"${att.alasan_lokasi_berbeda}"</p>
                    </div>
                `;
            }
            content.innerHTML = mapContent || '<p class="text-gray-500">Tidak ada data lokasi</p>';
        } else if (att.ket === 'overtime') {
            // Show location and reason for overtime
            let overtimeContent = '';
            if (att.lat_masuk && att.lng_masuk && att.lokasi_masuk) {
                overtimeContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Overtime:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_overtime || att.lokasi_masuk}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_masuk}, ${att.lng_masuk}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_masuk},${att.lng_masuk}" target="_blank" class="inline-block bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.alasan_overtime) {
                overtimeContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2 text-purple-700"><i class="fi fi-rr-comment-info mr-2"></i>Alasan Overtime:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-purple-50/50 rounded-xl border border-purple-100">${att.alasan_overtime}</p>
                    </div>
                `;
            }
            if (att.alasan_pulang_awal) {
                overtimeContent += `
                    <div class="mb-4 mt-4 pt-4 border-t border-gray-100">
                        <h4 class="font-semibold mb-2 text-rose-600"><i class="fi fi-rr-time-past mr-2"></i>Alasan Pulang Lebih Awal:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-rose-50/50 rounded-xl border border-rose-100 italic">"${att.alasan_pulang_awal}"</p>
                    </div>
                `;
            }
            if (att.alasan_lokasi_berbeda) {
                overtimeContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2 text-orange-600"><i class="fi fi-rr-map-marker-slash mr-2"></i>Alasan Lokasi Pulang Berbeda:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-orange-50/50 rounded-xl border border-orange-100 italic">"${att.alasan_lokasi_berbeda}"</p>
                    </div>
                `;
            }
            content.innerHTML = overtimeContent || '<p class="text-gray-500">Tidak ada data overtime</p>';
        } else if (att.ket === 'izin' || att.ket === 'sakit') {
            // Show proof and reason for izin/sakit
            let proofContent = '';
            if (att.has_bis || att.bukti_izin_sakit) {
                const proofId = att.id;
                const proofType = 'izin_sakit';
                proofContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Bukti ${att.ket.toUpperCase()}:</h4>
                        <div class="flex justify-center" id="lazy-proof-container-${att.id}">
                            ${att.bukti_izin_sakit ? 
                                `<img src="${att.bukti_izin_sakit}" alt="Bukti ${att.ket}" class="max-w-full max-h-96 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">` :
                                `<button type="button" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors" onclick="loadLazyProof('${att.id}', 'izin_sakit', 'lazy-proof-container-${att.id}')">
                                    <i class="fi fi-rr-picture mr-2"></i> Lihat Bukti Gambar
                                </button>`
                            }
                        </div>
                    </div>
                `;
            }
            if (att.alasan_izin_sakit) {
                proofContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2 text-gray-700"><i class="fi fi-rr-comment-info mr-2"></i>Keterangan:</h4>
                        <p class="text-sm text-gray-600 p-4 bg-gray-50 rounded-xl border border-gray-100">${att.alasan_izin_sakit}</p>
                    </div>
                `;
            }
            if (att.alasan_pulang_awal) {
                proofContent += `
                    <div class="mb-4 mt-4 pt-4 border-t border-gray-100">
                        <h4 class="font-semibold mb-2 text-rose-600"><i class="fi fi-rr-time-past mr-2"></i>Alasan Pulang Lebih Awal:</h4>
                        <p class="text-sm text-gray-700 p-4 bg-rose-50/50 rounded-xl border border-rose-100 italic">"${att.alasan_pulang_awal}"</p>
                    </div>
                `;
            }
            content.innerHTML = proofContent || '<p class="text-gray-500">Tidak ada bukti</p>';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
});

memberForm && memberForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = qs('#member-id').value;
    const payload = {
        id,
        email: qs('#email').value,
        nim: qs('#nim').value,
        nama: qs('#nama').value,
        prodi: qs('#prodi').value,
        startup: qs('#startup').value,
        foto: fotoDataUrlInput.value,
    };
    if(!id){ payload.password = qs('#password-new').value; const confirm = qs('#password-confirm').value; if(!payload.password || payload.password!==confirm){ showNotif('Password admin untuk member baru wajib dan harus cocok'); return; } }
    const r = await api('?ajax=save_member', payload);
    if(r.ok){ 
        renderMembers(); 
        if (typeof loadLabeledFaceDescriptors === 'function') {
            loadLabeledFaceDescriptors(); 
        }
        stopModalCamera(); 
        memberModal.classList.add('hidden'); 
    } else { 
        showNotif(r.message||'Gagal menyimpan'); 
    }
});

// Load startup options for filter
async function loadStartupOptions() {
    const filterStartup = qs('#filter-startup');
    if (filterStartup && filterStartup.options.length <= 1) {
        const res = await fetch('?ajax=get_startups');
        const j = await res.json();
        if (j.ok && j.data) {
            j.data.forEach(startup => {
                const o = document.createElement('option');
                o.value = startup;
                o.textContent = startup;
                filterStartup.appendChild(o);
            });
        }
    }
}

<!-- SPBW_SYSTEM_FIX_MARKER: POLICY_SYNC_V3 -->
// Laporan
async function renderLaporan(){
    const j = await api('?ajax=get_attendance', {}, { suppressModal: true, cache: true });
    const list = (j.data||[]);
    const term = (qs('#search-laporan')?.value||'').toLowerCase();
    const startupFilter = qs('#filter-startup')?.value || '';
    const tglMulai = qs('#filter-tanggal-mulai')?.value || '';
    const tglSelesai = qs('#filter-tanggal-selesai')?.value || '';
    const sortBy = qs('#sort-presensi')?.value || 'tanggal-desc';
    
    // NEW: Get new filter values
    const statusFilter = qs('#filter-status')?.value || '';
    const ketFilter = qs('#filter-ket')?.value || '';
    const laporanFilter = qs('#filter-laporan')?.value || '';
    
    // NEW: Check if showing today only (using 5 AM reset)
    const btnToggleToday = qs('#btn-toggle-today');
    const showTodayOnly = btnToggleToday && btnToggleToday.textContent.includes('Hari Ini');
    
    // Calculate "today" with 5 AM reset (not midnight)
    const now = new Date();
    const currentHour = now.getHours();
    let todayDate;
    if (currentHour < 5) {
        // Before 5 AM = still yesterday
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        todayDate = yesterday.toISOString().slice(0, 10);
    } else {
        todayDate = now.toISOString().slice(0, 10);
    }
    
    const filtered = list.filter(a=>{
        const nameMatch = (a.nama||'').toLowerCase().includes(term);
        const nimMatch = (a.nim||'').toLowerCase().includes(term);
        const startupMatch = !startupFilter || (a.startup||'') === startupFilter;
        const recordDate = a.jam_masuk_iso ? a.jam_masuk_iso.slice(0,10) : '';
        const dateMatch = (!tglMulai || recordDate>=tglMulai) && (!tglSelesai || recordDate<=tglSelesai);
        
        // NEW: Today filter (5 AM reset)
        const todayMatch = !showTodayOnly || recordDate === todayDate;
        
        // NEW: Status filter
        const statusMatch = !statusFilter || (a.status||'').toLowerCase() === statusFilter.toLowerCase();
        
        // NEW: Ket filter
        const ketMatch = !ketFilter || (a.ket||'').toLowerCase() === ketFilter.toLowerCase();
        
        // NEW: Laporan filter
        let laporanMatch = true;
        if (laporanFilter === 'belum-ada') {
            laporanMatch = !a.daily_report_status || a.daily_report_status === '';
        } else if (laporanFilter === 'pending') {
            laporanMatch = a.daily_report_status === 'pending' || a.daily_report_status === 'disapproved';
        } else if (laporanFilter === 'approved') {
            laporanMatch = a.daily_report_status === 'approved';
        }
        
        return (nameMatch||nimMatch) && startupMatch && dateMatch && todayMatch && statusMatch && ketMatch && laporanMatch;
    });
    
    // Sorting
    filtered.sort((a,b) => {
        switch(sortBy) {
            case 'tanggal-asc':
                return new Date(a.jam_masuk_iso||0) - new Date(b.jam_masuk_iso||0);
            case 'tanggal-desc':
                return new Date(b.jam_masuk_iso||0) - new Date(a.jam_masuk_iso||0);
            case 'jam-masuk-asc':
                return (a.jam_masuk||'').localeCompare(b.jam_masuk||'');
            case 'jam-masuk-desc':
                return (b.jam_masuk||'').localeCompare(a.jam_masuk||'');
            case 'nama-asc':
                return (a.nama||'').localeCompare(b.nama||'');
            case 'nama-desc':
                return (b.nama||'').localeCompare(a.nama||'');
            default:
                return new Date(b.jam_masuk_iso||0) - new Date(a.jam_masuk_iso||0);
        }
    });
    
        const body = qs('#table-laporan-body'); if(!body) return; body.innerHTML='';
    if(filtered.length===0){ body.innerHTML = `<tr><td colspan="12" class="text-center py-4">Tidak ada data kehadiran.</td></tr>`; return; }
    filtered.forEach(att=>{
        const d = new Date(att.jam_masuk_iso);
        const tanggal = isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', { year:'numeric', month:'long', day:'numeric'});
        const statusClass = att.status === 'terlambat' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
        const statusText = att.status === 'terlambat' ? 'Terlambat' : 'On Time';

        let dailyReportStatus = 'Belum ada laporan';
        let dailyReportClass = 'badge-orange'; // Changed from badge-gray to badge-orange for "Belum ada laporan"
        if(att.daily_report_status) {
            dailyReportStatus = att.daily_report_status === 'approved' ? 'Sudah di-approve' : (att.daily_report_status === 'disapproved' ? 'Tidak di-approve' : 'Belum di-approve');
            dailyReportClass = att.daily_report_status === 'approved' ? 'badge-green' : (att.daily_report_status === 'disapproved' ? 'badge-red' : 'badge-blue');
        }

        const tr = document.createElement('tr'); tr.className='border-b hover:bg-gray-50';
        
        // Format jam untuk tampilan (hanya jam:menit)
        const formatTime = (timeStr) => {
            if (!timeStr || timeStr === '-') return '-';
            if (timeStr === 'izin' || timeStr === 'sakit' || timeStr === 'wfa') return timeStr;
            // Extract only HH:MM from HH:MM:SS
            return timeStr.substring(0, 5);
        };
        
        const jamMasuk = formatTime(att.jam_masuk);
        const jamPulang = formatTime(att.jam_pulang);
        
        // NEW: Robust Bukti Display Logic (10-Day Policy)
        const createBuktiDisplay = (attId, hasLandmarkFlag, landmarkData, fotoData, ekspresi, mode, attKet, dateIso, timeValue) => {
            // 0. If no time recorded (e.g. not yet clocked out), show a simple strip
            if (!timeValue || timeValue === '-') {
                return '<div class="text-center text-gray-400">-</div>';
            }
            
            const isExpired = !isWithin10WorkingDays(dateIso);
            const label = translateExpression(ekspresi || 'neutral');
            
            // DEBUG
            if (attId === 2451 || attId === 2447) {
                console.log(`[BUKTI] ID ${attId}, expired: ${isExpired}, fotoData type: ${typeof fotoData}, has value: ${!!fotoData}, first 30 chars: ${fotoData ? fotoData.substring(0, 30) : 'NULL'}`);
            }

            // 1. Policy: If expired (>10 working days), show Expired button with expression
            if (isExpired) {
                return `<div class="text-center">
                    <button type="button" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-500 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase transition-all shadow-sm border border-gray-200 cursor-pointer"
                        onclick="showExpiredModal()"
                        title="Foto sudah dihapus (>10 hari kerja)">
                        ${label}
                    </button>
                </div>`;
            }

            // 2. Izin/Sakit: show special marker
            if (attKet === 'izin' || attKet === 'sakit') {
                return `<div class="text-center text-gray-400 text-xs italic bg-gray-50 py-1 rounded-lg border border-dashed border-gray-200">Izin/Sakit</div>`;
            }

            // 3. Within 10 days: Show Screenshot Thumbnail (Prioritize existing photo data)
            // Check: fotoData must be a non-empty string
            if (fotoData && typeof fotoData === 'string' && fotoData.length > 10) {
                let imgSrc;
                if (fotoData.startsWith('data:image/')) {
                    imgSrc = fotoData;
                } else if (fotoData.startsWith('public/')) {
                    imgSrc = '/' + fotoData.substring(7);
                } else if (fotoData.startsWith('storage/')) {
                    imgSrc = '/' + fotoData;
                } else if (fotoData.startsWith('attendance/')) {
                    imgSrc = '/storage/' + fotoData;
                } else {
                    // Assume it's a filename in attendance folder
                    const cleanFoto = fotoData.trim();
                    if (cleanFoto === '' || cleanFoto === 'attendance/') {
                        return `<div class="text-center text-gray-400">-</div>`;
                    }
                    imgSrc = '/storage/attendance/' + cleanFoto;
                }
                
                if (attId === 2451 || attId === 2447) {
                    console.log(`[BUKTI-IMG] ID ${attId}, mode: ${mode}, imgSrc starts with: ${imgSrc.substring(0, 50)}`);
                }
                
                return `<div class="flex justify-center">
                    <img src="${imgSrc}" 
                        class="w-12 h-10 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:scale-110 transition-transform" 
                        onclick="showScreenshotModal('${imgSrc}', 'Bukti ${mode === 'masuk' ? 'Masuk' : 'Pulang'}')"
                        onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Err&background=fee2e2&color=ef4444';">
                </div>`;
            }

            // 4. Has landmark but no photo
            if (hasLandmarkFlag && landmarkData) {
                const containerId = `proof-${mode}-${attId}`;
                const type = mode === 'masuk' ? 'masuk' : 'pulang';
                const html = `<div id="${containerId}" data-id="${attId}" data-type="${type}" class="lazy-evidence text-center w-12 h-10 mx-auto bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden cursor-pointer border border-gray-200 shadow-sm" title="Klik untuk memperbesar">
                    <i class="fi fi-rr-spinner animate-spin text-gray-400 text-[10px]"></i>
                </div>`;
                
                setTimeout(() => {
                    const el = document.getElementById(containerId);
                    if (el) {
                        if (window.evidenceObserver) window.evidenceObserver.observe(el);
                        else if (window.loadLazyProof) window.loadLazyProof(attId, type, containerId);
                    }
                }, 100);
                
                return html;
            }

            // 5. No photo/landmark - show expression as clickable badge
            return `<div class="flex justify-center">
                <button type="button" 
                    class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-2 py-1 rounded-lg text-[10px] font-semibold uppercase transition-all shadow-sm border border-blue-200 cursor-pointer"
                    onclick="${isExpired ? 'showExpiredModal()' : ''}">
                    ${label}
                </button>
            </div>`;
        };
        
        const buktiMasuk  = createBuktiDisplay(att.id, att.has_sm, att.landmark_masuk, (att.foto_masuk || att.screenshot_masuk), att.ekspresi_masuk, 'masuk', att.ket, att.jam_masuk_iso, jamMasuk);
        const buktiPulang = createBuktiDisplay(att.id, att.has_sp, att.landmark_pulang, (att.foto_pulang || att.screenshot_pulang), att.ekspresi_pulang, 'pulang', att.ket, att.jam_pulang_iso || att.jam_masuk_iso, jamPulang);
        
        // Ket button logic with oval styling and colors
        let ketButton = '';
        if (att.ket && (att.ket === 'wfo' || att.ket === 'wfa' || att.ket === 'izin' || att.ket === 'sakit' || att.ket === 'overtime')) {
            const ketColors = {
                'wfo': 'bg-green-500 hover:bg-green-600 text-white',
                'wfa': 'bg-amber-400 hover:bg-amber-500 text-white', 
                'izin': 'bg-yellow-500 hover:bg-yellow-600 text-white',
                'sakit': 'bg-yellow-500 hover:bg-yellow-600 text-white',
                'overtime': 'bg-orange-500 hover:bg-orange-600 text-white'
            };
            const colorClass = ketColors[att.ket] || 'bg-gray-500 hover:bg-gray-600 text-white';
            ketButton = `<button class="btn-view-ket ${colorClass} px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200" data-json='${JSON.stringify(att).replace(/'/g,"&apos;")}' title="Lihat Detail ${att.ket.toUpperCase()}">${att.ket.toUpperCase()}</button>`;
        } else {
            ketButton = '<span class="text-gray-400">-</span>';
        }

        tr.innerHTML = `
            <td class="py-2 px-4">${tanggal}</td>
            <td class="py-2 px-4">${att.nim||''}</td>
            <td class="py-2 px-4">${att.nama||''}</td>
            <td class="py-2 px-4">${att.startup||'-'}</td>
            <td class="py-2 px-4">${jamMasuk}</td>
            <td class="py-2 px-4">${buktiMasuk}</td>
            <td class="py-2 px-4"><span class="badge ${statusClass}">${statusText}</span></td>
            <td class="py-2 px-4">${ketButton}</td>
            <td class="py-2 px-4">${jamPulang}</td>
            <td class="py-2 px-4">${buktiPulang}</td>
            <td class="py-2 px-4"><span class="badge ${dailyReportClass}">${dailyReportStatus}</span></td>
            <td class="py-2 px-4">
                <button title="Lihat Laporan" class="btn-view-dr-admin text-blue-600 font-bold" data-user="${att.user_id}" data-date="${(att.jam_masuk_iso||'').slice(0,10)}"><i class="fi fi-ss-eye"></i></button>
                <button title="Edit" class="btn-edit-att text-yellow-600 font-bold ml-1" data-json='${JSON.stringify(att).replace(/'/g,"&apos;")}'><i class="fi fi-sr-pen-square"></i></button>
                <button title="Hapus" class="btn-delete-laporan text-red-600 font-bold ml-1" data-id="${att.id}"><i class="fi fi-ss-trash"></i></button>
            </td>`;
        body.appendChild(tr);

        // Render landmark canvases
        if (att.landmark_masuk) {
            const cMasuk = document.getElementById(`lm-thumb-${att.id}-masuk`);
            if (cMasuk) {
                renderLandmarkOnCanvas(cMasuk, att.landmark_masuk, 80, 60);
                cMasuk._lmData = att.landmark_masuk;
            }
        }
        if (att.landmark_pulang) {
            const cPulang = document.getElementById(`lm-thumb-${att.id}-pulang`);
            if (cPulang) {
                renderLandmarkOnCanvas(cPulang, att.landmark_pulang, 80, 60);
                cPulang._lmData = att.landmark_pulang;
            }
        }
    });
}

[qs('#search-laporan'), qs('#filter-startup'), qs('#filter-tanggal-mulai'), qs('#filter-tanggal-selesai'), qs('#sort-presensi'), qs('#filter-status'), qs('#filter-ket'), qs('#filter-laporan')].forEach(el=>{ if(el) el.addEventListener('input', renderLaporan); });

// NEW: Toggle today/all button
qs('#btn-toggle-today') && qs('#btn-toggle-today').addEventListener('click', function() {
    const btn = this;
    if (btn.textContent.includes('Hari Ini')) {
        btn.textContent = '📊 Lihat Semua';
        btn.classList.remove('bg-indigo-500', 'hover:bg-indigo-600');
        btn.classList.add('bg-purple-500', 'hover:bg-purple-600');
    } else {
        btn.textContent = '📅 Hari Ini';
        btn.classList.remove('bg-purple-500', 'hover:bg-purple-600');
        btn.classList.add('bg-indigo-500', 'hover:bg-indigo-600');
    }
    renderLaporan();
});


qs('#btn-show-all') && qs('#btn-show-all').addEventListener('click', ()=>{
    if(qs('#search-laporan')) qs('#search-laporan').value = '';
    if(qs('#filter-startup')) qs('#filter-startup').value = '';
    if(qs('#filter-tanggal-mulai')) qs('#filter-tanggal-mulai').value = '';
    if(qs('#filter-tanggal-selesai')) qs('#filter-tanggal-selesai').value = '';
    if(qs('#sort-presensi')) qs('#sort-presensi').value = 'tanggal-desc';
    renderLaporan();
});

// Absence modal handlers
let selectedUsers = new Set();
let allMembers = [];

function renderSelectedUsers() {
    const container = qs('#abs-selected-container');
    const list = qs('#abs-items-list');
    const configSection = qs('#abs-config-section');
    const countBadge = qs('#abs-count-badge');
    
    if (!container || !list) return;

    container.innerHTML = '';
    list.innerHTML = '';
    
    if (selectedUsers.size === 0) {
        container.innerHTML = '<p class="text-xs text-gray-400 italic w-full text-center py-2">Belum ada pegawai yang dipilih</p>';
        if (configSection) configSection.classList.add('hidden');
        if (countBadge) countBadge.classList.add('hidden');
        return;
    }
    
    if (configSection) configSection.classList.remove('hidden');
    if (countBadge) {
        countBadge.textContent = `${selectedUsers.size} pegawai dipilih`;
        countBadge.classList.remove('hidden');
    }

    const isGlobalAuto = qs('#abs-auto-time') ? qs('#abs-auto-time').checked : true;

    selectedUsers.forEach(userId => {
        const member = allMembers.find(m => m.id == userId);
        if (member) {
            const chip = document.createElement('div');
            chip.className = 'bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1 border border-indigo-100 animate-fade-in-up';
            chip.innerHTML = `
                <span>${member.nama}</span>
                <button type="button" class="abs-remove-user hover:text-red-500 transition-colors" data-id="${userId}">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            `;
            container.appendChild(chip);

            const row = document.createElement('div');
            row.className = 'abs-user-item bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all space-y-3';
            row.dataset.userId = userId;
            row.innerHTML = `
                <div class="flex items-center justify-between border-b border-gray-50 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs uppercase">${member.nama.charAt(0)}</div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 leading-none">${member.nama}</p>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mt-1">${member.nim}</p>
                        </div>
                    </div>
                    <select class="item-type text-[10px] font-bold uppercase px-2 py-1 bg-gray-100 border-none rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="wfo">WFO</option>
                        <option value="wfa">WFA</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="overtime">Overtime</option>
                    </select>
                </div>

                <div class="item-time-toggle flex items-center gap-2 px-1">
                    <input type="checkbox" class="item-auto-time rounded w-3 h-3 text-indigo-600" ${isGlobalAuto ? 'checked' : ''}>
                    <span class="text-[9px] font-bold text-gray-500 uppercase">Set Jam Otomatis (Default)</span>
                </div>
                
                <div class="item-times grid grid-cols-2 gap-3 hidden">
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Jam Masuk</label>
                        <input type="time" class="item-jam-masuk w-full px-3 py-1.5 border border-gray-200 rounded-lg text-xs" value="08:00">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Jam Pulang</label>
                        <input type="time" class="item-jam-pulang w-full px-3 py-1.5 border border-gray-200 rounded-lg text-xs" value="17:00">
                    </div>
                </div>

                <div class="item-extra space-y-2">
                    <div>
                        <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1 item-reason-label">Keterangan</label>
                        <textarea class="item-reason w-full px-3 py-2 border border-gray-200 rounded-lg text-xs resize-none" rows="1" placeholder="Opsional..."></textarea>
                    </div>
                    <div class="item-location-wrapper hidden">
                        <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Lokasi Overtime</label>
                        <input type="text" class="item-location w-full px-3 py-1.5 border border-gray-200 rounded-lg text-xs" placeholder="Lokasi...">
                    </div>
                </div>
            `;
            
            const typeSelect = row.querySelector('.item-type');
            const autoTimeCheck = row.querySelector('.item-auto-time');
            const timeFields = row.querySelector('.item-times');
            const timeToggle = row.querySelector('.item-time-toggle');
            
            const updateUI = () => {
                const type = typeSelect.value;
                const isAuto = autoTimeCheck.checked;
                const reasonLabel = row.querySelector('.item-reason-label');
                const locationWrapper = row.querySelector('.item-location-wrapper');

                // Time fields logic
                if (type === 'izin' || type === 'sakit') {
                    timeToggle.classList.add('hidden');
                    timeFields.classList.add('hidden');
                } else {
                    timeToggle.classList.remove('hidden');
                    if (isAuto) {
                        timeFields.classList.add('hidden');
                    } else {
                        timeFields.classList.remove('hidden');
                    }
                }
                
                if (type === 'overtime') {
                    locationWrapper.classList.remove('hidden');
                    reasonLabel.textContent = 'Alasan Overtime';
                } else if (type === 'wfa') {
                    locationWrapper.classList.add('hidden');
                    reasonLabel.textContent = 'Alasan WFA';
                } else if (type === 'wfo') {
                    locationWrapper.classList.add('hidden');
                    reasonLabel.textContent = 'Keterangan WFO';
                } else {
                    locationWrapper.classList.add('hidden');
                    reasonLabel.textContent = 'Alasan Izin/Sakit';
                }
            };
            
            typeSelect.onchange = updateUI;
            autoTimeCheck.onchange = updateUI;
            updateUI();
            list.appendChild(row);
        }
    });
}

document.addEventListener('click', (e) => {
    if (e.target.closest('.abs-remove-user')) {
        const id = e.target.closest('.abs-remove-user').dataset.id;
        selectedUsers.delete(id);
        renderSelectedUsers();
        if (qs('#abs-select-all')) qs('#abs-select-all').checked = false;
    }
});

qs('#btn-open-absence') && qs('#btn-open-absence').addEventListener('click', async ()=>{
    const modal = qs('#absence-modal');
    const search = qs('#abs-search');
    const results = qs('#abs-search-results');
    
    selectedUsers.clear();
    renderSelectedUsers();
    if (qs('#abs-select-all')) qs('#abs-select-all').checked = false;
    if (search) {
        search.value = '';
        if (results) results.classList.add('hidden');
    }

    const r = await fetch('?ajax=get_members&light=1&no_embeddings=1'); const j = await r.json(); 
    allMembers = (j.data||[]);
    
    const fill = (term='')=>{ 
        if (!results) return;
        const filtered = allMembers.filter(m=> 
            (m.nama||'').toLowerCase().includes(term) || 
            (m.nim||'').toLowerCase().includes(term)
        ).slice(0, 30);

        if (filtered.length === 0) {
            results.innerHTML = '<div class="p-4 text-xs text-gray-400 text-center">Tidak ada hasil ditemukan</div>';
        } else {
            results.innerHTML = filtered.map(m => `
                <div class="abs-search-item flex items-center justify-between px-4 py-3 hover:bg-indigo-50/50 cursor-pointer transition-all border-b border-gray-50 last:border-0 ${selectedUsers.has(m.id.toString()) ? 'bg-indigo-50' : ''}" data-id="${m.id}">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                            ${(m.nama||'').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">${m.nama}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">${m.nim}</p>
                        </div>
                    </div>
                    <div class="checkbox-ui w-5 h-5 rounded-full border-2 ${selectedUsers.has(m.id.toString()) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-200'} flex items-center justify-center transition-all">
                        ${selectedUsers.has(m.id.toString()) ? '<i class="fi fi-rr-check text-[10px] text-white"></i>' : ''}
                    </div>
                </div>
            `).join('');
        }
        results.classList.remove('hidden');
    };

    if (search) {
        search.oninput = () => fill(search.value.toLowerCase());
        search.onfocus = () => fill(search.value.toLowerCase());
    }
    
    modal.classList.remove('hidden');
});

// Handle clicking search results
document.addEventListener('click', (e) => {
    const item = e.target.closest('.abs-search-item');
    if (item) {
        const id = item.dataset.id.toString();
        if (selectedUsers.has(id)) {
            selectedUsers.delete(id);
        } else {
            selectedUsers.add(id);
        }
        renderSelectedUsers();
        // Refresh search list UI
        const search = qs('#abs-search');
        if (search) {
            const results = qs('#abs-search-results');
            const filtered = allMembers.filter(m=> 
                (m.nama||'').toLowerCase().includes(search.value.toLowerCase()) || 
                (m.nim||'').toLowerCase().includes(search.value.toLowerCase())
            ).slice(0, 30);
            
            // Just update the checkboxes in the results list without full re-render if possible, 
            // but full re-render is safer for now.
            const fill = (term='')=>{ 
                const results = qs('#abs-search-results');
                if (!results) return;
                const filtered = allMembers.filter(m=> (m.nama||'').toLowerCase().includes(term) || (m.nim||'').toLowerCase().includes(term)).slice(0, 30);
                results.innerHTML = filtered.map(m => `
                    <div class="abs-search-item flex items-center justify-between px-4 py-3 hover:bg-indigo-50/50 cursor-pointer transition-all border-b border-gray-50 last:border-0 ${selectedUsers.has(m.id.toString()) ? 'bg-indigo-50' : ''}" data-id="${m.id}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">${(m.nama||'').charAt(0).toUpperCase()}</div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">${m.nama}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">${m.nim}</p>
                            </div>
                        </div>
                        <div class="checkbox-ui w-5 h-5 rounded-full border-2 ${selectedUsers.has(m.id.toString()) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-200'} flex items-center justify-center transition-all">
                            ${selectedUsers.has(m.id.toString()) ? '<i class="fi fi-rr-check text-[10px] text-white"></i>' : ''}
                        </div>
                    </div>
                `).join('');
            };
            fill(search.value.toLowerCase());
        }
        if (qs('#abs-select-all')) qs('#abs-select-all').checked = false;
        return;
    }

    // Hide search results when clicking outside
    const searchArea = e.target.closest('.group.relative');
    if (!searchArea) {
        const results = qs('#abs-search-results');
        if (results) results.classList.add('hidden');
    }
});

qs('#abs-select-all') && qs('#abs-select-all').addEventListener('change', (e) => {
    if (e.target.checked) {
        allMembers.forEach(m => selectedUsers.add(m.id.toString()));
    } else {
        selectedUsers.clear();
    }
    renderSelectedUsers();
    // Close search results if open
    const results = qs('#abs-search-results');
    if (results) results.classList.add('hidden');
});

qs('#abs-clear-selection') && qs('#abs-clear-selection').addEventListener('click', () => {
    selectedUsers.clear();
    renderSelectedUsers();
    if (qs('#abs-select-all')) qs('#abs-select-all').checked = false;
    const results = qs('#abs-search-results');
    if (results) results.classList.add('hidden');
});

// Sync global config to all rows
qs('#abs-apply-global') && qs('#abs-apply-global').addEventListener('click', () => {
    const type = qs('#abs-type').value;
    const isAuto = qs('#abs-auto-time').checked;
    const items = qsa('.abs-user-item');
    items.forEach(row => {
        const typeSelect = row.querySelector('.item-type');
        const autoCheck = row.querySelector('.item-auto-time');
        if (typeSelect) typeSelect.value = type;
        if (autoCheck) autoCheck.checked = isAuto;
        if (typeSelect) typeSelect.dispatchEvent(new Event('change'));
    });
});

// Handle global auto-time toggle
qs('#abs-auto-time') && qs('#abs-auto-time').addEventListener('change', (e) => {
    const items = qsa('.abs-user-item');
    items.forEach(row => {
        const autoCheck = row.querySelector('.item-auto-time');
        if (autoCheck) {
            autoCheck.checked = e.target.checked;
            autoCheck.dispatchEvent(new Event('change'));
        }
    });
});
// Manual holidays handlers
qs('#btn-manual-holidays') && qs('#btn-manual-holidays').addEventListener('click', async ()=>{
    await renderManualHolidays();
    qs('#manual-holidays-modal').classList.remove('hidden');
});
qs('#mh-close') && qs('#mh-close').addEventListener('click', ()=> qs('#manual-holidays-modal').classList.add('hidden'));

async function renderManualHolidays(){
    const start = new Date(new Date().getFullYear(),0,1).toISOString().slice(0,10);
    const end = new Date(new Date().getFullYear(),11,31).toISOString().slice(0,10);
    const r = await fetch(`?ajax=admin_get_manual_holidays&start=${start}&end=${end}`);
    const j = await r.json();
    const list = j.data||[];
    const body = qs('#mh-body'); body.innerHTML='';
    if(list.length===0){ body.innerHTML = '<tr><td colspan="3" class="text-center py-3">Belum ada data.</td></tr>'; return; }
    list.forEach(it=>{
        const tr=document.createElement('tr'); tr.className='border-b';
        tr.innerHTML = `<td class="py-2 px-3">${it.date}</td><td class="py-2 px-3">${it.name}</td><td class="py-2 px-3 text-center"><button class="mh-del bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded" data-id="${it.id}">Hapus</button></td>`;
        body.appendChild(tr);
    });
}

// Global handler for Bulk Fix Jam Pulang
document.addEventListener('click', async (e) => {
    // Check for Bulk Fix button
    const bulkFixBtn = e.target.closest('#btn-bulk-fix-checkout');
    if (bulkFixBtn) {
        // Get date from filter or default to today
        const dateInput = document.getElementById('filter-tanggal-mulai');
        const date = (dateInput && dateInput.value) ? dateInput.value : new Date().toISOString().split('T')[0];
        
        const confirmed = await customConfirm(`Anda yakin ingin mengisi jam pulang kosong untuk SEMUA data pegawai yang belum clock-out (keseluruhan data)?`, 'Konfirmasi Bulk Fix Global');
        if (!confirmed) return;
        
        bulkFixBtn.disabled = true;
        const originalContent = bulkFixBtn.innerHTML;
        bulkFixBtn.innerHTML = '<i class="fi fi-sr-spinner animate-spin"></i> Processing...';
        
        try {
            const res = await api('?ajax=admin_bulk_fix_empty_checkout', { date: date });
            if (res.ok) {
                showNotif(res.message || 'Berhasil memperbarui data', true);
                // Refresh table if on Laporan page
                if (typeof renderLaporan === 'function') renderLaporan();
            } else {
                showNotif(res.message || 'Gagal memperbarui data', false);
            }
        } catch (error) {
            console.error('Bulk fix error:', error);
            showNotif('Terjadi kesalahan sistem', false);
        } finally {
            bulkFixBtn.disabled = false;
            bulkFixBtn.innerHTML = originalContent;
        }
        return; // Handled
    }

    if(e.target && e.target.id==='mh-add'){
        const date = qs('#mh-date').value; const name = qs('#mh-name').value.trim();
        if(!date || !name){ showNotif('Isi tanggal dan keterangan', false); return; }
        
        try {
        const r = await api('?ajax=admin_add_manual_holiday', { date, name });
            if(r.ok){ 
                await renderManualHolidays(); 
                qs('#mh-name').value='';
                showNotif('Hari libur berhasil ditambahkan', true);
            } else {
                showNotif(r.message || 'Gagal menambahkan hari libur', false);
                console.error('API Error:', r);
            }
        } catch (error) {
            showNotif('Terjadi kesalahan: ' + error.message, false);
            console.error('Error adding manual holiday:', error);
        }
    }
    if(e.target && e.target.classList.contains('mh-del')){
        const id = e.target.getAttribute('data-id');
        showConfirmModal('Hapus hari libur ini?', async ()=>{ await api('?ajax=admin_delete_manual_holiday', { id }); await renderManualHolidays(); });
    }
});
qs('#abs-cancel') && qs('#abs-cancel').addEventListener('click', ()=> qs('#absence-modal').classList.add('hidden'));
// Add event listener for abs-type change
document.addEventListener('change', (e) => {
    if (e.target.id === 'abs-type') {
        const wfaForm = qs('#abs-wfa-form');
        const overtimeForm = qs('#abs-overtime-form');
        const type = e.target.value;
        
        // Hide all forms first
        wfaForm.classList.add('hidden');
        overtimeForm.classList.add('hidden');
        
        // Show appropriate form based on type
        if (type === 'wfa') {
            wfaForm.classList.remove('hidden');
        } else if (type === 'overtime') {
            overtimeForm.classList.remove('hidden');
        }
    }
});

qs('#abs-save') && qs('#abs-save').addEventListener('click', async ()=>{
    if (selectedUsers.size === 0) {
        showNotif('Pilih minimal satu pegawai', false);
        return;
    }

    const date = qs('#abs-date').value;
    const items = qsa('.abs-user-item');
    const bulk_data = [];
    
    let isValid = true;
    items.forEach(row => {
        const userId = row.dataset.userId;
        const typeSelect = row.querySelector('.item-type');
        const type = typeSelect ? typeSelect.value : 'wfo';
        const isAuto = row.querySelector('.item-auto-time').checked;
        const jam_masuk = row.querySelector('.item-jam-masuk').value;
        const jam_pulang = row.querySelector('.item-jam-pulang').value;
        const alasan = row.querySelector('.item-reason').value.trim();
        const lokasi = row.querySelector('.item-location').value.trim();
        
        // Basic validation for WFA/Overtime/WFO when NOT auto
        const needsTime = ['wfo', 'wfa', 'overtime'].includes(type);
        if (needsTime && !isAuto && (!jam_masuk || !jam_pulang)) {
            isValid = false;
            row.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => row.classList.remove('ring-2', 'ring-red-500'), 3000);
        }
        
        bulk_data.push({
            user_id: userId,
            type: type,
            jam_masuk: (needsTime && !isAuto) ? jam_masuk : null,
            jam_pulang: (needsTime && !isAuto) ? jam_pulang : null,
            alasan: alasan,
            lokasi: lokasi
        });
    });
    
    if (!isValid) {
        showNotif('Harap lengkapi jam masuk & pulang atau gunakan fitur Otomatis', false);
        return;
    }

    const payload = {
        date: date,
        bulk_data: JSON.stringify(bulk_data)
    };
    
    const btn = qs('#abs-save');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin"></i> Menyimpan...';

    const r = await api('?ajax=admin_add_absence', payload);
    btn.disabled = false;
    btn.innerHTML = originalText;

    if(r.ok){
        qs('#absence-modal').classList.add('hidden');
        selectedUsers.clear();
        renderSelectedUsers();
        if (qs('#abs-select-all')) qs('#abs-select-all').checked = false;
        
        if (typeof renderLaporan === 'function') renderLaporan();
        showNotif(r.message || 'Data berhasil disimpan', true);
    } else {
        showNotif(r.message||'Gagal simpan', false);
    }
});

// Update WFA locations button handler
qs('#btn-update-wfa-locations') && qs('#btn-update-wfa-locations').addEventListener('click', async ()=>{
    showConfirmModal('Apakah Anda yakin ingin memperbarui semua lokasi WFA yang masih dalam bentuk koordinat menjadi nama jalan? Proses ini mungkin memakan waktu beberapa saat.', async () => {
    
    const button = qs('#btn-update-wfa-locations');
    const originalText = button.textContent;
    button.textContent = 'Memproses...';
    button.disabled = true;
    
    try {
        const r = await api('?ajax=admin_update_wfa_locations', {});
        if (r.ok) {
            showNotif(r.message || 'Lokasi WFA berhasil diperbarui', true);
            renderLaporan(); // Refresh the table
        } else {
            showNotif(r.message || 'Gagal memperbarui lokasi WFA', false);
        }
    } catch (error) {
        showNotif('Terjadi kesalahan saat memperbarui lokasi WFA', false);
        console.error('Error updating WFA locations:', error);
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
    });
});

// Backup management handlers - moved to below for better integration with loadBackupFiles

qs('#btn-backup-status') && qs('#btn-backup-status').addEventListener('click', async ()=>{
    try {
        const r = await api('?ajax=get_backup_status', {});
        if (r.ok && r.data) {
            const data = r.data;
            let message = '';
            
            if (data.exists) {
                message = `Backup tersedia:\n`;
                message += `File: ${data.file}\n`;
                message += `Ukuran: ${data.size_formatted}\n`;
                message += `Dibuat: ${data.created}`;
            } else {
                message = 'Tidak ada file backup tersedia';
            }
            
            showNotif(message, false);
        } else {
            showNotif(r.message || 'Gagal mendapatkan status backup', false);
        }
    } catch (error) {
        showNotif('Terjadi kesalahan saat mendapatkan status backup', false);
        console.error('Error getting backup status:', error);
    }
});

// Load and render backup files list
async function loadBackupFiles() {
    const listContainer = qs('#backup-files-list');
    if (!listContainer) return;
    
    listContainer.innerHTML = `
        <div class="text-center text-gray-500 py-8">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
            <p class="mt-2">Memuat daftar file backup...</p>
        </div>
    `;
    
    try {
        const r = await api('?ajax=list_backup_files', {});
        if (r.ok && r.data) {
            const files = r.data;
            
            if (files.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fi fi-sr-database text-4xl mb-2"></i>
                        <p>Tidak ada file backup tersedia</p>
                        <p class="text-sm mt-2">Klik "Buat Backup Baru" untuk membuat backup pertama</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="space-y-2">';
            files.forEach(file => {
                html += `
                    <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">${file.name}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="mr-4"><i class="fi fi-sr-file"></i> ${file.size_formatted}</span>
                                <span><i class="fi fi-sr-calendar"></i> ${file.modified}</span>
                            </div>
                        </div>
                        <div>
                            <a href="?ajax=download_backup&file=${encodeURIComponent(file.name)}" 
                               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition inline-flex items-center">
                                <i class="fi fi-sr-download mr-2"></i> Download
                            </a>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            listContainer.innerHTML = html;
        } else {
            listContainer.innerHTML = `
                <div class="text-center text-red-500 py-8">
                    <i class="fi fi-sr-exclamation-triangle text-4xl mb-2"></i>
                    <p>Gagal memuat daftar file backup</p>
                    <p class="text-sm mt-2">${r.message || 'Terjadi kesalahan'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading backup files:', error);
        listContainer.innerHTML = `
            <div class="text-center text-red-500 py-8">
                <i class="fi fi-sr-exclamation-triangle text-4xl mb-2"></i>
                <p>Terjadi kesalahan saat memuat daftar file backup</p>
            </div>
        `;
    }
}

// Refresh backup list button
qs('#btn-refresh-backup-list') && qs('#btn-refresh-backup-list').addEventListener('click', () => {
    loadBackupFiles();
});

// Create backup button handler
qs('#btn-create-backup') && qs('#btn-create-backup').addEventListener('click', async () => {
    showConfirmModal('Apakah Anda yakin ingin membuat backup database? Proses ini mungkin memakan waktu beberapa saat.', async () => {
        const button = qs('#btn-create-backup');
        const originalText = button.textContent;
        button.textContent = 'Membuat Backup...';
        button.disabled = true;
        
        try {
            const r = await api('?ajax=create_backup', {});
            if (r.ok) {
                showNotif(r.message || 'Backup berhasil dibuat', true);
                // Refresh list after successful backup
                setTimeout(() => loadBackupFiles(), 500);
            } else {
                showNotif(r.message || 'Gagal membuat backup', false);
            }
        } catch (error) {
            showNotif('Terjadi kesalahan saat membuat backup', false);
            console.error('Error creating backup:', error);
        } finally {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
});


// Daily report review modal
qs('#dr-close') && qs('#dr-close').addEventListener('click', ()=> qs('#dr-modal').classList.add('hidden'));
qs('#dr-approve') && qs('#dr-approve').addEventListener('click', ()=> handleDrApproveDisapprove('approved'));
qs('#dr-disapprove') && qs('#dr-disapprove').addEventListener('click', ()=> handleDrApproveDisapprove('disapproved'));
async function handleDrApproveDisapprove(status){
    const id = qs('#dr-modal').dataset.reportId; const evaluation = qs('#dr-evaluation').value;
    if(!id){ showNotif('Tidak ada laporan.'); return; }
    showConfirmModal('Yakin '+(status==='approved'?'approve':'disapprove')+'?', async ()=>{
        const r = await api('?ajax=admin_set_daily_status', { id, status, evaluation });
        if(r.ok){ qs('#dr-modal').classList.add('hidden'); renderLaporan(); } else { showNotif(r.message||'Gagal'); }
    });
}

const editAttModal = qs('#edit-att-modal');
qs('#edit-att-cancel') && qs('#edit-att-cancel').addEventListener('click', ()=> editAttModal.classList.add('hidden'));

// Handle change event for edit-att-ket to show/hide WFA and Overtime forms
document.addEventListener('change', (e) => {
    if (e.target.id === 'edit-att-ket') {
        const wfaForm = qs('#edit-att-wfa-form');
        const overtimeForm = qs('#edit-att-overtime-form');
        const ket = e.target.value;
        
        // Hide all forms first
        wfaForm.classList.add('hidden');
        overtimeForm.classList.add('hidden');
        
        // Show appropriate form based on ket
        if (ket === 'wfa') {
            wfaForm.classList.remove('hidden');
        } else if (ket === 'overtime') {
            overtimeForm.classList.remove('hidden');
        }
    }
});

// Handle screenshot upload for edit attendance modal
let editAttScreenshotMasuk = null;
let editAttScreenshotPulang = null;

// Upload screenshot masuk
qs('#edit-att-upload-masuk') && qs('#edit-att-upload-masuk').addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                editAttScreenshotMasuk = e.target.result;
                qs('#edit-att-screenshot-masuk-data').value = editAttScreenshotMasuk;
                qs('#edit-att-screenshot-masuk-img').src = editAttScreenshotMasuk;
                qs('#edit-att-screenshot-masuk-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
});

// Upload screenshot pulang
qs('#edit-att-upload-pulang') && qs('#edit-att-upload-pulang').addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                editAttScreenshotPulang = e.target.result;
                qs('#edit-att-screenshot-pulang-data').value = editAttScreenshotPulang;
                qs('#edit-att-screenshot-pulang-img').src = editAttScreenshotPulang;
                qs('#edit-att-screenshot-pulang-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
});

// Remove screenshot masuk
qs('#edit-att-remove-masuk') && qs('#edit-att-remove-masuk').addEventListener('click', () => {
    editAttScreenshotMasuk = null;
    qs('#edit-att-screenshot-masuk-data').value = '';
    qs('#edit-att-screenshot-masuk-preview').classList.add('hidden');
});

// Remove screenshot pulang
qs('#edit-att-remove-pulang') && qs('#edit-att-remove-pulang').addEventListener('click', () => {
    editAttScreenshotPulang = null;
    qs('#edit-att-screenshot-pulang-data').value = '';
    qs('#edit-att-screenshot-pulang-preview').classList.add('hidden');
});
qs('#edit-att-form') && qs('#edit-att-form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = qs('#edit-att-id').value;
    const jam_masuk = qs('#edit-att-jam-masuk').value || '';
    const jam_pulang = qs('#edit-att-jam-pulang').value || '';
    const ket = qs('#edit-att-ket').value || '';
    const status = qs('#edit-att-status').value || '';
    const foto_masuk = qs('#edit-att-screenshot-masuk-data').value || '';
    const foto_pulang = qs('#edit-att-screenshot-pulang-data').value || '';
    
    // Add seconds to time values
    const jam_masuk_with_seconds = jam_masuk ? jam_masuk + ':00' : '';
    const jam_pulang_with_seconds = jam_pulang ? jam_pulang + ':00' : '';
    
    const payload = { 
        id, 
        jam_masuk: jam_masuk_with_seconds, 
        jam_pulang: jam_pulang_with_seconds, 
        ket, 
        status,
        foto_masuk,
        foto_pulang
    };
    
    // Add WFA or Overtime fields based on ket
    if (ket === 'wfa') {
        payload.alasan_wfa = qs('#edit-att-alasan-wfa')?.value || '';
    } else if (ket === 'overtime') {
        payload.alasan_overtime = qs('#edit-att-alasan-overtime')?.value || '';
        payload.lokasi_overtime = qs('#edit-att-lokasi-overtime')?.value || '';
    }
    
    const r = await api('?ajax=admin_update_attendance', payload);
    showNotif(r.ok ? 'Berhasil disimpan.' : (r.message || 'Gagal menyimpan'), r.ok);
    if(r.ok){ 
        editAttModal.classList.add('hidden'); 
        renderLaporan(); 
    }
});

// Event listener untuk tombol "Tambahkan Laporan"
qs('#edit-att-add-report') && qs('#edit-att-add-report').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const nama = qs('#edit-att-nama').value;
    
    if (!userId || !date) {
        showNotif('Data tidak lengkap', false);
        return;
    }
    
    // Set info di modal laporan harian
    qs('#admin-dr-nama').textContent = nama;
    qs('#admin-dr-date').textContent = new Date(date).toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Cek apakah sudah ada laporan
    try {
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date: date });
        if (r.ok && r.data && r.data.content) {
            qs('#admin-dr-content').value = r.data.content;
        } else {
            qs('#admin-dr-content').value = '';
        }
    } catch (error) {
        console.error('Error checking daily report:', error);
        qs('#admin-dr-content').value = '';
    }
    
    // Sembunyikan modal edit kehadiran dan tampilkan modal laporan harian
    editAttModal.classList.add('hidden');
    qs('#admin-daily-report-modal').classList.remove('hidden');
});

// Event listener untuk modal laporan harian admin
qs('#admin-dr-cancel') && qs('#admin-dr-cancel').addEventListener('click', ()=>{
    qs('#admin-daily-report-modal').classList.add('hidden');
    editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
});

qs('#admin-dr-save') && qs('#admin-dr-save').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const content = qs('#admin-dr-content').value;
    
    if (!content.trim()) {
        showNotif('Isi laporan tidak boleh kosong', false);
        return;
    }
    
    try {
        const r = await api('?ajax=admin_save_daily_report', { 
            user_id: userId, 
            date: date, 
            content: content 
        });
        
        if (r.ok) {
            showNotif('Laporan harian berhasil disimpan');
            qs('#admin-daily-report-modal').classList.add('hidden');
            editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
        } else {
            showNotif(r.message || 'Gagal menyimpan laporan', false);
        }
    } catch (error) {
        console.error('Error saving daily report:', error);
        showNotif('Terjadi kesalahan saat menyimpan', false);
    }
});

// Event listener untuk tombol "Tambahkan Laporan"
qs('#edit-att-add-report') && qs('#edit-att-add-report').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const nama = qs('#edit-att-nama').value;
    
    if (!userId || !date) {
        showNotif('Data tidak lengkap', false);
        return;
    }
    
    // Set info di modal laporan harian
    qs('#admin-dr-nama').textContent = nama;
    qs('#admin-dr-date').textContent = new Date(date).toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Cek apakah sudah ada laporan
    try {
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date: date });
        if (r.ok && r.data && r.data.content) {
            qs('#admin-dr-content').value = r.data.content;
        } else {
            qs('#admin-dr-content').value = '';
        }
    } catch (error) {
        console.error('Error checking daily report:', error);
        qs('#admin-dr-content').value = '';
    }
    
    // Sembunyikan modal edit kehadiran dan tampilkan modal laporan harian
    editAttModal.classList.add('hidden');
    qs('#admin-daily-report-modal').classList.remove('hidden');
});

// Event listener untuk modal laporan harian admin
qs('#admin-dr-cancel') && qs('#admin-dr-cancel').addEventListener('click', ()=>{
    qs('#admin-daily-report-modal').classList.add('hidden');
    editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
});

qs('#admin-dr-save') && qs('#admin-dr-save').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const content = qs('#admin-dr-content').value;
    
    if (!content.trim()) {
        showNotif('Isi laporan tidak boleh kosong', false);
        return;
    }
    
    try {
        const r = await api('?ajax=admin_save_daily_report', { 
            user_id: userId, 
            date: date, 
            content: content 
        });
        
        if (r.ok) {
            showNotif('Laporan harian berhasil disimpan');
            qs('#admin-daily-report-modal').classList.add('hidden');
            editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
        } else {
            showNotif(r.message || 'Gagal menyimpan laporan', false);
        }
    } catch (error) {
        console.error('Error saving daily report:', error);
        showNotif('Terjadi kesalahan saat menyimpan', false);
    }
});

// Redundant click listener removed

// Removed duplicate showWFAModal as it is already defined earlier in this file

function submitAttendanceWithWFA(attendanceData, wfaReason) {
    // Add WFA reason to attendance data
    const dataWithWFA = {
        ...attendanceData,
        wfa_reason: wfaReason,
        is_wfa: true
    };
    
    // Submit attendance with WFA reason
    api('?ajax=save_attendance', dataWithWFA)
        .then(response => {
            if (response.ok) {
                statusMessage('Presensi berhasil dengan alasan WFA!', 'bg-green-100 text-green-700');
                // Clear pending data
                window.pendingWFAReson = null;
                window.pendingAttendanceData = null;
                isProcessingRecognition = false;
            } else {
                const errorMsg = response.message || 'Presensi gagal. Silakan coba lagi.';
                statusMessage('Gagal menyimpan presensi: ' + errorMsg, 'bg-red-100 text-red-700');
                isProcessingRecognition = false;
            }
        })
        .catch(error => {
            console.error('Error submitting attendance with WFA:', error);
            statusMessage('Terjadi kesalahan saat menyimpan presensi.', 'bg-red-100 text-red-700');
            isProcessingRecognition = false;
        });
}

function showConfirmModal(message, cb){
    const modal=qs('#confirm-modal');
    qs('#confirm-modal-message').textContent=message;
    onConfirmCallback=cb;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
qs('#btn-confirm-yes') && qs('#btn-confirm-yes').addEventListener('click', ()=>{
    if(typeof onConfirmCallback==='function') onConfirmCallback();
    const m = qs('#confirm-modal');
    if (m) {
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    onConfirmCallback=null;
});
qs('#btn-confirm-no') && qs('#btn-confirm-no').addEventListener('click', ()=>{
    const m = qs('#confirm-modal');
    if (m) {
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    onConfirmCallback=null;
});

// Pegawai app: setup Rekap and Monthly pages
const pageMonthlyList = qs('#page-laporan-bulanan');
const pageMonthlyForm = qs('#page-monthly-form');

function addAchievementRow(data = { achievement: '', detail: '' }) {
    const body = qs('#table-achievements-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.achievement}" placeholder="Capaian..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.detail}" placeholder="Detail capaian..."></td>
        <td class="p-1 text-center"><button type="button" class="btn-delete-row text-red-500 font-bold">Hapus</button></td>
    `;
    body.appendChild(tr);
}

function addObstacleRow(data = { obstacle: '', solution: '', note: '' }) {
    const body = qs('#table-obstacles-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.obstacle}" placeholder="Kendala..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.solution}" placeholder="Solusi..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.note}" placeholder="Catatan..."></td>
        <td class="p-1 text-center"><button type="button" class="btn-delete-row text-red-500 font-bold">Hapus</button></td>
    `;
    body.appendChild(tr);
}

// Event listeners untuk tombol tambah baris
qs('#btn-add-achievement')?.addEventListener('click', () => addAchievementRow());
qs('#btn-add-obstacle')?.addEventListener('click', () => addObstacleRow());

// Event listener untuk hapus baris (delegation)
pageMonthlyForm?.addEventListener('click', e => {
    if (e.target.classList.contains('btn-delete-row')) {
        e.target.closest('tr').remove();
    }
});

// Kembali ke daftar (Tutup modal)
qs('#btn-back-to-monthly-list')?.addEventListener('click', () => {
    pageMonthlyForm.classList.add('hidden');
    pageMonthlyForm.classList.remove('flex');
});

// Close modal when clicking backdrop
qs('#monthly-modal-overlay') && qs('#monthly-modal-overlay').addEventListener('click', () => {
    pageMonthlyForm.classList.add('hidden');
    pageMonthlyForm.classList.remove('flex');
});

// Fungsi untuk menyimpan laporan (baik draft maupun submit)
async function saveMonthlyReport(isSubmit) {
    const year = qs('#monthly-report-year').value;
    const month = qs('#monthly-report-month').value;
    const summary = qs('#monthly-summary').value;

    const achievements = qsa('#table-achievements-body tr').map(tr => {
        const inputs = tr.querySelectorAll('input');
        return { achievement: inputs[0].value, detail: inputs[1].value };
    }).filter(item => item.achievement || item.detail);

    const obstacles = qsa('#table-obstacles-body tr').map(tr => {
        const inputs = tr.querySelectorAll('input');
        return { obstacle: inputs[0].value, solution: inputs[1].value, note: inputs[2].value };
    }).filter(item => item.obstacle || item.solution || item.note);

    const payload = {
        year: parseInt(year),
        month: parseInt(month),
        summary,
        achievements: JSON.stringify(achievements),
        obstacles: JSON.stringify(obstacles),
        submit: isSubmit
    };
    
    const r = await api('?ajax=save_monthly_report', payload);
    if (r.ok) {
        showNotif(isSubmit ? 'Laporan berhasil disubmit!' : 'Laporan berhasil disimpan sebagai draft.');
        
        // Clear API Cache to force fresh data in renderMonthly
        if (typeof apiCache !== 'undefined' && apiCache.clear) {
            apiCache.clear();
        }
        
        pageMonthlyForm.classList.add('hidden');
        pageMonthlyForm.classList.remove('flex');
        renderMonthly(); // Refresh list
    } else {
        showNotif(r.message || 'Gagal menyimpan laporan.');
    }
}

qs('#btn-save-draft')?.addEventListener('click', () => saveMonthlyReport(false));
qs('#form-monthly-report')?.addEventListener('submit', (e) => {
    e.preventDefault();
    saveMonthlyReport(true);
});
// --- End Monthly Report Form Logic ---

function getWeekNumberInMonth(date) {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    const firstDayOfMonth = new Date(d.getFullYear(), d.getMonth(), 1);
    const firstDayOfWeek = firstDayOfMonth.getDay();
    const offsetDays = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1; // Monday = 0, Sunday = 6
    const weekNumber = Math.ceil((d.getDate() + offsetDays) / 7);
    return weekNumber;
}

// Flag to prevent multiple calls

async function initRekapPage() {
    // SECURITY: Don't run on public pages or if not authenticated
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page');
    if (['presensi-masuk', 'presensi-pulang', 'landing'].includes(page)) return;

    if (isInitRekapRunning) {
        console.log('initRekapPage already running, skipping...');
        return;
    }
    
    isInitRekapRunning = true;
    
    // Load settings for max days back for daily reports
    try {
        const settingsJson = await api('?ajax=get_settings', {}, { cache: false });
        if (settingsJson.ok && settingsJson.data && settingsJson.data.max_daily_report_days_back) {
            window.maxDailyReportDaysBack = parseInt(settingsJson.data.max_daily_report_days_back.value) || 5;
        } else {
            window.maxDailyReportDaysBack = 5; // Default: 5 days
        }
    } catch (e) {
        window.maxDailyReportDaysBack = 5; // Default: 5 days on error
    }
    
    const m = parseInt(qs('#rekap-month')?.value || String(new Date().getMonth() + 1));
    const y = parseInt(qs('#rekap-year')?.value || String(new Date().getFullYear()));
    const viewMode = qs('#rekap-view-mode')?.value || 'monthly';
    
    console.log('Loading rekap for month:', m, 'year:', y, 'mode:', viewMode);
    const r = await api('?ajax=get_rekap', { month: m, year: y });
    console.log('Rekap data:', r);
    
    // Load missing daily reports
    await loadMissingDailyReports();

    const weekSel = qs('#rekap-week');
    if (weekSel) {
        // Toggle visibility based on mode
        if (viewMode === 'weekly') {
            weekSel.classList.remove('hidden');
        } else {
            weekSel.classList.add('hidden');
        }
    
        // Only repopulate if empty or we just switched context significantly? 
        // Better to always refresh just in case data changed, but try to preserve selection if valid.
        const currentVal = weekSel.value;
        weekSel.innerHTML = '';
        
        if (r.ok && r.data.length > 0) {
            const datesInMonth = r.data.map(d => new Date(d.date));
            const weeks = [...new Set(datesInMonth.map(d => getWeekNumberInMonth(d)))].sort((a, b) => a - b);
            
            if (weeks.length >= 1) {
                // Add "All Weeks" option
                const allOption = document.createElement('option');
                allOption.value = '0';
                allOption.textContent = 'Semua Minggu';
                weekSel.appendChild(allOption);
                
                weeks.forEach(w => {
                    const option = document.createElement('option');
                    option.value = w;
                    option.textContent = `Minggu ke-${w}`;
                    weekSel.appendChild(option);
                });
                
                // Restore selection or set default
                if (currentVal && weeks.includes(parseInt(currentVal))) {
                    weekSel.value = currentVal;
                } else if (!currentVal && m === (new Date().getMonth() + 1) && y === new Date().getFullYear()) {
                    // Default to current week if viewing current month
                    const currentWeek = getWeekNumberInMonth(new Date());
                    if (weeks.includes(currentWeek)) weekSel.value = currentWeek;
                }
            }
        }
    }

    // Get selected week
    let selectedWeek = parseInt(qs('#rekap-week')?.value || 0);
    
    // Force 0 if monthly mode
    if (viewMode === 'monthly') {
        selectedWeek = 0;
    }
    
    // Debug logging
    console.log('Selected week:', selectedWeek);
    
    const body = qs('#table-rekap-body');
    if (!body) {
        isInitRekapRunning = false;
        return;
    }
    body.innerHTML = '';
    if (!r.ok || !r.data || r.data.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data.</td></tr>`;
        isInitRekapRunning = false;
        // Also clear/hide KPI?
        return;
    }

    // Store current data globally
    window.currentRekapData = r.data;
    
    // Render the table data
    renderRekapData(r.data, m, y);
    
    // Calculate custom dates for KPI if in weekly mode
    let customStart = null;
    let customEnd = null;
    
    if (viewMode === 'weekly' && selectedWeek > 0) {
        // Filter data to find date range
        const weekData = r.data.filter(d => getWeekNumberInMonth(new Date(d.date)) === selectedWeek);
        if (weekData.length > 0) {
             // Sort by date
             weekData.sort((a, b) => new Date(a.date) - new Date(b.date));
             customStart = weekData[0].date;
             customEnd = weekData[weekData.length - 1].date;
             
             // Expand range to cover full week (optional/bonus)?
             // For now, strict range of ATTENDANCE/HOLIDAY days known to system is safer.
        }
    }
    
    // Load KPI data with appropriate filter
    // This replaces loadEmployeeKPIData()
    loadKPIChart(m, y, customStart, customEnd);
    
    // Reset flag
    isInitRekapRunning = false;
}

// Load KPI data for employee
async function loadEmployeeKPIData() {
    try {
        const response = await fetch('?ajax=get_kpi_data');
        const result = await response.json();
        
        if (result.ok && result.data) {
            renderEmployeeKPIChart(result.data);
        } else {
            console.error('Failed to load KPI data:', result.message);
        }
    } catch (error) {
        console.error('Error loading KPI data:', error);
    }
}

// Render KPI chart for employee
function renderEmployeeKPIChart(kpiData) {
    const ctx = qs('#kpi-chart');
    const summary = qs('#kpi-summary');
    
    if (!ctx || !summary) return;
    
    // Destroy existing chart if it exists
    if (window.employeeKPIChart) {
        try {
            window.employeeKPIChart.destroy();
        } catch (e) {
            console.log('Chart destroy error (ignored):', e);
        }
        window.employeeKPIChart = null;
    }
    
    // Create bar chart data
    const labels = ['Ontime', 'Terlambat', 'Izin/Sakit', 'Alpha', 'Overtime'];
    const data = [
        kpiData.ontime_count || 0,
        kpiData.late_count || 0,
        kpiData.izin_sakit_count || 0,
        kpiData.alpha_count || 0,
        kpiData.overtime_count || 0
    ];
    
    const colors = [
        '#22c55e', // Green for ontime
        '#ef4444', // Red for late
        '#eab308', // Yellow for izin/sakit
        '#6b7280', // Gray for alpha
        '#10b981'  // Emerald for overtime
    ];
    
    window.employeeKPIChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Hari',
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: `KPI Score: ${kpiData.kpi_score}% - ${kpiData.status}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Update summary cards
    summary.innerHTML = `
        <div class="bg-green-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">${kpiData.ontime_count || 0}</div>
            <div class="text-sm text-green-700">Ontime</div>
        </div>
        <div class="bg-red-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600">${kpiData.late_count || 0}</div>
            <div class="text-sm text-red-700">Terlambat</div>
        </div>
        <div class="bg-yellow-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-600">${kpiData.izin_sakit_count || 0}</div>
            <div class="text-sm text-yellow-700">Izin/Sakit</div>
        </div>
        <div class="bg-gray-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-gray-600">${kpiData.alpha_count || 0}</div>
            <div class="text-sm text-gray-700">Alpha</div>
        </div>
        <div class="bg-emerald-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-emerald-600">${kpiData.overtime_count || 0}</div>
            <div class="text-sm text-emerald-700">Overtime</div>
        </div>
        <div class="bg-indigo-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-indigo-600">${kpiData.kpi_score || 0}%</div>
            <div class="text-sm text-indigo-700">KPI Score</div>
        </div>
    `;
}

function renderRekapData(data, m, y) {
    const body = qs('#table-rekap-body');
    if (!body) return;
    body.innerHTML = '';
    
    if (!data || data.length === 0) {
        body.innerHTML = `<div class="col-span-7 text-center py-8 text-gray-500">Tidak ada data.</div>`;
        return;
    }

    const currentWeek = getWeekNumberInMonth(new Date());
    let selectedWeek = parseInt(qs('#rekap-week')?.value || 0);
    if (!qs('#rekap-week') || qs('#rekap-week').classList.contains('hidden')) {
        selectedWeek = 0; 
    }

    // --- Calendar Padding Logic ---
    // Only pad if showing full month (selectedWeek === 0)
    if (selectedWeek === 0) {
        const firstDay = new Date(data[0].date);
        let dayOfWeek = firstDay.getDay(); // 0 Sun, 1 Mon ... 6 Sat
        // Adjust to 0 Mon, 1 Tue ... 6 Sun (Monday starts at index 0)
        let offset = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        
        for (let i = 0; i < offset; i++) {
            const empty = document.createElement('div');
            empty.className = 'mood-item empty-slot opacity-20';
            body.appendChild(empty);
        }
    }

    let dataToShow = data;
    if (selectedWeek > 0) {
        dataToShow = data.filter(row => getWeekNumberInMonth(new Date(row.date)) === selectedWeek);
    }

    // --- Custom Cat Icon SVGs ---
    const catDecor = `
        <path d="M4 6L2 2L8 4" stroke="currentColor" fill="currentColor" stroke-width="1" stroke-linejoin="round" />
        <path d="M20 6L22 2L16 4" stroke="currentColor" fill="currentColor" stroke-width="1" stroke-linejoin="round" />
        <line x1="1" y1="12" x2="4" y2="13" stroke="currentColor" stroke-width="1" opacity="0.6" />
        <line x1="1" y1="15" x2="4" y2="15" stroke="currentColor" stroke-width="1" opacity="0.6" />
        <line x1="20" y1="13" x2="23" y2="12" stroke="currentColor" stroke-width="1" opacity="0.6" />
        <line x1="20" y1="15" x2="23" y2="15" stroke="currentColor" stroke-width="1" opacity="0.6" />
    `;

    const icons = {
        happy: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <path d="M6 12c0-1 1-2 2-2s2 1 2 2" />
            <path d="M14 12c0-1 1-2 2-2s2 1 2 2" />
            <path d="M9 17s1.5 2 3 2 3-2 3-2" />
        </svg>`,
        sleeping: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <path d="M6 13h3M15 13h3" />
            <path d="M11 17c0 1 1 1 1 1s1 0 1-1" />
            <path d="M12 15v1" stroke-width="1" />
        </svg>`,
        energetic: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <circle cx="7" cy="12" r="1.5" fill="currentColor" />
            <circle cx="17" cy="12" r="1.5" fill="currentColor" />
            <path d="M12 16c1.5 0 2.5 1 2.5 2.5S13.5 21 12 21s-2.5-1-2.5-2.5 1-2.5 1.5-2.5z" fill="currentColor" opacity="0.3" />
            <path d="M10 16.5c0 0 .5-.5 2-.5s2 .5 2 .5" />
        </svg>`,
        bored: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <path d="M7 11h.01M17 11h.01" stroke-width="3" />
            <path d="M9 17h6" />
            <path d="M6 9l2 1M18 9l-2 1" />
        </svg>`,
        unknown: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <circle cx="12" cy="12" r="1" fill="currentColor" />
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>`,
        future: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            ${catDecor}
            <circle cx="12" cy="13" r="1" fill="currentColor" />
            <path d="M12 7v6l3 2" />
        </svg>`
    };

    // --- Counters for Stats ---
    const stats = { happy: 0, leave: 0, wfo: 0, wfa: 0, overtime: 0, alpha: 0, total: 0 };

    dataToShow.forEach(row => {
        const d = new Date(row.date);
        const today = new Date().toISOString().slice(0, 10);
        const isFuture = row.date > today;
        const isToday = row.date === today;
        
        const isManualHoliday = row.is_manual_holiday || false;
        const isWorkingDay = row.is_working_day !== undefined ? row.is_working_day : true;
        const isWeekend = row.is_weekend || false;
        const isHoliday = isManualHoliday || !isWorkingDay || isWeekend;
        
        const dr = row.daily_report;
        const ket = (row.ket || '').toLowerCase();
        
        let moodClass = 'mood-gray';
        let icon = icons.bored;
        let bubbleText = '...';
        let type = 'unknown';

        if (isHoliday) {
            if (ket === 'wfo') {
                moodClass = 'mood-green';
                icon = icons.energetic;
                bubbleText = 'WFO Hard!';
                type = 'work';
                stats.wfo++;
            } else if (ket === 'wfa') {
                moodClass = 'mood-yellow';
                icon = icons.energetic;
                bubbleText = 'WFA Chill!';
                type = 'work';
                stats.wfa++;
            } else if (ket === 'overtime') {
                moodClass = 'mood-orange';
                icon = icons.energetic;
                bubbleText = 'Overtime!';
                type = 'work';
                stats.overtime++;
            } else {
                moodClass = 'mood-blue-bright';
                icon = icons.happy;
                bubbleText = 'holi-yay!';
                type = 'holiday';
                stats.happy++;
            }
        } else if (ket === 'sakit' || ket === 'izin') {
            moodClass = 'mood-purple-dark';
            icon = icons.sleeping;
            bubbleText = 'on leave zzz..';
            type = 'leave';
            stats.leave++;
        } else if (ket === 'wfo') {
            moodClass = 'mood-green';
            icon = icons.energetic;
            bubbleText = 'WFO Hard!';
            type = 'work';
            stats.wfo++;
        } else if (ket === 'wfa') {
            moodClass = 'mood-yellow';
            icon = icons.energetic;
            bubbleText = 'WFA Chill!';
            type = 'work';
            stats.wfa++;
        } else if (ket === 'overtime') {
            moodClass = 'mood-orange';
            icon = icons.energetic;
            bubbleText = 'Overtime!';
            type = 'work';
            stats.overtime++;
        } else if (!isFuture && isWorkingDay && (!ket || ket === 'na')) {
            if (isToday) {
                moodClass = 'mood-today-empty';
                icon = icons.unknown;
                bubbleText = 'Belum presensi';
                type = 'today_empty';
            } else {
                moodClass = 'mood-red';
                icon = icons.bored;
                bubbleText = 'missing...';
                type = 'alpha';
                stats.alpha++;
            }
        } else if (isFuture) {
            moodClass = 'mood-gray opacity-40';
            icon = icons.future;
            bubbleText = 'coming soon';
            type = 'future';
        }

        if (!isFuture) stats.total++;

        let dotClass = 'dot-gray';
        if (dr) {
            if (dr.status === 'approved') dotClass = 'dot-green';
            else if (dr.status === 'disapproved') dotClass = 'dot-red';
            else dotClass = 'dot-blue';
        }

        const moodItem = document.createElement('div');
        moodItem.className = `mood-item ${isToday ? 'today-active z-10' : ''}`;
        
        const dayInt = d.getDate();
        
        moodItem.innerHTML = `
            <div class="mood-bubble">${bubbleText}</div>
            <button class="mood-button ${moodClass}" data-date="${row.date}" data-type="${type}">
                ${icon}
                ${(type === 'work' || type === 'leave') ? `<div class="mood-status-dot ${dotClass}"></div>` : ''}
            </button>
            <div class="mood-date">${dayInt}</div>
        `;

        const btn = moodItem.querySelector('.mood-button');
        btn.addEventListener('click', () => {
            if (type === 'holiday') {
                if (window.confetti) {
                    confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, colors: ['#0ea5e9', '#38bdf8', '#7dd3fc'] });
                }
            } else if (type === 'alpha') {
                const overlay = qs('#angry-cat-overlay');
                if (overlay) { overlay.style.display = 'flex'; setTimeout(() => overlay.style.display = 'none', 2500); }
            } else if (type === 'work') {
                showWorkModal(row);
            } else if (type === 'leave') {
                showLeaveModal(row);
            } else if (type === 'today_empty') {
                window.showAttendanceNoteModal();
            }
        });

        body.appendChild(moodItem);
    });

    // Update Stats DOM
    if (qs('#stat-happy-count')) qs('#stat-happy-count').textContent = stats.happy;
    if (qs('#stat-leave-count')) qs('#stat-leave-count').textContent = stats.leave;
    if (qs('#stat-wfo-count')) qs('#stat-wfo-count').textContent = stats.wfo;
    if (qs('#stat-wfa-count')) qs('#stat-wfa-count').textContent = stats.wfa;
    if (qs('#stat-overtime-count')) qs('#stat-overtime-count').textContent = stats.overtime;
    if (qs('#stat-alpha-count')) qs('#stat-alpha-count').textContent = stats.alpha;
    if (qs('#stat-total-count')) qs('#stat-total-count').textContent = stats.total + ' Hari';

    isInitRekapRunning = false;
    loadKPIChart(m, y);
}

window.showAttendanceNoteModal = function() {
    const modal = qs('#izin-sakit-modal');
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        showNotif('Silakan input keterangan presensi hari ini.');
    }
};

window.showWorkModal = function(row) {
    const d = new Date(row.date);
    const dayMap = { Monday: 'Senin', Tuesday: 'Selasa', Wednesday: 'Rabu', Thursday: 'Kamis', Friday: 'Jumat', Saturday: 'Sabtu', Sunday: 'Minggu' };
    const dayName = dayMap[d.toLocaleDateString('en-US', { weekday: 'long' })] || '';
    const tanggal = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    
    const dr = row.daily_report;
    let statusText = 'Belum ada laporan';
    let statusClass = 'text-gray-500';
    if (dr) {
        if (dr.status === 'approved') { statusText = 'Disetujui'; statusClass = 'text-green-600'; }
        else if (dr.status === 'disapproved') { statusText = 'Ditolak'; statusClass = 'text-red-600'; }
        else { statusText = 'Menunggu Persetujuan'; statusClass = 'text-blue-600'; }
    }

    const content = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Hari / Tanggal</p>
                    <p class="font-semibold text-gray-800">${dayName}, ${tanggal}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Keterangan Presensi</p>
                    <p class="font-semibold text-gray-800">${(row.ket || '').toUpperCase()}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Jam Masuk</p>
                    <p class="font-semibold text-gray-800">${row.jam_masuk || '-'}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Jam Keluar</p>
                    <p class="font-semibold text-gray-800">${row.jam_pulang || '-'}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold">Status Laporan</p>
                <p class="font-bold ${statusClass}">${statusText}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Laporan Harian</p>
                <div id="modal-dr-column" class="p-3 bg-gray-50 rounded-xl border border-gray-200 min-h-[100px] cursor-pointer hover:bg-white transition-colors" onclick="makeDrEditable(this, '${row.date}')">
                    ${dr ? (dr.content || '<span class="text-gray-400 italic">Isi laporan kosong...</span>') : '<span class="text-gray-400 italic">Belum ada isi laporan...</span>'}
                </div>
                <div id="dr-edit-actions" class="hidden mt-2 flex justify-end gap-2">
                    <button onclick="saveDrFromModal('${row.date}')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold">Simpan</button>
                </div>
            </div>
        </div>
    `;

    customAlert(content, 'Detail Kehadiran & Laporan');
    const modalMessage = qs('#global-modal-message');
    if (modalMessage) { modalMessage.innerHTML = content; }
};

window.makeDrEditable = function(el, date) {
    if (el.querySelector('textarea')) return;
    const currentText = (el.innerText === 'Belum ada isi laporan...' || el.innerText === 'Isi laporan kosong...') ? '' : el.innerText;
    el.innerHTML = `<textarea id="modal-dr-textarea" class="w-full h-32 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Tulis laporan harian Anda...">${currentText}</textarea>`;
    qs('#dr-edit-actions').classList.remove('hidden');
    el.onclick = null;
};

window.saveDrFromModal = async function(date) {
    const textarea = qs('#modal-dr-textarea');
    if (!textarea) return;
    const val = textarea.value;
    
    try {
        const res = await api('?ajax=save_daily_report', { date: date, content: val });
        if (res.ok) {
            const container = qs('#modal-dr-column');
            container.innerHTML = val || '<span class="text-gray-400 italic">Belum ada isi laporan...</span>';
            qs('#dr-edit-actions').classList.add('hidden');
            container.onclick = () => makeDrEditable(container, date);
            showNotif('Laporan berhasil disimpan');
            initRekapPage();
        } else {
            showNotif(res.message || 'Gagal menyimpan laporan', false);
        }
    } catch (e) {
        showNotif('Terjadi kesalahan', false);
    }
};

window.showLeaveModal = function(row) {
    const tanggal = new Date(row.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    
    let evidenceHtml = '<p class="text-gray-400 italic">Tidak ada bukti</p>';
    const evidenceId = row.note_id || row.attendance_id;
    const evidenceType = row.note_id ? 'note' : 'masuk';
    
    if (evidenceId) {
        evidenceHtml = `<div class="mt-2 w-full h-48 bg-gray-100 rounded-xl overflow-hidden relative cursor-pointer" onclick="loadAndShowEvidence('${evidenceType === 'note' ? 'note_' + evidenceId : evidenceId}', '${evidenceType}', 'Bukti Izin/Sakit')">
            <div id="leave-proof-container-${evidenceId}" class="w-full h-full flex items-center justify-center">
                <i class="fi fi-rr-picture text-3xl text-gray-300"></i>
            </div>
        </div>`;
        setTimeout(() => loadLazyProof(evidenceId, evidenceType, `leave-proof-container-${evidenceId}`), 100);
    }

    const content = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Tanggal</p>
                    <p class="font-semibold text-gray-800">${tanggal}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Tipe</p>
                    <p class="font-bold text-indigo-600">${(row.ket || '').toUpperCase()}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold">Keterangan</p>
                <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded-xl border border-gray-200">
                    ${row.daily_report ? (row.daily_report.content || '-') : '-'}
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Bukti Izin/Sakit</p>
                ${evidenceHtml}
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold">Status Laporan</p>
                <p class="font-bold text-indigo-600">Terdaftar</p>
            </div>
        </div>
    `;

    customAlert(content, 'Detail Izin / Sakit');
    const modalMessage = qs('#global-modal-message');
    if (modalMessage) { modalMessage.innerHTML = content; }
};

// Global variable to store chart instance
let kpiChartInstance = null;

// Function to load and display KPI chart
async function loadKPIChart(month, year, customStart = null, customEnd = null) {
    try {
        console.log('Loading KPI chart for month:', month, 'year:', year, 'customRange:', customStart, 'to', customEnd);
        
        // Get period start and end dates
        let periodStart, periodEnd;
        
        if (customStart && customEnd) {
            periodStart = customStart;
            periodEnd = customEnd;
        } else {
            periodStart = `${year}-${String(month).padStart(2, '0')}-01`;
            const lastDay = new Date(year, month, 0).getDate();
            periodEnd = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
        }
        
        console.log('KPI period:', periodStart, 'to', periodEnd);
        
        // Fetch KPI data - check if we're viewing a specific user's data
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id') || (window.currentUserId || '2'); // Default to user 2 for testing
        const kpiUrl = userId ? 
            `?ajax=get_kpi_data&period_start=${periodStart}&period_end=${periodEnd}&user_id=${userId}&t=${Date.now()}` :
            `?ajax=get_kpi_data&period_start=${periodStart}&period_end=${periodEnd}&t=${Date.now()}`;
        
        console.log('KPI URL:', kpiUrl);
        console.log('Using user_id:', userId);
        const response = await api(kpiUrl);
        
        console.log('KPI response:', response);
        
        if (response && response.ok && response.data) {
            const kpiData = response.data;
            console.log('KPI data received:', kpiData);
            console.log('Izin/Sakit count:', kpiData.izin_sakit_count);
            
            // Show KPI chart section
            const kpiSection = qs('#kpi-chart-section');
            if (kpiSection) {
                kpiSection.classList.remove('hidden');
                console.log('KPI section shown');
            } else {
                console.error('KPI section element not found');
            }
            
            // Render KPI chart
            renderKPIChart(kpiData);
            console.log('KPI chart rendered');
            
            // Render KPI summary
            renderKPISummary(kpiData);
            console.log('KPI summary rendered');
        } else {
            console.error('No KPI data in response:', response);
            // Hide KPI section if no data
            const kpiSection = qs('#kpi-chart-section');
            if (kpiSection) {
                kpiSection.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading KPI chart:', error);
        // Hide KPI section on error
        const kpiSection = qs('#kpi-chart-section');
        if (kpiSection) {
            kpiSection.classList.add('hidden');
        }
    }
}

// Function to render KPI chart
function renderKPIChart(kpiData) {
    const canvas = qs('#kpi-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (kpiChartInstance) {
        kpiChartInstance.destroy();
    }
    
    // Prepare data
    const labels = [
        'Total WFO', 
        'Total WFA', 
        'Hadir Ontime', 
        'Terlambat', 
        'Izin/Sakit', 
        'Alpha'
    ];
    
    const dataValues = [
        kpiData.wfo_count || 0,
        kpiData.wfa_count || 0,
        kpiData.ontime_count || 0,
        kpiData.late_count || 0,
        kpiData.izin_sakit_count || 0,
        kpiData.alpha_count || 0
    ];
    
    // Create reference line data (Total Hari Kerja repeated for each label)
    const totalDays = kpiData.total_working_days || 0;
    
    // Colors matching the cards
    const colors = [
        '#10b981', // Emerald (WFO)
        '#06b6d4', // Cyan (WFA)
        '#22c55e', // Green (Ontime)
        '#eab308', // Yellow (Late)
        '#3b82f6', // Blue (Izin)
        '#ef4444'  // Red (Alpha)
    ];
    
    console.log('Rendering Chart with:', { labels, dataValues, totalDays });
    
    // Create chart
    kpiChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah Hari',
                    data: dataValues,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1,
                    borderRadius: 6, // Rounded bars
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        stepSize: 1,
                        font: {
                            family: "'Inter', sans-serif"
                        }
                    },
                    max: totalDays > 0 ? totalDays : undefined, // Set max to total working days
                    title: {
                        display: true,
                        text: `Total Hari Kerja: ${totalDays}`,
                        font: {
                            family: "'Inter', sans-serif",
                            weight: 'bold',
                            size: 13
                        },
                        color: '#4b5563'
                    }
                },
                x: {
                   grid: {
                       display: false
                   },
                   ticks: {
                       font: {
                           family: "'Inter', sans-serif",
                           size: 11
                       }
                   }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1f2937',
                    bodyColor: '#4b5563',
                    titleFont: {
                        family: "'Inter', sans-serif",
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: "'Inter', sans-serif"
                    },
                    padding: 10,
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    displayColors: true,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            }
        }
    });
}

// Function to render KPI summary (Header only)
function renderKPISummary(kpiData) {
    const summaryHeader = qs('#kpi-score-header');
    if (!summaryHeader) return;
    
    // Show header
    summaryHeader.classList.remove('hidden');
    
    // Update Score
    const scoreEl = qs('#kpi-score-value');
    if (scoreEl) scoreEl.textContent = kpiData.kpi_score || 0;
    
    // Update Status with color
    const statusEl = qs('#kpi-status-value');
    if (statusEl) {
        statusEl.textContent = kpiData.status || 'N/A';
        
        statusEl.className = 'text-lg font-bold'; // Reset classes
        if (kpiData.status === 'Excellent') statusEl.classList.add('text-green-600');
        else if (kpiData.status === 'Good') statusEl.classList.add('text-blue-600');
        else if (kpiData.status === 'Fair') statusEl.classList.add('text-yellow-600');
        else statusEl.classList.add('text-red-600');
    }
}

// Load missing daily reports for shortcut
async function loadMissingDailyReports() {
    try {
        const result = await api('?ajax=get_missing_daily_reports', {}, { suppressModal: true, cache: true, ttl: 30000 });
        
        if (!result.ok || !result.data) {
            qs('#missing-daily-reports-shortcut')?.classList.add('hidden');
            return;
        }
        
        const missingDates = result.data;
        const shortcutDiv = qs('#missing-daily-reports-shortcut');
        const countSpan = qs('#missing-reports-count');
        const listDiv = qs('#missing-reports-list');
        
        if (!shortcutDiv || !countSpan || !listDiv) return;
        
        if (missingDates.length === 0) {
            shortcutDiv.classList.add('hidden');
            return;
        }
        
        shortcutDiv.classList.remove('hidden');
        countSpan.textContent = missingDates.length;
        
        // Format dates and create buttons
        listDiv.innerHTML = missingDates.map(date => {
            const dateObj = new Date(date + 'T00:00:00');
            const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'short' });
            const day = dateObj.getDate();
            const month = dateObj.toLocaleDateString('id-ID', { month: 'short' });
            const formattedDate = `${dayName}, ${day} ${month}`;
            
            return `
                <button 
                    class="missing-report-date-btn"
                    data-date="${date}"
                    title="Klik untuk mengisi laporan harian tanggal ${formattedDate}">
                    <i class="fi fi-rr-document-signed"></i>
                    <span>${formattedDate}</span>
                </button>
            `;
        }).join('');
        
        // Add event listeners to buttons
        listDiv.querySelectorAll('.missing-report-date-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const date = btn.getAttribute('data-date');
                await openDailyReportEditModal(date);
            });
        });
        
    } catch (error) {
        console.error('Error loading missing daily reports:', error);
        qs('#missing-daily-reports-shortcut')?.classList.add('hidden');
    }
}

// Initialize rekap page controls
const rekapControls = qs('#rekap-controls');
if (rekapControls) {
    console.log('Initializing rekap controls...');
    
    // Helper to handle view mode change
    const handleViewModeChange = () => {
        const mode = qs('#rekap-view-mode').value;
        const weekSel = qs('#rekap-week');
        if (mode === 'weekly') {
            weekSel.classList.remove('hidden');
        } else {
            weekSel.classList.add('hidden');
        }
        initRekapPage();
    };

    // Add event listeners
    qs('#rekap-view-mode') && qs('#rekap-view-mode').addEventListener('change', handleViewModeChange);
    
    qs('#rekap-month') && qs('#rekap-month').addEventListener('change', () => {
        console.log('Month changed to:', qs('#rekap-month').value);
        initRekapPage();
    });
    
    qs('#rekap-year') && qs('#rekap-year').addEventListener('change', () => {
        console.log('Year changed to:', qs('#rekap-year').value);
        initRekapPage();
    });
    
    qs('#rekap-week') && qs('#rekap-week').addEventListener('change', () => {
        console.log('Week selector changed to:', qs('#rekap-week').value);
        // Force full reload to update KPI data with new week filter
        initRekapPage(); 
    });
    
    qs('#btn-load-rekap') && qs('#btn-load-rekap').addEventListener('click', () => {
        console.log('Load rekap button clicked');
        initRekapPage();
    });
}

// Modal View Laporan Harian (hanya lihat, tidak bisa edit)
const drUserViewModal = document.createElement('div');
drUserViewModal.id='dr-user-view-modal';
drUserViewModal.className='fixed inset-0 bg-black/50 hidden items-center justify-center z-50';
drUserViewModal.innerHTML = `
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
        <h3 class="text-xl font-bold mb-2">Laporan Harian</h3>
        <div class="text-sm text-gray-500 mb-2" id="dr-user-view-date"></div>
        
        <!-- Bukti Izin/Sakit Section (View Only) -->
        <div id="dr-user-view-bukti-section" class="mb-4 hidden">
        <label class="block text-sm text-gray-600 mb-2">Bukti Izin/Sakit:</label>
            <div id="dr-user-view-bukti-container" class="mb-2">
            <!-- Bukti image will be inserted here -->
        </div>
        </div>
        
        <div id="dr-user-view-content" class="whitespace-pre-wrap border p-3 rounded bg-gray-50 mb-4 min-h-[200px]"></div>
        
        <div id="dr-user-view-evaluation-container" class="mt-4 hidden">
            <h4 class="text-sm font-bold text-gray-700 mb-1">Evaluasi Admin:</h4>
            <p id="dr-user-view-evaluation" class="whitespace-pre-wrap border p-3 rounded bg-gray-100"></p>
    </div>
    
        <div class="flex justify-end gap-2 mt-4">
            <button id="dr-user-view-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Tutup</button>
        </div>
    </div>`;
document.body.appendChild(drUserViewModal);

// Modal Edit Laporan Harian (bisa edit, tanpa tombol hapus bukti)
const drUserEditModal = document.createElement('div');
drUserEditModal.id='dr-user-edit-modal';
drUserEditModal.className='fixed inset-0 bg-black/50 hidden items-center justify-center z-50';
drUserEditModal.innerHTML = `
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
        <h3 class="text-xl font-bold mb-2">Laporan Harian</h3>
        <div class="text-sm text-gray-500 mb-2" id="dr-user-edit-date"></div>
        
        <!-- Bukti Izin/Sakit Section (Edit Mode) -->
        <div id="dr-user-edit-bukti-section" class="mb-4 hidden">
            <label class="block text-sm text-gray-600 mb-2">Bukti Izin/Sakit:</label>
            <div id="dr-user-edit-bukti-container" class="mb-2">
                <!-- Bukti image will be inserted here -->
            </div>
            <div id="dr-user-edit-bukti-actions" class="flex gap-2 hidden">
                <button type="button" id="dr-user-edit-bukti-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Ganti Bukti</button>
            </div>
        </div>
        
        <textarea id="dr-user-edit-content" class="w-full border rounded p-2" rows="8" placeholder="Tulis detail pekerjaan hari ini..."></textarea>
        
        <div id="dr-user-edit-evaluation-container" class="mt-4 hidden">
        <h4 class="text-sm font-bold text-gray-700 mb-1">Evaluasi Admin:</h4>
            <p id="dr-user-edit-evaluation" class="whitespace-pre-wrap border p-3 rounded bg-gray-100"></p>
    </div>
        
    <div class="flex justify-end gap-2 mt-4">
            <button id="dr-user-edit-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
            <button id="dr-user-edit-save" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Simpan</button>
    </div>
    </div>`;
document.body.appendChild(drUserEditModal);

// Izin/Sakit modal handlers
const izinSakitModal = qs('#izin-sakit-modal');
const izinSakitForm = qs('#izin-sakit-form');
const izinSakitBukti = qs('#izin-sakit-bukti');
const izinSakitPreview = qs('#izin-sakit-preview');
const izinSakitPreviewImg = qs('#izin-sakit-preview-img');

// File upload preview with size validation
izinSakitBukti && izinSakitBukti.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const errorDiv = qs('#izin-sakit-error');
    
    if (file) {
        // Check file size (5MB = 5 * 1024 * 1024 bytes)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            errorDiv.textContent = `File terlalu besar. Maksimal 5MB. Ukuran saat ini: ${(file.size / (1024 * 1024)).toFixed(2)}MB`;
            errorDiv.classList.remove('hidden');
            izinSakitPreview.classList.add('hidden');
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            errorDiv.textContent = 'File harus berupa gambar (JPG, PNG, GIF)';
            errorDiv.classList.remove('hidden');
            izinSakitPreview.classList.add('hidden');
            return;
        }
        
        // Clear error and show preview
        errorDiv.classList.add('hidden');
        const reader = new FileReader();
        reader.onload = (e) => {
            izinSakitPreviewImg.src = e.target.result;
            izinSakitPreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        errorDiv.classList.add('hidden');
        izinSakitPreview.classList.add('hidden');
    }
});

// Cancel button
qs('#izin-sakit-cancel') && qs('#izin-sakit-cancel').addEventListener('click', () => {
    izinSakitModal.classList.add('hidden');
    izinSakitForm.reset();
    izinSakitPreview.classList.add('hidden');
});

// Form submit
izinSakitForm && izinSakitForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const type = qs('#izin-sakit-type').value;
    const alasan = qs('#izin-sakit-alasan').value;
    const file = izinSakitBukti.files[0];
    
    if (!type || !alasan || !file) {
        showNotif('Semua field harus diisi', false);
        return;
    }
    
    // Convert file to base64
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const r = await api('?ajax=submit_izin_sakit', {
                type: type,
                alasan: alasan,
                bukti: e.target.result
            });
            
            if (r.ok) {
                showNotif(r.message, true);
                izinSakitModal.classList.add('hidden');
                izinSakitForm.reset();
                izinSakitPreview.classList.add('hidden');
                
                // Refresh all components
                refreshDashboardComponents();
            } else {
                showNotif(r.message || 'Gagal menyimpan', false);
            }
        } catch (error) {
            console.error('Error submitting izin/sakit:', error);
            showNotif('Terjadi kesalahan', false);
        }
    };
    reader.readAsDataURL(file);
});

// Input keterangan button handler
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-input-keterangan')) {
        const date = e.target.getAttribute('data-date');
        izinSakitModal.classList.remove('hidden');
        izinSakitModal.classList.add('flex');
    }
});

// Fungsi untuk membuka modal view laporan harian
async function openDailyReportViewModal(date) {
    qs('#dr-user-view-date').textContent = 'Tanggal: ' + date;
        
        const r = await api('?ajax=get_rekap', { month: new Date(date).getMonth()+1, year: new Date(date).getFullYear() });
        const item = (r.data||[]).find(x=> x.date===date);
    
        if(item && item.daily_report){
        qs('#dr-user-view-content').textContent = item.daily_report.content||'';
                if (item.daily_report.evaluation) {
            qs('#dr-user-view-evaluation').textContent = item.daily_report.evaluation;
            qs('#dr-user-view-evaluation-container').classList.remove('hidden');
        } else {
            qs('#dr-user-view-evaluation-container').classList.add('hidden');
                }
            } else {
        qs('#dr-user-view-content').textContent = 'Belum ada laporan harian untuk tanggal ini.';
        qs('#dr-user-view-evaluation-container').classList.add('hidden');
    }
    
    // Cek apakah ada bukti izin/sakit untuk tanggal ini
    if (item && (item.ket === 'izin' || item.ket === 'sakit')) {
        // Get attendance data to find bukti
        const attendanceData = await api('?ajax=get_attendance');
        if (attendanceData.ok && attendanceData.data) {
            const todayRecord = attendanceData.data.find(att => 
                att.jam_masuk_iso && 
                att.jam_masuk_iso.slice(0, 10) === date &&
                (att.ket === 'izin' || att.ket === 'sakit') &&
                att.bukti_izin_sakit
            );
            
            if (todayRecord) {
                // Tampilkan bukti izin/sakit (view only)
                qs('#dr-user-view-bukti-section').classList.remove('hidden');
                qs('#dr-user-view-bukti-container').innerHTML = `
                    <div class="flex justify-center">
                        <img src="${todayRecord.bukti_izin_sakit}" alt="Bukti ${todayRecord.ket}" class="max-w-full max-h-64 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">
                    </div>
                    <p class="text-sm text-gray-600 mt-2 text-center">Bukti ${todayRecord.ket.toUpperCase()}</p>
                `;
            } else {
                qs('#dr-user-view-bukti-section').classList.add('hidden');
            }
        }
    } else {
        qs('#dr-user-view-bukti-section').classList.add('hidden');
    }
    
    qs('#dr-user-view-modal').classList.remove('hidden'); 
    qs('#dr-user-view-modal').classList.add('flex');
}

// Fungsi untuk membuka modal edit laporan harian
async function openDailyReportEditModal(date) {
    qs('#dr-user-edit-date').textContent = 'Tanggal: ' + date;
    qs('#dr-user-edit-modal').dataset.date = date;
    
    const r = await api('?ajax=get_rekap', { month: new Date(date).getMonth()+1, year: new Date(date).getFullYear() });
    const item = (r.data||[]).find(x=> x.date===date);
    
    if(item && item.daily_report){
        qs('#dr-user-edit-content').value = item.daily_report.content||'';
        if (item.daily_report.evaluation) {
            qs('#dr-user-edit-evaluation').textContent = item.daily_report.evaluation;
            qs('#dr-user-edit-evaluation-container').classList.remove('hidden');
        } else {
            qs('#dr-user-edit-evaluation-container').classList.add('hidden');
        }
    } else {
        qs('#dr-user-edit-content').value = '';
        qs('#dr-user-edit-evaluation-container').classList.add('hidden');
        }
        
        // Cek apakah ada bukti izin/sakit untuk tanggal ini
        if (item && (item.ket === 'izin' || item.ket === 'sakit')) {
            // Get attendance data to find bukti
            const attendanceData = await api('?ajax=get_attendance');
            if (attendanceData.ok && attendanceData.data) {
                const todayRecord = attendanceData.data.find(att => 
                    att.jam_masuk_iso && 
                    att.jam_masuk_iso.slice(0, 10) === date &&
                    (att.ket === 'izin' || att.ket === 'sakit') &&
                    att.bukti_izin_sakit
                );
                
                if (todayRecord) {
                // Tampilkan bukti izin/sakit (edit mode)
                qs('#dr-user-edit-bukti-section').classList.remove('hidden');
                qs('#dr-user-edit-bukti-container').innerHTML = `
                        <div class="flex justify-center">
                            <img src="${todayRecord.bukti_izin_sakit}" alt="Bukti ${todayRecord.ket}" class="max-w-full max-h-64 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">
                        </div>
                        <p class="text-sm text-gray-600 mt-2 text-center">Bukti ${todayRecord.ket.toUpperCase()}</p>
                    `;
                // Show edit button
                qs('#dr-user-edit-bukti-actions').classList.remove('hidden');
                qs('#dr-user-edit-bukti-btn').dataset.date = date;
                } else {
                qs('#dr-user-edit-bukti-section').classList.add('hidden');
                qs('#dr-user-edit-bukti-actions').classList.add('hidden');
                }
            }
        } else {
        qs('#dr-user-edit-bukti-section').classList.add('hidden');
        qs('#dr-user-edit-bukti-actions').classList.add('hidden');
    }
    
    qs('#dr-user-edit-modal').classList.remove('hidden'); 
    qs('#dr-user-edit-modal').classList.add('flex');
}

// Event listener untuk tombol laporan harian
document.addEventListener('click', async (e)=>{
    const target = e.target.closest('.btn-create-dr, .btn-edit-dr, .btn-view-dr');
    if(target){
        const date = target.getAttribute('data-date');
        const isView = target.classList.contains('btn-view-dr');
        const isEdit = target.classList.contains('btn-edit-dr');
        
        if (isView) {
            await openDailyReportViewModal(date);
        } else if (isEdit) {
            await openDailyReportEditModal(date);
        } else {
            // Create new report - use edit modal
            await openDailyReportEditModal(date);
        }
    }
});
// Event handlers untuk modal view laporan harian
qs('#dr-user-view-cancel') && qs('#dr-user-view-cancel').addEventListener('click', ()=>{ 
    qs('#dr-user-view-modal').classList.add('hidden'); 
    qs('#dr-user-view-modal').classList.remove('flex'); 
});

// Event handlers untuk modal edit laporan harian
qs('#dr-user-edit-cancel') && qs('#dr-user-edit-cancel').addEventListener('click', ()=>{ 
    qs('#dr-user-edit-modal').classList.add('hidden'); 
    qs('#dr-user-edit-modal').classList.remove('flex'); 
});

qs('#dr-user-edit-save') && qs('#dr-user-edit-save').addEventListener('click', async ()=>{
    const date = qs('#dr-user-edit-modal').dataset.date; 
    const content = qs('#dr-user-edit-content').value;
    const r = await api('?ajax=save_daily_report', { date, content });
    if(r.ok){ 
        showNotif('Laporan harian disimpan', true);
        qs('#dr-user-edit-modal').classList.add('hidden'); 
        qs('#dr-user-edit-modal').classList.remove('flex'); 
        
        // Refresh all components
        refreshDashboardComponents();
    } else { 
        showNotif(r.message||'Gagal simpan', false); 
    }
});

// Event handler untuk ganti bukti izin/sakit (modal edit)
qs('#dr-user-edit-bukti-btn') && qs('#dr-user-edit-bukti-btn').addEventListener('click', () => {
    const date = qs('#dr-user-edit-bukti-btn').dataset.date;
    // Open edit bukti modal
    qs('#edit-bukti-modal').classList.remove('hidden');
    qs('#edit-bukti-modal').classList.add('flex');
    qs('#edit-bukti-save').dataset.date = date;
    
    // Show current bukti if exists
    const currentImg = qs('#dr-user-edit-bukti-container img');
    if (currentImg) {
        qs('#edit-bukti-current').classList.remove('hidden');
        qs('#edit-bukti-current-img').src = currentImg.src;
    } else {
        // If no current bukti, hide current section
        qs('#edit-bukti-current').classList.add('hidden');
    }
    
    // Reset file input and preview
    qs('#edit-bukti-file').value = '';
    qs('#edit-bukti-preview').classList.add('hidden');
});

// Event handler untuk modal edit bukti
qs('#edit-bukti-cancel') && qs('#edit-bukti-cancel').addEventListener('click', () => {
    qs('#edit-bukti-modal').classList.add('hidden');
    qs('#edit-bukti-modal').classList.remove('flex');
    qs('#edit-bukti-file').value = '';
    qs('#edit-bukti-preview').classList.add('hidden');
    qs('#edit-bukti-current').classList.add('hidden');
});

qs('#edit-bukti-save') && qs('#edit-bukti-save').addEventListener('click', async () => {
    const date = qs('#edit-bukti-save').dataset.date;
    const file = qs('#edit-bukti-file').files[0];
    
    if (!file) {
        showNotif('Pilih file gambar terlebih dahulu', false);
        return;
    }
    
    // Check file size (5MB = 5 * 1024 * 1024 bytes)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotif(`File terlalu besar. Maksimal 5MB. Ukuran saat ini: ${(file.size / (1024 * 1024)).toFixed(2)}MB`, false);
        return;
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
        showNotif('File harus berupa gambar (JPG, PNG, GIF)', false);
        return;
    }
    
    // Convert file to base64
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const r = await api('?ajax=update_bukti_izin_sakit', {
                date: date,
                action_type: 'update',
                bukti: e.target.result
            });
            
            if (r.ok) {
                showNotif('Bukti berhasil diperbarui');
                qs('#edit-bukti-modal').classList.add('hidden');
                qs('#edit-bukti-modal').classList.remove('flex');
                qs('#edit-bukti-file').value = '';
                qs('#edit-bukti-preview').classList.add('hidden');
                qs('#edit-bukti-current').classList.add('hidden');
                
                // Refresh all components
                refreshDashboardComponents();
                
                // Refresh the daily report modal to show updated bukti if it's open
                const drEditModal = qs('#dr-user-edit-modal');
                if (drEditModal && !drEditModal.classList.contains('hidden')) {
                    openDailyReportEditModal(date);
                }
            } else {
                showNotif(r.message || 'Gagal memperbarui bukti', false);
            }
        } catch (error) {
            console.error('Error updating bukti:', error);
            showNotif('Terjadi kesalahan', false);
        }
    };
    reader.readAsDataURL(file);
});

// File upload preview for edit bukti modal
qs('#edit-bukti-file') && qs('#edit-bukti-file').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const preview = qs('#edit-bukti-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            qs('#edit-bukti-preview').src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

// Helper function for month names
function monthName(monthIndex) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return months[monthIndex] || '';
}

// Tambahkan state untuk paginasi di atas fungsi renderMonthly
let currentMonthlyPageYear = new Date().getFullYear();

async function renderMonthly() {
    // Load settings for max months back and end year
    let monthlyReportEndYear = 2026; // Default: 2026
    try {
        const settingsJson = await api('?ajax=get_settings', {}, { suppressModal: true, cache: true, ttl: 300000 }); // Settings can be cached longer
        if (settingsJson.ok && settingsJson.data) {
            if (settingsJson.data.max_monthly_report_months_back) {
                window.maxMonthlyReportMonthsBack = parseInt(settingsJson.data.max_monthly_report_months_back.value) || 999;
            } else {
                window.maxMonthlyReportMonthsBack = 999; // Default: no limit
            }
            if (settingsJson.data.monthly_report_end_year) {
                monthlyReportEndYear = parseInt(settingsJson.data.monthly_report_end_year.value) || 2026;
            }
        } else {
            window.maxMonthlyReportMonthsBack = 999; // Default: no limit
        }
    } catch (e) {
        window.maxMonthlyReportMonthsBack = 999; // Default: no limit on error
    }
    
    // Validate currentMonthlyPageYear - should be between 2025 and monthlyReportEndYear
    if (currentMonthlyPageYear < 2025) {
        currentMonthlyPageYear = 2025;
    }
    if (currentMonthlyPageYear > monthlyReportEndYear) {
        currentMonthlyPageYear = monthlyReportEndYear;
    }
    
    const j = await api('?ajax=get_monthly_reports', {}, { suppressModal: true, cache: false });
    const list = (j.data || []);
    const body = qs('#table-monthly-body');
    if (!body) return;
    body.innerHTML = ''; // Kosongkan tabel body

    const monthName = (m) => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][m - 1];

    const year = currentMonthlyPageYear; // Gunakan tahun dari state
    const allMonths = Array.from({ length: 12 }, (_, i) => i + 1);

    // Logic untuk aturan waktu (2 bulan terakhir)
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // 1-12

    allMonths.forEach(m => {
        // Handle case where year/month might be 0 or invalid
        let item = list.find(it => {
            const itemYear = parseInt(it.year) || 0;
            const itemMonth = parseInt(it.month) || 0;
            return itemMonth === m && itemYear === year;
        });
        
        // If no item found for this month, check if there's a record with year=0 or month=0 for this month
        if (!item && m === 8 && year === 2025) {
            item = list.find(it => {
                const itemYear = parseInt(it.year) || 0;
                const itemMonth = parseInt(it.month) || 0;
                return (itemYear === 0 || itemMonth === 0) && it.status === 'approved';
            });
        }
        
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50 text-center';
        const label = `${monthName(m)} ${year}`;

        let actionBtn;
        let statusBadge;

        // Cek apakah bulan ini valid untuk diedit/dibuat
        // Check settings for max months back (default: no limit, allow all months)
        // For now, allow all months - can be restricted via settings later
        const maxMonthsBack = window.maxMonthlyReportMonthsBack || 999; // Default: no limit
        const reportDate = new Date(year, m - 1, 1);
        const todayDate = new Date(currentYear, currentMonth - 1, 1);
        const monthsDiff = (todayDate.getFullYear() - reportDate.getFullYear()) * 12 + (todayDate.getMonth() - reportDate.getMonth());
        const isEditableTime = monthsDiff <= maxMonthsBack; // Allow all months by default

        if (item) { // Jika laporan sudah ada
            const isApproved = item.status === 'approved';
            const isDraft = item.status === 'draft';
            const isSubmitted = item.status === 'belum di approve';
            
            if (isApproved) {
                // Jika sudah di-approve, hanya bisa view (regardless of timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
            } else if (isDraft) {
                // Jika draft, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit Draft</button>`;
                }
            } else if (isSubmitted) {
                // Jika belum di approve, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit</button>`;
                }
            } else {
                // Jika disapproved, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit</button>`;
                }
            }
            
            // Status badge
            if (isApproved) {
                statusBadge = `<span class="badge badge-green">Di-approve</span>`;
            } else if (item.status === 'disapproved') {
                statusBadge = `<span class="badge badge-red">Tidak di-approve</span>`;
            } else if (isDraft) {
                statusBadge = `<span class="badge badge-gray">Draft</span>`;
            } else if (isSubmitted) {
                statusBadge = `<span class="badge badge-blue">Belum di Approve</span>`;
            } else {
                statusBadge = `<span class="badge badge-gray">${item.status}</span>`;
            }
        } else { // Jika laporan belum ada
            if (isEditableTime) {
                actionBtn = `<button class="btn-create-month bg-emerald-500 hover:bg-emerald-600 text-white btn-pill" data-year="${year}" data-month="${m}">Buat</button>`;
            } else {
                actionBtn = `<span class="text-gray-400">Not Available</span>`;
            }
            statusBadge = `<span class="badge badge-orange">Belum ada laporan</span>`;
        }

        tr.innerHTML = `
            <td class="py-2 px-4">${label}</td>
            <td class="py-2 px-4">${actionBtn}</td>
            <td class="py-2 px-4">${statusBadge}</td>`;
        body.appendChild(tr);
    });
    
    // Hapus dan buat ulang tombol paginasi - generate from 2025 to monthlyReportEndYear
    let paginationDiv = qs('#monthly-pagination');
    if (paginationDiv) paginationDiv.remove();
    
    paginationDiv = document.createElement('div');
    paginationDiv.id = 'monthly-pagination';
    paginationDiv.className = 'mt-4 flex justify-center gap-2 flex-wrap';
    
    // Generate year buttons from 2025 to monthlyReportEndYear
    const yearButtons = [];
    for (let y = 2025; y <= monthlyReportEndYear; y++) {
        yearButtons.push(`<button data-year="${y}" class="page-btn px-4 py-2 rounded ${currentMonthlyPageYear === y ? 'bg-indigo-600 text-white' : 'bg-gray-200 hover:bg-gray-300'}">${y}</button>`);
    }
    paginationDiv.innerHTML = yearButtons.join('');
    body.closest('.overflow-x-auto').insertAdjacentElement('afterend', paginationDiv);
}


async function renderAdminMonthly(){
    const mSel = qs('#am-month'); const ySel = qs('#am-year'); const sSel = qs('#am-startup');
    if(mSel && mSel.options.length<=2){
        const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        months.forEach((m,i)=>{ const o=document.createElement('option'); o.value=String(i+1); o.textContent=m; mSel.appendChild(o); });
        const yNow=new Date().getFullYear(); for(let y=yNow-2;y<=yNow+1;y++){ const o=document.createElement('option'); o.value=String(y); o.textContent=String(y); ySel.appendChild(o);}
    }
    if(sSel && sSel.options.length<=1){
        const j = await api('?ajax=get_startups', {}, { suppressModal: true, cache: true, ttl: 300000 });
        if(j.ok && j.data){
            j.data.forEach(startup => {
                const o = document.createElement('option');
                o.value = startup;
                o.textContent = startup;
                sSel.appendChild(o);
            });
        }
    }
    const body = qs('#am-body'); if(!body) return; body.innerHTML='';
    const payload = { term: qs('#am-search')?.value||'', startup: qs('#am-startup')?.value||'', month: qs('#am-month')?.value||'', year: qs('#am-year')?.value||'' };
    const r = await api('?ajax=admin_get_monthly_reports', payload);
    const j = r.data||[];
    // Filter out draft reports from admin view
    const filteredReports = j.filter(it => it.status !== 'draft');
    if(filteredReports.length===0){ body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data.</td></tr>`; return; }
    const monthName=(m)=>['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m-1];
    filteredReports.forEach(it=>{
        const tr=document.createElement('tr'); tr.className='border-b hover:bg-gray-50';
        const label = `${monthName(parseInt(it.month))} ${it.year}`;
        const detailBtn = `<button class="btn-view-month-detail text-blue-600 font-bold text-center" data-id="${it.id}"><i class="fi fi-ss-eye text-xl"></i></button>`;
        const statusBadge = it.status==='approved'? `<span class="badge badge-green">Di-approve</span>`:(it.status==='disapproved'?`<span class="badge badge-red">Tidak di-approve</span>`:`<span class="badge badge-blue">Belum di Approve</span>`);
        const actions = (it.status === 'belum di approve' || it.status === 'approved' || it.status === 'disapproved') ?
            `<button class="btn-am-approve bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded mr-1" data-id="${it.id}">Approve</button>
            <button class="btn-am-disapprove bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-id="${it.id}">Disapprove</button>` : '';

        tr.innerHTML = `
            <td class="py-2 px-4">${label}</td>
            <td class="py-2 px-4">${it.nama||''}</td>
            <td class="py-2 px-4">${it.startup||'-'}</td>
            <td class="py-2 px-4">${detailBtn}</td>
            <td class="py-2 px-4">${statusBadge}</td>
            <td class="py-2 px-4">${actions}</td>`;
        body.appendChild(tr);
    });
}

['#am-search','#am-startup','#am-month','#am-year'].forEach(sel=>{ if(qs(sel)) qs(sel).addEventListener('input', renderAdminMonthly); });
qs('#am-reset') && qs('#am-reset').addEventListener('click', ()=>{ if(qs('#am-search')) qs('#am-search').value=''; if(qs('#am-startup')) qs('#am-startup').value=''; if(qs('#am-month')) qs('#am-month').value=''; if(qs('#am-year')) qs('#am-year').value=''; renderAdminMonthly(); });

// Export event handlers (Delegated)
// Global Export Wrappers for Inline Onclick
window.openExportDailyModal = function() {
    qs('#export-presensi-modal').classList.remove('hidden');
};

window.triggerExportMonthly = function() {
    const startup = qs('#am-startup')?.value || '';
    const month = qs('#am-month')?.value || '';
    const year = qs('#am-year')?.value || '';
    const term = qs('#am-search')?.value || '';
    
    const params = new URLSearchParams({
        startup: startup,
        month: month,
        year: year,
        term: term,
        format: 'per_employee'
    });
    
    window.location.href = `/export/monthly?${params.toString()}`;
};

window.triggerExportKPI = function() {
    initKpiGlobals();
    const type = kpiFilterType ? kpiFilterType.value : 'period';
    const month = kpiFilterMonth ? kpiFilterMonth.value : '';
    const year = kpiFilterYear ? kpiFilterYear.value : '';
    
    const params = new URLSearchParams();
    params.append('filter_type', type);
    if (type === 'monthly' && month && year) {
        params.append('month', month);
        params.append('year', year);
    }
    const url = `/export/kpi?${params.toString()}`;
    console.log('Exporting KPI (Global):', url);
    window.location.href = url;
};

// Keep delegation as backup
document.addEventListener('click', (e) => {
    // Handlers kept for redundancy
});


// Modal Logic
qs('#export-p-range') && qs('#export-p-range').addEventListener('change', (e) => {
    const opts = qs('#export-p-monthly-opts');
    if (e.target.value === 'monthly') opts.classList.remove('hidden');
    else opts.classList.add('hidden');
});

qs('#export-presensi-form') && qs('#export-presensi-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const range = qs('#export-p-range').value;
    const year = qs('#export-p-year').value;
    const format = qs('input[name="export_format"]:checked').value;
    
    const selectedMonths = Array.from(document.querySelectorAll('.export-month-cb:checked')).map(cb => cb.value);
    
    const params = new URLSearchParams();
    params.append('filter_type', range);
    if(range === 'monthly') {
        if (selectedMonths.length === 0) {
            customAlert('Silakan pilih minimal satu bulan', 'Peringatan');
            return;
        }
        params.append('months', selectedMonths.join(','));
        params.append('year', year);
    }
    params.append('format', format);
    
    window.location.href = `/export/daily?${params.toString()}`;
    qs('#export-presensi-modal').classList.add('hidden');
});

// Settings functions
async function renderSettings() {
    try {
        const result = await api('?ajax=get_settings', {}, { suppressModal: true, cache: true, ttl: 300000 });
        
        if (result.ok && result.data) {
            const settings = result.data;
            
            // Format hour (e.g., "8") to "08:00" for type="time"
            const formatTime = (h) => {
                if(!h) return h;
                let hour = parseInt(h);
                if(isNaN(hour)) return h;
                return (hour < 10 ? '0' : '') + hour + ':00';
            };

            qs('#max-ontime-hour').value = formatTime(settings.max_ontime_hour?.value || '8');
            qs('#min-checkout-hour').value = formatTime(settings.min_checkout_hour?.value || '17');
            if(qs('#wfo-address')) qs('#wfo-address').value = settings.wfo_address?.value || '';
            if(qs('#wfo-radius')) qs('#wfo-radius').value = settings.wfo_radius_m?.value || '1200';
            if(qs('#attendance-period-end')) qs('#attendance-period-end').value = settings.attendance_period_end?.value || '';
            if(qs('#kpi-late-penalty')) qs('#kpi-late-penalty').value = settings.kpi_late_penalty_per_minute?.value || '1';
            if(qs('#kpi-izin-sakit')) qs('#kpi-izin-sakit').value = settings.kpi_izin_sakit_score?.value || '85';
            if(qs('#kpi-alpha')) qs('#kpi-alpha').value = settings.kpi_alpha_score?.value || '0';
            if(qs('#kpi-overtime-bonus')) qs('#kpi-overtime-bonus').value = settings.kpi_overtime_bonus?.value || '5';
            if(qs('#max-daily-report-days-back')) qs('#max-daily-report-days-back').value = settings.max_daily_report_days_back?.value || '5';
            if(qs('#max-monthly-report-months-back')) qs('#max-monthly-report-months-back').value = settings.max_monthly_report_months_back?.value || '999';
            if(qs('#monthly-report-end-year')) qs('#monthly-report-end-year').value = settings.monthly_report_end_year?.value || '2026';
            if(qs('#face-recognition-threshold')) qs('#face-recognition-threshold').value = settings.face_recognition_threshold?.value || '0.38';
            if(qs('#face-recognition-input-size')) qs('#face-recognition-input-size').value = settings.face_recognition_input_size?.value || '416';
            if(qs('#face-recognition-score-threshold')) qs('#face-recognition-score-threshold').value = settings.face_recognition_score_threshold?.value || '0.35';
            if(qs('#face-recognition-quality-threshold')) qs('#face-recognition-quality-threshold').value = settings.face_recognition_quality_threshold?.value || '0.55';
            if(qs('#geocode-timeout')) qs('#geocode-timeout').value = settings.geocode_timeout?.value || '3';
            if(qs('#geocode-accuracy-radius')) qs('#geocode-accuracy-radius').value = settings.geocode_accuracy_radius?.value || '50';
            
            // WFO API settings
            if(qs('#wfo-mode')) qs('#wfo-mode').value = settings.wfo_mode?.value || 'api';
            if(qs('#wfo-api-provider')) qs('#wfo-api-provider').value = settings.wfo_api_provider?.value || 'ipinfo';
            if(qs('#wfo-api-token')) qs('#wfo-api-token').value = settings.wfo_api_token?.value || '';
            if(qs('#wfo-api-org-keywords')) qs('#wfo-api-org-keywords').value = settings.wfo_api_org_keywords?.value || '';
            if(qs('#wfo-api-asn-list')) qs('#wfo-api-asn-list').value = settings.wfo_api_asn_list?.value || '';
            if(qs('#wfo-api-cidr-list')) qs('#wfo-api-cidr-list').value = settings.wfo_api_cidr_list?.value || '';
            if(qs('#wfo-wifi-ssids')) qs('#wfo-wifi-ssids').value = settings.wfo_wifi_ssids?.value || 'Telkom University,TelU,WiFi Telkom University';
            if(qs('#wfo-require-wifi')) qs('#wfo-require-wifi').value = settings.wfo_require_wifi?.value || '1';
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showNotif('Gagal memuat pengaturan', false);
    }
}

// Address search functionality
let addressSearchTimeout;
let selectedAddress = null;

// Initialize address search when settings page loads
function initAddressSearch() {
    const addressInput = qs('#wfo-address');
    const suggestionsDiv = qs('#address-suggestions');
    
    if (!addressInput || !suggestionsDiv) return;
    
    addressInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        if (addressSearchTimeout) {
            clearTimeout(addressSearchTimeout);
        }
        
        // Hide suggestions if query is empty
        if (query.length < 3) {
            suggestionsDiv.classList.add('hidden');
            return;
        }
        
        // Debounce search
        addressSearchTimeout = setTimeout(() => {
            searchAddresses(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!addressInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.add('hidden');
        }
    });
    
    // Handle keyboard navigation
    addressInput.addEventListener('keydown', (e) => {
        const suggestions = suggestionsDiv.querySelectorAll('.suggestion-item');
        const activeSuggestion = suggestionsDiv.querySelector('.suggestion-item.active');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeSuggestion) {
                activeSuggestion.classList.remove('active');
                const next = activeSuggestion.nextElementSibling;
                if (next) {
                    next.classList.add('active');
                } else {
                    suggestions[0]?.classList.add('active');
                }
            } else {
                suggestions[0]?.classList.add('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeSuggestion) {
                activeSuggestion.classList.remove('active');
                const prev = activeSuggestion.previousElementSibling;
                if (prev) {
                    prev.classList.add('active');
                } else {
                    suggestions[suggestions.length - 1]?.classList.add('active');
                }
            } else {
                suggestions[suggestions.length - 1]?.classList.add('active');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeSuggestion) {
                activeSuggestion.click();
            }
        } else if (e.key === 'Escape') {
            suggestionsDiv.classList.add('hidden');
        }
    });
}

async function searchAddresses(query) {
    try {
        const res = await fetch(`?ajax=search_address&q=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const json = await res.json();
        
        if (!json.ok) throw new Error(json.message || 'Search failed');
        
        let allResults = json.data || [];
        
        // If still no results, create a manual entry
        if (allResults.length === 0) {
            allResults = [{
                display_name: query,
                lat: '',
                lon: '',
                place_id: 'manual',
                type: 'manual'
            }];
        }
        
        displayAddressSuggestions(allResults.slice(0, 5)); // Limit to 5 results
        
    } catch (error) {
        console.error('Error searching addresses:', error);
        // Fallback: show a simple suggestion
        displayAddressSuggestions([{
            display_name: query,
            lat: '',
            lon: '',
            place_id: 'manual',
            type: 'manual'
        }]);
    }
}

function displayAddressSuggestions(results) {
    const suggestionsDiv = qs('#address-suggestions');
    if (!suggestionsDiv) return;
    
    if (results.length === 0) {
        suggestionsDiv.innerHTML = '<div class="p-3 text-gray-500 text-sm">Tidak ada hasil ditemukan</div>';
    } else {
        suggestionsDiv.innerHTML = results.map((result, index) => {
            const isManual = result.type === 'manual' || result.place_id === 'manual';
            const hasCoordinates = result.lat && result.lon;
            
            return `
                <div class="suggestion-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 ${index === 0 ? 'active' : ''}" 
                     data-address="${result.display_name}" 
                     data-lat="${result.lat || ''}" 
                     data-lon="${result.lon || ''}">
                    <div class="font-medium text-sm">${result.display_name}</div>
                    ${hasCoordinates ? 
                        `<div class="text-xs text-gray-500 mt-1">Koordinat: ${result.lat}, ${result.lon}</div>` : 
                        `<div class="text-xs text-orange-500 mt-1">${isManual ? 'Manual entry - koordinat akan diisi otomatis' : 'Koordinat tidak tersedia'}</div>`
                    }
                    ${isManual ? '<div class="text-xs text-blue-500 mt-1">💡 Pilih untuk menggunakan alamat ini</div>' : ''}
                </div>
            `;
        }).join('');
        
        // Add click handlers
        suggestionsDiv.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                selectAddress(item);
            });
            
            item.addEventListener('mouseenter', () => {
                suggestionsDiv.querySelectorAll('.suggestion-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });
    }
    
    suggestionsDiv.classList.remove('hidden');
}

async function selectAddress(item) {
    const address = item.dataset.address;
    let lat = item.dataset.lat;
    let lon = item.dataset.lon;
    
    // Coordinates are now provided by searchAddresses or handled server-side during save
    
    // Update input field
    const addressInput = qs('#wfo-address');
    if (addressInput) {
        addressInput.value = address;
    }
    
    // Store selected address data
    selectedAddress = {
        address: address,
        lat: lat,
        lon: lon
    };
    
    // Show selected address info
    const infoDiv = qs('#selected-address-info');
    const addressText = qs('#selected-address-text');
    const coordinatesSpan = qs('#selected-coordinates');
    
    if (infoDiv && addressText && coordinatesSpan) {
        addressText.textContent = address;
        if (lat && lon) {
            coordinatesSpan.textContent = `${lat}, ${lon}`;
        } else {
            coordinatesSpan.textContent = 'Koordinat akan diisi otomatis saat disimpan';
        }
        infoDiv.classList.remove('hidden');
    }
    
    // Hide suggestions
    const suggestionsDiv = qs('#address-suggestions');
    if (suggestionsDiv) {
        suggestionsDiv.classList.add('hidden');
    }
}

// Settings save logic moved to settings.php for isolation and robustness

qs('#reset-settings') && qs('#reset-settings').addEventListener('click', () => {
    qs('#max-ontime-hour').value = '8';
    qs('#min-checkout-hour').value = '17';
    if(qs('#kpi-late-penalty')) qs('#kpi-late-penalty').value = '1';
    if(qs('#kpi-izin-sakit')) qs('#kpi-izin-sakit').value = '85';
    if(qs('#kpi-alpha')) qs('#kpi-alpha').value = '0';
    showNotif('Pengaturan direset ke default', true);
});

// Auto-detect WFO button handler
qs('#auto-detect-wfo') && qs('#auto-detect-wfo').addEventListener('click', async () => {
    const button = qs('#auto-detect-wfo');
    const resultDiv = qs('#auto-detect-result');
    const orgDiv = qs('#detect-org');
    const asnDiv = qs('#detect-asn');
    const ipDiv = qs('#detect-ip');
    
    button.disabled = true;
    button.textContent = '🔄 Mendeteksi...';
    
    try {
        // Get current IP
        const ipResponse = await fetch('https://api.ipify.org?format=json');
        const ipData = await ipResponse.json();
        const currentIp = ipData.ip;
        
        // Get IP info using current provider setting
        const provider = qs('#wfo-api-provider')?.value || 'ipinfo';
        const token = qs('#wfo-api-token')?.value || '';
        
        let apiUrl = '';
        if (provider === 'ipinfo') {
            apiUrl = `https://ipinfo.io/${currentIp}/json${token ? `?token=${token}` : ''}`;
        } else if (provider === 'ipapi') {
            apiUrl = `https://ipapi.co/${currentIp}/json/`;
        } else {
            apiUrl = `http://ip-api.com/json/${currentIp}?fields=status,message,org,as,asname,query`;
        }
        
        const headers = {};
        if (provider === 'ipapi' && token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        
        const infoResponse = await fetch(apiUrl, { headers });
        const infoData = await infoResponse.json();
        
        // Extract organization and ASN based on provider
        let org = '';
        let asn = '';
        
        if (provider === 'ipinfo') {
            org = infoData.company?.name || infoData.org || '';
            asn = infoData.org ? infoData.org.split(' ')[0] : '';
        } else if (provider === 'ipapi') {
            org = infoData.org || infoData.company || '';
            asn = infoData.asn || infoData.as || '';
        } else {
            org = infoData.org || infoData.asname || '';
            asn = infoData.as || '';
        }
        
        // Display results with guards
        if (ipDiv) ipDiv.innerHTML = `<strong>IP:</strong> ${currentIp}`;
        if (orgDiv) orgDiv.innerHTML = `<strong>Organisasi:</strong> ${org || 'Tidak ditemukan'}`;
        if (asnDiv) asnDiv.innerHTML = `<strong>ASN:</strong> ${asn || 'Tidak ditemukan'}`;
        
        if (resultDiv) resultDiv.classList.remove('hidden');
        
        // Auto-fill if organization contains Telkom University
        if (org && org.toLowerCase().includes('telkom')) {
            const orgKeywordsEl = qs('#wfo-api-org-keywords');
            if (orgKeywordsEl) {
                const currentOrgKeywords = orgKeywordsEl.value || '';
                if (!currentOrgKeywords.includes(org)) {
                    const newKeywords = currentOrgKeywords ? `${currentOrgKeywords}, ${org}` : org;
                    orgKeywordsEl.value = newKeywords;
                    showNotif(`Organisasi "${org}" ditambahkan ke kata kunci WFO`, true);
                }
            }
        }
        
        if (asn && asn.startsWith('AS')) {
            const asnListEl = qs('#wfo-api-asn-list');
            if (asnListEl) {
                const currentAsnList = asnListEl.value || '';
                if (!currentAsnList.includes(asn)) {
                    const newAsnList = currentAsnList ? `${currentAsnList}, ${asn}` : asn;
                    asnListEl.value = newAsnList;
                    showNotif(`ASN "${asn}" ditambahkan ke daftar ASN WFO`, true);
                }
            }
        }
        
    } catch (error) {
        console.error('Error detecting WFO:', error);
        if (typeof showNotif === 'function') {
            showNotif('Gagal mendeteksi informasi IP. Periksa koneksi internet atau token API.', false);
        }
        if (resultDiv) resultDiv.classList.add('hidden');
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = 'Auto-Detect WFO dari IP Admin Saat Ini';
        }
    }
});

// Dashboard functions
// dashboardCharts is declared globally at top

function updateDashboardClock() {
    const clock = qs('#dash-realtime-clock');
    if (!clock) return;
    
    // Use server offset if available, else fallback to browser time
    const now = window.serverTimeOffset ? new Date(Date.now() + window.serverTimeOffset) : new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    clock.textContent = `${h}:${m}`;
}
setInterval(updateDashboardClock, 60000); // Update every minute

async function renderDashboard() {
    try {
        const result = await api('?ajax=get_dashboard_data', {}, { suppressModal: true, cache: true, ttl: 30000 });
        
        if (!result.ok) {
            showNotif('Gagal memuat data dashboard', false);
            return;
        }
        
        const data = result.data;
        
        // Update summary cards with guards
        const setElText = (id, text) => { const el = qs(id); if (el) el.textContent = text; };
        setElText('#totalEmployees', data.summary.total_employees);
        setElText('#presentToday', data.summary.present_today);
        setElText('#lateToday', data.summary.late_today);
        setElText('#absentToday', data.summary.absent_today);
        
        // Update daily report statistics
        if (data.daily_report_stats) {
            setElText('#employeesWithoutReports', data.daily_report_stats.employees_without_reports || 0);
            setElText('#totalMissingReports', data.daily_report_stats.total_missing_reports || 0);
            
            // Render employee list
            const employeeListDiv = qs('#daily-report-employees-list');
            if (employeeListDiv && data.daily_report_stats.employee_details) {
                const employees = data.daily_report_stats.employee_details;
                if (employees.length > 0) {
                    employeeListDiv.innerHTML = `
                        <div class="bg-white rounded-2xl p-4 border border-orange-100">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Pegawai Belum Isi Laporan (Top 10)</h4>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1 custom-scrollbar">
                                ${employees.map((emp, index) => `
                                    <div class="flex items-center justify-between p-2 hover:bg-orange-50 rounded-xl transition-all border border-transparent hover:border-orange-100">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="relative flex-shrink-0">
                                                <img src="${emp.foto_base64 || 'https://ui-avatars.com/api/?background=f97316&color=fff&name=' + encodeURIComponent(emp.nama) + '&size=80'}" 
                                                     alt="${emp.nama}" 
                                                     class="w-10 h-10 rounded-full border-2 border-white shadow-sm" style="object-fit: cover;">
                                                <div class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold">
                                                    ${index + 1}
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-800 truncate">${emp.nama}</p>
                                                <p class="text-[10px] text-gray-500">${emp.missing_count} laporan hilang</p>
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-1 rounded-lg">
                                                ${emp.missing_count}
                                            </span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                } else {
                employeeListDiv.innerHTML = `
                        <div class="bg-gray-50 rounded-2xl p-8 border border-dashed border-gray-200 text-center text-gray-400">
                            <i class="fi fi-rr-badge-check text-3xl mb-2 text-emerald-400"></i>
                            <p class="text-sm font-medium">Semua pegawai sudah mengisi laporan harian</p>
                        </div>
                    `;
                }
            }
        }
        
        // Render charts
        renderTodayLateChart(data.today_late);
        renderMonthlyPerformanceCharts(data.monthly_stats);
        renderAttendanceTrendChart(data.attendance_trend);
        
        // Initialize KPI filter options first
        initKPIFilterOptions();
        
        // Load KPI data
        if (qs('#kpi-table-body')) loadKPIData();
        
        // Update clock immediately
        updateDashboardClock();
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showNotif('Gagal memuat data dashboard', false);
    }
}

function renderTodayLateChart(todayLateData) {
    const ctx = qs('#todayLateChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (dashboardCharts.todayLate) {
        dashboardCharts.todayLate.destroy();
    }
    
    if (todayLateData.length === 0) {
        ctx.style.display = 'none';
        ctx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Tidak ada pegawai yang terlambat hari ini</div>';
        return;
    }
    
    ctx.style.display = 'block';
    
    // Create a horizontal bar chart with employee photos
    const chartContainer = ctx.parentElement;
    chartContainer.innerHTML = `
        <div class="space-y-3">
            ${todayLateData.map((item, index) => {
                const checkInTime = item.jam_masuk ? item.jam_masuk.substring(0, 5) : 'N/A';
                const delayMinutes = item.jam_masuk ? 
                    Math.max(0, (parseInt(item.jam_masuk.split(':')[0]) - 8) * 60 + parseInt(item.jam_masuk.split(':')[1])) : 0;
                
                return `
                    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="relative flex-shrink-0">
                                <img src="${item.foto_base64 || 'https://ui-avatars.com/api/?background=ef4444&color=fff&name=' + encodeURIComponent(item.nama) + '&size=128'}" 
                                     alt="${item.nama}" 
                                     class="w-12 h-12 rounded-2xl border-2 border-white shadow-sm object-cover group-hover:scale-105 transition-transform">
                                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] rounded-lg px-1.5 py-0.5 font-bold shadow-sm">
                                    #${index + 1}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-800 truncate">${item.nama}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-red-500 bg-red-50 px-2 py-0.5 rounded-lg">Terlambat</span>
                                    <span class="text-[10px] text-gray-400">${delayMinutes} menit</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 pl-4">
                            <div class="text-lg font-black text-red-600 leading-none">${checkInTime}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Check-in</div>
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderMonthlyPerformanceCharts(monthlyStats) {
    // Helper function to convert time string to seconds for comparison
    const timeToSeconds = (timeStr) => {
        if (!timeStr) return 0;
        const parts = timeStr.split(':');
        return parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + parseInt(parts[2] || 0);
    };
    
    // Most Frequently Late Chart
    const mostLateCtx = qs('#most-late-list');
    if (mostLateCtx) {
        if (dashboardCharts.mostLate) {
            dashboardCharts.mostLate.destroy();
        }
        
        // Sort by late_count DESC, then by avg_late_time DESC (most late time first)
        const sortedLate = monthlyStats
            .filter(item => item.late_count > 0)
            .sort((a, b) => {
                if (b.late_count !== a.late_count) {
                    return b.late_count - a.late_count;
                }
                // If counts are equal, sort by average late time (later time = more late)
                const timeA = timeToSeconds(a.avg_late_time);
                const timeB = timeToSeconds(b.avg_late_time);
                return timeB - timeA; // Later time (higher seconds) comes first
            });
        
        const topLate = sortedLate.slice(0, 5);
        
        if (topLate.length === 0) {
            mostLateCtx.style.display = 'none';
            mostLateCtx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Tidak ada data keterlambatan bulan ini</div>';
        } else {
            mostLateCtx.style.display = 'block';
            
            // Create bar chart with employee photos
            const lateContainer = mostLateCtx.parentElement;
            lateContainer.innerHTML = `
                <div class="grid grid-cols-1 gap-3">
                    ${topLate.map((item, index) => {
                        const maxLate = Math.max(...topLate.map(x => x.late_count));
                        const percentage = (item.late_count / maxLate) * 100;
                        
                        return `
                            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm group hover:shadow-md transition-all">
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="relative flex-shrink-0">
                                        <img src="${item.foto_base64 || 'https://ui-avatars.com/api/?background=ef4444&color=fff&name=' + encodeURIComponent(item.nama) + '&size=96'}" 
                                             alt="${item.nama}" 
                                             class="w-12 h-12 rounded-xl border-2 border-white shadow-sm object-cover group-hover:rotate-3 transition-transform">
                                        <div class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] rounded-lg w-5 h-5 flex items-center justify-center font-black shadow-sm">
                                            ${index + 1}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 truncate">${item.nama}</h4>
                                        <p class="text-[10px] text-red-500 font-bold uppercase tracking-wider">${item.late_count}x Terlambat</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <div class="text-xl font-black text-red-600 leading-none">${item.late_count}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Total</div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-red-400 to-red-600 h-full rounded-full transition-all duration-1000" 
                                         style="width: ${percentage}%"></div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }
    }
    
    // Most Attentive Chart
    const mostAttentiveCtx = qs('#most-attentive-list');
    if (mostAttentiveCtx) {
        if (dashboardCharts.mostAttentive) {
            dashboardCharts.mostAttentive.destroy();
        }
        
        // Sort by ontime_count DESC, then by avg_ontime_time ASC (earlier time = better)
        const topAttentive = monthlyStats
            .filter(item => item.ontime_count > 0)
            .sort((a, b) => {
                if (b.ontime_count !== a.ontime_count) {
                    return b.ontime_count - a.ontime_count;
                }
                // If counts are equal, sort by average ontime (earlier time = better)
                const timeA = timeToSeconds(a.avg_ontime_time) || 86400; // Default to 23:59:59 if null
                const timeB = timeToSeconds(b.avg_ontime_time) || 86400;
                return timeA - timeB; // Earlier time (lower seconds) comes first
            })
            .slice(0, 5);
        
        if (topAttentive.length === 0) {
            mostAttentiveCtx.style.display = 'none';
            mostAttentiveCtx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Tidak ada data kehadiran bulan ini</div>';
        } else {
            mostAttentiveCtx.style.display = 'block';
            
            // Create pie chart style layout with employee photos
            const attentiveContainer = mostAttentiveCtx.parentElement;
            const totalOnTime = topAttentive.reduce((sum, item) => sum + item.ontime_count, 0);
            
            attentiveContainer.innerHTML = `
                <div class="grid grid-cols-1 gap-3">
                    ${topAttentive.map((item, index) => {
                        const percentage = ((item.ontime_count / totalOnTime) * 100).toFixed(1);
                        const colors = ['#10b981', '#059669', '#047857', '#065f46', '#064e3b'];
                        
                        return `
                            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm group hover:shadow-md transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-14 h-14 rounded-full flex items-center justify-center p-1 group-hover:scale-105 transition-transform" 
                                             style="background: conic-gradient(${colors[index]} 0deg ${percentage * 3.6}deg, #f3f4f6 ${percentage * 3.6}deg 360deg)">
                                            <img src="${item.foto_base64 || 'https://ui-avatars.com/api/?background=10b981&color=fff&name=' + encodeURIComponent(item.nama) + '&size=96'}" 
                                                 alt="${item.nama}" 
                                                 class="w-11 h-11 rounded-full border-2 border-white shadow-sm object-cover">
                                        </div>
                                        <div class="absolute -top-1 -right-1 bg-emerald-500 text-white text-[10px] rounded-lg px-1.5 py-0.5 font-bold shadow-sm">
                                            #${index + 1}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 truncate">${item.nama}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg">${item.ontime_count}x On-Time</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 pr-1">
                                        <div class="text-xl font-black text-emerald-600 leading-none">${percentage}%</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Share</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }
    }
}

function renderAttendanceTrendChart(trendData) {
    const ctx = qs('#attendanceTrendChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (dashboardCharts.attendanceTrend) {
        dashboardCharts.attendanceTrend.destroy();
    }
    
    if (!trendData || trendData.length === 0) {
        ctx.style.display = 'none';
        ctx.parentElement.innerHTML = '<div class="text-center text-gray-500 py-8">Tidak ada data tren kehadiran</div>';
        return;
    }
    
    ctx.style.display = 'block';
    
    const labels = trendData.map(item => item.day);
    const presentData = trendData.map(item => item.present);
    const lateData = trendData.map(item => item.late);
    const absentData = trendData.map(item => item.absent);
    
    dashboardCharts.attendanceTrend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Kejadian On-Time',
                    data: presentData,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                },
                {
                    label: 'Kejadian Terlambat',
                    data: lateData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                },
                {
                    label: 'Kejadian Tidak Hadir',
                    data: absentData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#ffffff',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// KPI Functions
// Global KPI Data
let kpiGlobalData = null;
let kpiOverviewChart = null;

async function loadKPIData() {
    try {
        console.log('Loading KPI data...');
        
        // Get filter parameters
        const filterType = kpiFilterType ? kpiFilterType.value : 'period';
        const month = kpiFilterMonth ? kpiFilterMonth.value : '';
        const year = kpiFilterYear ? kpiFilterYear.value : '';
        
        // Build query parameters
        const params = new URLSearchParams();
        if (filterType === 'monthly' && month && year) {
            params.append('filter_type', 'monthly');
            params.append('month', month);
            params.append('year', year);
            console.log('KPI Filter: Monthly mode -', month, year);
        } else {
            params.append('filter_type', 'period');
            console.log('KPI Filter: Period mode');
        }
        
        const result = await api('?ajax=get_kpi_data', Object.fromEntries(params), { suppressModal: true, cache: true });
        
        console.log('KPI response:', result);
        
        if (!result.ok) {
            console.error('KPI API error:', result.message);
            const errorMsg = result.message || 'Gagal memuat data KPI. Silakan refresh halaman.';
            showNotif('Gagal memuat data KPI: ' + errorMsg, false);
            return;
        }
        
        if (!result.data || !result.data.kpi_data) {
            console.error('No KPI data in response');
            showNotif('Tidak ada data KPI tersedia', false);
            return;
        }
        
        // Store globally
        kpiGlobalData = result.data;
        
        console.log('KPI data loaded:', result.data.kpi_data.length, 'employees');
        
        // Render Table (Default)
        renderKPITable(result.data);
        
        // Initialize View Controls (Idempotent)
        initKPIViewControls();
        
        // If graph view is active, update it
        if (qs('#kpi-graph-view') && !qs('#kpi-graph-view').classList.contains('hidden')) {
            renderKPIOverviewChart();
        }
        
    } catch (error) {
        console.error('Error loading KPI data:', error);
        showNotif('Gagal memuat data KPI: ' + error.message, false);
    }
}

function initKPIViewControls() {
    const btnTable = qs('#view-toggle-table');
    const btnGraph = qs('#view-toggle-graph');
    const viewTable = qs('#kpi-table-view');
    const viewGraph = qs('#kpi-graph-view');
    const btnExportPDF = qs('#btn-export-kpi-pdf');
    
    if (!btnTable || !btnGraph || !viewTable || !viewGraph) return;
    
    // Remove old listeners to avoid duplicates (naive approach, assume reliable replacement)
    // Better: just overwrite onclick or use a flag. 
    // We'll use onclick for simplicity in this context or standard event listeners
    
    btnTable.onclick = () => {
        viewTable.classList.remove('hidden');
        viewGraph.classList.add('hidden');
        
        // Update button styles
        btnTable.className = 'px-4 py-2 rounded-lg text-sm font-bold bg-white text-indigo-600 shadow-sm transition-all flex items-center gap-2';
        btnGraph.className = 'px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-indigo-600 transition-all flex items-center gap-2';
    };
    
    btnGraph.onclick = () => {
        viewTable.classList.add('hidden');
        viewGraph.classList.remove('hidden');
        
        // Update button styles
        btnGraph.className = 'px-4 py-2 rounded-lg text-sm font-bold bg-white text-indigo-600 shadow-sm transition-all flex items-center gap-2';
        btnTable.className = 'px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-indigo-600 transition-all flex items-center gap-2';
        
        // Render chart
        renderKPIOverviewChart();
    };
    
    if (btnExportPDF) {
        btnExportPDF.onclick = exportKPIPDF;
    }
}

function renderKPIOverviewChart() {
    if (!kpiGlobalData || !kpiGlobalData.kpi_data) return;
    
    const ctx = qs('#kpi-overview-chart');
    if (!ctx) return;
    
    if (kpiOverviewChart) {
        kpiOverviewChart.destroy();
    }
    
    const employees = kpiGlobalData.kpi_data;
    const names = employees.map(e => e.nama);
    
    // Datasets
    // Stacking WFO, WFA, Izin, Alpha
    const wfoData = employees.map(e => e.wfo_count || 0);
    const wfaData = employees.map(e => e.wfa_count || 0);
    const izinData = employees.map(e => e.izin_sakit_count || 0);
    const alphaData = employees.map(e => e.alpha_count || 0);
    
    const maxDays = Math.max(...employees.map(e => e.total_working_days)) || 20;
    
    kpiOverviewChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: names,
            datasets: [
                {
                    label: 'WFO',
                    data: wfoData,
                    backgroundColor: '#10b981', // Emerald
                    stack: 'Stack 0'
                },
                {
                    label: 'WFA',
                    data: wfaData,
                    backgroundColor: '#06b6d4', // Cyan
                    stack: 'Stack 0'
                },
                {
                    label: 'Izin/Sakit',
                    data: izinData,
                    backgroundColor: '#eab308', // Yellow
                    stack: 'Stack 0'
                },
                {
                    label: 'Alpha',
                    data: alphaData,
                    backgroundColor: '#ef4444', // Red
                    stack: 'Stack 0'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 }
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    max: maxDays + 2,
                    title: {
                        display: true,
                        text: 'Total Hari Kerja',
                        font: { family: "'Inter', sans-serif", weight: 'bold' }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterBody: function(context) {
                            // Add extra info like Ontime/Late
                            const idx = context[0].dataIndex;
                            const emp = employees[idx];
                            return `\nDetail:\nOntime: ${emp.ontime_count}\nTerlambat: ${emp.late_count}\nOvertime: ${emp.overtime_count || 0}`;
                        }
                    }
                }
            }
        }
    });
}

async function exportKPIPDF() {
    if (!kpiGlobalData || !kpiGlobalData.kpi_data) {
        showNotif('Tidak ada data untuk diexport', false);
        return;
    }
    
    if (!window.jspdf) {
        showNotif('Library PDF belum siap, coba refresh halaman', false);
        return;
    }
    
    showNotif('Sedang men-generate PDF...', true);
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4'); // Portrait, mm, A4
    const employees = kpiGlobalData.kpi_data;
    
    // Helper to add chart to PDF
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = 800; // High res
    tempCanvas.height = 400;
    tempCanvas.style.display = 'none';
    document.body.appendChild(tempCanvas);
    
    for (let i = 0; i < employees.length; i++) {
        const emp = employees[i];
        
        if (i > 0) doc.addPage();
        
        // --- 1. Header with branding ---
        doc.setFillColor(67, 56, 202); // Indigo 700
        doc.rect(0, 0, 210, 24, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(14);
        doc.setFont(undefined, 'bold');
        doc.text('LAPORAN PERFORMANSI PEGAWAI', 15, 16);
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.text(new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }), 195, 16, { align: 'right' });

        // --- 2. Profile Section (Modern Card) ---
        doc.setFillColor(248, 250, 252); // Slate 50
        doc.setDrawColor(226, 232, 240); // Slate 200
        doc.roundedRect(15, 35, 180, 55, 3, 3, 'FD'); // Background Box

        // Photo (Left)
        let hasPhoto = false;
        if (emp.foto_base64 && emp.foto_base64.length > 100) {
             try {
                // Helper to crop image to square (prevent "gepeng"/stretching)
                const cropToSquare = (base64) => {
                    return new Promise((resolve) => {
                        const img = new Image();
                        img.onload = () => {
                            const size = Math.min(img.width, img.height);
                            const canvas = document.createElement('canvas');
                            canvas.width = size;
                            canvas.height = size;
                            const ctx = canvas.getContext('2d');
                            
                            // Center crop
                            const sx = (img.width - size) / 2;
                            const sy = (img.height - size) / 2;
                            
                            ctx.drawImage(img, sx, sy, size, size, 0, 0, size, size);
                            resolve(canvas.toDataURL('image/png'));
                        };
                        img.onerror = () => resolve(null);
                        img.src = base64;
                    });
                };
                
                // Await the cropped image
                // Note: since this is an async function inside a loop, we need to be careful. 
                // exportKPIPDF is async, so we can await.
                const croppedImg = await cropToSquare(emp.foto_base64);
                
                if (croppedImg) {
                    doc.addImage(croppedImg, 'PNG', 22, 42, 40, 40); 
                    // Draw border
                    doc.setDrawColor(203, 213, 225);
                    doc.setLineWidth(0.5);
                    doc.rect(22, 42, 40, 40); 
                    hasPhoto = true;
                }
             } catch(e) { console.error('Image err', e); }
        }
        
        if (!hasPhoto) {
             // Placeholder Avatar
             doc.setFillColor(226, 232, 240);
             doc.circle(42, 62, 20, 'F');
             doc.setTextColor(148, 163, 184);
             doc.setFontSize(8);
             doc.text('No Photo', 42, 62, { align: 'center' });
        }

        // Info Text (Middle)
        doc.setTextColor(30, 41, 59); // Slate 800
        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text(emp.nama || 'Nama Tidak Tersedia', 70, 48);
        
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(100, 116, 139); // Slate 500
        
        const labels = ['NIM / ID', 'Startup / Divisi', 'Tot. Hari Kerja'];
        const values = [
            emp.nim || emp.user_id || '-', 
            emp.startup || '-', 
            (emp.total_working_days || 0) + ' Hari'
        ];
        
        let yPos = 58;
        labels.forEach((label, idx) => {
            doc.setFont(undefined, 'normal');
            doc.setTextColor(100, 116, 139);
            doc.text(label, 70, yPos);
            
            doc.setFont(undefined, 'bold');
            doc.setTextColor(51, 65, 85);
            doc.text(`:  ${values[idx]}`, 105, yPos);
            yPos += 7;
        });

        // KPI Score Badge (Right)
        const score = emp.kpi_score;
        let scoreColor = [220, 38, 38]; // Red
        if (score >= 90) scoreColor = [22, 163, 74]; // Green
        else if (score >= 80) scoreColor = [37, 99, 235]; // Blue
        else if (score >= 70) scoreColor = [202, 138, 4]; // Yellow
        else if (score >= 60) scoreColor = [217, 119, 6]; // Orange
        
        // Circular Score
        doc.setDrawColor(...scoreColor);
        doc.setLineWidth(2);
        doc.setFillColor(255, 255, 255);
        doc.circle(170, 62, 18, 'FD');
        
        doc.setTextColor(...scoreColor);
        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text(`${score}`, 170, 64, { align: 'center' });
        doc.setFontSize(7);
        doc.text('KPI SCORE', 170, 72, { align: 'center' });
        
        doc.setTextColor(...scoreColor);
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text(getKPIStatusText(score).toUpperCase(), 170, 40, { align: 'center' });

        // --- 3. Chart Section ---
        doc.setTextColor(30, 41, 59);
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('Grafik Metrik Kehadiran', 15, 105);
        
        // Render Separated Bar Chart
        const chartCtx = tempCanvas.getContext('2d');
        chartCtx.clearRect(0, 0, tempCanvas.width, tempCanvas.height);
        chartCtx.fillStyle = '#ffffff';
        chartCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        
        const empChart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: ['WFO', 'WFA', 'Hadir (Ontime)', 'Terlambat', 'Izin/Sakit', 'Alpha'],
                datasets: [{
                    label: 'Jumlah Hari',
                    data: [
                        emp.wfo_count || 0,
                        emp.wfa_count || 0,
                        emp.ontime_count || 0,
                        emp.late_count || 0,
                        emp.izin_sakit_count || 0,
                        emp.alpha_count || 0
                    ],
                    backgroundColor: [
                        '#10b981', // WFO - Emerald
                        '#06b6d4', // WFA - Cyan
                        '#22c55e', // Ontime - Green
                        '#eab308', // Late - Yellow
                        '#3b82f6', // Izin - Blue
                        '#ef4444'  // Alpha - Red
                    ],
                    borderWidth: 0,
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                animation: false,
                responsive: false,
                plugins: { 
                    legend: { display: false },
                    datalabels: { display: true, color: 'black', anchor: 'end', align: 'top' } 
                },
                scales: {
                     x: {
                        grid: { display: false },
                        ticks: { font: { size: 14, weight: 'bold' } }
                     },
                     y: {
                        beginAtZero: true,
                        max: (emp.total_working_days || 20) + 2,
                        title: { 
                            display: true, 
                            text: 'Total Hari Kerja',
                            font: { size: 14, weight: 'bold' }
                        },
                        ticks: { font: { size: 14 } }
                     }
                }
            }
        });
        
        const chartImg = empChart.toBase64Image();
        empChart.destroy();
        doc.addImage(chartImg, 'PNG', 15, 110, 180, 90);
        
        // --- 4. Detailed Table ---
        doc.autoTable({
            startY: 210,
            margin: { left: 15, right: 15 },
            head: [['Metrik', 'Jumlah', 'Keterangan']],
            body: [
                ['Total Hari Kerja', emp.total_working_days, 'Total hari kerja dalam periode ini'],
                ['WFO (Work From Office)', emp.wfo_count, ''],
                ['WFA (Work From Anywhere)', emp.wfa_count, ''],
                ['Hadir Ontime', emp.ontime_count, 'Tepat waktu sesuai jadwal'],
                ['Terlambat', emp.late_count, `Total keterlambatan: ${emp.total_late_minutes || 0} menit`],
                ['Izin / Sakit', emp.izin_sakit_count, 'Ketidakhadiran dengan keterangan'],
                ['Alpha', emp.alpha_count, 'Ketidakhadiran tanpa keterangan'],
                ['Laporan Harian Kosong', emp.missing_daily_reports_count, 'Hari hadir tapi tidak isi laporan'],
                ['Overtime / Lembur', emp.overtime_count || 0, '']
            ],
            theme: 'grid',
            headStyles: { 
                fillColor: [79, 70, 229],
                fontSize: 10,
                fontStyle: 'bold',
                halign: 'center'
            },
            columnStyles: {
                0: { cellWidth: 80 },
                1: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
                2: { cellWidth: 'auto' }
            },
            styles: {
                fontSize: 9,
                cellPadding: 3
            },
            alternateRowStyles: {
                fillColor: [248, 250, 252]
            }
        });
        
        // Footer Number
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text(`Halaman ${i + 1} dari ${employees.length}`, 195, 290, { align: 'right' });
    }
    
    document.body.removeChild(tempCanvas);
    doc.save(`KPI_Report_Lengkap_${new Date().toISOString().slice(0,10)}.pdf`);
    
    showNotif('PDF berhasil didownload', true);
}

function renderKPITable(kpiData) {
    const tbody = qs('#kpi-table-body');
    const loading = qs('#kpi-loading');
    const empty = qs('#kpi-empty');
    const periodRange = qs('#kpi-period-range');
    
    if (!tbody || !loading || !empty || !periodRange) return;
    
    // Hide loading
    loading.style.display = 'none';
    
    // Update period range
    const filterType = kpiFilterType ? kpiFilterType.value : 'period';
    if (filterType === 'monthly') {
        const month = kpiFilterMonth ? kpiFilterMonth.value : '';
        const year = kpiFilterYear ? kpiFilterYear.value : '';
        if (month && year) {
            const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            periodRange.textContent = `${monthNames[parseInt(month)]} ${year}`;
        } else {
            periodRange.textContent = 'Pilih bulan dan tahun';
        }
    } else {
        if (kpiData.period_start && kpiData.period_end) {
            periodRange.textContent = `${kpiData.period_start} - ${kpiData.period_end}`;
        } else {
            periodRange.textContent = 'Seluruh Periode';
        }
    }
    
    // // Add note about individual employee periods
    // const periodNote = document.createElement('p');
    // periodNote.className = 'text-xs text-gray-500 mt-1';
    // periodNote.textContent = filterType === 'monthly' 
    //     ? 'Perhitungan KPI untuk bulan yang dipilih (disesuaikan dengan tanggal registrasi masing-masing pegawai)'
    //     : 'Periode perhitungan disesuaikan dengan tanggal registrasi masing-masing pegawai';
    // periodRange.parentNode.appendChild(periodNote);
    
    // if (!kpiData.kpi_data || kpiData.kpi_data.length === 0) {
    //     empty.style.display = 'block';
    //     tbody.innerHTML = '';
    //     return;
    // }
    
    // Hide empty message
    empty.style.display = 'none';
    
    // Render table rows
    tbody.innerHTML = kpiData.kpi_data.map((employee, index) => {
        const statusClass = getKPIStatusClass(employee.kpi_score);
        const statusText = getKPIStatusText(employee.kpi_score);
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-900">${index + 1}</td>
                <td class="px-4 py-3 text-gray-900 font-medium">${employee.nama}</td>
                <td class="px-4 py-3 text-center text-gray-700">${employee.total_working_days}</td>
                <td class="px-4 py-3 text-center text-green-600 font-semibold">${employee.ontime_count}</td>
                <td class="px-4 py-3 text-center text-blue-600 font-semibold">${employee.wfa_count || 0}</td>
                <td class="px-4 py-3 text-center text-red-600 font-semibold">${employee.late_count}</td>
                <td class="px-4 py-3 text-center text-yellow-600 font-semibold">${employee.izin_sakit_count}</td>
                <td class="px-4 py-3 text-center text-gray-600 font-semibold">${employee.alpha_count}</td>
                <td class="px-4 py-3 text-center text-emerald-600 font-semibold">${employee.overtime_count || 0}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded-full text-sm font-semibold ${employee.missing_daily_reports_count > 0 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'}">
                        ${employee.missing_daily_reports_count || 0}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded-full text-sm font-semibold ${statusClass}">
                        ${employee.kpi_score}%
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm ${statusClass}">${statusText}</span>
                </td>
            </tr>
        `;
    }).join('');
}

function getKPIStatusClass(score) {
    if (score >= 90) return 'bg-green-100 text-green-800';
    if (score >= 80) return 'bg-blue-100 text-blue-800';
    if (score >= 70) return 'bg-yellow-100 text-yellow-800';
    if (score >= 60) return 'bg-orange-100 text-orange-800';
    return 'bg-red-100 text-red-800';
}

function getKPIStatusText(score) {
    if (score >= 90) return 'Excellent';
    if (score >= 80) return 'Good';
    if (score >= 70) return 'Fair';
    if (score >= 60) return 'Poor';
    return 'Very Poor';
}

// KPI Handlers moved to top of app block for reliability


// KPI Filter handlers
// KPI Filter handlers
// Declarations moved to top


// Initialize month and year options
function initKPIFilterOptions() {
    if (!kpiFilterMonth || !kpiFilterYear || !kpiFilterType) {
        console.warn('KPI filter elements not found:', {
            type: !!kpiFilterType,
            month: !!kpiFilterMonth,
            year: !!kpiFilterYear
        });
        return;
    }
    
    console.log('Initializing KPI filter options...');
    
    // Hide/show based on current filter type
    const isMonthly = kpiFilterType.value === 'monthly';
    const monthlyControls = document.getElementById('kpi-monthly-controls');
    if (monthlyControls) {
        if (isMonthly) {
            monthlyControls.classList.remove('hidden');
            monthlyControls.style.display = 'flex';
        } else {
            monthlyControls.classList.add('hidden');
            monthlyControls.style.display = 'none';
        }
    }
    
    // Populate months
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    kpiFilterMonth.innerHTML = '<option value="">Pilih Bulan</option>';
    months.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = month;
        kpiFilterMonth.appendChild(option);
    });
    
    // Populate years (current year and previous 2 years)
    const currentYear = new Date().getFullYear();
    kpiFilterYear.innerHTML = '<option value="">Pilih Tahun</option>';
    for (let year = currentYear; year >= currentYear - 2; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        kpiFilterYear.appendChild(option);
    }
    
    // ===== ATTACH EVENT LISTENERS HERE (after elements are confirmed to exist) =====
    
    // Filter type change listener
    kpiFilterType.addEventListener('change', (e) => {
        const isMonthly = e.target.value === 'monthly';
        console.log('=== KPI FILTER CHANGE ===');
        console.log('Filter type changed to:', e.target.value);
        console.log('isMonthly:', isMonthly);
        
        const monthlyControls = document.getElementById('kpi-monthly-controls');
        const monthSelect = document.getElementById('kpi-filter-month');
        const yearSelect = document.getElementById('kpi-filter-year');
        
        console.log('Elements found:', {
            container: !!monthlyControls,
            month: !!monthSelect,
            year: !!yearSelect
        });
        
        if (monthlyControls) {
            // Use multiple methods to ensure visibility
            if (isMonthly) {
                monthlyControls.classList.remove('hidden');
                monthlyControls.style.display = 'flex';
                console.log('SHOWING monthly controls');
            } else {
                monthlyControls.classList.add('hidden');
                monthlyControls.style.display = 'none';
                console.log('HIDING monthly controls');
            }
            
            // Verify the change
            setTimeout(() => {
                const isHidden = monthlyControls.classList.contains('hidden');
                const displayStyle = window.getComputedStyle(monthlyControls).display;
                console.log('After toggle - Hidden class:', isHidden, 'Display style:', displayStyle);
            }, 100);
        } else {
            console.warn('Monthly controls container NOT FOUND!');
            // Fallback to individual selects
            if (monthSelect) {
                monthSelect.style.display = isMonthly ? 'block' : 'none';
            }
            if (yearSelect) {
                yearSelect.style.display = isMonthly ? 'block' : 'none';
            }
        }
        
        if (isMonthly) {
            // Set current month and year as default if empty
            const now = new Date();
            if (monthSelect && !monthSelect.value) {
                monthSelect.value = now.getMonth() + 1;
            }
            if (yearSelect && !yearSelect.value) {
                yearSelect.value = now.getFullYear();
            }
            console.log('Set default values - Month:', monthSelect?.value, 'Year:', yearSelect?.value);
        }
        
        // Reload data when filter type changes
        loadKPIData();
        console.log('=== END FILTER CHANGE ===');
    });
    
    // Month change listener
    kpiFilterMonth.addEventListener('change', () => {
        console.log('Month changed to:', kpiFilterMonth.value);
        if (kpiFilterType && kpiFilterType.value === 'monthly') {
            loadKPIData();
        }
    });
    
    // Year change listener
    kpiFilterYear.addEventListener('change', () => {
        console.log('Year changed to:', kpiFilterYear.value);
        if (kpiFilterType && kpiFilterType.value === 'monthly') {
            loadKPIData();
        }
    });
    
    console.log('KPI filter options initialized successfully');
}

document.addEventListener('click', async (e)=>{
    if(e.target.classList.contains('btn-am-approve')||e.target.classList.contains('btn-am-disapprove')){
        const id = e.target.getAttribute('data-id'); const status = e.target.classList.contains('btn-am-approve') ? 'approved' : 'disapproved';
        showConfirmModal('Yakin set status laporan bulanan?', async ()=>{ await api('?ajax=admin_set_monthly_status', { id, status }); renderAdminMonthly(); });
    }
});
<?php endif; ?>

// Tambahkan event listener untuk tombol-tombol di tabel laporan bulanan
document.addEventListener('click', async (e) => {
    const target = e.target.closest('.btn-create-month, .btn-edit-month, .btn-view-month, .page-btn');
    if (!target) return;

    if (target.classList.contains('page-btn')) {
        currentMonthlyPageYear = parseInt(target.dataset.year);
        renderMonthly();
        return;
    }

    // Tampilkan form di modal
    pageMonthlyForm.classList.remove('hidden');
    pageMonthlyForm.classList.add('flex');

    let isViewOnly = target.classList.contains('btn-view-month');
    
    let year, month, reportData = null;

    if (target.classList.contains('btn-create-month')) {
        year = parseInt(target.dataset.year);
        month = parseInt(target.dataset.month);
        qs('#monthly-form-title').textContent = `Buat Laporan Bulan ${monthName(month-1)} ${year}`;
    } else { // Edit or View
        reportData = JSON.parse(target.dataset.json.replace(/&apos;/g, "'"));
        year = parseInt(reportData.year) || 0;
        month = parseInt(reportData.month) || 0;
        qs('#monthly-form-title').textContent = (isViewOnly ? 'Lihat' : 'Edit') + ` Laporan Bulan ${monthName(month-1)} ${year}`;
    }

    // Set info pegawai di form
    qs('#pegawai-info-monthly-form').innerHTML = qs('#pegawai-info-monthly').innerHTML;
    
    // Reset dan isi form
    qs('#form-monthly-report').reset();
    qs('#table-achievements-body').innerHTML = '';
    qs('#table-obstacles-body').innerHTML = '';
    qs('#monthly-report-year').value = year;
    qs('#monthly-report-month').value = month;

    if (reportData) {
        qs('#monthly-summary').value = reportData.summary || '';
        const achievements = JSON.parse(reportData.achievements || '[]');
        const obstacles = JSON.parse(reportData.obstacles || '[]');
        achievements.forEach(addAchievementRow);
        obstacles.forEach(addObstacleRow);
    } else {
        // Tambah satu baris kosong saat membuat baru
        addAchievementRow();
        addObstacleRow();
    }
    
    // Field disabled jika view only
    const fields = qsa('#form-monthly-report input, #form-monthly-report textarea, #form-monthly-report button');
    fields.forEach(field => {
        // Jangan disable tombol kembali
        if(field.id !== 'btn-back-to-monthly-list') {
            field.disabled = isViewOnly;
        }
    });

    // Sembunyikan tombol simpan jika view only
    qs('#btn-save-draft').style.display = isViewOnly ? 'none' : 'inline-block';
    qs('button[type="submit"]', qs('#form-monthly-report')).style.display = isViewOnly ? 'none' : 'inline-block';
});

// Register Service Worker for offline functionality
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Work Schedule Modal Functions
async function openWorkScheduleModal(userId, userName) {
    const modal = qs('#work-schedule-modal');
    const userSelect = qs('#work-schedule-user');
    const form = qs('#work-schedule-form');
    const startDateInput = qs('#work-start-date');
    
    // Load members for dropdown
    const membersData = await api('?ajax=get_members&light=1&no_embeddings=1', {}, { suppressModal: true, cache: true });
    const members = membersData.data || [];
    
    // Populate user dropdown
    userSelect.innerHTML = '<option value="">Pilih pegawai...</option>';
    members.forEach(member => {
        const option = document.createElement('option');
        option.value = member.id;
        option.textContent = `${member.nama} (${member.nim})`;
        if (member.id == userId) {
            option.selected = true;
        }
        userSelect.appendChild(option);
    });
    
    // Load schedule for selected user
    if (userId) {
        await loadWorkSchedule(userId);
        form.classList.remove('hidden');
        // Preload current start date from member JSON if available
        try{
            const md = await api('?ajax=get_members&light=1&no_embeddings=1', {}, { suppressModal: true, cache: true });
            const m = (md.data||[]).find(x=>x.id==userId);
            if(m && m.created_at && startDateInput){ startDateInput.value = (m.work_start_date||m.created_at||'').slice(0,10); }
        }catch{}
    } else {
        form.classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
}

async function loadWorkSchedule(userId) {
    try {
        const response = await api('?ajax=admin_get_work_schedule', { user_id: userId });
        if (response.ok) {
            const schedule = response.data;
            renderWorkScheduleDays(schedule);
        } else {
            showNotif('Gagal memuat jadwal kerja', false);
        }
    } catch (error) {
        console.error('Error loading work schedule:', error);
        showNotif('Gagal memuat jadwal kerja', false);
    }
}

function renderWorkScheduleDays(schedule) {
    const container = qs('#work-schedule-days');
    container.innerHTML = '';
    
    const days = [
        { key: 'monday', label: 'Senin' },
        { key: 'tuesday', label: 'Selasa' },
        { key: 'wednesday', label: 'Rabu' },
        { key: 'thursday', label: 'Kamis' },
        { key: 'friday', label: 'Jumat' },
        { key: 'saturday', label: 'Sabtu' },
        { key: 'sunday', label: 'Minggu' }
    ];
    
    days.forEach(day => {
        const dayData = schedule[day.key] || {
            is_working_day: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].includes(day.key),
            start_time: '08:00:00',
            end_time: '17:00:00'
        };
        
        const row = document.createElement('div');
        row.className = 'grid grid-cols-7 gap-2 items-center p-2 border rounded';
        row.innerHTML = `
            <div class="font-medium">${day.label}</div>
            <div>
                <input type="checkbox" ${dayData.is_working_day ? 'checked' : ''} 
                       class="work-day-checkbox" data-day="${day.key}">
            </div>
            <div>
                <input type="time" value="${dayData.start_time}" 
                       class="work-start-time w-full p-1 border rounded text-sm" data-day="${day.key}">
            </div>
            <div>
                <input type="time" value="${dayData.end_time}" 
                       class="work-end-time w-full p-1 border rounded text-sm" data-day="${day.key}">
            </div>
            <div class="text-sm text-gray-600 work-duration" data-day="${day.key}">
                ${calculateDuration(dayData.start_time, dayData.end_time)}
            </div>
            <div class="text-sm">
                <span class="work-status px-2 py-1 rounded text-xs ${dayData.is_working_day ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}" data-day="${day.key}">
                    ${dayData.is_working_day ? 'Bekerja' : 'Libur'}
                </span>
            </div>
            <div>
                <button type="button" class="copy-schedule-btn text-blue-600 hover:text-blue-800 text-sm" data-day="${day.key}">
                    Copy
                </button>
            </div>
        `;
        
        container.appendChild(row);
    });
    
    // Add event listeners
    addWorkScheduleEventListeners();
}

function addWorkScheduleEventListeners() {
    // Handle checkbox changes
    qsa('.work-day-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const day = this.dataset.day;
            const statusSpan = qs(`.work-status[data-day="${day}"]`);
            const startTime = qs(`.work-start-time[data-day="${day}"]`);
            const endTime = qs(`.work-end-time[data-day="${day}"]`);
            
            if (this.checked) {
                statusSpan.textContent = 'Bekerja';
                statusSpan.className = 'work-status px-2 py-1 rounded text-xs bg-green-100 text-green-800';
                startTime.disabled = false;
                endTime.disabled = false;
            } else {
                statusSpan.textContent = 'Libur';
                statusSpan.className = 'work-status px-2 py-1 rounded text-xs bg-gray-100 text-gray-800';
                startTime.disabled = true;
                endTime.disabled = true;
            }
            updateDuration(day);
        });
    });
    
    // Handle time changes
    qsa('.work-start-time, .work-end-time').forEach(input => {
        input.addEventListener('change', function() {
            const day = this.dataset.day;
            updateDuration(day);
        });
    });
    
    // Handle copy buttons
    qsa('.copy-schedule-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const day = this.dataset.day;
            const checkbox = qs(`.work-day-checkbox[data-day="${day}"]`);
            const startTime = qs(`.work-start-time[data-day="${day}"]`);
            const endTime = qs(`.work-end-time[data-day="${day}"]`);
            
            // Copy to all other days
            qsa('.work-day-checkbox').forEach(otherCheckbox => {
                if (otherCheckbox.dataset.day !== day) {
                    otherCheckbox.checked = checkbox.checked;
                    otherCheckbox.dispatchEvent(new Event('change'));
                }
            });
            
            qsa('.work-start-time').forEach(otherStart => {
                if (otherStart.dataset.day !== day) {
                    otherStart.value = startTime.value;
                }
            });
            
            qsa('.work-end-time').forEach(otherEnd => {
                if (otherEnd.dataset.day !== day) {
                    otherEnd.value = endTime.value;
                }
            });
            
            // Update all durations
            qsa('.work-day-checkbox').forEach(cb => updateDuration(cb.dataset.day));
            
            showNotif('Jadwal berhasil disalin ke semua hari');
        });
    });
}

function updateDuration(day) {
    const startTime = qs(`.work-start-time[data-day="${day}"]`);
    const endTime = qs(`.work-end-time[data-day="${day}"]`);
    const durationSpan = qs(`.work-duration[data-day="${day}"]`);
    
    if (startTime && endTime && durationSpan) {
        durationSpan.textContent = calculateDuration(startTime.value, endTime.value);
    }
}

function calculateDuration(startTime, endTime) {
    if (!startTime || !endTime) return '0h 0m';
    
    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);
    
    if (end <= start) return '0h 0m';
    
    const diffMs = end - start;
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours}h ${minutes}m`;
}

// Work Schedule Modal Event Listeners
qs('#work-schedule-close') && qs('#work-schedule-close').addEventListener('click', () => {
    qs('#work-schedule-modal').classList.add('hidden');
});

qs('#work-schedule-cancel') && qs('#work-schedule-cancel').addEventListener('click', () => {
    qs('#work-schedule-modal').classList.add('hidden');
});

qs('#work-schedule-user') && qs('#work-schedule-user').addEventListener('change', async function() {
    const userId = this.value;
    const form = qs('#work-schedule-form');
    
    if (userId) {
        await loadWorkSchedule(userId);
        form.classList.remove('hidden');
    } else {
        form.classList.add('hidden');
    }
});

qs('#work-schedule-save') && qs('#work-schedule-save').addEventListener('click', async function() {
    const userId = qs('#work-schedule-user').value;
    
    if (!userId) {
        showNotif('Pilih pegawai terlebih dahulu', false);
        return;
    }
    
    // Collect schedule data
    const schedule = {};
    qsa('.work-day-checkbox').forEach(checkbox => {
        const day = checkbox.dataset.day;
        const startTime = qs(`.work-start-time[data-day="${day}"]`).value;
        const endTime = qs(`.work-end-time[data-day="${day}"]`).value;
        
        schedule[day] = {
            is_working_day: checkbox.checked,
            start_time: startTime,
            end_time: endTime
        };
    });
    
    try {
        const response = await api('?ajax=admin_save_work_schedule', {
            user_id: userId,
            schedule: schedule
        });
        
        if (response.ok) {
            // Save per-user work start date setting if provided
            const startDateVal = qs('#work-start-date')?.value || '';
            if(startDateVal){ await api('?ajax=save_setting', { key: `work_start_date_user_${userId}`, value: startDateVal }); }
            showNotif('Jadwal kerja berhasil disimpan');
            qs('#work-schedule-modal').classList.add('hidden');
        } else {
            showNotif(response.message || 'Gagal menyimpan jadwal kerja', false);
        }
    } catch (error) {
        console.error('Error saving work schedule:', error);
        showNotif('Gagal menyimpan jadwal kerja', false);
    }
});

// Admin Help Notifications & Requests Logic
(function() {
    const btnNotif = qs('#btn-notifications');
    const ddNotif = qs('#dropdown-notifications');
    const badgeNotif = qs('#notif-badge');
    const countNotif = qs('#notif-count');
    const itemsNotif = qs('#notif-items');

    if (btnNotif && ddNotif) {
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            ddNotif.classList.toggle('hidden');
            if (!ddNotif.classList.contains('hidden')) {
                loadAdminNotifications();
            }
        });
        document.addEventListener('click', (e) => {
            if (!btnNotif.contains(e.target) && !ddNotif.contains(e.target)) ddNotif.classList.add('hidden');
        });
    }

    async function loadAdminNotifications() {
        try {
            const res = await api('?ajax=admin_get_help_notifications', {}, { cache: false, suppressModal: true });
            if (res.ok) {
                renderNotificationDropdown(res.data);
            }
        } catch (e) { console.error('Load notifications error:', e); }
    }
    window.loadAdminNotifications = loadAdminNotifications;

    function renderNotificationDropdown(items) {
        if (!itemsNotif) return;
        
        const pending = items.filter(i => i.status === 'pending');
        if (badgeNotif) badgeNotif.classList.toggle('hidden', pending.length === 0);
        if (countNotif) countNotif.textContent = pending.length;

        if (items.length === 0) {
            itemsNotif.innerHTML = `
                <div class="p-8 text-center text-gray-400">
                    <i class="fi fi-rr-inbox text-3xl mb-2 block"></i>
                    <p class="text-xs">Tidak ada permintaan baru</p>
                </div>`;
            return;
        }

        itemsNotif.innerHTML = items.map(item => `
            <div class="p-3 hover:bg-gray-50 rounded-xl transition-all cursor-pointer group border border-transparent hover:border-indigo-100" onclick="showRequestDetail(${item.id})">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex-shrink-0 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase">
                        ${item.nama ? item.nama.charAt(0) : '?'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">${item.nama}</p>
                        <p class="text-xs text-gray-500 truncate">${getRequestTypeLabel(item.request_type)}</p>
                        <p class="text-[10px] text-gray-400 mt-1">${formatTimeAgo(item.created_at)}</p>
                    </div>
                    ${item.status === 'pending' ? '<span class="w-2 h-2 bg-indigo-500 rounded-full mt-1.5 flex-shrink-0 animate-pulse"></span>' : ''}
                </div>
            </div>
        `).join('');
    }
    window.renderNotificationDropdown = renderNotificationDropdown;

    // Polling for notifications every 30 seconds
    if (isAdmin()) {
        setInterval(loadAdminNotifications, 30000);
        loadAdminNotifications();
    }

    // Help Requests Tab Logic
    window.allHelpRequests = [];
    const tableBody = qs('#table-requests-body');
    const emptyState = qs('#requests-empty');

    async function loadAllHelpRequests() {
        if (!tableBody) return;
        try {
            const res = await api('?ajax=admin_get_all_help_requests', {}, { cache: false });
            if (res.ok) {
                window.allHelpRequests = res.data;
                renderHelpRequests();
                updateRequestStats();
            }
        } catch (e) { console.error('Load all requests error:', e); }
    }
    window.loadAllHelpRequests = loadAllHelpRequests;

    function renderHelpRequests() {
        if (!tableBody) return;
        const statusFilter = document.querySelector('.filter-req.active')?.dataset.status || 'all';
        const search = qs('#search-requests')?.value.toLowerCase() || '';

        const filtered = window.allHelpRequests.filter(i => {
            const matchesStatus = statusFilter === 'all' || i.status === statusFilter;
            const nama = (i.nama || i.user_nama || '').toLowerCase();
            const nim = (i.nim || '').toLowerCase();
            const matchesSearch = nama.includes(search) || nim.includes(search);
            return matchesStatus && matchesSearch;
        });

        if (filtered.length === 0) {
            tableBody.innerHTML = '';
            emptyState?.classList.remove('hidden');
            return;
        }

        emptyState?.classList.add('hidden');
        tableBody.innerHTML = filtered.map(i => `
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=${encodeURIComponent(i.nama)}&size=64" class="w-8 h-8 rounded-full border border-gray-100">
                        <div>
                            <p class="text-sm font-bold text-gray-800">${i.nama}</p>
                            <p class="text-xs text-gray-400 font-mono">${i.nim}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${getRequestTypeClass(i.request_type)}">
                        ${getRequestTypeLabel(i.request_type)}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-600">${formatTimestamp(i.created_at)}</p>
                    <p class="text-[10px] text-gray-400">${formatTimeAgo(i.created_at)}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="flex items-center gap-1.5 text-xs font-bold ${getStatusClass(i.status)}">
                        <i class="fi ${getStatusIcon(i.status)}"></i>
                        ${i.status.charAt(0).toUpperCase() + i.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <button onclick="showRequestDetail(${i.id})" class="p-2 hover:bg-white text-indigo-600 rounded-xl transition-all shadow-sm border border-gray-100">
                        <i class="fi fi-sr-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    window.renderHelpRequests = renderHelpRequests;

    function updateRequestStats() {
        if (!qs('#stat-pending-requests')) return;
        qs('#stat-pending-requests').textContent = window.allHelpRequests.filter(i => i.status === 'pending').length;
        qs('#stat-approved-requests').textContent = window.allHelpRequests.filter(i => i.status === 'approved').length;
        qs('#stat-disapproved-requests').textContent = window.allHelpRequests.filter(i => i.status === 'disapproved').length;
    }

    // Modal Details Logic
    window.showRequestDetail = async function(id) {
        const item = window.allHelpRequests.find(i => i.id === id) || (await fetchSingleRequest(id));
        if (!item) return;

        const modal = qs('#request-detail-modal');
        const body = qs('#request-detail-body');
        const footer = qs('#request-action-footer');

        if (!modal || !body || !footer) return;

        let content = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Informasi Pegawai</h4>
                    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-3xl">
                        <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=${encodeURIComponent(item.nama)}&size=128" class="w-16 h-16 rounded-2xl border-4 border-white shadow-sm">
                        <div>
                            <h5 class="text-lg font-bold text-gray-800">${item.nama}</h5>
                            <p class="text-indigo-600 font-mono text-sm">${item.nim}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Status Permintaan</h4>
                    <div class="p-4 rounded-3xl ${item.status === 'pending' ? 'bg-orange-50 text-orange-700' : (item.status === 'approved' || item.status === 'solved' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700')}">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <i class="fi ${getStatusIcon(item.status)}"></i>
                            <span class="uppercase tracking-wider text-sm">${getRequestStatusLabel(item.status, item.request_type)}</span>
                        </div>
                        <p class="text-xs opacity-75">${formatTimestamp(item.created_at)}</p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Detail Permintaan</h4>
                <div class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-700">Jenis Layanan</span>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase">${getRequestTypeLabel(item.request_type)}</span>
                    </div>
                    <div class="p-6 space-y-4">
                        ${renderRequestTypeDetails(item)}
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Catatan Admin</h4>
                <textarea id="admin-req-note" class="w-full p-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-sm" rows="3" placeholder="Tulis catatan persetujuan atau penolakan..." ${item.status !== 'pending' ? 'readonly' : ''}>${item.admin_note || ''}</textarea>
            </div>
        `;

        body.innerHTML = content;

        if (item.status === 'pending') {
            if (item.request_type === 'bug_report') {
                footer.innerHTML = `
                    <button id="close-detail-btn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition-all">Tutup</button>
                    <button onclick="handleRequest(${item.id}, 'disapproved')" class="flex-[1.5] py-3 bg-white border border-red-100 text-red-600 font-bold rounded-2xl hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                        <i class="fi fi-sr-cross-circle"></i> Abaikan
                    </button>
                    <button onclick="handleRequest(${item.id}, 'solved')" class="flex-[1.5] py-3 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
                        <i class="fi fi-sr-check-circle"></i> Solved
                    </button>
                `;
            } else {
                footer.innerHTML = `
                    <button id="close-detail-btn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition-all">Tutup</button>
                    <button onclick="handleRequest(${item.id}, 'disapproved')" class="flex-[1.5] py-3 bg-white border border-red-100 text-red-600 font-bold rounded-2xl hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                        <i class="fi fi-sr-cross-circle"></i> Tolak
                    </button>
                    <button onclick="handleRequest(${item.id}, 'approved')" class="flex-[1.5] py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                        <i class="fi fi-sr-check-circle"></i> Setujui
                    </button>
                `;
            }
        } else {
            footer.innerHTML = `
                <button id="close-detail-btn" class="w-full py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition-all">Tutup</button>
            `;
        }

        // Add event listener for the dynamic button
        setTimeout(() => {
            const btn = qs('#close-detail-btn');
            if (btn) {
                btn.focus();
                btn.onclick = () => modal.classList.add('hidden');
            }
        }, 10);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    async function fetchSingleRequest(id) {
        // Fallback to reload if not found
        await loadAllHelpRequests();
        return window.allHelpRequests.find(i => i.id === id);
    }

    window.handleRequest = async function(id, action) {
        const note = qs('#admin-req-note')?.value || '';
        if (action === 'disapproved' && !note.trim()) {
            showNotif('Mohon berikan catatan alasan penolakan', false);
            return;
        }

        try {
            const res = await api('?ajax=admin_handle_help_request', { id, status: action, note });
            if (res.ok) {
                showNotif(`Permintaan berhasil ${action === 'approved' ? 'disetujui' : 'ditolak'}`);
                qs('#request-detail-modal').classList.add('hidden');
                loadAllHelpRequests();
                loadAdminNotifications();
            }
        } catch (e) { showNotif('Gagal memproses permintaan', false); }
    }

    function renderRequestTypeDetails(item) {
        if (item.request_type === 'past_attendance') {
            return `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Absen</p>
                        <p class="text-sm font-bold text-gray-800">${formatDate(item.tanggal)}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Jenis Izin</p>
                        <p class="text-sm font-bold text-gray-800 uppercase">${item.jenis_izin || '-'}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Alasan</p>
                    <p class="text-sm text-gray-700 leading-relaxed">${item.alasan_izin || '-'}</p>
                </div>
                ${item.bukti_izin ? `
                    <div>
                        <p class="text-xs text-gray-400 mb-2">Bukti Pendukung</p>
                        <img src="${item.bukti_izin}" class="w-full h-48 object-cover rounded-2xl cursor-pointer hover:opacity-90 transition-opacity" onclick="showScreenshotModal('${item.bukti_izin}', 'Bukti Izin/Sakit')">
                    </div>
                ` : ''}
            `;
        } else if (item.request_type === 'late_attendance') {
            return `
                <div class="mb-4 pb-4 border-b border-gray-50">
                    <p class="text-xs text-gray-400 mb-1">Tanggal Request</p>
                    <p class="text-sm font-bold text-gray-800">${item.tanggal ? formatDate(item.tanggal) : '-'}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Jam Masuk</p>
                        <p class="text-sm font-bold text-gray-800">${item.jam_masuk ? item.jam_masuk.substring(0,5) : '-'}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Jam Pulang</p>
                        <p class="text-sm font-bold text-gray-800">${item.jam_pulang ? item.jam_pulang.substring(0,5) : '-'}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Lokasi Verifikasi</p>
                    <p class="text-xs text-gray-700 italic">${item.lokasi_presensi || '-'}</p>
                </div>
                ${item.bukti_presensi ? `
                    <div>
                        <p class="text-xs text-gray-400 mb-2">Wajah Verifikasi</p>
                        <img src="${item.bukti_presensi}" class="w-full h-48 object-cover rounded-2xl cursor-pointer hover:opacity-90 transition-opacity" onclick="showScreenshotModal('${item.bukti_presensi}', 'Verifikasi Wajah')">
                    </div>
                ` : ''}
            `;
        } else if (item.request_type === 'bug_report') {
            return `
                <div>
                    <p class="text-xs text-gray-400 mb-2">Deskripsi Bug</p>
                    <div class="bg-gray-50 p-4 rounded-2xl text-sm text-gray-700 leading-relaxed italic border-l-4 border-indigo-200">
                        "${item.bug_description || 'Tidak ada deskripsi'}"
                    </div>
                </div>
                ${item.bug_proof ? `
                    <div>
                        <p class="text-xs text-gray-400 mb-2">Bukti Visual (Screenshot)</p>
                        <img src="${item.bug_proof}" class="w-full h-48 object-cover rounded-2xl cursor-pointer hover:opacity-90 transition-opacity" onclick="showScreenshotModal('${item.bug_proof}', 'Screenshot Bug')">
                    </div>
                ` : ''}
            `;
        }
        return ``;
    }

    // Event Listeners for Tab Requests

    // Event Listeners for Tab Requests
    document.addEventListener('DOMContentLoaded', () => {
        qs('#btn-refresh-requests')?.addEventListener('click', loadAllHelpRequests);
        qs('#close-request-detail')?.addEventListener('click', () => qs('#request-detail-modal').classList.add('hidden'));
        
        qsa('.filter-req').forEach(btn => {
            btn.addEventListener('click', function() {
                qsa('.filter-req').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderHelpRequests();
            });
        });

        qs('#search-requests')?.addEventListener('input', renderHelpRequests);


    });

    // Handle profile dropdown logout with session clearing
    const logoutBtn = qs('a[href="?page=logout"]');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
             // Clear any local state if needed
             sessionStorage.removeItem('late_req_face_verified');
        });
    }

})();

    // Pegawai Notifications Logic
    (function() {
        const btnNotif = qs('#btn-pegawai-notif');
        const ddNotif = qs('#dropdown-pegawai-notif');
        const badgeNotif = qs('#notif-pegawai-badge');
        const itemsNotif = qs('#notif-pegawai-items');
        const btnMarkRead = qs('#btn-mark-all-read');

        let currentFilter = 'unread';

        if (btnNotif && ddNotif) {
            btnNotif.addEventListener('click', (e) => {
                e.stopPropagation();
                ddNotif.classList.toggle('hidden');
                if (!ddNotif.classList.contains('hidden')) {
                    loadPegawaiNotifications(currentFilter);
                }
            });
            document.addEventListener('click', (e) => {
                if (!btnNotif.contains(e.target) && !ddNotif.contains(e.target)) ddNotif.classList.add('hidden');
            });
        }

        const tabUnread = qs('#tab-notif-unread');
        const tabRead = qs('#tab-notif-read');

        if (tabUnread && tabRead) {
            tabUnread.onclick = (e) => {
                e.stopPropagation();
                currentFilter = 'unread';
                updateTabs();
                loadPegawaiNotifications('unread');
            };
            tabRead.onclick = (e) => {
                e.stopPropagation();
                currentFilter = 'read';
                updateTabs();
                loadPegawaiNotifications('read');
            };
        }

        function updateTabs() {
            if (currentFilter === 'unread') {
                tabUnread.className = 'flex-1 py-3 text-xs font-bold text-blue-600 border-b-2 border-blue-600 bg-white transition-all';
                tabRead.className = 'flex-1 py-3 text-xs font-bold text-gray-400 hover:text-gray-600 transition-all';
            } else {
                tabRead.className = 'flex-1 py-3 text-xs font-bold text-green-600 border-b-2 border-green-600 bg-white transition-all';
                tabUnread.className = 'flex-1 py-3 text-xs font-bold text-gray-400 hover:text-gray-600 transition-all';
            }
        }

        if (btnMarkRead) {
            btnMarkRead.addEventListener('click', async (e) => {
                e.stopPropagation();
                try {
                    const res = await api('?ajax=pegawai_mark_notifications_read', {}, { method: 'POST' });
                    if (res.ok) {
                        loadPegawaiNotifications(currentFilter);
                    }
                } catch (e) { console.error('Mark read error:', e); }
            });
        }

        async function loadPegawaiNotifications(filter = 'unread') {
            if (!isPegawai()) return;
            try {
                const res = await api('?ajax=pegawai_get_notifications&filter=' + filter, {}, { cache: false, suppressModal: true });
                if (res.ok) {
                    renderPegawaiNotificationDropdown(res.data);
                    // Update badge for unread only
                    if (filter === 'unread') {
                        if (badgeNotif) badgeNotif.classList.toggle('hidden', res.data.length === 0);
                    }
                }
            } catch (e) { console.error('Load pegawai notifications error:', e); }
        }
        window.loadPegawaiNotifications = loadPegawaiNotifications;

        function renderPegawaiNotificationDropdown(items) {
            if (!itemsNotif) return;
            
            if (items.length === 0) {
                itemsNotif.innerHTML = `
                    <div class="p-8 text-center text-gray-400">
                        <i class="fi fi-sr-inbox text-3xl mb-2 block"></i>
                        <p class="text-xs">Tidak ada notifikasi ${currentFilter === 'unread' ? '' : 'lama'}</p>
                    </div>`;
                return;
            }

            itemsNotif.innerHTML = items.map(item => {
                const isPositive = item.status === 'approved' || item.status === 'solved';
                const statusColor = isPositive ? 'text-emerald-600' : 'text-red-600';
                const statusIcon = isPositive ? 'fi-sr-check-circle' : 'fi-sr-cross-circle';
                const statusLabel = getRequestStatusLabel(item.status, item.request_type);
                
                return `
                <div class="p-4 hover:bg-gray-50 rounded-xl transition-all border border-transparent hover:border-blue-100 bg-white shadow-sm mb-1">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl ${isPositive ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'} flex-shrink-0 flex items-center justify-center">
                            <i class="fi ${statusIcon} text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">${getRequestTypeLabel(item.request_type)}</span>
                                <span class="text-[10px] text-gray-400">${formatTimeAgo(item.created_at)}</span>
                            </div>
                            <p class="text-sm font-bold text-gray-800">Request Anda telah <span class="${statusColor} uppercase">${statusLabel}</span></p>
                            
                            ${(item.status === 'disapproved' || item.admin_note) ? `
                                <div class="mt-2 p-2.5 bg-gray-50 rounded-lg border-l-2 ${item.status === 'disapproved' ? 'border-red-400' : 'border-emerald-400'}">
                                    <p class="text-xs text-gray-500 font-bold mb-1">Catatan Admin:</p>
                                    <p class="text-xs text-gray-600 italic">"${item.admin_note || 'Tidak ada catatan'}"</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }
        window.renderPegawaiNotificationDropdown = renderPegawaiNotificationDropdown;

        // Polling for employees
        if (isPegawai()) {
            setInterval(() => loadPegawaiNotifications(currentFilter), 30000); // 30s
            loadPegawaiNotifications('unread');
        }
    })();

// Global Helper Functions for Help Requests
function getRequestStatusLabel(status, type) {
    if (!status) return 'WAITING';
    if (type === 'bug_report' && status === 'disapproved') return 'IGNORED';
    if (status === 'solved') return 'SOLVED';
    if (status === 'approved') return 'APPROVED';
    if (status === 'disapproved') return 'REJECTED';
    return status.toUpperCase();
}
function getRequestTypeLabel(type) {
    const labels = {
        'past_attendance': 'Absen/Izin Kemarin',
        'late_attendance': 'Presensi Terlambat',
        'bug_report': 'Laporan Bug'
    };
    return labels[type] || type;
}

function getRequestTypeClass(type) {
    const classes = {
        'past_attendance': 'bg-blue-50 text-blue-600',
        'late_attendance': 'bg-purple-50 text-purple-600',
        'bug_report': 'bg-amber-50 text-amber-600'
    };
    return classes[type] || 'bg-gray-50 text-gray-600';
}

function getStatusClass(status) {
    if (status === 'pending') return 'text-orange-500';
    if (status === 'approved' || status === 'solved') return 'text-emerald-500';
    return 'text-red-500';
}

function getStatusIcon(status) {
    if (status === 'pending') return 'fi-sr-clock';
    if (status === 'approved' || status === 'solved') return 'fi-sr-check-circle';
    return 'fi-sr-cross-circle';
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const date = new Date(timestamp);
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
    return Math.floor(diff / 86400) + ' hari lalu';
}

function formatTimestamp(ts) {
    const d = new Date(ts);
    return d.toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) + ' ' + 
           d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
}

function isAdmin() {
    return window.USER_ROLE === 'admin';
}
function isPegawai() {
    return window.USER_ROLE === 'pegawai';
}

// Manual Holidays Logic
(function(){
    const tBody = document.getElementById('mh-table-body');
    if(!tBody) return; // Not on settings page or table missing

    const loadHolidays = async () => {
        tBody.innerHTML = '<tr><td colspan="3" class="p-4 text-center"><i class="fi fi-sr-spinner animate-spin"></i> Loading...</td></tr>';
        try {
            const res = await api('?ajax=get_manual_holidays');
            if(res.ok) {
                const holidays = res.data;
                if(holidays.length === 0) {
                     tBody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-gray-400">Belum ada hari libur manual</td></tr>';
                } else {
                    tBody.innerHTML = holidays.map(h => `
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 border-b border-gray-50">${new Date(h.date).toLocaleDateString('id-ID', {weekday:'long', year:'numeric', month:'long', day:'numeric'})}</td>
                            <td class="p-4 border-b border-gray-50 font-medium text-gray-800">${h.name || h.description}</td>
                            <td class="p-4 border-b border-gray-50 text-center">
                                <button onclick="deleteHoliday(${h.id})" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-all">
                                    <i class="fi fi-sr-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                }
            } else {
                 tBody.innerHTML = `<tr><td colspan="3" class="p-4 text-center text-red-500">${res.message || 'Gagal memuat data'}</td></tr>`;
            }
        } catch(e) {
             tBody.innerHTML = `<tr><td colspan="3" class="p-4 text-center text-red-500">${e.message}</td></tr>`;
        }
    };

    const addBtn = document.getElementById('add-holiday');
    if(addBtn) {
        addBtn.addEventListener('click', async () => {
             const dParams = {
                 date: document.getElementById('mh-date-input').value,
                 description: document.getElementById('mh-name-input').value
             };
             if(!dParams.date || !dParams.description) {
                 showNotif('Mohon lengkapi data', false);
                 return;
             }
             
             addBtn.innerHTML = '<i class="fi fi-sr-spinner animate-spin"></i>';
             addBtn.disabled = true;
             
             try {
                 const res = await api('?ajax=add_manual_holiday', dParams);
                 if(res.ok) {
                     showNotif('Hari libur ditambahkan');
                     document.getElementById('mh-date-input').value = '';
                     document.getElementById('mh-name-input').value = '';
                     loadHolidays();
                 } else {
                     showNotif(res.message || 'Gagal', false);
                 }
             } catch(e) { showNotif(e.message, false); }
             finally {
                 addBtn.innerHTML = '<i class="fi fi-sr-plus"></i> Tambah';
                 addBtn.disabled = false;
             }
        });
    }

    // Expose delete function to window
    window.deleteHoliday = async (id) => {
        if(!await customConfirm('Hapus hari libur ini?')) return;
        try {
            const res = await api('?ajax=delete_manual_holiday', {id});
            if(res.ok) {
                showNotif('Hari libur dihapus');
                loadHolidays();
            } else {
                showNotif(res.message || 'Gagal', false);
            }
        } catch(e) { showNotif(e.message, false); }
    };

    // Initial load
    loadHolidays();
})();

/**
 * Show a full-size image in a modal (Base64 or Path)
 */
window.showImageModal = function(src, title) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title || 'Bukti Presensi',
            imageUrl: src,
            imageAlt: 'Bukti Presensi',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Tutup',
            width: 'auto',
            imageWidth: 600,
            customClass: {
                image: 'rounded-xl shadow-lg border border-gray-100'
            }
        });
    } else {
        window.open(src, '_blank');
    }
};
</script>

<!-- Modern Layout Enhancements -->
</body>
</html>

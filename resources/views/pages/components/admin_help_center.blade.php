<?php
/**
 * Admin Help Center Component
 * Floating button and Help Center modal for employees
 */
?>

<?php if (!isAdmin()): ?>
    <!-- Floating Help Button -->
    <div id="admin-help-btn" class="fixed bottom-6 left-6 z-[60] animate-bounce-slow">
        <button class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-tr from-blue-600 to-indigo-700 rounded-full shadow-2xl flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-all group relative">
            <i class="fi fi-rr-interrogation text-2xl md:text-3xl"></i>
            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full border-2 border-white hidden" id="help-notif-dot"></span>
        </button>
    </div>

    <!-- Admin Help Modal -->
    <div id="admin-help-modal" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="help-modal-overlay"></div>
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col relative z-80 animate-fade-in-up max-h-[90vh]">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-5 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i class="fi fi-rs-headset text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Admin Help Center</h3>
                            <p class="text-xs text-blue-100">Solusi bantuan cepat untuk Anda</p>
                        </div>
                    </div>
                    <button id="close-help-modal" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                        <i class="fi fi-rr-cross-small text-xl"></i>
                    </button>
                </div>
                <!-- Tab Navigation -->
                <div class="flex gap-1 bg-white/10 rounded-2xl p-1">
                    <button id="tab-bantuan" onclick="switchHelpTab('bantuan')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all bg-white text-blue-700">
                        <i class="fi fi-rr-headset mr-1"></i> Bantuan
                    </button>
                    <button id="tab-status" onclick="switchHelpTab('status')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all text-white/80 hover:bg-white/10 relative">
                        <i class="fi fi-rr-list-check mr-1"></i> Status Request
                        <span id="status-tab-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[9px] flex items-center justify-center"></span>
                    </button>
                </div>
            </div>

            <!-- Tab: Bantuan (Chat Content) -->
            <div id="panel-bantuan" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 flex flex-col min-h-[400px]">
                <!-- Initial Message -->
                <div id="help-chat-content" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <div class="bg-indigo-600 text-white p-4 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] text-sm leading-relaxed">
                            Halo <b><?php echo explode(' ', $_SESSION['user']['nama'])[0]; ?></b>, ada yang bisa kami bantu hari ini? Silakan pilih jenis bantuan di bawah ini.
                        </div>
                        <span class="text-[10px] text-gray-400 px-1"><?php echo date('H:i'); ?></span>
                    </div>

                    <!-- Action Options -->
                    <div id="help-options" class="grid gap-2">
                        <button onclick="showHelpForm('past_attendance')" class="w-full text-left p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:border-blue-500 hover:shadow-md transition-all flex items-center gap-4 group">
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <i class="fi fi-rr-calendar-clock"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Presensi yang Terlewat</p>
                                <p class="text-[10px] text-gray-500">Izin/Sakit hari sebelumnya</p>
                            </div>
                        </button>
                        <button onclick="showHelpForm('late_attendance')" class="w-full text-left p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:border-blue-500 hover:shadow-md transition-all flex items-center gap-4 group">
                            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all">
                                <i class="fi fi-rr-clock-three"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Lupa/Kendala Presensi</p>
                                <p class="text-[10px] text-gray-500">Belum absen atau aplikasi error</p>
                            </div>
                        </button>
                        <button onclick="showHelpForm('bug_report')" class="w-full text-left p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:border-red-500 hover:shadow-md transition-all flex items-center gap-4 group">
                            <div class="w-10 h-10 bg-red-50 text-red-600 rounded-xl flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all">
                                <i class="fi fi-rr-bug"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Laporkan Masalah/Bug</p>
                                <p class="text-[10px] text-gray-500">Aplikasi tidak berjalan semestinya</p>
                            </div>
                        </button>
                    </div>

                    <!-- Dynamic Forms Container -->
                    <div id="help-form-container" class="hidden space-y-4">
                    <!-- Past Attendance Form -->
                    <div id="form-past_attendance" class="hidden bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-4">
                        <h4 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <i class="fi fi-rr-calendar-clock text-blue-600"></i> Request Izin/Sakit
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Tanggal</label>
                                <input type="date" id="past-date" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Jenis Izin</label>
                                <select id="past-type" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Alasan</label>
                                <textarea id="past-reason" rows="3" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="Tulis alasan lengkap..."></textarea>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Bukti (Foto)</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <button type="button" onclick="qs('#past-bukti-input').click()" class="flex-1 bg-blue-50 text-blue-600 p-3 rounded-xl border border-dashed border-blue-200 text-xs font-semibold hover:bg-blue-100 transition-all flex items-center justify-center gap-2">
                                        <i class="fi fi-rr-camera"></i> <span id="past-bukti-text">Pilih Foto</span>
                                    </button>
                                    <input type="file" id="past-bukti-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'past-bukti-text', 'past-bukti-data')">
                                    <input type="hidden" id="past-bukti-data">
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button onclick="cancelHelpForm()" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-all">Batal</button>
                            <button onclick="submitHelpRequest('past_attendance')" class="flex-[2] py-3 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-md">Kirim Request</button>
                        </div>
                    </div>

                    <div id="form-late_attendance" class="hidden bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-4">
                        <h4 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <i class="fi fi-rr-clock-three text-purple-600"></i> Request Lupa Presensi
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Tanggal</label>
                                <input type="date" id="late-date" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <!-- New Fields: Attendance Type & Reason -->
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Tipe Presensi</label>
                                <select id="late-attendance-type" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500" onchange="toggleLateReason()">
                                    <option value="wfo">Work From Office (WFO)</option>
                                    <option value="wfa">Work From Anywhere (WFA)</option>
                                    <option value="overtime">Lembur (Overtime)</option>
                                </select>
                            </div>
                            <div id="late-reason-container" class="hidden">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1 text-red-500">Alasan (Wajib untuk WFA/Overtime)</label>
                                <textarea id="late-reason" rows="2" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan alasan WFA atau detail Overtime..."></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Jam Masuk</label>
                                    <input type="time" id="late-jam-masuk" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                     <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Jam Pulang <span class="text-[8px] font-normal text-gray-300">(Opsional)</span></label>
                                     <input type="time" id="late-jam-pulang" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                                 </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Bukti Presensi</label>
                                <!-- FIX: Show screenshot preview when face is verified -->
                                <div id="late-verification-proof" class="hidden bg-green-50 border border-green-200 rounded-xl p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fi fi-sr-check-circle text-green-600"></i>
                                        <span class="text-xs font-bold text-green-700">Wajah Terverifikasi</span>
                                        <span id="late-verify-time" class="text-[10px] text-green-500 ml-auto"></span>
                                    </div>
                                    <img id="late-verify-screenshot" src="" class="w-full h-32 object-cover rounded-lg border border-green-300 shadow-sm" alt="Bukti Verifikasi Wajah">
                                    <p class="text-[9px] text-green-600 mt-1.5 text-center">Screenshot wajah saat verifikasi</p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="late-bukti-actions">
                                    <button onclick="startLatePresensiNow()" class="bg-indigo-600 text-white p-3 rounded-xl text-xs font-semibold hover:bg-indigo-700 transition-all flex flex-col items-center justify-center gap-1 shadow-sm">
                                        <i class="fi fi-rr-face-viewfinder text-lg"></i>
                                        <span id="late-presick-now-label">Presensi Sekarang</span>
                                    </button>
                                    <button type="button" onclick="qs('#late-bukti-input').click()" class="bg-purple-50 text-purple-600 p-3 rounded-xl border border-dashed border-purple-200 text-xs font-semibold hover:bg-purple-100 transition-all flex flex-col items-center justify-center gap-1">
                                        <i class="fi fi-rr-upload text-lg"></i>
                                        <span id="late-bukti-text">Upload Foto</span>
                                    </button>
                                    <input type="file" id="late-bukti-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'late-bukti-text', 'late-bukti-data')">
                                    <input type="hidden" id="late-bukti-data">
                                </div>
                                <p class="text-[9px] text-gray-400 italic">* Pilih 'Presensi Sekarang' untuk validasi wajah & lokasi real-time. Kosongkan jam pulang jika Anda berencana presensi pulang secara normal nanti.</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button onclick="cancelHelpForm()" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-all">Batal</button>
                            <button onclick="submitHelpRequest('late_attendance')" class="flex-[2] py-3 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md">Kirim Request</button>
                        </div>
                    </div>

                    <!-- Bug Report Form -->
                    <div id="form-bug_report" class="hidden bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-4">
                        <h4 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <i class="fi fi-rr-bug text-red-600"></i> Laporkan Masalah
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Deskripsi Masalah</label>
                                <textarea id="bug-desc" rows="4" class="w-full mt-1 p-3 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan kendala yang dialami..."></textarea>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Bukti Foto (Opsional)</label>
                                <div class="mt-1">
                                    <button type="button" onclick="qs('#bug-bukti-input').click()" class="w-full bg-red-50 text-red-600 p-3 rounded-xl border border-dashed border-red-200 text-xs font-semibold hover:bg-red-100 transition-all flex items-center justify-center gap-2">
                                        <i class="fi fi-rr-camera"></i> <span id="bug-bukti-text">Lampirkan Screenshot</span>
                                    </button>
                                    <input type="file" id="bug-bukti-input" class="hidden" accept="image/*" onchange="handleFileSelect(this, 'bug-bukti-text', 'bug-bukti-data')">
                                    <input type="hidden" id="bug-bukti-data">
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button onclick="cancelHelpForm()" class="flex-1 py-3 text-xs font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition-all">Batal</button>
                            <button onclick="submitHelpRequest('bug_report')" class="flex-[2] py-3 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-md">Laporkan</button>
                        </div>
                    </div>
                </div><!-- /#help-form-container -->
                </div><!-- /#help-chat-content -->
            </div><!-- /#panel-bantuan -->

            <!-- Tab: Status Request Panel -->
            <div id="panel-status" class="hidden flex-1 overflow-y-auto bg-gray-50 min-h-[400px]">
                <!-- Header strip -->
                <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-700">Riwayat Request Saya</span>
                    <button onclick="loadUserRequestStatus()" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <i class="fi fi-rr-refresh"></i> Refresh
                    </button>
                </div>
                <!-- List container -->
                <div id="status-request-list" class="p-4 space-y-3">
                    <div class="text-center text-gray-400 text-xs py-8">
                        <i class="fi fi-rr-spinner animate-spin text-2xl mb-2 block"></i>
                        Memuat data...
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 bg-white border-t border-gray-100 text-center">
                <p class="text-[10px] text-gray-400">Request Anda akan ditinjau oleh Administrator.</p>
            </div>
        </div>
    </div>

    <script>
        // Chat Toggle Logic
        const helpBtn = document.getElementById('admin-help-btn');
        const helpModal = document.getElementById('admin-help-modal');
        const helpClose = document.getElementById('close-help-modal');
        const helpOverlay = document.getElementById('help-modal-overlay');

        if (helpClose) helpClose.onclick = () => helpModal.classList.add('hidden');
        if (helpOverlay) helpOverlay.onclick = () => helpModal.classList.add('hidden');

        function showHelpForm(type) {
            // Ensure we're on the Bantuan tab first
            switchHelpTab('bantuan');
            
            const options = document.getElementById('help-options');
            const container = document.getElementById('help-form-container');
            if (options) options.classList.add('hidden');
            if (container) container.classList.remove('hidden');
            
            // Hide all forms first
            ['past_attendance', 'late_attendance', 'bug_report'].forEach(t => {
                const f = document.getElementById('form-' + t);
                if (f) f.classList.add('hidden');
            });
            
            // Show requested form
            const targetForm = document.getElementById('form-' + type);
            if (targetForm) {
                targetForm.classList.remove('hidden');
            } else {
                console.warn('Help form not found:', type);
                return;
            }
            
            // Reset forms
            if (type === 'late_attendance') {
                // Reset new fields
                qs('#late-attendance-type').value = 'wfo';
                qs('#late-reason').value = '';
                toggleLateReason();

                const pending = sessionStorage.getItem('late_req_pending');
                if (pending) {
                    const data = JSON.parse(pending);
                    qs('#late-date').value = data.tanggal;
                    qs('#late-jam-masuk').value = data.jam_masuk;
                    qs('#late-jam-pulang').value = data.jam_pulang;
                }
                
                // FIX: Show screenshot evidence if face was just verified
                const faceVerifiedData = sessionStorage.getItem('late_req_face_verified');
                const proofSection = qs('#late-verification-proof');
                const verifyImg = qs('#late-verify-screenshot');
                const verifyTime = qs('#late-verify-time');
                const lateText = qs('#late-bukti-text');
                const lateNowLabel = qs('#late-presick-now-label');
                
                if (faceVerifiedData) {
                    const verifiedData = JSON.parse(faceVerifiedData);
                    
                    // Show the verification proof section with screenshot
                    if (proofSection) proofSection.classList.remove('hidden');
                    if (verifyImg && verifiedData.screenshot) {
                        verifyImg.src = verifiedData.screenshot;
                    }
                    if (verifyTime && verifiedData.timestamp) {
                        const ts = new Date(verifiedData.timestamp);
                        verifyTime.textContent = ts.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                    }
                    
                    // Update button label to show it's been verified
                    if (lateText) { lateText.textContent = '\u2705 Wajah Terverifikasi'; }
                    if (lateNowLabel) { lateNowLabel.textContent = '\uD83D\uDD04 Verifikasi Ulang'; }
                    
                    // Auto-fill lokasi if available
                    if (verifiedData.lokasi && qs('#late-lokasi-verified')) {
                        qs('#late-lokasi-verified').textContent = verifiedData.lokasi;
                    }
                } else {
                    // Not yet verified - hide proof section
                    if (proofSection) proofSection.classList.add('hidden');
                    if (verifyImg) verifyImg.src = '';
                    if (lateText) lateText.textContent = 'Upload Foto';
                    if (lateNowLabel) lateNowLabel.textContent = 'Presensi Sekarang';
                }
            }
        }

        function cancelHelpForm() {
            document.getElementById('help-options').classList.remove('hidden');
            document.getElementById('help-form-container').classList.add('hidden');
        }

        async function handleFileSelect(input, labelId, hiddenId) {
            const label = document.getElementById(labelId);
            const hidden = document.getElementById(hiddenId);
            const file = input.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                showNotif('Ukuran file terlalu besar. Maksimal 2MB.', false);
                input.value = '';
                return;
            }

            label.textContent = "⌛ Memproses...";
            const reader = new FileReader();
            reader.onload = function(e) {
                hidden.value = e.target.result;
                label.textContent = "✅ " + file.name.substring(0, 15) + "...";
            };
            reader.readAsDataURL(file);
        }

        function toggleLateReason() {
            const type = document.getElementById('late-attendance-type').value;
            const container = document.getElementById('late-reason-container');
            if (type === 'wfa' || type === 'overtime') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        async function submitHelpRequest(type) {
            let data = { ajax: 'submit_help_request', request_type: type };

            if (type === 'past_attendance') {
                data.tanggal = qs('#past-date').value;
                data.jenis_izin = qs('#past-type').value;
                data.alasan_izin = qs('#past-reason').value;
                data.bukti_izin = qs('#past-bukti-data').value;
                if (!data.tanggal || !data.alasan_izin || !data.bukti_izin) return showNotif('Lengkapi semua field!', false);
            } else if (type === 'late_attendance') {
                data.tanggal = qs('#late-date').value;
                data.jam_masuk = qs('#late-jam-masuk').value;
                data.jam_pulang = qs('#late-jam-pulang').value;
                
                // New Fields
                data.attendance_type = qs('#late-attendance-type').value;
                data.attendance_reason = qs('#late-reason').value;

                if ((data.attendance_type === 'wfa' || data.attendance_type === 'overtime') && !data.attendance_reason) {
                    return showNotif('Wajib mengisi alasan untuk ' + data.attendance_type.toUpperCase(), false);
                }

                // Priority for Bukti: Session-based face verification first
                const faceVerified = sessionStorage.getItem('late_req_face_verified');
                if (faceVerified) {
                    const verifiedData = JSON.parse(faceVerified);
                    data.bukti_presensi = verifiedData.screenshot;
                    data.lokasi_presensi = verifiedData.lokasi;
                } else {
                    data.bukti_presensi = qs('#late-bukti-data').value;
                    data.lokasi_presensi = "Upload Bukti Manual";
                }
                
                if (!data.tanggal || !data.jam_masuk || !data.bukti_presensi) return showNotif('Lengkapi tanggal, jam masuk, dan bukti wajah!', false);
                if (!data.jam_pulang) data.jam_pulang = null; // Ensure null if empty
            } else if (type === 'bug_report') {
                data.bug_description = qs('#bug-desc').value;
                data.bug_proof = qs('#bug-bukti-data').value;
                if (!data.bug_description) return showNotif('Deskripsi bug wajib diisi!', false);
            }

            try {
                const res = await api('?ajax=submit_help_request', data, { method: 'POST' });
                if (res.ok) {
                    showNotif('✅ ' + res.message, true);
                    // Reset form
                    cancelHelpForm();
                    
                    // Clear late req session
                    sessionStorage.removeItem('late_req_pending');
                    sessionStorage.removeItem('late_req_face_verified');
                    sessionStorage.removeItem('late_req_redirected');
                    
                    // FIX: Instead of closing modal, switch to Status tab so user
                    // can immediately see their request with Pending status
                    setTimeout(() => switchHelpTab('status'), 300);
                } else {
                    showNotif(res.message, false);
                }
            } catch (e) {
                showNotif('Gagal mengirim request: ' + e.message, false);
            }
        }

        function addChatMessage(msg) {
            const chat = document.getElementById('help-chat-content');
            if (!chat) return;
            const div = document.createElement('div');
            div.className = "flex flex-col gap-1 items-end";
            div.innerHTML = `
                <div class="bg-blue-50 text-gray-700 p-4 rounded-2xl rounded-tr-none shadow-sm max-w-[85%] text-xs italic">
                    ${msg}
                </div>
                <span class="text-[9px] text-gray-400 px-1">${new Date().getHours()}:${String(new Date().getMinutes()).padStart(2,'0')}</span>
            `;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        // =====================
        // TAB SWITCHING
        // =====================
        function switchHelpTab(tab) {
            const tabs = ['bantuan', 'status'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t);
                const panel = document.getElementById('panel-' + t);
                if (t === tab) {
                    btn?.classList.add('bg-white', 'text-blue-700');
                    btn?.classList.remove('text-white/80');
                    panel?.classList.remove('hidden');
                } else {
                    btn?.classList.remove('bg-white', 'text-blue-700');
                    btn?.classList.add('text-white/80');
                    panel?.classList.add('hidden');
                }
            });
            // Auto-load status when switching to status tab
            if (tab === 'status') {
                loadUserRequestStatus();
                markHelpRequestsAsRead();
            }
        }

        // =====================
        // LOAD REQUEST STATUS
        // =====================
        async function loadUserRequestStatus() {
            const container = document.getElementById('status-request-list');
            if (!container) return;
            container.innerHTML = '<div class="text-center text-gray-400 text-xs py-8"><i class="fi fi-rr-spinner animate-spin text-2xl mb-2 block"></i>Memuat data...</div>';
            try {
                const res = await api('?ajax=get_user_help_requests', {}, { suppressModal: true, cache: false });
                if (!res.ok || !res.data || res.data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-400 text-xs py-10"><i class="fi fi-rr-inbox text-4xl mb-3 block opacity-40"></i><p class="font-semibold">Belum ada request</p><p class="mt-1 opacity-70">Request yang Anda kirim akan muncul di sini</p></div>';
                    return;
                }
                let unreviewedCount = 0;
                container.innerHTML = res.data.map(r => {
                    const statusBadge = renderStatusBadge(r.status);
                    const typeLabel = {
                        'past_attendance': '<i class="fi fi-rr-calendar-clock text-blue-500"></i> Presensi Terlewat',
                        'late_attendance': '<i class="fi fi-rr-clock-three text-purple-500"></i> Lupa Presensi',
                        'bug_report': '<i class="fi fi-rr-bug text-red-500"></i> Laporan Bug',
                    }[r.request_type] || r.request_type;
                    const dateStr = r.tanggal ? `<span class="text-gray-400">📅 ${r.tanggal}</span>` :
                                   (r.created_at ? `<span class="text-gray-400">📅 ${r.created_at.slice(0,10)}</span>` : '');
                    let summary = '';
                    if (r.request_type === 'past_attendance') summary = (r.jenis_izin || '') + (r.alasan_izin ? ': ' + r.alasan_izin.slice(0,60) : '');
                    if (r.request_type === 'late_attendance') summary = (r.jam_masuk ? 'Jam masuk: ' + r.jam_masuk.slice(0,5) : '') + (r.attendance_type ? ' (' + r.attendance_type.toUpperCase() + ')' : '');
                    if (r.request_type === 'bug_report') summary = (r.bug_description || '').slice(0, 80);

                    const adminNote = (r.admin_note && r.status !== 'pending') ?
                        `<div class="mt-2 p-2.5 bg-gray-50 rounded-lg border-l-2 ${r.status === 'approved' || r.status === 'solved' ? 'border-green-400' : 'border-red-400'}">
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Catatan Admin</p>
                            <p class="text-xs text-gray-700">${r.admin_note}</p>
                        </div>` : '';

                    if (r.status !== 'pending') unreviewedCount++;

                    return `<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="text-xs font-bold text-gray-700 flex items-center gap-1.5">${typeLabel}</div>
                            ${statusBadge}
                        </div>
                        <div class="flex items-center gap-3 text-[10px]">${dateStr}</div>
                        ${summary ? `<p class="text-xs text-gray-500 leading-relaxed">${summary}</p>` : ''}
                        ${adminNote}
                    </div>`;
                }).join('');

                // Update notification dot
                checkNotifDot(unreviewedCount > 0);
            } catch(e) {
                container.innerHTML = '<div class="text-center text-red-400 text-xs py-8">Gagal memuat data. <button onclick="loadUserRequestStatus()" class="underline">Coba lagi</button></div>';
            }
        }

        async function markHelpRequestsAsRead() {
            try {
                const res = await api('?ajax=mark_help_requests_read', {}, { suppressModal: true });
                if (res.ok) {
                    checkNotifDot(false);
                }
            } catch (e) {
                console.error('Error marking as read:', e);
            }
        }

        function renderStatusBadge(status) {
            const map = {
                'pending':      { cls: 'bg-yellow-100 text-yellow-700 border border-yellow-200', icon: '⏳', label: 'Menunggu' },
                'approved':     { cls: 'bg-green-100 text-green-700 border border-green-200',   icon: '✅', label: 'Disetujui' },
                'disapproved':  { cls: 'bg-red-100 text-red-700 border border-red-200',         icon: '❌', label: 'Ditolak' },
                'solved':       { cls: 'bg-blue-100 text-blue-700 border border-blue-200',      icon: '✔️', label: 'Selesai' },
            };
            const s = map[status] || { cls: 'bg-gray-100 text-gray-600', icon: '❓', label: status };
            return `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold ${s.cls} whitespace-nowrap">${s.icon} ${s.label}</span>`;
        }

        function checkNotifDot(hasNew) {
            const dot = document.getElementById('help-notif-dot');
            const badge = document.getElementById('status-tab-badge');
            if (dot) dot.classList.toggle('hidden', !hasNew);
            if (badge) {
                badge.classList.toggle('hidden', !hasNew);
                if (hasNew) {
                    badge.innerHTML = '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>';
                }
            }
        }

        // Poll for status updates every 60 seconds when modal is open
        let statusPollInterval = null;
        if (helpBtn) {
            helpBtn.onclick = () => {
                helpModal.classList.remove('hidden');
                checkSessionRedirect();
                // Start polling
                if (!statusPollInterval) {
                    statusPollInterval = setInterval(() => {
                        // Silently check for badge updates
                        api('?ajax=get_user_help_requests', {}, { suppressModal: true, cache: false })
                            .then(res => {
                                if (res.ok && res.data) {
                                    const hasUnread = res.data.some(r => r.status !== 'pending' && (!r.is_read_by_user || r.is_read_by_user == '0'));
                                    checkNotifDot(hasUnread);
                                }
                            }).catch(() => {});
                    }, 60000);
                }
            };
        }

        function startLatePresensiNow() {
            const tanggal = qs('#late-date').value;
            const jm = qs('#late-jam-masuk').value;
            const jp = qs('#late-jam-pulang').value;
            
            if (!tanggal || !jm) {
                showNotif('Tolong isi tanggal dan jam masuk terlebih dahulu!', false);
                return;
            }

            // Save state to sessionStorage before redirect
            sessionStorage.setItem('late_req_pending', JSON.stringify({
                tanggal: tanggal,
                jam_masuk: jm,
                jam_pulang: jp
            }));
            sessionStorage.setItem('late_req_redirected', 'true');
            
            // Redirect to presensi with special mode
            window.location.href = '?page=presensi-masuk&mode=late_req';
        }

        function checkSessionRedirect() {
            // FIX: Check if we just came back from face verification
            // and keep the modal OPEN, showing the form with screenshot evidence
            if (sessionStorage.getItem('late_req_face_verified')) {
                // Open the modal first
                helpModal.classList.remove('hidden');
                // Then show the late_attendance form with verification data
                showHelpForm('late_attendance');
                showNotif('✅ Wajah berhasil diverifikasi! Silakan kirim request.', true);
                // Don't remove late_req_face_verified here - it's used in showHelpForm
            } else if (sessionStorage.getItem('late_req_redirected')) {
                // If redirected but didn't finish, still show the form
                helpModal.classList.remove('hidden');
                showHelpForm('late_attendance');
                sessionStorage.removeItem('late_req_redirected');
            }
        }

        // FIX: Auto-open modal on page load after returning from face verification.
        // Must be called AFTER all function definitions above.
        // Using DOMContentLoaded if DOM not yet ready, or immediate call if already ready.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                checkSessionRedirect();
                // Also silently check for reviewed requests to show notification dot
                api('?ajax=get_user_help_requests', {}, { suppressModal: true, cache: false })
                    .then(res => {
                        if (res.ok && res.data) {
                            const hasUnread = res.data.some(r => r.status !== 'pending' && (!r.is_read_by_user || r.is_read_by_user == '0'));
                            checkNotifDot(hasUnread);
                        }
                    }).catch(() => {});
            });
        } else {
            // DOM already ready (script runs after DOM parsed)
            checkSessionRedirect();
            // Silently check for reviewed requests to show notification dot on load
            api('?ajax=get_user_help_requests', {}, { suppressModal: true, cache: false })
                .then(res => {
                    if (res.ok && res.data) {
                        const hasUnread = res.data.some(r => r.status !== 'pending' && (!r.is_read_by_user || r.is_read_by_user == '0'));
                        checkNotifDot(hasUnread);
                    }
                }).catch(() => {});
        }
    </script>
    <style>
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
            50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 2s infinite;
        }
        .z-80 { z-index: 80; }
    </style>
<?php endif; ?>

<div class="min-h-[80vh] flex flex-col items-center justify-center p-4 animate-fade-in-up">
    <div class="relative w-full max-w-7xl bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-gray-100">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-4 -mt-4 z-0"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-50 rounded-tr-full -ml-4 -mb-4 z-0"></div>

        <div class="relative z-10">
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <div class="w-full">
                <div class="flex items-center justify-between mb-8 lg:col-span-2">
                     <button onclick="window.history.back()" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 transition-colors">
                         <i class="fi fi-sr-arrow-small-left text-xl"></i>
                         <span class="font-medium">Kembali</span>
                     </button>
                     <h2 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600 tracking-tight">
                         <?php echo ($_GET['page'] === 'presensi-masuk') ? 'Presensi Masuk' : 'Presensi Pulang'; ?>
                     </h2>
                </div>
    
                <!-- Video Container -->
                <div id="video-container" class="relative rounded-2xl overflow-hidden aspect-video bg-black shadow-inner group w-full">
                    <video id="video" class="w-full h-full object-contain opacity-90 group-hover:opacity-100 transition-opacity mirror-video" autoplay muted playsinline></video>
                    <canvas id="overlay" class="absolute inset-0 w-full h-full"></canvas>
                    
                    <!-- Scanner UI Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="w-64 h-64 border-2 border-white/30 rounded-full relative animate-pulse opacity-50">
                            <!-- Helper guide -->
                        </div>
                    </div>
    
                    <div id="loading-overlay" class="absolute inset-0 bg-black/90 flex flex-col items-center justify-center text-white z-20 hidden">
                        <i class="fi fi-sr-spinner animate-spin text-4xl mb-3 text-blue-500"></i>
                        <p class="font-medium tracking-wide">Memuat Kamera...</p>
                    </div>
                </div>

                <!-- Hidden controls required by attendance.js -->
                <div id="scan-buttons" class="hidden">
                     <button id="btn-scan-masuk">Scan Masuk</button>
                     <button id="btn-scan-pulang">Scan Pulang</button>
                </div>
                
                <!-- Manual control buttons -->
                <div class="mt-6 flex justify-center gap-4">
                    <button id="btn-stop-detection" onclick="window.history.back()" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3 px-6 rounded-xl transition-all shadow-sm flex items-center gap-2">
                        <i class="fi fi-sr-cross-circle"></i> Batal
                    </button>
                    <button id="btn-retry-scan" class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold py-3 px-6 rounded-xl transition-all shadow-sm flex items-center gap-2 hidden">
                        <i class="fi fi-sr-refresh"></i> Coba Lagi
                    </button>
                </div>

                <!-- Status Message -->
                <div id="presensi-status" class="mt-6 hidden p-4 rounded-xl text-center font-bold animate-fade-in-up border border-transparent"></div>
                
                <!-- Next Scan Button (Visible after success/pause) -->
                <div id="next-scan-container" class="mt-6 flex justify-center hidden">
                    <button id="btn-next-scan" onclick="resumeDetection()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-8 rounded-2xl transition-all shadow-lg flex items-center gap-3 animate-bounce">
                        <i class="fi fi-sr-user-add text-xl"></i>
                        <span>Scan Berikutnya</span>
                    </button>
                </div>
                
                <p class="text-center text-gray-400 text-sm mt-4">Pastikan wajah Anda berada tepat di tengah area.</p>
            </div>

            <div class="w-full bg-gray-50 rounded-2xl p-4 max-h-[500px] overflow-y-auto">
                <!-- Log Masuk Container -->
                <div id="log-masuk-container" class="hidden animate-fade-in-up">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b flex items-center gap-2">
                        <i class="fi fi-sr-time-past text-blue-500"></i> Log Presensi Masuk Hari Ini
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-white text-gray-600 font-semibold sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg text-center">#</th>
                                    <th class="py-3 px-4">Nama</th>
                                    <th class="py-3 px-4 text-center">Jam</th>
                                    <th class="py-3 px-4 text-center">Lokasi</th>
                                    <th class="py-3 px-4 text-center" title="Visualisasi 68 titik landmark wajah sebagai bukti presensi">
                                        Bukti Landmark
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="log-masuk-body" class="text-gray-600">
                                <!-- Data will be populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
    
                <!-- Log Pulang Container -->
                <div id="log-pulang-container" class="hidden animate-fade-in-up">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b flex items-center gap-2">
                         <i class="fi fi-sr-time-check text-green-500"></i> Log Presensi Pulang Hari Ini
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-white text-gray-600 font-semibold sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg text-center">#</th>
                                    <th class="py-3 px-4">Nama</th>
                                    <th class="py-3 px-4 text-center">Jam</th>
                                    <th class="py-3 px-4 text-center">Lokasi</th>
                                    <th class="py-3 px-4 text-center" title="Visualisasi 68 titik landmark wajah sebagai bukti presensi">
                                        Bukti Landmark
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="log-pulang-body" class="text-gray-600">
                                <!-- Data will be populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-presensi-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl animate-fade-in-up border border-indigo-50 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50 rounded-full"></div>
        
        <div class="relative z-10">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 rounded-2xl text-indigo-600 text-3xl mb-4">
                    <i class="fi fi-sr-shield-check"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Konfirmasi Presensi</h3>
                <p class="text-gray-500">Mohon verifikasi data Anda di bawah ini</p>
            </div>

            <!-- FIX: Screenshot preview section - shows captured face photo before confirmation -->
            <div id="confirm-screenshot-section" class="mb-4 hidden">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fi fi-sr-camera text-indigo-500 text-sm"></i>
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Bukti Foto</span>
                </div>
                <div class="relative rounded-xl overflow-hidden border-2 border-indigo-100 shadow-sm">
                    <img id="confirm-screenshot-img" src="" class="w-full max-h-[300px] object-contain" alt="Screenshot Presensi">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white text-[10px] text-center">Foto terambil otomatis saat wajah terdeteksi</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4 bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-8">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Nama Pegawai</span>
                    <span id="confirm-nama" class="text-lg font-bold text-gray-800">Memuat...</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">NIM / ID</span>
                    <span id="confirm-nim" class="font-medium text-gray-600">Memuat...</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Lokasi Terdeteksi</span>
                    <div class="flex items-start gap-2">
                        <i class="fi fi-sr-marker text-red-500 mt-1"></i>
                        <span id="confirm-lokasi" class="text-sm font-medium text-gray-600 leading-relaxed italic">Mencari lokasi...</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button id="btn-confirm-yes" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg flex items-center justify-center gap-2">
                    <i class="fi fi-sr-check"></i>
                    <span>Ya, Benar</span>
                </button>
                <button id="btn-confirm-no" class="bg-white hover:bg-red-50 text-gray-500 hover:text-red-600 font-bold py-4 px-6 rounded-2xl transition-all border border-gray-200 hover:border-red-200 flex items-center justify-center gap-2">
                    <i class="fi fi-sr-refresh"></i>
                    <span>Ulangi Scan</span>
                </button>
            </div>
        </div>
    </div>
</div>



<!-- WFA Reason Modal -->
<div id="wfa-reason-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl animate-fade-in-up">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-100 rounded-2xl text-orange-600 text-3xl mb-4">
                <i class="fi fi-sr-home-location-alt"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Alasan WFA</h3>
            <p class="text-gray-500 mt-2">Anda terdeteksi di luar area kantor. Harap berikan alasan Anda.</p>
        </div>
        <div class="mb-6">
            <textarea id="wfa-reason-input" class="w-full h-32 p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none" placeholder="Contoh: Bekerja dari rumah / Sedang ada meeting di luar"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <button id="wfa-reason-submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg flex items-center justify-center gap-2">
                <i class="fi fi-sr-paper-plane"></i>
                <span>Simpan</span>
            </button>
            <button id="wfa-reason-cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 px-6 rounded-2xl transition-all flex items-center justify-center gap-2">
                <span>Batal</span>
            </button>
        </div>
    </div>
</div>

<!-- Overtime Reason Modal -->
<div id="overtime-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl animate-fade-in-up">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-purple-100 rounded-2xl text-purple-600 text-3xl mb-4">
                <i class="fi fi-sr-clock-three"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Alasan Overtime</h3>
            <p class="text-gray-500 mt-2">Presensi di hari libur/weekend dianggap overtime.</p>
        </div>
        <div class="space-y-4 mb-6">
            <input id="overtime-reason-input" type="text" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Tujuan lembur / Keterangan tugas">
            <input id="overtime-location-input" type="text" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Lokasi lembur">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <button id="overtime-submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg">Simpan</button>
            <button id="overtime-cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 px-6 rounded-2xl transition-all">Batal</button>
        </div>
    </div>
</div>

<!-- Early Leave Modal -->
<div id="early-leave-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl animate-fade-in-up">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 rounded-2xl text-amber-600 text-3xl mb-4">
                <i class="fi fi-sr-exit"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Pulang Awal</h3>
            <p class="text-gray-500 mt-2">Anda melakukan presensi pulang sebelum waktunya.</p>
        </div>
        <div class="mb-6">
            <textarea id="early-leave-input" class="w-full h-32 p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none" placeholder="Alasan pulang mendahului jadwal..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <button id="early-leave-submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg">Simpan</button>
            <button id="early-leave-cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 px-6 rounded-2xl transition-all">Batal</button>
        </div>
    </div>
</div>

<!-- Diff Location Modal -->
<div id="diff-location-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl animate-fade-in-up">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-rose-100 rounded-2xl text-rose-600 text-3xl mb-4">
                <i class="fi fi-sr-map-marker-slash"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Lokasi Berbeda</h3>
            <p class="text-gray-500 mt-2">Lokasi pulang Anda terdeteksi jauh dari lokasi masuk. Harap berikan alasan.</p>
        </div>
        <div class="mb-6">
            <textarea id="diff-location-reason-input" class="w-full h-32 p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500 outline-none" placeholder="Alasan lokasi pulang berbeda..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <button id="diff-location-submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg">Simpan</button>
            <button id="diff-location-cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 px-6 rounded-2xl transition-all">Batal</button>
        </div>
    </div>
</div>

<script src="assets/js/attendance.js"></script>

<script>

document.addEventListener('DOMContentLoaded', () => {
    const page = new URLSearchParams(window.location.search).get('page');
    // Short delay to ensure libraries load
    setTimeout(() => {
        if(typeof startScan === 'function') {
            if(page === 'presensi-masuk') startScan('masuk');
            if(page === 'presensi-pulang') startScan('pulang');
        } else {
            console.error('startScan function not found. Ensure attendance.js is loaded.');
        }
    }, 500);
});
</script>

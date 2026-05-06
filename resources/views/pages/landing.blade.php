    <?php
    if (isset($_SESSION['user'])) {
        header('Location: ?page=dashboard');
        exit;
    }
    ?>
    
    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-panel bg-white/80 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-blue-500 font-outfit tracking-tight">SPBW<span class="text-blue-600">.PRO</span></span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="?page=login" class="text-gray-600 hover:text-blue-600 font-medium transition-colors px-4 py-2">Masuk</a>
                    <a href="?page=register" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-full font-medium transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transform hover:-translate-y-0.5">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen pt-24 pb-12 px-4 relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400/20 rounded-full blur-[100px] -z-10"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-400/20 rounded-full blur-[100px] -z-10"></div>

        <div id="page-presensi" class="max-w-7xl mx-auto">
            
            <!-- Video Container Overlay (Modal-like behavior when active) -->
            <div id="video-container" class="hidden fixed inset-0 z-[60] bg-black/95 flex items-center justify-center p-4 !max-w-full !w-full !h-full !m-0 !rounded-none">
                <div class="relative w-full max-w-2xl aspect-[4/3] bg-gray-900 rounded-2xl overflow-hidden shadow-2xl border border-gray-800">
                    <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="w-full h-full absolute inset-0"></canvas>
                    
                    <!-- Scanner UI Overlay -->
                    <div class="absolute inset-0 border-[3px] border-blue-500/30 rounded-2xl pointer-events-none"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 border-2 border-blue-400 rounded-full opacity-50 animate-pulse pointer-events-none"></div>
                    
                    <!-- Controls -->
                    <div class="absolute top-6 left-6 right-6 flex justify-between items-center z-20">
                        <button id="btn-back-scan" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white px-4 py-2 rounded-lg transition-all flex items-center gap-2 border border-white/10">
                            <i class="fi fi-ss-arrow-left"></i> Kembali
                        </button>
                        <div class="bg-black/50 backdrop-blur-md text-white px-4 py-2 rounded-full text-sm font-medium border border-white/10" id="scan-status-badge">
                            Pendeteksi Wajah Aktif
                        </div>
                    </div>

                    <!-- Hidden functionality retention -->
                    <button id="btn-stop-detection" class="hidden">Stop</button>
                    <button id="btn-start-detection" class="hidden">Start</button>
                </div>
                <!-- Status Message Toast -->
                <div id="presensi-status" class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-white text-gray-800 px-6 py-3 rounded-full font-medium shadow-xl hidden z-70 animate-fade-in-up"></div>
            </div>

            <!-- Hero Section -->
            <div id="landing-hero" class="text-center py-12 lg:py-20 max-w-4xl mx-auto animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-8">
                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                    Sistem Presensi Wajah V2.0
                </div>
                
                <h1 class="text-5xl lg:text-7xl font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
                    Presensi <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Cepat</span>,<br>
                    Laporan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Akurat</span>.
                </h1>
                
                <p class="text-xl text-gray-500 mb-12 max-w-2xl mx-auto leading-relaxed">
                    Solusi presensi berbasis wajah dengan anti-spoofing, deteksi lokasi otomatis, dan integrasi laporan real-time.
                </p>

                <!-- Action Cards -->
                <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto px-4">

                    <!-- Masuk Card -->
                    <a href="?page=presensi-masuk" class="group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-xl shadow-blue-900/5 hover:shadow-2xl hover:shadow-blue-900/10 hover:-translate-y-1 transition-all duration-300 text-center cursor-pointer overflow-hidden block">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-20 h-20 mx-auto bg-green-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Presensi Masuk</h3>
                            <p class="text-gray-500 text-sm">Scan wajah untuk mulai bekerja</p>
                        </div>
                    </a>

                    <!-- Pulang Card -->
                    <a href="?page=presensi-pulang" class="group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-xl shadow-blue-900/5 hover:shadow-2xl hover:shadow-blue-900/10 hover:-translate-y-1 transition-all duration-300 text-center cursor-pointer overflow-hidden block">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative z-10">
                            <div class="w-20 h-20 mx-auto bg-red-50 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 003 3h6a3 3 0 003-3V7a3 3 0 00-3-3h-6a3 3 0 00-3 3v1"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Presensi Pulang</h3>
                            <p class="text-gray-500 text-sm">Scan wajah untuk selesai bekerja</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Stats/Employee Section -->
            <div id="landing-daily-report-stats" class="mt-8 mx-auto max-w-5xl px-4 animate-fade-in-up delay-200">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Pegawai Yang Belum Isi Laporan Harian</h3>
                                <p class="text-gray-500 text-sm">Data diperbaharui secara real-time</p>
                            </div>
                        </div>
                    </div>
                    
                    <div id="landing-daily-report-employees-list" class="p-6">
                        <div id="landing-employees-list-container" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto custom-scrollbar">
                            <div class="text-center py-12 text-gray-400 col-span-full">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-200 border-t-blue-600"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden Logs Container (Preserved for existing JS logic) -->
            <div class="hidden">
                 <div id="log-masuk-container">
                    <tbody id="log-masuk-body"></tbody>
                 </div>
                 <div id="log-pulang-container">
                    <tbody id="log-pulang-body"></tbody>
                 </div>
            </div>

            <!-- Floating Help Button -->
            <button onclick="document.getElementById('help-modal').classList.remove('hidden')" class="fixed bottom-8 right-8 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center justify-center transition-transform hover:scale-110 z-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </button>

            <!-- Help Modal -->
            <div id="help-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-fade-in-up">
                    <div class="sticky top-0 z-10 p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/95 backdrop-blur">
                        <h3 class="text-xl font-bold flex items-center gap-2 text-indigo-900">
                            <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            Saran Solusi Gagal Presensi
                        </h3>
                        <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-2 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 font-medium">Jika Anda mengalami kendala saat melakukan presensi masuk/pulang, ikuti langkah demi langkah berikut:</p>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">1</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Gunakan Koneksi Aman (HTTPS)</h4>
                                    <p class="text-sm text-gray-600">Pastikan URL web presensi diawali dengan <code>https://</code> (ada logo gembok), bukan <code>http://</code>. Kamera dan lokasi tidak akan bisa diakses jika web tidak aman.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">2</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Hapus Cache Browser</h4>
                                    <p class="text-sm text-gray-600">Buka pengaturan browser Anda, lalu lakukan <span class="font-medium text-gray-800">Clear Browsing Data / Cache</span> untuk menghapus file error yang tersimpan.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">3</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Izinkan Akses Kamera & Lokasi</h4>
                                    <p class="text-sm text-gray-600">Sistem butuh izin. Klik ikon gembok/pengaturan di kiri atas URL browser, lalu pastikan opsi Kamera (Camera) dan Lokasi (Location) dipastikan berada di pilihan <strong>Allow (Izinkan)</strong>.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">4</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Matikan VPN & Aplikasi Fake GPS</h4>
                                    <p class="text-sm text-gray-600">Matikan koneksi VPN, Proxy, Cloudflare Warp, atau aplikasi pemalsu lokasi. Sistem memiliki proteksi anti-spoofing yang ketat dan akan menolak absen otomatis jika terdeteksi.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">5</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Cek Stabilitas Internet</h4>
                                    <p class="text-sm text-gray-600">Gunakan koneksi internet yang stabil. Hindari berganti jaringan (misal dari Wi-Fi ke Kuota Seluler) tepat saat halaman memuat face recognition karena dapat menyebabkan freeze.</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center shrink-0">6</div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Gunakan Browser Utama</h4>
                                    <p class="text-sm text-gray-600">Gunakan Google Chrome atau Safari versi terbaru. <strong>Jangan gunakan in-app browser</strong> (membuka URL langsung dari dalam chat WhatsApp, LINE, atau Instagram) karena banyak fiturnya yang dibatasi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
             <p class="text-gray-400 text-sm">© 2024 Sistem Presensi Berbasis Wajah. Built for Excellence.</p>
        </div>
    </footer>


<div id="page-settings" class="hidden animate-fade-in-up">
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight mb-2">Pengaturan Sistem</h2>
        <p class="text-sm sm:text-base text-gray-500">Kelola konfigurasi sistem dan preferensi akun. Klik pada setiap bagian untuk membuka/menutup form pengaturan.</p>
    </div>

    <style>
        .settings-accordion {
            transition: all 0.3s ease;
        }
        .settings-accordion[open] .accordion-icon {
            transform: rotate(180deg);
        }
        .settings-accordion summary {
            cursor: pointer;
            list-style: none;
        }
        .settings-accordion summary::-webkit-details-marker {
            display: none;
        }
        .settings-accordion .accordion-content {
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <form id="settings-form" class="space-y-4">
        <!-- Pengaturan Jam Presensi -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fi fi-sr-clock text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Pengaturan Jam Presensi</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Jam masuk dan pulang kerja</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Jam Maksimal On Time</label>
                        <input type="time" id="max-ontime-hour" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm" value="08:00">
                        <p class="text-xs text-gray-500 mt-1">Pegawai yang masuk setelah jam ini dianggap terlambat</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Jam Minimal Check Out</label>
                        <input type="time" id="min-checkout-hour" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm" value="17:00">
                        <p class="text-xs text-gray-500 mt-1">Pegawai tidak bisa pulang sebelum jam ini</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs text-gray-600 mb-1 font-medium">Jam Pulang Default (Bulk Fix)</label>
                    <input type="time" id="default-checkout-time" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm" value="17:00">
                    <p class="text-xs text-gray-500 mt-1">Jam pulang yang digunakan saat admin melakukan Bulk Fix data kosong</p>
                </div>
            </div>
        </details>

        <!-- Pengaturan WFO (Lokasi / API) -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <i class="fi fi-sr-marker text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Pengaturan WFO (Lokasi / API)</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Pilih mode deteksi WFO (Koordinat GPS / API Jaringan) dan konfigurasi parameternya</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4 space-y-4">
                
                <!-- Mode Deteksi WFO (Selalu Tampil) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Mode Deteksi WFO</label>
                    <select id="wfo-mode" onchange="toggleWfoFields()" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 text-sm font-semibold">
                        <option value="api">API (Deteksi via IP/ASN/Organisasi Jaringan)</option>
                        <option value="gps">GPS (Deteksi via Koordinat Lokasi)</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">
                        <strong>Mode API:</strong> Verifikasi presensi WFO berdasarkan koneksi internet (IP Publik, ISP/Organisasi, ASN, WiFi). Menghindari masalah akurasi GPS atau fake GPS.<br>
                        <strong>Mode GPS:</strong> Verifikasi presensi WFO berdasarkan koordinat latitude & longitude perangkat pegawai dengan radius tertentu dari pusat kantor.
                    </p>
                </div>

                <!-- Periode Selesai (Selalu Tampil) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Periode Selesai Absen</label>
                    <input type="date" id="attendance-period-end" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 text-sm" value="2026-12-31">
                    <p class="text-[11px] text-gray-500 mt-1">Batas tanggal akhir periode di mana sistem presensi aktif berjalan. Pengisian harus berformat tanggal YYYY-MM-DD. <em>Contoh: 2026-12-31</em></p>
                </div>

                <!-- Container Pengaturan Lokasi / GPS -->
                <div id="wfo-gps-fields" class="space-y-4 border-t border-gray-100 pt-4 hidden">
                    <h4 class="text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Konfigurasi Mode Lokasi / GPS</h4>
                    
                    <div class="relative">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Pusat WFO</label>
                        <textarea id="wfo-address" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 text-sm" placeholder="Masukkan alamat lengkap kantor..."></textarea>
                        <div id="address-suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 max-h-60 overflow-y-auto"></div>
                        <p class="text-[11px] text-gray-500 mt-1">
                            Alamat fisik pusat kantor untuk presensi WFO. Sistem akan mencari koordinat latitude/longitude secara otomatis berdasarkan alamat yang Anda ketik (pilih saran alamat yang muncul).<br>
                            <em>Contoh: Fakultas Ilmu Terapan, Jalan Sukabirus, Sukapura, Dayeuhkolot, Kabupaten Bandung, Jawa Barat 40257</em>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Radius WFO (Meter) <span class="text-indigo-500 font-bold">maks 2000m</span></label>
                        <input type="number" id="wfo-radius" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 text-sm" value="1200" max="2000" min="10">
                        <p class="text-[11px] text-gray-500 mt-1">Jarak radius batas toleransi presensi dari koordinat kantor pusat dalam satuan meter. Untuk area luas seperti kampus, disarankan 500m - 1500m. <em>Contoh: 1200</em></p>
                    </div>
                </div>

                <!-- Container Pengaturan API Jaringan -->
                <div id="wfo-api-fields" class="space-y-4 border-t border-gray-100 pt-4 hidden">
                    <h4 class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-2">Konfigurasi Mode API Jaringan Internet</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Provider IP API</label>
                            <select id="wfo-api-provider" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="ipinfo">IPInfo.io</option>
                                <option value="ipapi">IPAPI.co</option>
                                <option value="ip-api">IP-API.com</option>
                            </select>
                            <p class="text-[11px] text-gray-500 mt-1">Penyedia API penelusuran IP publik perangkat pegawai. Sangat direkomendasikan memilih <strong>IPInfo.io</strong> karena kestabilan datanya. <em>Contoh: IPInfo.io</em></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Token API (Opsional)</label>
                            <input type="text" id="wfo-api-token" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-mono" placeholder="Token API Anda...">
                            <p class="text-[11px] text-gray-500 mt-1">Token otentikasi dari provider terpilih untuk kuota request yang lebih besar atau detail yang lebih lengkap. Kosongkan jika menggunakan paket gratis bawaan. <em>Contoh: 9a8b7c6d5e4f3g</em></p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Kunci Organisasi WFO</label>
                        <textarea id="wfo-api-org-keywords" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-mono" placeholder="Telkom University, Yayasan Pendidikan Telkom"></textarea>
                        <p class="text-[11px] text-gray-500 mt-1">Kata kunci nama perusahaan/kampus ISP/penyedia internet kantor yang dideteksi oleh API jaringan. Pisahkan dengan tanda koma jika ada beberapa. <em>Contoh: Telkom University, Yayasan Pendidikan Telkom, Telkom University Bandung</em></p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Daftar ASN WFO</label>
                            <textarea id="wfo-api-asn-list" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-mono" placeholder="AS133357"></textarea>
                            <p class="text-[11px] text-gray-500 mt-1">Daftar Autonomous System Number (ASN) jaringan internet kantor yang valid untuk presensi. Pisahkan dengan koma. <em>Contoh: AS133357, AS17974</em></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Daftar CIDR WFO</label>
                            <textarea id="wfo-api-cidr-list" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-mono" placeholder="103.23.44.0/22"></textarea>
                            <p class="text-[11px] text-gray-500 mt-1">Daftar blok/range IP CIDR khusus jaringan kantor (apabila kantor memiliki range IP publik statis sendiri). Pisahkan dengan koma. <em>Contoh: 103.23.44.0/22, 202.158.3.0/24</em></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">SSID WiFi WFO</label>
                            <input type="text" id="wfo-wifi-ssids" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Nama-Nama WiFi kantor...">
                            <p class="text-[11px] text-gray-500 mt-1">Nama (SSID) jaringan WiFi kantor yang valid. Pegawai harus terhubung ke salah satu WiFi ini. Pisahkan nama dengan tanda koma. <em>Contoh: Telkom University, WiFi-TelU, Eduroam</em></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Wajib Menggunakan WiFi Kantor</label>
                            <select id="wfo-require-wifi" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="1">Ya (Harus terhubung ke SSID WiFi di atas)</option>
                                <option value="0">Tidak (Boleh menggunakan jaringan kabel/seluler selagi IP terdeteksi sebagai WFO)</option>
                            </select>
                            <p class="text-[11px] text-gray-500 mt-1">Jika diset ke "Ya", pegawai wajib melakukan presensi sambil terhubung ke salah satu SSID WiFi kantor yang terdaftar di samping.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-bold text-gray-700">Deteksi Jaringan IP Anda Saat Ini</h5>
                                <p class="text-[10px] text-gray-500">Gunakan tombol ini untuk mendeteksi informasi jaringan Anda saat ini secara otomatis.</p>
                            </div>
                            <button type="button" id="auto-detect-wfo" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-xs">
                                <i class="fi fi-sr-refresh"></i> Auto-Detect Jaringan
                            </button>
                        </div>
                        <div id="auto-detect-result" class="hidden animate-fade-in p-3 bg-white rounded-lg border border-gray-200 space-y-1">
                            <p id="detect-ip" class="text-xs text-indigo-700 font-mono"></p>
                            <p id="detect-org" class="text-xs text-indigo-700 font-mono"></p>
                            <p id="detect-asn" class="text-xs text-indigo-700 font-mono"></p>
                        </div>
                    </div>
                </div>
            </div>
        </details>

        <!-- Pengaturan KPI -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <i class="fi fi-sr-chart-line-up text-emerald-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Pengaturan KPI Absen</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Nilai dan perhitungan KPI</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Penalti Terlambat (%)</label>
                        <input type="number" id="kpi-late-penalty" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" value="1" step="0.1">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Nilai Izin/Sakit (%)</label>
                        <input type="number" id="kpi-izin-sakit" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" value="85">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Nilai Alpha (%)</label>
                        <input type="number" id="kpi-alpha" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" value="0">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Bonus Overtime (%)</label>
                        <input type="number" id="kpi-overtime-bonus" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" value="10">
                    </div>
                </div>
            </div>
        </details>

        <!-- Pengaturan Laporan -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                        <i class="fi fi-sr-document text-orange-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Pengaturan Laporan</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Batas waktu pengisian laporan</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Maks Hari Laporan Harian</label>
                        <input type="number" id="max-daily-report-days-back" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 text-sm" value="5">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Maks Bulan Laporan Bulanan</label>
                        <input type="number" id="max-monthly-report-months-back" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 text-sm" value="999">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Tahun Awal Laporan</label>
                        <input type="number" id="monthly-report-end-year" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 text-sm" value="2026">
                    </div>
                </div>
            </div>
        </details>

        <!-- Pengaturan Face Recognition -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                        <i class="fi fi-sr-face-viewfinder text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Face Recognition</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Threshold dan akurasi pengenalan wajah</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Threshold Recognition</label>
                        <input type="number" id="face-recognition-threshold" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm" value="0.58" step="0.01" min="0" max="1">
                        <p class="text-[10px] text-gray-500 mt-1">Batas kemiripan wajah (Makin kecil = makin ketat/sulit dikenali). Standar: 0.40 - 0.50.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Minimum Keyakinan Wajah (%)</label>
                        <input type="number" id="face-recognition-min-confidence" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm" value="65" step="1" min="50" max="100">
                        <p class="text-[10px] text-gray-500 mt-1">Persentase keyakinan/kemiripan minimum agar presensi diterima (default: 65%). Di bawah nilai ini akan ditolak.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Ukuran Input</label>
                        <input type="number" id="face-recognition-input-size" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm" value="416">
                        <p class="text-[10px] text-gray-500 mt-1">Ukuran skala deteksi. (Contoh: 320, 416). Makin besar = jarak wajah bisa lebih jauh.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Score Threshold</label>
                        <input type="number" id="face-recognition-score-threshold" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm" value="0.35" step="0.01" min="0" max="1">
                        <p class="text-[10px] text-gray-500 mt-1">Skor keyakinan AI adanya wajah di kamera. Standar: 0.30 - 0.50.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Quality Threshold</label>
                        <input type="number" id="face-recognition-quality-threshold" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm" value="0.65" step="0.01" min="0" max="1">
                        <p class="text-[10px] text-gray-500 mt-1">Kualitas posisi &amp; cahaya. (Makin besar = harus terang dan di tengah). Standar: 0.20.</p>
                    </div>
                </div>
            </div>
        </details>

        <!-- Pengaturan Geocode -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fi fi-sr-map-marker text-red-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Geocode & Lokasi</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Pengaturan GPS dan reverse geocoding</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Timeout Geocoding (detik)</label>
                        <input type="number" id="geocode-timeout" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 text-sm" value="3">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1 font-medium">Radius Akurasi GPS (meter)</label>
                        <input type="number" id="geocode-accuracy-radius" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 text-sm" value="50">
                    </div>
                </div>
            </div>
        </details>

        <!-- Pengaturan Help Center -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center">
                        <i class="fi fi-sr-headset text-sky-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Admin Help Center & WA</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Nomor dan pesan WhatsApp Bantuan</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4 space-y-4">
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-medium">Nomor WhatsApp Admin</label>
                    <input type="text" id="help-wa-number" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 text-sm font-mono" placeholder="Contoh: 6287890004465">
                    <p class="text-xs text-gray-500 mt-1">Gunakan kode negara (62 untuk Indonesia) tanpa tanda + atau angka 0 di depan.</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1 font-medium">Pesan Default WhatsApp</label>
                    <textarea id="help-wa-message" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 text-sm" placeholder="Contoh: Hai Admin, Saya ingin meminta bantuan terkait ...."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Teks awal otomatis yang muncul saat pegawai membuka chat WA admin.</p>
                </div>
            </div>
        </details>

        <!-- Kelola Hari Libur -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center">
                        <i class="fi fi-sr-calendar-clock text-pink-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Kelola Hari Libur</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Tambah dan kelola hari libur manual</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input type="date" id="mh-date-input" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 text-sm">
                        <input type="text" id="mh-name-input" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 text-sm w-full" placeholder="Nama Libur">
                        <button type="button" id="add-holiday" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-sm">
                            <i class="fi fi-sr-plus"></i> Tambah
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-100 max-h-60 overflow-y-auto">
                    <table class="w-full min-w-max text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/50 sticky top-0">
                            <tr>
                                <th class="py-3 px-4 font-bold">Tanggal</th>
                                <th class="py-3 px-4 font-bold">Keterangan</th>
                                <th class="py-3 px-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="mh-table-body" class="bg-white divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <!-- Manajemen Backup Database -->
        <details class="settings-accordion bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <summary class="p-4 sm:p-6 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center">
                        <i class="fi fi-sr-database text-cyan-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Manajemen Backup Database</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Buat dan kelola backup database</p>
                    </div>
                </div>
                <i class="fi fi-sr-angle-down accordion-icon text-gray-400 transition-transform duration-300"></i>
            </summary>
            <div class="accordion-content px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-100 pt-4">
                <div class="flex flex-wrap gap-3 mb-4">
                    <button type="button" id="btn-create-backup" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                        <i class="fi fi-sr-disk"></i> Buat Backup Baru
                    </button>
                    <button type="button" id="btn-refresh-backup-list" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold py-2.5 px-3 rounded-xl transition-all flex items-center gap-2 text-sm">
                        <i class="fi fi-sr-refresh"></i> Refresh
                    </button>
                </div>
                <div id="backup-files-list" class="rounded-xl border border-gray-100 min-h-[100px]">
                    <div class="text-center text-gray-400 py-8">
                        <i class="fi fi-sr-spinner text-2xl animate-spin mb-2"></i>
                        <p class="text-sm">Memuat daftar backup...</p>
                    </div>
                </div>
                
                <!-- IMPORT / RESTORE DB SECTION -->
                <div class="mt-6 pt-5 border-t border-gray-100">
                     <p class="text-sm font-bold text-cyan-800 mb-3 flex items-center gap-2">
                        <i class="fi fi-sr-upload"></i> Restore / Import Database (.sql)
                     </p>
                     <p class="text-xs text-red-500 mb-3 font-semibold">Perhatian: Proses ini bersifat destruktif dan akan MEREPALCE seluruh data sistem absen.</p>
                     <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                         <input type="file" id="db-import-file" accept=".sql" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 border border-gray-200 rounded-xl p-1 bg-gray-50">
                         <button type="button" id="btn-import-db" onclick="handleImportDB()" class="whitespace-nowrap bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                             <i class="fi fi-sr-replace"></i> Upload & Replace
                         </button>
                     </div>
                </div>
                <!-- =============================== -->

                <div class="mt-4 bg-cyan-50 border border-cyan-100 rounded-xl p-3">
                    <p class="text-xs text-cyan-700">
                        <i class="fi fi-sr-info mr-1"></i>
                        <strong>Tips:</strong> Backup database secara berkala untuk mengamankan data.
                    </p>
                </div>
            </div>
        </details>

        <!-- Informasi -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 sm:p-6 rounded-2xl border border-blue-100">
            <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fi fi-sr-info text-blue-500"></i>
                Informasi Penting
            </h3>
            <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start gap-2">
                    <i class="fi fi-sr-check-circle text-blue-500 mt-0.5"></i>
                    <span>Anda hanya perlu mengubah field yang ingin diubah saja</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fi fi-sr-exclamation-triangle text-orange-500 mt-0.5"></i>
                    <span>Data presensi yang sudah ada tidak akan berubah</span>
                </li>
            </ul>
        </div>

        <!-- Save Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 sticky bottom-4 bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-gray-200 shadow-lg">
            <button type="button" id="reset-settings" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all text-sm">
                Reset ke Default
            </button>
            <button type="button" id="btn-save-settings" onclick="handleSaveSettings(this)" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-sm flex items-center justify-center gap-2">
                <i class="fi fi-sr-disk"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
function toggleWfoFields() {
    const mode = document.getElementById('wfo-mode')?.value || 'api';
    const gpsFields = document.getElementById('wfo-gps-fields');
    const apiFields = document.getElementById('wfo-api-fields');
    
    if (mode === 'gps') {
        gpsFields?.classList.remove('hidden');
        apiFields?.classList.add('hidden');
    } else {
        gpsFields?.classList.add('hidden');
        apiFields?.classList.remove('hidden');
    }
}

// Initial toggle when settings.blade.php script runs
setTimeout(toggleWfoFields, 100);

async function handleSaveSettings(btn) {
    const confirmed = await customConfirm('Simpan perubahan pengaturan?', 'Konfirmasi Simpan');
    if(!confirmed) return;
    
    const originalText = btn.innerHTML;
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin"></i> Menyimpan...';
        
        const getValue = (id) => document.getElementById(id)?.value || '';
        const getHour = (id) => (getValue(id).split(':')[0] || '');

        // Collect all settings as POST data to the ajax_handler endpoint
        const data = new URLSearchParams();
        data.append('ajax', 'update_settings');
        data.append('max_ontime_hour', getHour('max-ontime-hour'));
        data.append('min_checkout_hour', getHour('min-checkout-hour'));
        data.append('wfo_address', getValue('wfo-address'));
        data.append('wfo_radius_m', getValue('wfo-radius'));
        data.append('attendance_period_end', getValue('attendance-period-end'));
        data.append('kpi_late_penalty', getValue('kpi-late-penalty'));
        data.append('kpi_izin_sakit', getValue('kpi-izin-sakit'));
        data.append('kpi_alpha', getValue('kpi-alpha'));
        data.append('kpi_overtime_bonus', getValue('kpi-overtime-bonus'));
        data.append('default_checkout_time', getValue('default-checkout-time'));
        data.append('max_daily_report_days_back', getValue('max-daily-report-days-back'));
        data.append('max_monthly_report_months_back', getValue('max-monthly-report-months-back'));
        data.append('monthly_report_end_year', getValue('monthly-report-end-year'));
        data.append('face_recognition_threshold', getValue('face-recognition-threshold'));
        data.append('face_recognition_min_confidence', getValue('face-recognition-min-confidence'));
        data.append('face_recognition_input_size', getValue('face-recognition-input-size'));
        data.append('face_recognition_score_threshold', getValue('face-recognition-score-threshold'));
        data.append('face_recognition_quality_threshold', getValue('face-recognition-quality-threshold'));
        data.append('geocode_timeout', getValue('geocode-timeout'));
        data.append('geocode_accuracy_radius', getValue('geocode-accuracy-radius'));
        
        data.append('help_wa_number', getValue('help-wa-number'));
        data.append('help_wa_message', getValue('help-wa-message'));
        
        // WFO API settings
        data.append('wfo_mode', getValue('wfo-mode'));
        data.append('wfo_api_provider', getValue('wfo-api-provider'));
        data.append('wfo_api_token', getValue('wfo-api-token'));
        data.append('wfo_api_org_keywords', getValue('wfo-api-org-keywords'));
        data.append('wfo_api_asn_list', getValue('wfo-api-asn-list'));
        data.append('wfo_api_cidr_list', getValue('wfo-api-cidr-list'));
        data.append('wfo_wifi_ssids', getValue('wfo-wifi-ssids'));
        data.append('wfo_require_wifi', getValue('wfo-require-wifi'));

        // Use global selectedAddress coordinates if available (from address search)
        if (window.selectedAddress && window.selectedAddress.lat) {
            data.append('wfo_lat', window.selectedAddress.lat);
            data.append('wfo_lng', window.selectedAddress.lon);
        }

        // POST to ajax_handler (NOT to Laravel API route which requires setting_key)
        const resp = await fetch('?ajax=update_settings', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();
        
        if (json.ok) {
            if (typeof showNotif === 'function') showNotif('Pengaturan berhasil disimpan! ✓', true);
            else customAlert('Pengaturan berhasil disimpan', 'Berhasil');
        } else {
            if (typeof showNotif === 'function') showNotif(json.message || 'Gagal menyimpan', false);
            else customAlert('Gagal: ' + (json.message || 'Gagal menyimpan'), 'Error');
        }
    } catch (err) {
        console.error('[CRITICAL] Save error:', err);
        if (typeof customAlert === 'function') customAlert('Terjadi kesalahan sistem: ' + err.message, 'Error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function handleImportDB() {
    const fileInput = document.getElementById('db-import-file');
    if(!fileInput.files || fileInput.files.length === 0) {
        if(typeof customAlert === 'function') customAlert('Silahkan pilih file .sql terlebih dahulu', 'Peringatan');
        else alert('Pilih file SQL terlebih dahulu');
        return;
    }

    const file = fileInput.files[0];
    if(!file.name.endsWith('.sql')) {
        if(typeof customAlert === 'function') customAlert('File harus berformat .sql', 'Peringatan');
        else alert('Format file harus .sql');
        return;
    }

    const confirmed = await customConfirm('PERINGATAN! Mengimport database akan menghapus dan me-replace semua data Anda saat ini. Pastikan Anda punya backup terbaru. Apakah Anda yakin ingin melanjutkan?', 'Konfirmasi Kritis');
    if(!confirmed) return;

    const btn = document.getElementById('btn-import-db');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin"></i> Mengimport... (Jangan tutup tab)';
        
        const formData = new FormData();
        formData.append('ajax', 'import_db');
        formData.append('db_file', file);

        const res = await fetch('?ajax=import_db', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const json = await res.json();
        
        if (json.ok) {
            if (typeof customAlert === 'function') customAlert('Database berhasil di-restore! Sistem akan memuat ulang.', 'Berhasil');
            else alert('Berhasil restore database!');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            if (typeof customAlert === 'function') customAlert('Gagal: ' + (json.message || 'Error import'), 'Error');
            else alert('Error: ' + json.message);
        }
    } catch (err) {
        console.error('[CRITICAL] Import error:', err);
        if (typeof customAlert === 'function') customAlert('Terjadi kesalahan sistem atau timeout saat mengestrak SQL. Silahkan refresh manual jika web stuck: ' + err.message, 'Error');
        else alert('Error: ' + err.message);
    } finally {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
}
</script>

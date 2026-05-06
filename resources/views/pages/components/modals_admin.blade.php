<?php
/**
 * Admin-specific Modals
 */
?>

<!-- Modal Tambah/Edit Member -->
<div id="member-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-40 hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between z-10">
            <h2 id="modal-title" class="text-lg font-bold flex items-center gap-2">
                <i class="fi fi-sr-user-add"></i>
                Tambah Member Baru
            </h2>
            <button type="button" id="btn-cancel-modal" class="text-white/80 hover:text-white hover:bg-white/20 rounded-full p-1.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="member-form" class="p-6 space-y-4">
            <input type="hidden" id="member-id">
            <input type="hidden" id="foto-data-url">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" id="email" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">NIM <span class="text-red-500">*</span></label>
                <input type="text" id="nim" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="nama" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Program Studi <span class="text-red-500">*</span></label>
                <input type="text" id="prodi" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Startup</label>
                <input type="text" id="startup" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Wajah</label>
                <div id="modal-video-container" class="relative bg-gray-100 rounded-xl w-full aspect-video mb-3 hidden overflow-hidden">
                    <video id="modal-video" autoplay playsinline class="w-full h-full object-cover"></video>
                </div>
                <canvas id="modal-canvas" class="hidden"></canvas>
                <img id="foto-preview" class="mt-2 h-40 w-40 object-cover rounded-xl border-2 border-gray-200 hidden mx-auto mb-3">
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <button type="button" id="btn-start-camera" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <i class="fi fi-sr-camera"></i>
                        <span class="hidden sm:inline">Kamera</span>
                    </button>
                    <button type="button" id="btn-upload-photo" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <i class="fi fi-sr-upload"></i>
                        <span class="hidden sm:inline">Upload</span>
                    </button>
                </div>
                <input type="file" id="photo-file-input" accept="image/*" class="hidden">
                <button type="button" id="btn-take-photo" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2.5 px-4 rounded-xl hidden transition-all shadow-sm hover:shadow-md">
                    <i class="fi fi-sr-camera"></i> Ambil Foto
                </button>
            </div>
            
            <div id="password-admin-wrapper" class="grid grid-cols-1 sm:grid-cols-2 gap-3 hidden">
                <input type="password" id="password-new" placeholder="Password Baru" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                <input type="password" id="password-confirm" placeholder="Konfirmasi Password" class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="button" id="btn-cancel-modal-btn" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-xl font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Jadwal Kerja -->
<div id="work-schedule-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Kelola Jadwal Kerja</h3>
            <button id="work-schedule-close" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Pegawai</label>
            <select id="work-schedule-user" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Pilih pegawai...</option>
            </select>
        </div>
        
        <div id="work-schedule-form" class="hidden">
            <div class="mb-4">
                <h4 class="text-lg font-semibold mb-3">Jadwal Kerja Mingguan</h4>
                <div class="space-y-3">
                    <div class="grid grid-cols-7 gap-2 text-sm font-medium text-gray-700">
                        <div>Hari</div>
                        <div>Bekerja</div>
                        <div>Jam Masuk</div>
                        <div>Jam Pulang</div>
                        <div>Durasi</div>
                        <div>Status</div>
                        <div>Aksi</div>
                    </div>
                    
                    <div id="work-schedule-days" class="space-y-2">
                        <!-- Days will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai Bekerja</label>
                <input id="work-start-date" type="date" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <p class="text-xs text-gray-500 mt-1">Digunakan sebagai tanggal awal perhitungan KPI pegawai.</p>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button id="work-schedule-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button id="work-schedule-save" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Simpan Jadwal</button>
            </div>
        </div>
    </div>
</div>

<!-- Manual Holidays Modal -->
<div id="manual-holidays-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-xl">
        <h3 class="text-xl font-bold mb-4">Kelola Hari Libur Manual</h3>
        <div class="flex gap-2 mb-3">
            <input type="date" id="mh-date" class="p-2 border rounded">
            <input type="text" id="mh-name" class="flex-1 p-2 border rounded" placeholder="Nama/Alasan libur (mis. Demo, Bencana)">
            <button id="mh-add" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Tambah</button>
        </div>
        <div class="overflow-x-auto max-h-80 overflow-y-auto">
            <table class="min-w-full bg-white bordered">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-3 text-left">Tanggal</th>
                        <th class="py-2 px-3 text-left">Keterangan</th>
                        <th class="py-2 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="mh-body"></tbody>
            </table>
        </div>
        <div class="text-right mt-3">
            <button id="mh-close" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Pilihan Export Data Presensi -->
<div id="export-presensi-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Export Data Presensi</h3>
            <button onclick="closeExportDailyModal()" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
        </div>
        <form id="export-presensi-form" method="GET" action="?">
            <input type="hidden" name="ajax" value="export_daily">
            <input type="hidden" name="filter_type" id="export-p-filter-type-fallback" value="period">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode Data</label>
                <select id="export-p-range" class="w-full p-2 border border-gray-300 rounded-lg" onchange="const opts=document.getElementById('export-p-monthly-opts'); const fallback=document.getElementById('export-p-filter-type-fallback'); fallback.value=this.value; if(this.value==='monthly') { opts.style.display='block'; opts.classList.remove('hidden'); } else { opts.style.display='none'; opts.classList.add('hidden'); }">
                    <option value="period">Seluruh Periode (Default)</option>
                    <option value="monthly">Bulan Tertentu</option>
                </select>
            </div>
            
            <div id="export-p-monthly-opts" class="mb-4 hidden p-3 border rounded-lg bg-gray-50">
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-sm font-bold text-gray-800">Pilih Bulan & Tahun</label>
                    <select id="export-p-year" name="year" class="p-1 border rounded text-xs bg-white focus:ring-1 focus:ring-indigo-500" onchange="updateMonthLabels(this.value)">
                        <?php for($y=2024;$y<=2026;$y++) echo "<option value='$y'".($y==date('Y')?' selected':'').">$y</option>"; ?>
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <?php 
                    $m_names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for($i=1;$i<=12;$i++): ?>
                        <label class="flex items-center space-x-2 text-xs p-1.5 border bg-white rounded shadow-sm hover:border-indigo-400 cursor-pointer transition-colors month-item">
                            <input type="checkbox" name="export_months[]" value="<?= $i ?>" <?= ($i==date('n')?'checked':'') ?> class="export-month-cb w-3 h-3 text-indigo-600 rounded">
                            <span class="month-label text-gray-700"><?= $m_names[$i] ?> <span class="text-[9px] text-gray-400 year-suffix"><?= date('Y') ?></span></span>
                        </label>
                    <?php endfor; ?>
                </div>
                <div class="mt-3 flex justify-between items-center px-1">
                    <button type="button" onclick="qsa('.export-month-cb').forEach(cb=>cb.checked=true)" class="text-[10px] text-indigo-600 hover:font-bold transition-all">Select All</button>
                    <button type="button" onclick="qsa('.export-month-cb').forEach(cb=>cb.checked=false)" class="text-[10px] text-red-500 hover:font-bold transition-all">Clear All</button>
                </div>
            </div>
            <script>
            function updateMonthLabels(year) {
                qsa('.year-suffix').forEach(el => el.textContent = year);
            }
            </script>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Format Tampilan</label>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="export_format" value="combined" checked>
                        <span class="text-sm">Combined (Satu sheet untuk semua pegawai)</span>
                    </label>
                    <label class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="export_format" value="per_employee">
                        <span class="text-sm">Per Pegawai (Satu sheet per pegawai)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeExportDailyModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Download Excel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Landmark Wajah (Bukti Presensi) -->
<div id="landmark-modal" class="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-[100] hidden p-4">
    <div class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-700">
        <div class="flex justify-between items-center p-6 border-b border-slate-800">
            <h3 id="landmark-modal-title" class="text-xl font-bold text-white flex items-center gap-3">
                <i class="fi fi-sr-face-recognition text-blue-400"></i>
                Visualisasi Landmark Wajah
            </h3>
            <button onclick="closeLandmarkModal()" class="text-slate-400 hover:text-white transition-colors">
                <i class="fi fi-rr-cross-small text-2xl"></i>
            </button>
        </div>
        <div class="p-8 flex flex-col items-center">
            <div class="relative group">
                <canvas id="landmark-modal-canvas" class="rounded-2xl shadow-inner bg-slate-950 border border-slate-800 w-full max-w-md aspect-[4/3]"></canvas>
                <div class="absolute inset-0 pointer-events-none border-2 border-blue-500/20 rounded-2xl group-hover:border-blue-500/40 transition-colors"></div>
            </div>
            <p class="mt-6 text-slate-400 text-sm text-center leading-relaxed max-w-sm">
                Visualisasi 68 titik pola wajah yang digunakan sebagai bukti presensi tanpa menyimpan foto asli untuk keamanan data dan efisiensi server.
            </p>
        </div>
        <div class="p-6 bg-slate-800/50 flex justify-center">
            <button onclick="closeLandmarkModal()" class="bg-slate-700 hover:bg-slate-600 text-white px-8 py-3 rounded-xl font-bold transition-all transform hover:scale-105">
                Tutup
            </button>
        </div>
    </div>
</div>

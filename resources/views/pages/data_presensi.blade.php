<div id="page-laporan" class="hidden animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center gap-3">
                <i class="fi fi-sr-document-signed text-indigo-600"></i>
                Laporan Kehadiran
            </h2>
            <div class="flex flex-wrap gap-2">
                <button id="btn-open-absence" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                     <i class="fi fi-sr-edit"></i> Input Manual
                </button>
                <button id="btn-manual-holidays" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                     <i class="fi fi-sr-calendar-clock"></i> Libur Manual
                </button>
                <button id="btn-bulk-fix-checkout" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                     <i class="fi fi-sr-clock-check"></i> Fix Jam Pulang
                </button>
                <button id="btn-export-daily" onclick="openExportDailyModal()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                     <i class="fi fi-sr-file-excel"></i> Export Info
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-gray-50/50 p-5 rounded-2xl border border-gray-200/60 mb-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fi fi-sr-search text-gray-400"></i>
                    </span>
                    <input type="text" id="search-laporan" placeholder="Cari Nama/NIM..." class="w-full pl-10 p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm">
                </div>
                <select id="filter-startup" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Startup</option>
                </select>
                <input type="date" id="filter-tanggal-mulai" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                <input type="date" id="filter-tanggal-selesai" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                 <select id="sort-presensi" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="tanggal-desc">Tanggal (Terbaru)</option>
                    <option value="tanggal-asc">Tanggal (Terlama)</option>
                    <option value="jam-masuk-desc">Jam Masuk (Terlambat)</option>
                    <option value="jam-masuk-asc">Jam Masuk (Tepat Waktu)</option>
                    <option value="nama-asc">Nama (A-Z)</option>
                    <option value="nama-desc">Nama (Z-A)</option>
                </select>
                 <select id="filter-status" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Status Waktu</option>
                    <option value="ontime">Ontime</option>
                    <option value="terlambat">Terlambat</option>
                </select>
                 <select id="filter-ket" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Keterangan</option>
                    <option value="wfo">WFO</option>
                    <option value="wfa">WFA</option>
                    <option value="overtime">Overtime</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                </select>
                 <select id="filter-laporan" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Status Laporan</option>
                    <option value="belum-ada">Belum Ada Laporan</option>
                    <option value="pending">Belum Di-approve</option>
                    <option value="approved">Sudah Di-approve</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-2 pt-2">
                 <button id="btn-show-all" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded-xl text-sm transition-colors">Reset Filter</button>
                 <button id="btn-toggle-today" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded-xl text-sm transition-colors flex items-center gap-2">
                    <i class="fi fi-sr-calendar-check"></i> Hari Ini
                 </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-100">
            <table class="w-full min-w-max text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-bold text-gray-800">Tanggal</th>
                        <th class="py-4 px-6 font-bold text-gray-800">NIM</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Nama</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Startup</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Jam Masuk</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Bukti Masuk</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Status</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Ket</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Jam Pulang</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Bukti Pulang</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Laporan</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-laporan-body" class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

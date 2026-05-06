<div id="page-rekap" class="<?php echo isAdmin() ? 'hidden' : '';?> animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Rekap Daftar Hadir</h2>
            <div id="rekap-controls" class="flex flex-wrap items-center gap-2">
                <select id="rekap-view-mode" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors">
                    <option value="monthly">Bulanan</option>
                    <option value="weekly">Mingguan</option>
                </select>
                <select id="rekap-month" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors"></select>
                <select id="rekap-year" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors"></select>
                <select id="rekap-week" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 hidden transition-colors"></select>
                <button id="btn-load-rekap" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fi fi-sr-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div id="pegawai-info" class="text-sm text-gray-600 mb-6 bg-blue-50 p-4 rounded-2xl hidden user-info-box"></div>
        
        <!-- KPI Chart Section -->
        <div id="kpi-chart-section" class="mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                Penilaian KPI Absen
            </h3>
            <!-- Score Summary Header -->
            <div id="kpi-score-header" class="mb-6 bg-indigo-50 p-4 rounded-2xl flex items-center justify-between shadow-sm border border-indigo-100 hidden">
                <div>
                    <div class="text-sm text-indigo-600 font-medium">KPI Score</div>
                    <div id="kpi-score-value" class="text-3xl font-bold text-indigo-700">0</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-indigo-600 font-medium">Status</div>
                    <div id="kpi-status-value" class="text-lg font-bold text-indigo-700">-</div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 h-80 relative">
                <canvas id="kpi-chart"></canvas>
            </div>
            
            <!-- Hidden container for raw values if needed later -->
            <div id="kpi-summary" class="hidden"></div>
        </div>
        
        <!-- Shortcut Laporan Harian Belum Diisi -->
        <div id="missing-daily-reports-shortcut" class="mb-6 hidden">
            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-md font-bold text-orange-800 flex items-center gap-2">
                        <i class="fi fi-sr-exclamation text-orange-500"></i>
                        Laporan Harian Belum Diisi
                    </h3>
                    <span id="missing-reports-count" class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">0</span>
                </div>
                <div id="missing-reports-list" class="flex flex-wrap gap-2">
                    <!-- List of dates with missing reports will be populated here -->
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto rounded-2xl border border-gray-100">
            <table class="w-full min-w-max text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Hari</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Tanggal</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Jam Masuk</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Jam Keluar</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Keterangan</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Laporan</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="table-rekap-body" class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

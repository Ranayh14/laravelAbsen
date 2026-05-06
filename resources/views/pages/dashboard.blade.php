<div id="page-dashboard" class="hidden animate-fade-in-up">
    <!-- Dashboard Banner -->
    <div class="relative overflow-hidden mb-10 rounded-3xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-700 p-8 md:p-12 shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-3 tracking-tight">Admin Dashboard</h1>
                <p class="text-indigo-100 text-lg font-medium opacity-90">Overview performa karyawan dan presensi hari ini.</p>
            </div>
            <div class="flex items-center gap-4 bg-white/15 backdrop-blur-xl p-6 rounded-2xl border border-white/20 shadow-inner">
                <div class="text-right">
                    <div class="text-xs uppercase tracking-widest text-indigo-200 font-bold mb-1">Tanggal</div>
                    <div class="text-xl font-bold text-white"><?= date('d M Y') ?></div>
                </div>
                <div class="w-px h-10 bg-white/30"></div>
                <div class="text-left">
                    <div class="text-xs uppercase tracking-widest text-indigo-200 font-bold mb-1">Waktu</div>
                    <div class="text-xl font-bold text-white tracking-wider" id="dash-realtime-clock">00:00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Pegawai Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                    <i class="fi fi-sr-users text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Pegawai</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="totalEmployees">0</h3>
                </div>
            </div>
        </div>
        <!-- Hadir Hari Ini Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                    <i class="fi fi-sr-user-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Hadir Hari Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="presentToday">0</h3>
                </div>
            </div>
        </div>
        <!-- Terlambat Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all">
                    <i class="fi fi-sr-alarm-clock text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Terlambat</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="lateToday">0</h3>
                </div>
            </div>
        </div>
        <!-- Tidak Hadir Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all">
                    <i class="fi fi-sr-user-xmark text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Tidak Hadir</p>
                    <h3 class="text-2xl font-bold text-gray-800" id="absentToday">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Penilaian KPI Absen Section (Full Width) -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-10 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Penilaian KPI Absen</h3>
                <p class="text-gray-500 font-medium">Periode: <span id="kpi-period-range" class="font-bold text-indigo-600">Loading...</span></p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- View Toggle -->
                <div class="flex bg-gray-100 p-1 rounded-xl">
                    <button id="view-toggle-table" class="px-4 py-2 rounded-lg text-sm font-bold bg-white text-indigo-600 shadow-sm transition-all flex items-center gap-2">
                        <i class="fi fi-sr-table-list"></i> Table
                    </button>
                    <button id="view-toggle-graph" class="px-4 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-indigo-600 transition-all flex items-center gap-2">
                        <i class="fi fi-sr-chart-histogram"></i> Grafik
                    </button>
                </div>

                <div class="w-px h-8 bg-gray-200 mx-2 hidden md:block"></div>

                <select id="kpi-filter-type" class="bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 outline-none ring-1 ring-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                    <option value="period" selected>Seluruh Periode</option>
                    <option value="monthly">Filter Bulanan</option>
                </select>
                <div id="kpi-monthly-controls" class="flex items-center gap-2 hidden">
                    <select id="kpi-filter-month" class="bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 outline-none ring-1 ring-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                        <!-- Populated by JS -->
                    </select>
                    <select id="kpi-filter-year" class="bg-gray-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700 outline-none ring-1 ring-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                        <!-- Populated by JS -->
                    </select>
                </div>
                <button id="refresh-kpi" class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                    <i class="fi fi-sr-refresh text-lg"></i>
                </button>
                <div class="flex gap-2">
                    <button id="btn-export-kpi" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                        <i class="fi fi-sr-file-excel"></i> Excel
                    </button>
                    <button id="btn-export-kpi-pdf" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                        <i class="fi fi-sr-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Graph View Container (Hidden Default) -->
        <div id="kpi-graph-view" class="hidden p-6 md:p-8 min-h-[400px]">
            <canvas id="kpi-overview-chart"></canvas>
        </div>

        <!-- Table View Container -->
        <div id="kpi-table-view" class="overflow-x-auto min-h-[300px] relative">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-widest font-bold">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Pegawai</th>
                        <th class="px-6 py-4 text-center">Hari Kerja</th>
                        <th class="px-6 py-4 text-center">Ontime</th>
                        <th class="px-6 py-4 text-center">WFA</th>
                        <th class="px-6 py-4 text-center">Terlambat</th>
                        <th class="px-6 py-4 text-center">Izin/Sakit</th>
                        <th class="px-6 py-4 text-center">Alpha</th>
                        <th class="px-6 py-4 text-center">Overtime</th>
                        <th class="px-6 py-4 text-center">Laporan Kosong</th>
                        <th class="px-6 py-4 text-center">KPI Score</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="kpi-table-body" class="divide-y divide-gray-50">
                    <!-- Populated by JS -->
                </tbody>
            </table>
            <div id="kpi-loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10 hidden">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="font-bold text-gray-500 italic">Menghitung KPI...</p>
                </div>
            </div>
            <div id="kpi-empty" class="hidden py-20 text-center">
                <i class="fi fi-sr-search text-5xl text-gray-200 mb-4 block"></i>
                <p class="text-gray-500 font-bold">Tidak ada data KPI tersedia untuk periode ini</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- Statistik Laporan Harian -->
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 h-full">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Statistik Laporan Harian</h3>
                    <p class="text-sm font-medium text-gray-500">
                        <span id="employeesWithoutReports" class="font-bold text-rose-600">0</span> pegawai belum isi laporan hari ini.
                    </p>
                </div>
                <div class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl text-lg font-black shadow-sm" id="totalMissingReports">0</div>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-600 uppercase tracking-widest mb-4">Daftar Pegawai</p>
                <div id="daily-report-employees-list" class="space-y-4 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>

        <!-- Tren Kehadiran -->
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 h-full">
            <div class="mb-8 text-center">
                <h3 class="text-xl font-bold text-gray-800 mb-1">Tren Kehadiran</h3>
                <p class="text-sm font-medium text-gray-500">Persentase kehadiran selama periode aktif.</p>
            </div>
            <div class="flex justify-center items-center h-[350px]">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- Pegawai Terlambat Chart -->
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fi fi-sr-stats text-rose-500"></i> Top Terlambat
            </h3>
            <div class="h-[300px] flex items-center justify-center">
                <canvas id="mostLateChart"></canvas>
            </div>
            <div id="most-late-list" class="mt-4 space-y-2"></div>
        </div>

        <!-- Pegawai Rajin Chart -->
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fi fi-sr-trophy text-amber-500"></i> Pegawai Paling Rajin
            </h3>
            <div class="h-[300px] flex items-center justify-center">
                <canvas id="mostAttentiveChart"></canvas>
            </div>
            <div id="most-attentive-list" class="mt-4 space-y-2"></div>
        </div>
    </div>

    <!-- Today's Lateness Detail -->
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 mb-10">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i class="fi fi-sr-clock text-indigo-500"></i> Detail Terlambat Hari Ini
        </h3>
        <div id="today-late-container">
            <canvas id="todayLateChart"></canvas>
        </div>
    </div>
</div>

<script>
// Dashboard Logic Sync
(function() {
    // Calculate server time offset to ensure WIB accuracy
    const serverTimestamp = <?= time() * 1000 ?>;
    const browserTimestamp = Date.now();
    window.serverTimeOffset = serverTimestamp - browserTimestamp;
    
    // Immediate clock update if defined
    if (typeof updateDashboardClock === 'function') {
        updateDashboardClock();
    }
    
    console.log('Dashboard clock synced with server. Offset:', window.serverTimeOffset, 'ms');
})();
</script>

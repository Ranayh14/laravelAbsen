<div id="tab-help-requests" class="tab-content hidden animate-fade-in">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Help Requests</h2>
            <p class="text-gray-500 mt-1">Kelola permintaan izin, presensi terlambat, dan laporan bug dari pegawai.</p>
        </div>
        <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100">
            <button id="btn-refresh-requests" class="p-2.5 hover:bg-gray-50 rounded-xl text-indigo-600 transition-all" title="Refresh Data">
                <i class="fi fi-sr-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Filters & Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-xl">
                <i class="fi fi-sr-clock"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Menunggu</p>
                <h4 id="stat-pending-requests" class="text-2xl font-bold text-gray-800">0</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl">
                <i class="fi fi-sr-check-circle"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Disetujui</p>
                <h4 id="stat-approved-requests" class="text-2xl font-bold text-gray-800">0</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-xl">
                <i class="fi fi-sr-cross-circle"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Ditolak</p>
                <h4 id="stat-disapproved-requests" class="text-2xl font-bold text-gray-800">0</h4>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative w-full sm:w-64">
                <i class="fi fi-sr-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="search-requests" placeholder="Cari nama atau NIM..." class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <button class="filter-req active flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap" data-status="all">Semua</button>
                <button class="filter-req flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all whitespace-nowrap" data-status="pending">Pending</button>
                <button class="filter-req flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all whitespace-nowrap" data-status="approved">Approved</button>
                <button class="filter-req flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all whitespace-nowrap" data-status="disapproved">Disapproved</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-requests-body" class="divide-y divide-gray-50">
                    <!-- Data populated by JS -->
                </tbody>
            </table>
        </div>
        
        <div id="requests-empty" class="hidden p-20 text-center">
            <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                <i class="fi fi-sr-inbox"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Tidak ada permintaan</h3>
            <p class="text-gray-500">Belum ada permintaan bantuan yang masuk saat ini.</p>
        </div>
    </div>
</div>

<!-- Request Detail Modal -->
<div id="request-detail-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden animate-zoom-in max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-indigo-600 text-white">
            <h3 class="text-xl font-bold">Detail Permintaan</h3>
            <button id="close-request-detail" class="p-2 hover:bg-white/20 rounded-xl transition-colors">
                <i class="fi fi-sr-cross text-sm"></i>
            </button>
        </div>
        <div id="request-detail-body" class="p-6 sm:p-8 flex-1 overflow-y-auto">
            <!-- Data populated by JS -->
        </div>
        <div id="request-action-footer" class="p-6 bg-gray-50 border-t border-gray-100 flex gap-4">
            <!-- Buttons populated by JS -->
        </div>
    </div>
</div>

<style>
    .filter-req.active {
        background-color: #4f46e5;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
</style>

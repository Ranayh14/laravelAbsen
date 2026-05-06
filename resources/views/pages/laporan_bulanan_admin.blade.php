<div id="page-admin-monthly" class="hidden animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center gap-3">
                <i class="fi fi-sr-calendar-lines text-indigo-600"></i>
                Laporan Bulanan (Admin)
            </h2>
             <button id="btn-export-monthly" onclick="triggerExportMonthly()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fi fi-sr-file-excel"></i> Export Excel
            </button>
        </div>

        <div class="bg-gray-50/50 p-5 rounded-2xl border border-gray-200/60 mb-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="relative lg:col-span-2">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fi fi-sr-search text-gray-400"></i>
                    </span>
                    <input type="text" id="am-search" class="w-full pl-10 p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm" placeholder="Cari Nama/NIM...">
                </div>
                <select id="am-startup" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Startup</option>
                </select>
                <select id="am-month" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Bulan</option>
                </select>
                <select id="am-year" class="p-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-colors text-sm text-gray-700">
                    <option value="">Semua Tahun</option>
                </select>
            </div>
            
             <div class="flex justify-end pt-2">
                 <button id="am-reset" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-4 rounded-xl text-sm transition-colors">Reset Filter</button>
            </div>
        </div>
        
        <div class="overflow-x-auto rounded-2xl border border-gray-100">
            <table class="w-full min-w-max text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-bold text-gray-800">Bulan</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Nama</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Startup</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Detail</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Status</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="am-body" class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

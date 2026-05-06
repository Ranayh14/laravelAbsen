<div id="page-laporan-bulanan" class="hidden animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight mb-6">Laporan Bulanan</h2>
        <div id="pegawai-info-monthly" class="text-sm text-gray-600 mb-6 bg-blue-50 p-4 rounded-2xl hidden"></div>
        <div class="overflow-hidden rounded-2xl border border-gray-100">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-bold text-gray-800">Bulan</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Laporan</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Status</th>
                    </tr>
                </thead>
                <tbody id="table-monthly-body" class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pegawai: Modal Form Laporan Bulanan -->
<div id="page-monthly-form" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop Overlay -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="monthly-modal-overlay"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-3xl shadow-2xl border border-gray-100 flex flex-col animate-fade-in-up">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight" id="monthly-form-title">Buat Laporan Bulanan</h2>
                <div id="pegawai-info-monthly-form" class="text-xs text-gray-500 mt-1">
                    <!-- Content will be filled by JS -->
                </div>
            </div>
            <button id="btn-back-to-monthly-list" class="bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 p-2 rounded-full transition-all flex items-center justify-center">
                <i class="fi fi-sr-cross-small text-xl leading-none"></i>
            </button>
        </div>
        
        <div class="p-6 md:p-8">
            <form id="form-monthly-report" class="space-y-8">
                <input type="hidden" id="monthly-report-year">
                <input type="hidden" id="monthly-report-month">

                <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                    <label class="block text-gray-800 font-bold mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>
                        Ringkasan Pekerjaan
                    </label>
                    <textarea id="monthly-summary" rows="5" class="w-full p-4 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm" placeholder="Jelaskan ringkasan pekerjaan Anda selama sebulan..."></textarea>
                </div>

                <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                    <label class="block text-gray-800 font-bold mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">2</span>
                        Pencapaian dan Hasil Kerja
                    </label>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 mb-3 bg-white">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="py-3 px-4 w-2/5">Pencapaian</th>
                                    <th class="py-3 px-4 w-2/5">Detail</th>
                                    <th class="py-3 px-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-achievements-body" class="bg-white divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                    <button type="button" id="btn-add-achievement" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg text-sm transition-colors flex items-center gap-2">
                        <i class="fi fi-sr-plus"></i> Tambah Pencapaian
                    </button>
                </div>

                <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                    <label class="block text-gray-800 font-bold mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">3</span>
                        Kendala dan Solusi
                    </label>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 mb-3 bg-white">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="py-3 px-4 w-1/4">Kendala</th>
                                    <th class="py-3 px-4 w-1/4">Solusi</th>
                                    <th class="py-3 px-4 w-1/4">Catatan</th>
                                    <th class="py-3 px-4 w-1/4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-obstacles-body" class="bg-white divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                    <button type="button" id="btn-add-obstacle" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg text-sm transition-colors flex items-center gap-2">
                        <i class="fi fi-sr-plus"></i> Tambah Kendala
                    </button>
                </div>

                <div class="flex flex-col md:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" id="btn-save-draft" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm order-2 md:order-1">Simpan Draft</button>
                    <button type="submit" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 order-1 md:order-2">Submit Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

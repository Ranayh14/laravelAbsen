<div id="page-members" class="hidden animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center gap-3">
                <i class="fi fi-sr-users-alt text-blue-600"></i>
                Daftar Member
            </h2>
            <button id="btn-add-member" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fi fi-sr-user-add"></i> Tambah Member
            </button>
        </div>
        
        <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fi fi-sr-search text-gray-400"></i>
            </div>
            <input type="text" id="search-member" placeholder="Cari member berdasarkan nama atau NIM..." class="w-full pl-10 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
        </div>
        
        <div class="overflow-x-auto rounded-2xl border border-gray-100">
            <table class="w-full min-w-max text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-bold text-gray-800">Foto</th>
                        <th class="py-4 px-6 font-bold text-gray-800">NIM</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Nama</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Program Studi</th>
                        <th class="py-4 px-6 font-bold text-gray-800">Nama Startup</th>
                        <th class="py-4 px-6 font-bold text-gray-800">QR Code GA</th>
                        <th class="py-4 px-6 font-bold text-gray-800 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-members-body" class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

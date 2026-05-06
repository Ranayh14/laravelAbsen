    <!-- Modern Responsive Sidebar & Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Backdrop (Mobile) -->
        <div id="sidebar-backdrop" class="fixed inset-0 z-20 bg-black/50 transition-opacity opacity-0 pointer-events-none md:hidden" aria-hidden="true"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col glass-panel shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-center border-b border-slate-100 bg-indigo-600/5">
                <div class="flex items-center gap-2 font-bold text-xl text-indigo-600">
                    <span class="text-2xl">⚡</span> Presensi<span class="text-slate-700">Wajah</span>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 no-scrollbar">
                <?php if (isAdmin()): ?>
                    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Admin</p>
                    <button data-tab="dashboard" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-apps text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Dashboard
                    </button>
                    <button data-tab="members" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-users-alt text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Kelola Member
                    </button>
                    <button data-tab="laporan" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-document text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Data Presensi
                    </button>
                    <button data-tab="admin-monthly" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-calendar text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Laporan Bulanan
                    </button>
                    <button data-tab="settings" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-settings text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Settings
                    </button>
                <?php else: ?>
                    <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Pegawai</p>
                    <button data-tab="rekap" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-list-check text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Rekap Hadir
                    </button>
                    <button data-tab="laporan-bulanan" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all hover:bg-indigo-50 hover:text-indigo-600 group">
                        <i class="fi fi-sr-file-chart-line text-lg text-slate-400 group-hover:text-indigo-500 transition-colors"></i> Laporan Bulanan
                    </button>
                <?php endif; ?>
                
                <div class="mt-8 px-4">
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="relative z-10">
                            <h4 class="font-bold text-indigo-900 mb-1">Butuh Bantuan?</h4>
                            <p class="text-xs text-indigo-700 mb-2">Hubungi HRD jika ada kendala.</p>
                            <button class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-full shadow-sm hover:bg-indigo-700 transition">Kontak HR</button>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-indigo-200/50 rounded-full blur-xl group-hover:bg-indigo-200/70 transition"></div>
                    </div>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-100">
                <button onclick="window.location.href='?page=logout'" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                    <i class="fi fi-sr-sign-out-alt"></i> Logout
                </button>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
            <!-- Top Header -->
            <header class="h-16 glass z-10 flex items-center justify-between px-4 md:px-6 shadow-sm sticky top-0">
                <div class="flex items-center gap-3 md:hidden">
                    <button id="sidebar-toggle" class="p-2 -ml-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <span class="font-bold text-slate-700 text-lg">Presensi</span>
                </div>

                <!-- Page Title (Desktop) -->
                <div class="hidden md:block">
                     <h1 id="header-title" class="text-xl font-bold text-slate-800">
                        <?php echo isAdmin() ? 'Dashboard Admin' : 'Portal Pegawai'; ?>
                     </h1>
                     <p class="text-xs text-slate-500">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?></p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <?php if (!isAdmin()): ?>
                        <div class="hidden md:flex gap-2">
                            <a href="?page=presensi-masuk" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-lg shadow-blue-500/30 font-medium py-1.5 px-4 rounded-full text-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                                <i class="fi fi-sr-arrow-right-to-bracket"></i> Presensi Masuk
                            </a>
                            <a href="?page=presensi-pulang" class="bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white shadow-lg shadow-red-500/30 font-medium py-1.5 px-4 rounded-full text-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                                <i class="fi fi-sr-arrow-right-from-bracket"></i> Presensi Pulang
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="relative group">
                        <button class="flex items-center gap-3 pl-3 pr-1 py-1 rounded-full hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-slate-700 leading-tight"><?php echo htmlspecialchars($_SESSION['user']['nama'] ?? 'Akun'); ?></p>
                                <p class="text-[10px] text-slate-500 uppercase tracking-wide"><?php echo htmlspecialchars($_SESSION['user']['role'] ?? 'User'); ?></p>
                            </div>
                            <?php 
                            $avatar_src = 'https://ui-avatars.com/api/?background=4f46e5&color=fff&name=' . urlencode($_SESSION['user']['nama'] ?? 'A') . '&size=80';
                            if (!empty($_SESSION['user']['foto_base64'])) {
                                $foto = $_SESSION['user']['foto_base64'];
                                if (strpos($foto, 'data:') !== 0) {
                                    $foto = 'data:image/png;base64,' . $foto;
                                }
                                $avatar_src = $foto;
                            }
                            ?>
                            <img src="<?php echo $avatar_src; ?>" class="w-9 h-9 rounded-full shadow-sm ring-2 ring-white object-cover" alt="profile">
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 hidden group-hover:block animate-fade-in-up">
                            <div class="px-4 py-3 border-b border-slate-50 mb-1">
                                <p class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></p>
                            </div>
                            <!-- <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">Profile</a> -->
                            <a href="?page=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-4 md:p-6 relative">
                 <!-- Background Element -->
                 <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-indigo-50/50 to-transparent pointer-events-none -z-10"></div>

        
        <!-- Pegawai: Rekap Hadir -->
        <?php include __DIR__ . '/app/pegawai/rekap.php'; ?>

        <!-- Pegawai: Laporan Bulanan -->
        <?php include __DIR__ . '/app/pegawai/monthly.php'; ?>

        <?php include __DIR__ . '/app/pegawai/monthly_form.php'; ?>

        <?php if (isAdmin()): ?>
        <?php include __DIR__ . '/app/admin/members.php'; ?>

            <?php include __DIR__ . '/app/admin/attendance.php'; ?>

        <?php if (isAdmin()): ?>
        <!-- Admin Monthly Reports -->
        <?php include __DIR__ . '/app/admin/monthly.php'; ?>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Admin Settings -->
        <?php if (isAdmin()): ?>
        <?php include __DIR__ . '/app/admin/settings.php'; ?>
        <?php endif; ?>

        <!-- Admin Dashboard -->
        <?php if (isAdmin()): ?>
        <?php include __DIR__ . '/app/admin/dashboard.php'; ?>
        <?php endif; ?>
    </main>
        </div>
    </div>

    <!-- Modal Tambah/Edit Member -->
    <div id="member-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40 hidden">
        <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 id="modal-title" class="text-2xl font-bold mb-6">Tambah Member Baru</h2>
            <form id="member-form">
                <input type="hidden" id="member-id">
                <input type="hidden" id="foto-data-url">
                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" id="email" class="w-full p-2 border rounded-lg">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">NIM</label>
                    <input type="text" id="nim" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Program Studi</label>
                    <input type="text" id="prodi" class="w-full p-2 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Nama Startup</label>
                    <input type="text" id="startup" class="w-full p-2 border rounded-lg">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Foto Wajah</label>
                    <div id="modal-video-container" class="relative bg-gray-200 rounded-lg w-full aspect-video mb-2 hidden">
                        <video id="modal-video" autoplay playsinline class="w-full h-full object-cover rounded-lg"></video>
                    </div>
                    <canvas id="modal-canvas" class="hidden"></canvas>
                    <img id="foto-preview" class="mt-2 h-32 w-32 object-cover rounded-lg hidden mx-auto mb-2">
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <button type="button" id="btn-start-camera" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg transition">Buka Kamera</button>
                        <button type="button" id="btn-upload-photo" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg transition">Upload Foto</button>
                    </div>
                    <input type="file" id="photo-file-input" accept="image/*" class="hidden">
                    <button type="button" id="btn-take-photo" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg hidden transition">Ambil Foto</button>
                </div>
                <div id="password-admin-wrapper" class="grid grid-cols-2 gap-2 hidden">
                    <input type="password" id="password-new" placeholder="Password" class="p-2 border rounded-lg">
                    <input type="password" id="password-confirm" placeholder="Konfirmasi" class="p-2 border rounded-lg">
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="btn-cancel-modal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">Batal</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal QR Code Google Authenticator -->
    <div id="ga-qr-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4">QR Code Google Authenticator</h2>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2" id="ga-qr-email"></p>
                <p class="text-xs text-gray-500 mb-4">Scan QR code ini dengan aplikasi Google Authenticator di smartphone Anda.</p>
                <div class="flex justify-center bg-gray-50 p-4 rounded-lg">
                    <img id="ga-qr-image" src="" alt="QR Code" class="max-w-full h-auto">
                </div>
                <p class="text-xs text-gray-500 mt-4 text-center">
                    Setelah memindai QR code, gunakan kode OTP dari Google Authenticator untuk reset password.
                </p>
            </div>
            <div class="flex justify-end">
                <button type="button" id="btn-close-ga-qr" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kehadiran -->
    <div id="edit-att-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-sm">
            <h3 class="text-xl font-bold mb-4">Edit Data Kehadiran</h3>
            <form id="edit-att-form">
                <input type="hidden" id="edit-att-id">
                <input type="hidden" id="edit-att-user-id">
                <input type="hidden" id="edit-att-screenshot-masuk-data">
                <input type="hidden" id="edit-att-screenshot-pulang-data">
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" id="edit-att-date" class="w-full p-2 border rounded-lg" disabled>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama</label>
                    <input type="text" id="edit-att-nama" class="w-full p-2 border rounded-lg" disabled>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Jam Masuk</label>
                    <div class="flex gap-2">
                        <input type="time" id="edit-att-jam-masuk" class="flex-1 p-2 border rounded-lg">
                        <button type="button" id="edit-att-upload-masuk" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm">Upload Bukti</button>
                    </div>
                    <div id="edit-att-screenshot-masuk-preview" class="mt-2 hidden">
                        <img id="edit-att-screenshot-masuk-img" src="" alt="Screenshot Masuk" class="w-full h-32 object-cover rounded border">
                        <button type="button" id="edit-att-remove-masuk" class="mt-1 text-red-600 text-sm hover:underline">Hapus</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Jam Pulang</label>
                    <div class="flex gap-2">
                        <input type="time" id="edit-att-jam-pulang" class="flex-1 p-2 border rounded-lg">
                        <button type="button" id="edit-att-upload-pulang" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm">Upload Bukti</button>
                    </div>
                    <div id="edit-att-screenshot-pulang-preview" class="mt-2 hidden">
                        <img id="edit-att-screenshot-pulang-img" src="" alt="Screenshot Pulang" class="w-full h-32 object-cover rounded border">
                        <button type="button" id="edit-att-remove-pulang" class="mt-1 text-red-600 text-sm hover:underline">Hapus</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                    <select id="edit-att-ket" class="w-full p-2 border rounded-lg">
                        <option value="wfo">WFO</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                        <option value="wfa">WFA</option>
                        <option value="overtime">Overtime</option>
                    </select>
                </div>
                <div id="edit-att-wfa-form" class="mb-3 hidden">
                    <label class="block text-sm text-gray-600 mb-1">Alasan WFA</label>
                    <textarea id="edit-att-alasan-wfa" class="w-full p-2 border rounded-lg" rows="3" placeholder="Tulis alasan WFA..."></textarea>
                </div>
                <div id="edit-att-overtime-form" class="mb-3 hidden">
                    <label class="block text-sm text-gray-600 mb-1">Alasan Overtime</label>
                    <textarea id="edit-att-alasan-overtime" class="w-full p-2 border rounded-lg mb-3" rows="3" placeholder="Tulis alasan overtime..."></textarea>
                    <label class="block text-sm text-gray-600 mb-1">Lokasi Overtime</label>
                    <input type="text" id="edit-att-lokasi-overtime" class="w-full p-2 border rounded-lg" placeholder="Tulis lokasi overtime...">
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Status</label>
                    <select id="edit-att-status" class="w-full p-2 border rounded-lg">
                        <option value="ontime">On Time</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                </div>
                <div class="mb-3">
                    <button type="button" id="edit-att-add-report" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Tambahkan Laporan</button>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="edit-att-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                    <button type="submit" id="edit-att-save" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Laporan Harian Admin -->
    <div id="admin-daily-report-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
            <h3 class="text-xl font-bold mb-4">Laporan Harian Pegawai</h3>
            <div class="mb-4">
                <p class="text-sm text-gray-600">Nama: <span id="admin-dr-nama" class="font-semibold"></span></p>
                <p class="text-sm text-gray-600">Tanggal: <span id="admin-dr-date" class="font-semibold"></span></p>
            </div>
            
            <!-- Bukti Izin/Sakit Section -->
            <div id="admin-dr-bukti-section" class="mb-4 hidden">
                <label class="block text-sm text-gray-600 mb-2">Bukti Izin/Sakit:</label>
                <div id="admin-dr-bukti-container" class="mb-2">
                    <!-- Bukti image will be inserted here -->
                </div>
                <div class="flex gap-2">
                    <button type="button" id="admin-dr-edit-bukti" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Edit Bukti</button>
                    <button type="button" id="admin-dr-delete-bukti" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Hapus Bukti</button>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-2">Isi Laporan Harian:</label>
                <textarea id="admin-dr-content" rows="8" class="w-full p-3 border rounded-lg" placeholder="Tulis detail pekerjaan pegawai hari ini..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="admin-dr-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button type="button" id="admin-dr-save" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-70 hidden">
        <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-sm text-center">
            <p id="confirm-modal-message" class="text-lg mb-6">Apakah Anda yakin?</p>
            <div class="flex justify-center space-x-4">
                <button id="btn-confirm-no" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg">Tidak</button>
                <button id="btn-confirm-yes" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Ya</button>
            </div>
        </div>
    </div>

    <!-- WFA Reason Modal -->
    <div id="wfa-reason-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold mb-3">Alasan Kerja di Luar Kantor</h3>
            <p class="text-sm text-gray-600 mb-3">Anda berada di luar wilayah Telkom University. Silakan isi alasan bekerja di luar kantor untuk melanjutkan presensi (WFA).</p>
            <textarea id="wfa-reason-input" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Tulis alasan Anda..."></textarea>
            <div class="flex justify-end gap-2">
                <button id="wfa-reason-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button id="wfa-reason-submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Kirim</button>
            </div>
        </div>
    </div>

    <!-- Early Leave Reason Modal -->
    <div id="early-leave-reason-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold mb-3">Alasan Pulang Awal</h3>
            <p class="text-sm text-gray-600 mb-3">Anda pulang sebelum jam yang ditentukan. Silakan isi alasan pulang awal untuk melanjutkan presensi pulang.</p>
            <textarea id="early-leave-reason-input" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Tulis alasan pulang awal Anda..."></textarea>
            <div class="flex justify-end gap-2">
                <button id="early-leave-reason-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button id="early-leave-reason-submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Kirim</button>
            </div>
        </div>
    </div>

    <!-- Different Clock-out Location Modal -->
    <div id="diff-location-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold mb-3">Lokasi Pulang Berbeda</h3>
            <p class="text-sm text-gray-600 mb-3">Anda melakukan presensi pulang di lokasi yang berbeda signifikan dari lokasi masuk. Harap berikan alasan Anda.</p>
            <textarea id="diff-location-reason-input" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Contoh: Pulang langsung dari lokasi meeting klien..."></textarea>
            <div class="flex justify-end gap-2">
                <button id="diff-location-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button id="diff-location-submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Lanjutkan</button>
            </div>
        </div>
    </div>

    <!-- Izin/Sakit Input Modal -->
    <div id="izin-sakit-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Input Keterangan</h3>
            <form id="izin-sakit-form">
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-2">Jenis Keterangan</label>
                    <select id="izin-sakit-type" class="w-full p-2 border rounded-lg" required>
                        <option value="">Pilih jenis...</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-2">Keterangan</label>
                    <textarea id="izin-sakit-alasan" class="w-full p-3 border rounded" rows="4" placeholder="Tulis keterangan izin/sakit..." required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-2">Upload Bukti</label>
                    <input type="file" id="izin-sakit-bukti" accept="image/*" class="w-full p-2 border rounded" required>
                    <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Format: JPG, PNG, GIF</p>
                    <div id="izin-sakit-preview" class="mt-2 hidden">
                        <img id="izin-sakit-preview-img" src="" alt="Preview" class="w-full h-32 object-cover rounded border">
                    </div>
                    <div id="izin-sakit-error" class="mt-2 text-red-600 text-sm hidden"></div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="izin-sakit-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ket Detail Modal -->
    <div id="ket-detail-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 id="ket-detail-title" class="text-xl font-bold"></h3>
                <button onclick="qs('#ket-detail-modal').classList.add('hidden'); qs('#ket-detail-modal').classList.remove('flex')" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>
            <div id="ket-detail-content"></div>
        </div>
    </div>

    <!-- Modal Absence was removed as it is now centrally managed in modals_common.blade.php -->


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
                <table class="w-full text-sm text-left border-collapse">
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

    <!-- Modal Edit Bukti Izin/Sakit -->
    <div id="edit-bukti-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-70 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-lg">
            <h3 class="text-xl font-bold mb-4">Edit Bukti Izin/Sakit</h3>
            <div class="grid gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Upload Bukti Baru</label>
                    <input type="file" id="edit-bukti-file" accept="image/*" class="w-full p-3 border rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Format: JPG, PNG, GIF</p>
                </div>
                <div class="mt-2">
                    <video id="edit-bukti-video" autoplay playsinline class="w-full h-48 object-cover rounded-lg hidden"></video>
                    <canvas id="edit-bukti-canvas" class="hidden"></canvas>
                    <img id="edit-bukti-preview" class="mt-2 h-32 w-32 object-cover rounded-lg hidden">
                    <button type="button" id="edit-bukti-capture" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm hidden">Ambil Foto</button>
                </div>
                <div id="edit-bukti-current" class="hidden">
                    <label class="block text-sm text-gray-600 mb-1">Bukti Saat Ini:</label>
                    <img id="edit-bukti-current-img" class="w-full max-w-md h-48 object-cover rounded border">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" id="edit-bukti-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button type="button" id="edit-bukti-save" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal Screenshot -->
    <div id="screenshot-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 id="screenshot-modal-title" class="text-xl font-bold"></h3>
                <button onclick="closeScreenshotModal()" class="text-gray-500 hover:text-gray-700 text-2xl">✕</button>
            </div>
            <div class="text-center">
                <img id="screenshot-modal-image" src="" alt="Screenshot" class="max-w-full max-h-[70vh] object-contain mx-auto rounded-lg shadow-lg">
            </div>
        </div>
    </div>

    <!-- Modal Daily Report Review -->
    <div id="dr-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl relative">
            <button id="dr-close" class="absolute top-3 right-3 text-gray-500">✕</button>
            <h3 class="text-xl font-bold mb-2">Laporan Harian</h3>
            <div id="dr-content" class="whitespace-pre-wrap border p-3 rounded mb-3 text-sm"></div>
            <textarea id="dr-evaluation" class="w-full border rounded p-2" rows="4" placeholder="Evaluasi admin..."></textarea>
            <div class="flex justify-end gap-2 mt-4">
                <button id="dr-disapprove" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Disapprove</button>
                <button id="dr-approve" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Approve</button>
            </div>
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

<!-- Loading Overlay for model -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-75 flex flex-col items-center justify-center z-60 hidden">
    <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-24 w-24 mb-4"></div>
    <h2 class="text-center text-white text-xl font-semibold">Memuat Sistem Presensi...</h2>
    <p class="w-1/3 text-center text-white text-sm">Memuat model AI dan database wajah. Mohon tunggu sebentar.</p>
    <div class="mt-4 text-white text-xs opacity-75">
        <div id="loading-progress">Memulai...</div>
    </div>
</div>

<div id="notif-bar" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-lg z-70 hidden"></div>

<!-- Global Notification Modal -->
<div id="global-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] hidden">
    <div class="bg-white w-full max-w-md rounded-lg shadow-2xl p-6">
        <div id="global-modal-title" class="text-lg font-semibold mb-2">Notifikasi</div>
        <div id="global-modal-message" class="text-gray-700 mb-4"></div>
        <div class="text-right">
            <button id="global-modal-close" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Tutup</button>
        </div>
    </div>
    </div>

<script>
function showNotif(msg, success=true){
    const bar = qs('#notif-bar');
    bar.textContent = msg;
    bar.className = `fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-lg shadow-lg z-70 ${success?'bg-emerald-600':'bg-red-600'} text-white`;
    bar.classList.remove('hidden');
    setTimeout(()=> bar.classList.add('hidden'), 1500); // Faster notification dismissal
}
function showModalNotif(message, success=true, title='Notifikasi'){
    const m = qs('#global-modal');
    const t = qs('#global-modal-title');
    const c = qs('#global-modal-message');
    if(!m||!t||!c) return showNotif(message, success);
    t.textContent = title;
    c.textContent = message;
    m.classList.remove('hidden');
}
document.addEventListener('click', (e)=>{
    if(e.target.id==='global-modal-close' || e.target.id==='global-modal'){
        qs('#global-modal').classList.add('hidden');
    }
});
function qs(sel){ return document.querySelector(sel); }
function qsa(sel){ return Array.from(document.querySelectorAll(sel)); }

// Screenshot modal functions
function showScreenshotModal(imageSrc, title) {
    const modal = qs('#screenshot-modal');
    const modalTitle = qs('#screenshot-modal-title');
    const modalImage = qs('#screenshot-modal-image');
    
    if (modal && modalTitle && modalImage) {
        modalTitle.textContent = title;
        modalImage.src = imageSrc;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeScreenshotModal() {
    const modal = qs('#screenshot-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Lazy loading evidence function
/**
 * Render landmark face geometry onto a canvas element.
 * landmarkData: JSON string or parsed array of {x, y} objects (68 points)
 * Returns the canvas element (width x height).
 */
function renderLandmarkOnCanvas(canvas, landmarkData, w, h) {
    canvas.width  = w || 160;
    canvas.height = h || 120;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    let pts = landmarkData;
    if (typeof pts === 'string') {
        try { pts = JSON.parse(pts); } catch(e) { return canvas; }
    }
    if (!Array.isArray(pts) || pts.length < 5) return canvas;

    // Normalise: find bounding box and scale to canvas
    const xs = pts.map(p => p.x), ys = pts.map(p => p.y);
    const minX = Math.min(...xs), maxX = Math.max(...xs);
    const minY = Math.min(...ys), maxY = Math.max(...ys);
    const rangeX = maxX - minX || 1, rangeY = maxY - minY || 1;
    const pad = 0.1;
    const toX = x => ((x - minX) / rangeX * (1 - 2*pad) + pad) * canvas.width;
    const toY = y => ((y - minY) / rangeY * (1 - 2*pad) + pad) * canvas.height;

    // Color groups (68-point dlib layout)
    const groups = [
        { range:[0,16],  color:'#64748b', close:false }, // Jawline
        { range:[17,21], color:'#fbbf24', close:false }, // Left eyebrow
        { range:[22,26], color:'#fbbf24', close:false }, // Right eyebrow
        { range:[27,35], color:'#f97316', close:false }, // Nose
        { range:[36,41], color:'#60a5fa', close:true  }, // Left eye
        { range:[42,47], color:'#60a5fa', close:true  }, // Right eye
        { range:[48,59], color:'#f472b6', close:true  }, // Outer lips
        { range:[60,67], color:'#f472b6', close:true  }, // Inner lips
    ];

    groups.forEach(g => {
        const slice = pts.slice(g.range[0], g.range[1] + 1);
        if (!slice.length) return;
        ctx.beginPath();
        ctx.strokeStyle = g.color;
        ctx.lineWidth   = canvas.width < 100 ? 1 : 1.5;
        ctx.moveTo(toX(slice[0].x), toY(slice[0].y));
        slice.forEach(p => ctx.lineTo(toX(p.x), toY(p.y)));
        if (g.close) ctx.closePath();
        ctx.stroke();
        // Dots
        ctx.fillStyle = g.color;
        slice.forEach(p => {
            ctx.beginPath();
            ctx.arc(toX(p.x), toY(p.y), canvas.width < 100 ? 1 : 1.5, 0, Math.PI*2);
            ctx.fill();
        });
    });
    return canvas;
}

/**
 * Show a large landmark modal when admin clicks a thumbnail canvas.
 */
function showAdminLandmarkModal(landmarkData, title) {
    let modal = document.getElementById('admin-landmark-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'admin-landmark-modal';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem';
        modal.innerHTML = `
            <div style="background:#fff;border-radius:1rem;padding:1.5rem;max-width:480px;width:100%;box-shadow:0 25px 50px rgba(0,0,0,.5)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                    <h3 id="alm-title" style="font-weight:700;font-size:1rem;color:#1e293b"></h3>
                    <button onclick="document.getElementById('admin-landmark-modal').style.display='none'" style="font-size:1.5rem;color:#94a3b8;background:none;border:none;cursor:pointer">&times;</button>
                </div>
                <div style="background:#0f172a;border-radius:.75rem;overflow:hidden;margin-bottom:1rem">
                    <canvas id="alm-canvas" style="display:block;width:100%"></canvas>
                </div>
                <div style="font-size:.7rem;color:#64748b;line-height:1.8">
                    <span style="color:#64748b">&#9679;</span> Rahang &nbsp;
                    <span style="color:#fbbf24">&#9679;</span> Alis &nbsp;
                    <span style="color:#f97316">&#9679;</span> Hidung &nbsp;
                    <span style="color:#60a5fa">&#9679;</span> Mata &nbsp;
                    <span style="color:#f472b6">&#9679;</span> Mulut
                    <p style="margin-top:.5rem;font-style:italic">Visualisasi 68 titik landmark geometri wajah sebagai bukti presensi</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', e => { if(e.target===modal) modal.style.display='none'; });
    }
    document.getElementById('alm-title').textContent = title || 'Bukti Landmark Wajah';
    const bigCanvas = document.getElementById('alm-canvas');
    renderLandmarkOnCanvas(bigCanvas, landmarkData, 440, 320);
    modal.style.display = 'flex';
}

async function loadAndShowEvidence(id, type, title) {
    const modal = qs('#screenshot-modal');
    const modalTitle = qs('#screenshot-modal-title');
    const modalImage = qs('#screenshot-modal-image');
    
    if (!modal || !modalTitle) return;

    // Show loading
    modalTitle.textContent = 'Memuat ' + title + '...';
    if (modalImage) { modalImage.src = ''; modalImage.alt = 'Memuat...'; modalImage.style.display=''; }
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const res = await api('?ajax=get_attendance_evidence&id=' + id + '&type=' + type);
        
        // Handle landmark response (face attendance proof)
        if (res.ok && (res.landmark || res.type === 'landmark')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            showAdminLandmarkModal(res.landmark, title);
            return;
        }
        
        // Handle image/evidence response (izin, sakit, bukti notes)
        const imgData = res.image || res.evidence;
        if (res.ok && imgData) {
            if (modalImage) {
                modalImage.src = imgData;
                modalImage.alt = title;
                modalImage.style.display = '';
            }
            modalTitle.textContent = title;
        } else {
            modalTitle.textContent = 'Tidak Ada Bukti';
            if (modalImage) { modalImage.src = ''; modalImage.alt = 'Bukti tidak ditemukan.'; }
            showNotif('Tidak ada bukti tersedia untuk record ini', false);
        }
    } catch (err) {
        modalTitle.textContent = 'Error';
        if (modalImage) { modalImage.alt = 'Terjadi kesalahan saat memuat.'; }
        console.error(err);
    }
}

async function loadLazyProof(id, type, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const originalContent = container.innerHTML;
    container.innerHTML = '<div class="text-center py-4 text-indigo-600"><i class="fi fi-sr-spinner animate-spin text-2xl"></i><p class="text-xs mt-1">Memuat...</p></div>';

    try {
        const res = await api('?ajax=get_attendance_evidence&id=' + id + '&type=' + type);
        
        // Handle landmark data
        if (res.ok && (res.landmark || res.type === 'landmark')) {
            container.innerHTML = '';
            const c = document.createElement('canvas');
            c.style.cssText = 'border-radius:.5rem;cursor:pointer;width:100%';
            c.title = 'Klik untuk memperbesar';
            renderLandmarkOnCanvas(c, res.landmark, 320, 240);
            c.onclick = () => showAdminLandmarkModal(res.landmark, 'Bukti Presensi Wajah');
            container.appendChild(c);
            return;
        }
        
        // Handle image (bukti izin/sakit)
        const imgData = res.image || res.evidence;
        if (res.ok && imgData) {
            container.innerHTML = `<img src="${imgData}" alt="Bukti" class="max-w-full max-h-96 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">`;
        } else {
            container.innerHTML = originalContent;
            showNotif('Tidak ada bukti tersedia', false);
        }
    } catch (err) {
        container.innerHTML = originalContent;
        console.error(err);
        showNotif('Terjadi kesalahan saat memuat bukti', false);
    }
}}

// Close screenshot modal when clicking outside
document.addEventListener('click', (e) => {
    const modal = qs('#screenshot-modal');
    if (modal && !modal.contains(e.target) && !e.target.closest('img[onclick*="showScreenshotModal"]')) {
        closeScreenshotModal();
    }
});
// Add global variables to manage speech synthesis
let currentSpeech = null;
let speechQueue = [];
let isSpeaking = false;
let speechInterval = null;

function speak(text) {
    try {
        // Check if speech synthesis is available
        if (!('speechSynthesis' in window)) {
            console.warn('Speech synthesis not supported');
            return;
        }

        // Add to queue instead of canceling immediately
        if (text && text.trim() && text !== lastSpokenMessage) {
            speechQueue.push(text);
            lastSpokenMessage = text;
        }

        // Start speech processing if not already running
        if (!isSpeaking) {
            processSpeechQueue();
        }
        return;

    } catch (e) {
        console.error('Speech synthesis error:', e);
        isSpeaking = false;
        speechQueue = [];
    }
}

function processSpeechQueue() {
    if (isSpeaking || speechQueue.length === 0) return;
    
    isSpeaking = true;
    const text = speechQueue.shift();
    
    try {
        // Cancel any ongoing speech
        speechSynthesis.cancel();
        
        // Wait for voices to be loaded
        const speakWithVoice = () => {
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'id-ID';
            u.rate = 0.9; // Faster rate for speed
            u.pitch = 1.0;
            u.volume = 1.0;

            // Try to use a local voice if available
            const voices = speechSynthesis.getVoices();
            const indonesianVoice = voices.find(voice => 
                voice.lang.startsWith('id') || 
                voice.lang.includes('Indonesian') ||
                voice.name.includes('Indonesian')
            );
            
            if (indonesianVoice) {
                u.voice = indonesianVoice;
            } else if (voices.length > 0) {
                // Use any available voice as fallback
                u.voice = voices[0];
            }

            u.onstart = () => {
                console.log('Speech started:', text);
            };

            u.onend = () => {
                console.log('Speech ended:', text);
                isSpeaking = false;
                
                // Process next in queue after a short delay
                setTimeout(() => {
                    if (speechQueue.length > 0) {
                        processSpeechQueue();
                    } else if (isCameraActive && !videoInterval && !isDetectionStopped) {
                        startVideoInterval();
                    }
                }, 200); // 200ms interval between speeches
            };

            u.onerror = (e) => {
                console.error('Speech error:', e);
                isSpeaking = false;
                
                // Skip this speech and continue with queue
                setTimeout(() => {
                    if (speechQueue.length > 0) {
                        processSpeechQueue();
                    } else if (isCameraActive && !videoInterval && !isDetectionStopped) {
                        startVideoInterval();
                    }
                }, 100);
            };

            speechSynthesis.speak(u);
            currentSpeech = u;
        };

        // If voices are already loaded, speak immediately
        if (speechSynthesis.getVoices().length > 0) {
            speakWithVoice();
        } else {
            // Wait for voices to load
            speechSynthesis.addEventListener('voiceschanged', speakWithVoice, { once: true });
            
            // Fallback if no voices
            if (speechSynthesis.getVoices().length === 0) {
                console.warn('No voices available, speaking with default settings');
                speakWithVoice();
            }
        }

    } catch (e) {
        console.error('Speech processing error:', e);
        isSpeaking = false;
        
        // Continue with queue
        setTimeout(() => {
            if (speechQueue.length > 0) {
                processSpeechQueue();
            }
        }, 100);
    }
}

// Modify the `statusMessage` function to use the improved `speak` function
let notifLockUntil = 0;
function statusMessage(text, cls) {
    if (!presensiStatus) return;
    
    // Show the text notification
    presensiStatus.textContent = text;
    presensiStatus.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md ' + cls;
    presensiStatus.classList.remove('hidden');

    // Hindari interupsi suara untuk pesan non-kritis
    const now = Date.now();
    const isCritical = /bg-(green|yellow|red)-100/.test(cls || '');
    if (isCritical || now > notifLockUntil) {
        // Hitung durasi lock berdasarkan panjang teks agar tidak terpotong
        const dur = Math.max(2500, Math.min(7000, text.length * 60));
        notifLockUntil = now + dur;
        speak(text);
    }
}



// ===== IndexedDB caching for face descriptors =====
function simpleHash(str){
    let h = 5381; for (let i=0;i<str.length;i++){ h = ((h<<5)+h) + str.charCodeAt(i); h |= 0; }
    return 'v' + (h >>> 0).toString(16);
}

async function computeMembersVersionKey(membersList){
    try{
        const basis = membersList.map(m=>[m.nim, m.foto||m.photo||m.image||'', m.nama||'']).sort((a,b)=>String(a[0]).localeCompare(String(b[0])));
        return simpleHash(JSON.stringify(basis));
    }catch(e){ return 'v-default'; }
}

function idbOpen(){
    return new Promise((resolve,reject)=>{
        const req = indexedDB.open('presensi-cache', 1);
        req.onupgradeneeded = (e)=>{
            const db = e.target.result;
            if (!db.objectStoreNames.contains('descriptors')) {
                db.createObjectStore('descriptors');
            }
        };
        req.onsuccess = ()=> resolve(req.result);
        req.onerror = ()=> reject(req.error);
    });
}

async function idbGetDescriptors(versionKey){
    try{
        const db = await idbOpen();
        return await new Promise((resolve,reject)=>{
            const tx = db.transaction('descriptors','readonly');
            const store = tx.objectStore('descriptors');
            const getReq = store.get(versionKey);
            getReq.onsuccess = ()=> resolve(getReq.result||null);
            getReq.onerror = ()=> resolve(null);
        });
    }catch(e){ return null; }
}

async function idbSetDescriptors(versionKey, data){
    try{
        const db = await idbOpen();
        return await new Promise((resolve,reject)=>{
            const tx = db.transaction('descriptors','readwrite');
            const store = tx.objectStore('descriptors');
            const putReq = store.put(data, versionKey);
            putReq.onsuccess = ()=> resolve(true);
            putReq.onerror = ()=> resolve(false);
        });
    }catch(e){ return false; }
}

async function api(url, data, opts){
    const options = opts || {};
    try {
        // Log the data being sent (but not the full screenshot to avoid console spam)
        const logData = { ...data };
        if (logData.screenshot) {
            logData.screenshot = logData.screenshot.substring(0, 50) + '... (truncated)';
        }
        // ULTRA-FAST: Skip logging for maximum speed
        
        // Ensure URL is correct - use relative URL to avoid port issues
        if (url.startsWith('http')) {
            // If it's already a full URL, use it as is
        } else if (url.startsWith('/')) {
            // If it starts with /, it's already a proper relative URL
        } else if (url.startsWith('?')) {
            // If it starts with ?, it's a query string, use current page
            url = window.location.pathname + url;
        } else {
            // If it's a relative URL, make it start with /api/
            url = '/api/' + url.replace(/^\//, '');
        }
        
        // Fallback: if URL contains localhost:3000, replace with current host
        if (url.includes('localhost:3000')) {
            url = url.replace('localhost:3000', window.location.host);
        }
        
        // Additional fallback: if URL still contains localhost:3000, force use current origin
        if (url.includes('localhost:3000')) {
            url = window.location.origin + url.replace(/^https?:\/\/[^\/]+/, '');
        }
        
        // Check cache for GET-like requests (ajax queries) - NEVER cache POST/PUT/DELETE
        const method = (options.method || 'POST').toUpperCase();
        if (options.cache !== false && method === 'GET' && !(data instanceof FormData)) {
            const cachedResponse = apiCache.get(url, data);
            if (cachedResponse) {
                return cachedResponse;
            }
        }

        // ULTRA-FAST: Skip all logging for maximum speed
        const res = await fetch(url, { 
            method: method, 
            body: data instanceof FormData ? data : new URLSearchParams(data),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            // ULTRA-FAST: No timeout, let browser handle it for maximum speed
        });
        
        // Check if response is ok
        if (!res.ok) {
            const errorText = await res.text();
            console.error('API Error Response:', errorText);
            
            // Try to parse error response as JSON to check for special requirements
            try {
                const errorJson = JSON.parse(errorText);
                if (errorJson.need_reason || errorJson.need_overtime_reason || errorJson.need_early_leave_reason) {
                    // Return the error response directly instead of throwing
                    // This allows the calling function to handle modals for WFA/Overtime/Early Leave
                    return errorJson;
                }
            } catch (parseError) {
                // If not JSON, continue with normal error handling
            }
            
            // Try to parse error response to get specific message
            let errorMessage = `Terjadi kesalahan (${res.status})`;
            try {
                const errorJson = JSON.parse(errorText);
                if (errorJson.message) {
                    errorMessage = errorJson.message;
                } else if (errorJson.error) {
                    errorMessage = errorJson.error;
                }
            } catch (e) {
                // Use default message
            }
            
            if (!options.suppressModal) {
                showModalNotif(errorMessage, false, 'Gagal');
            }
            throw new Error(`HTTP error! status: ${res.status}, response: ${errorText}`);
        }
        
        // Get response text first to check if it's valid JSON
        const responseText = await res.text();
        
        // Try to parse as JSON
        let json;
        try {
            json = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Response is not valid JSON:', responseText);
            throw new Error('Server returned invalid JSON response');
        }
        
        // Return the JSON response regardless of HTTP status code
        // Let the calling function handle the business logic (ok: false, etc.)
        if (!options.suppressModal) {
            if(json && json.ok===true && json.message){
                showModalNotif(json.message, true, 'Berhasil');
            } else if(json && json.ok===false && json.message){
                showModalNotif(json.message, false, 'Gagal');
            }
        }
        return json;
    } catch (error) {
        console.error('API call failed:', error);
        
        // Perbaikan: Handle specific error types
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            console.error('Network error - check if server is running');
            throw new Error('Koneksi ke server gagal. Pastikan server berjalan.');
        } else if (error.message.includes('ERR_CONNECTION_REFUSED')) {
            console.error('Connection refused - server not responding');
            throw new Error('Server tidak merespons. Silakan coba lagi.');
        }
        
        // Provide more specific error messages
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            throw new Error('Tidak dapat terhubung ke server. Pastikan XAMPP sudah berjalan.');
        } else if (error.message.includes('HTTP error! status: 400')) {
            // Check if it's a time validation error
            if (error.message.includes('Presensi masuk hanya tersedia') || error.message.includes('Presensi masuk tersedia')) {
                throw new Error('Waktu presensi tidak sesuai. Silakan coba pada jam yang tepat.');
            } else {
                throw new Error('Data yang dikirim tidak valid. Silakan coba lagi.');
            }
        } else if (error.message.includes('HTTP error! status: 500')) {
            throw new Error('Server error. Silakan coba lagi.');
        }
        
        throw error;
    }
}

// Port Detection and Fix
(function() {
    // Check if we're on the wrong port
    if (window.location.port === '3000') {
        console.warn('Detected port 3000, redirecting to correct XAMPP port...');
        // Try common XAMPP ports
        const xamppPorts = ['80', '8080', '8000'];
        let redirectAttempted = false;
        
        for (const port of xamppPorts) {
            if (!redirectAttempted) {
                const testUrl = `http://localhost:${port}${window.location.pathname}${window.location.search}`;
                fetch(testUrl, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok && !redirectAttempted) {
                            redirectAttempted = true;
                            console.log(`Redirecting to port ${port}`);
                            window.location.href = testUrl;
                        }
                    })
                    .catch(() => {
                        // Port not available, try next
                    });
            }
        }
    }
})();

// Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
                // Force update if there's a new service worker
                if (registration.waiting) {
                    registration.waiting.postMessage({ action: 'skipWaiting' });
                }
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Profile dropdown
(function(){
    const btn = qs('#btn-profile');
    const dd = qs('#dropdown-profile');
    if(btn && dd){
        btn.addEventListener('click', ()=> dd.classList.toggle('hidden'));
        document.addEventListener('click', (e)=>{ if(!btn.contains(e.target) && !dd.contains(e.target)) dd.classList.add('hidden'); });
    }
})();

<?php if ($page === 'login'): ?>
// Login
const loginForm = qs('#form-login');
if (loginForm) {
    loginForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const r = await api('?ajax=login', fd);
        const msg = qs('#login-msg');
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = 'Login berhasil. Mengalihkan...';
            setTimeout(()=> location.href='?', 200); // Faster redirect
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Gagal login';
        }
    });
}
<?php elseif ($page === 'register'): ?>
// Register camera
const regStart = qs('#reg-start-camera');
const regTake = qs('#reg-take-photo');
const regUpload = qs('#reg-upload-photo');
const regRemove = qs('#reg-remove-photo');
const regVideo = qs('#reg-video');
const regCanvas = qs('#reg-canvas');
const regPreview = qs('#reg-foto-preview');
const regVidContainer = qs('#reg-video-container');
const regFotoData = qs('#reg-foto-data');
const regPhotoFileInput = qs('#reg-photo-file-input');
let regStream = null;

if (regStart) {
    regStart.addEventListener('click', async ()=>{
        try{
            regStream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } });
            regVideo.srcObject = regStream;
            regVidContainer.classList.remove('hidden');
            regTake.classList.remove('hidden');
            regStart.classList.add('hidden');
        }catch(err){ showNotif('Tidak bisa mengakses kamera'); console.error(err); }
    });
}

if (regTake) {
    regTake.addEventListener('click', ()=>{
        const ctx = regCanvas.getContext('2d');
        regCanvas.width = regVideo.videoWidth;
        regCanvas.height = regVideo.videoHeight;
        ctx.drawImage(regVideo,0,0,regCanvas.width,regCanvas.height);
        const dataUrl = regCanvas.toDataURL('image/jpeg');
        regPreview.src = dataUrl; regPreview.classList.remove('hidden');
        regFotoData.value = dataUrl;
        if(regStream){ regStream.getTracks().forEach(t=>t.stop()); regStream=null; }
        regVidContainer.classList.add('hidden');
        regTake.classList.add('hidden');
        regStart.classList.remove('hidden');
        regStart.textContent = 'Ambil Ulang Foto';
        regRemove.classList.remove('hidden');
    });
}

// Upload photo functionality
if (regUpload) {
    regUpload.addEventListener('click', ()=>{
        regPhotoFileInput.click();
    });
}

if (regPhotoFileInput) {
    regPhotoFileInput.addEventListener('change', (e)=>{
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showNotif('File harus berupa gambar', false);
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showNotif('Ukuran file maksimal 5MB', false);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                regPreview.src = dataUrl;
                regPreview.classList.remove('hidden');
                regFotoData.value = dataUrl;
                regRemove.classList.remove('hidden');
                regStart.textContent = 'Buka Kamera';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove photo functionality
if (regRemove) {
    regRemove.addEventListener('click', ()=>{
        regPreview.src = '';
        regPreview.classList.add('hidden');
        regFotoData.value = '';
        regRemove.classList.add('hidden');
        regPhotoFileInput.value = '';
        regStart.textContent = 'Buka Kamera';
        
        // Stop camera if running
        if(regStream){ 
            regStream.getTracks().forEach(t=>t.stop()); 
            regStream=null; 
        }
        regVidContainer.classList.add('hidden');
        regTake.classList.add('hidden');
    });
}

const registerForm = qs('#form-register');
if (registerForm) {
    registerForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const r = await api('?ajax=register', fd);
        const msg = qs('#register-msg');
        if(r.ok){ msg.className='text-green-600'; msg.textContent='Registrasi berhasil. Silakan login.'; setTimeout(()=>location.href='?page=login', 300); } // Faster redirect
        else { msg.className='text-red-600'; msg.textContent=r.message||'Gagal registrasi'; }
    });
}
<?php elseif ($page === 'forgot-password'): ?>
// Forgot Password
const forgotPasswordForm = qs('#form-forgot-password');
if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#forgot-password-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Mengirim permintaan...';
        
        try {
            const r = await api('?ajax=forgot_password', fd);
            if(r.ok){
                // Direct redirect to verify-otp without showing message
                if (r.token) {
                    window.location.href = '?page=verify-otp&token=' + encodeURIComponent(r.token);
                } else if (r.reset_url) {
                    window.location.href = r.reset_url;
                }
            } else {
                msg.className = 'text-red-600';
                msg.textContent = r.message || 'Email tidak ditemukan atau belum memiliki Google Authenticator';
            }
        } catch (error) {
            msg.className = 'text-red-600';
            msg.textContent = 'Email tidak ditemukan atau belum memiliki Google Authenticator';
            console.error('Forgot password error:', error);
        }
    });
}

// Check for token in URL and redirect to verify-otp
const urlParams = new URLSearchParams(window.location.search);
const tokenParam = urlParams.get('token');
if (tokenParam) {
    window.location.href = '?page=verify-otp&token=' + encodeURIComponent(tokenParam);
}
<?php elseif ($page === 'verify-otp'): ?>
// Verify OTP
const verifyOtpForm = qs('#form-verify-otp');
if (verifyOtpForm) {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tokenFromUrl = urlParams.get('token');
    
    if (tokenFromUrl) {
        qs('#reset-token').value = tokenFromUrl;
    }
    
    verifyOtpForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#verify-otp-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Memverifikasi OTP...';
        
        const r = await api('?ajax=verify_otp', fd);
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = r.message || 'OTP berhasil diverifikasi.';
            setTimeout(()=>{
                window.location.href = '?page=reset-password&token=' + encodeURIComponent(r.token || fd.get('token'));
            }, 1500);
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Kode OTP tidak valid';
        }
    });
    
    // Auto-focus OTP input
    const otpInput = verifyOtpForm.querySelector('input[name="otp"]');
    if (otpInput) {
        otpInput.focus();
    }
}
<?php elseif ($page === 'reset-password'): ?>
// Reset Password
const resetPasswordForm = qs('#form-reset-password');
if (resetPasswordForm) {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tokenFromUrl = urlParams.get('token');
    
    if (tokenFromUrl) {
        qs('#reset-token-final').value = tokenFromUrl;
    }
    
    resetPasswordForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(e.target);
        const msg = qs('#reset-password-msg');
        msg.className = 'text-blue-600';
        msg.textContent = 'Mereset password...';
        
        const r = await api('?ajax=reset_password', fd);
        if(r.ok){
            msg.className = 'text-green-600';
            msg.textContent = r.message || 'Password berhasil direset.';
            setTimeout(()=>{
                window.location.href = '?page=login';
            }, 2000);
        } else {
            msg.className = 'text-red-600';
            msg.textContent = r.message || 'Gagal mereset password';
        }
    });
}
<?php elseif ($page === 'landing'): ?>
// Browser compatibility polyfills
(function() {
    // Polyfill for getUserMedia for older browsers
    if (!navigator.mediaDevices) {
        navigator.mediaDevices = {};
    }
    if (!navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia = function(constraints) {
            const getUserMedia = navigator.getUserMedia || 
                                 navigator.webkitGetUserMedia || 
                                 navigator.mozGetUserMedia || 
                                 navigator.msGetUserMedia;
            
            if (!getUserMedia) {
                return Promise.reject(new Error('getUserMedia is not supported in this browser'));
            }
            
            return new Promise(function(resolve, reject) {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        };
    }
    
    // Polyfill for Promise if needed (for very old browsers)
    if (typeof Promise === 'undefined') {
        window.Promise = function(executor) {
            // Simple Promise polyfill
            const self = this;
            self.state = 'pending';
            self.value = undefined;
            self.handlers = [];
            
            function resolve(result) {
                if (self.state === 'pending') {
                    self.state = 'fulfilled';
                    self.value = result;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }
            
            function reject(error) {
                if (self.state === 'pending') {
                    self.state = 'rejected';
                    self.value = error;
                    self.handlers.forEach(handle);
                    self.handlers = null;
                }
            }
            
            function handle(handler) {
                if (self.state === 'pending') {
                    self.handlers.push(handler);
                } else {
                    if (self.state === 'fulfilled' && typeof handler.onFulfilled === 'function') {
                        handler.onFulfilled(self.value);
                    }
                    if (self.state === 'rejected' && typeof handler.onRejected === 'function') {
                        handler.onRejected(self.value);
                    }
                }
            }
            
            self.then = function(onFulfilled, onRejected) {
                return new Promise(function(resolve, reject) {
                    handle({
                        onFulfilled: function(result) {
                            try {
                                resolve(onFulfilled ? onFulfilled(result) : result);
                            } catch (ex) {
                                reject(ex);
                            }
                        },
                        onRejected: function(error) {
                            try {
                                resolve(onRejected ? onRejected(error) : error);
                            } catch (ex) {
                                reject(ex);
                            }
                        }
                    });
                });
            };
            
            executor(resolve, reject);
        };
    }
    
    // Performance optimization: RequestIdleCallback polyfill
    if (!window.requestIdleCallback) {
        window.requestIdleCallback = function(callback, options) {
            const start = Date.now();
            return setTimeout(function() {
                callback({
                    didTimeout: false,
                    timeRemaining: function() {
                        return Math.max(0, 50 - (Date.now() - start));
                    }
                });
            }, 1);
        };
    }
    
    if (!window.cancelIdleCallback) {
        window.cancelIdleCallback = function(id) {
            clearTimeout(id);
        };
    }
    
    // Browser-specific fixes
    const ua = navigator.userAgent.toLowerCase();
    const isSafari = /safari/.test(ua) && !/chrome/.test(ua) && !/chromium/.test(ua);
    const isFirefox = /firefox/.test(ua);
    const isChrome = /chrome/.test(ua) && !/edge/.test(ua);
    const isMIBrowser = /miui/.test(ua) || /xiaomi/.test(ua);
    const isEdge = /edge/.test(ua);
    
    // Safari-specific fixes
    if (isSafari) {
        // Safari has issues with video autoplay - ensure video plays
        if (HTMLVideoElement.prototype.play) {
            const originalPlay = HTMLVideoElement.prototype.play;
            HTMLVideoElement.prototype.play = function() {
                const promise = originalPlay.call(this);
                if (promise && promise.catch) {
                    promise.catch(() => {
                        // Ignore autoplay errors in Safari
                    });
                }
                return promise;
            };
        }
        
        // Safari canvas fix for better performance
        if (HTMLCanvasElement.prototype.getContext) {
            const originalGetContext = HTMLCanvasElement.prototype.getContext;
            HTMLCanvasElement.prototype.getContext = function(contextType, attributes) {
                if (contextType === '2d' && attributes) {
                    attributes.willReadFrequently = false; // Better performance in Safari
                }
                return originalGetContext.call(this, contextType, attributes);
            };
        }
    }
    
    // Firefox-specific fixes
    if (isFirefox) {
        // Firefox may need explicit video play
        if (HTMLVideoElement.prototype.play) {
            const originalPlay = HTMLVideoElement.prototype.play;
            HTMLVideoElement.prototype.play = function() {
                const promise = originalPlay.call(this);
                if (promise && promise.catch) {
                    promise.catch(() => {
                        // Try to play with user interaction
                        this.muted = true;
                        return originalPlay.call(this);
                    });
                }
                return promise;
            };
        }
    }
    
    // MI Browser / Xiaomi Browser fixes
    if (isMIBrowser) {
        // MI Browser may have issues with getUserMedia - add extra fallback
        if (!navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia = function(constraints) {
                const getUserMedia = navigator.getUserMedia || 
                                   navigator.webkitGetUserMedia || 
                                   navigator.mozGetUserMedia || 
                                   navigator.msGetUserMedia;
                
                if (!getUserMedia) {
                    return Promise.reject(new Error('getUserMedia is not supported'));
                }
                
                return new Promise(function(resolve, reject) {
                    getUserMedia.call(navigator, constraints, resolve, reject);
                });
            };
        }
    }
    
    // Edge-specific fixes
    if (isEdge) {
        // Edge may need specific handling
        if (HTMLVideoElement.prototype.srcObject === undefined) {
            Object.defineProperty(HTMLVideoElement.prototype, 'srcObject', {
                get: function() {
                    return this.mozSrcObject || this.webkitSrcObject || null;
                },
                set: function(stream) {
                    if (this.mozSrcObject !== undefined) {
                        this.mozSrcObject = stream;
                    } else if (this.webkitSrcObject !== undefined) {
                        this.webkitSrcObject = stream;
                    } else {
                        this.src = window.URL.createObjectURL(stream);
                    }
                }
            });
        }
    }
    
    // Cross-browser canvas optimization
    if (HTMLCanvasElement.prototype.getContext) {
        const originalGetContext = HTMLCanvasElement.prototype.getContext;
        HTMLCanvasElement.prototype.getContext = function(contextType, attributes) {
            if (contextType === '2d') {
                // Optimize canvas for better performance across all browsers
                const optimizedAttributes = attributes || {};
                optimizedAttributes.alpha = true;
                optimizedAttributes.desynchronized = false;
                optimizedAttributes.willReadFrequently = false; // Better performance
                return originalGetContext.call(this, contextType, optimizedAttributes);
            }
            return originalGetContext.call(this, contextType, attributes);
        };
    }
    
    // Log browser detection
    console.log(`Browser detected: ${isSafari ? 'Safari' : isFirefox ? 'Firefox' : isChrome ? 'Chrome' : isMIBrowser ? 'MI Browser' : isEdge ? 'Edge' : 'Other'}`);
})();

// Camera attendance logic moved to assets/js/attendance.js
<?php else: ?>
// App (logged in)
const pages = { rekap: qs('#page-rekap'), 'laporan-bulanan': qs('#page-laporan-bulanan'), members: qs('#page-members'), laporan: qs('#page-laporan'), 'admin-monthly': qs('#page-admin-monthly'), dashboard: qs('#page-dashboard'), settings: qs('#page-settings') };
qsa('.tab-link').forEach(btn=>{
    btn.addEventListener('click', ()=> showPage(btn.dataset.tab));
});

// Mobile sidebar tab links
qsa('.mobile-tab-link').forEach(btn=>{
    btn.addEventListener('click', ()=> {
        showPage(btn.dataset.tab);
        closeMobileSidebar(); // Close sidebar after clicking
    });
});

// Mobile sidebar functions
function openMobileSidebar() {
    const sidebar = qs('#mobile-sidebar');
    const overlay = qs('#mobile-sidebar-overlay');
    if (sidebar) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    }
    if (overlay) {
        overlay.classList.remove('hidden');
    }
    // Prevent body scroll when sidebar is open
    document.body.style.overflow = 'hidden';
}

function closeMobileSidebar() {
    const sidebar = qs('#mobile-sidebar');
    const overlay = qs('#mobile-sidebar-overlay');
    if (sidebar) {
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
    }
    if (overlay) {
        overlay.classList.add('hidden');
    }
    // Restore body scroll
    document.body.style.overflow = '';
}

// Auto clear cache when website is opened
(function() {
    'use strict';
    // Clear all caches
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
    // Clear service worker cache if exists
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        });
    }
    // Force reload if page is cached
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        window.location.reload();
    }
})();

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = qs('#mobile-menu-toggle');
    const sidebarClose = qs('#mobile-sidebar-close');
    const overlay = qs('#mobile-sidebar-overlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', openMobileSidebar);
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeMobileSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
    
    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });
});

function showPage(name){ 
    Object.values(pages).forEach(p=> p && (p.style.display='none')); 
    if(pages[name]) pages[name].style.display='block'; 
    
    // Update active state for desktop tabs
    qsa('.tab-link').forEach(btn => {
        if (btn.dataset.tab === name) {
            btn.classList.add('bg-indigo-700');
        } else {
            btn.classList.remove('bg-indigo-700');
        }
    });
    
    // Update active state for mobile tabs
    qsa('.mobile-tab-link').forEach(btn => {
        if (btn.dataset.tab === name) {
            btn.classList.add('bg-indigo-600', 'text-white');
            btn.classList.remove('text-gray-700', 'hover:bg-indigo-50', 'hover:text-indigo-600');
        } else {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-gray-700', 'hover:bg-indigo-50', 'hover:text-indigo-600');
        }
    });
    
    if(name==='members') renderMembers(); 
    if(name==='laporan') { loadStartupOptions(); renderLaporan(); } 
    if(name==='rekap') initRekapPage(); 
    if(name==='laporan-bulanan') renderMonthly(); 
    if(name==='admin-monthly') renderAdminMonthly(); 
    if(name==='dashboard') renderDashboard(); 
    if(name==='settings') { renderSettings(); initAddressSearch(); if(typeof loadBackupFiles === 'function') loadBackupFiles(); } 
}

// Ensure initial page sets after variables exist
<?php if (isAdmin()): ?>
showPage('dashboard');
<?php else: ?>
showPage('rekap');
<?php endif; ?>

// Header buttons for employees - navigate to landing page presensi with return parameter
document.addEventListener('DOMContentLoaded', () => {
    const btnHeaderMasuk = qs('#btn-header-presensi-masuk');
    const btnHeaderPulang = qs('#btn-header-presensi-pulang');
    
    if (btnHeaderMasuk) {
        btnHeaderMasuk.addEventListener('click', () => {
            window.location.href = '?page=landing&return=app&mode=masuk';
        });
    }
    
    if (btnHeaderPulang) {
        btnHeaderPulang.addEventListener('click', () => {
            window.location.href = '?page=landing&return=app&mode=pulang';
        });
    }
});

// Initialize month/year selectors for rekap page
document.addEventListener('DOMContentLoaded', () => {
    const monthSel = qs('#rekap-month');
    const yearSel = qs('#rekap-year');
    
    if (monthSel) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        months.forEach((month, index) => {
            const option = document.createElement('option');
            option.value = String(index + 1);
            option.textContent = month;
            if (index === new Date().getMonth()) {
                option.selected = true;
            }
            monthSel.appendChild(option);
        });
    }
    
    if (yearSel) {
        const currentYear = new Date().getFullYear();
        for (let year = currentYear - 2; year <= currentYear + 1; year++) {
            const option = document.createElement('option');
            option.value = String(year);
            option.textContent = String(year);
            if (year === currentYear) {
                option.selected = true;
            }
            yearSel.appendChild(option);
        }
    }
    
    // Initialize rekap page on load
    if (qs('#page-rekap')) {
        initRekapPage();
    }
});

// Presensi page for logged-in employees
let presensiVideo = null;
let presensiCanvas = null;
let presensiIsCameraActive = false;
let presensiVideoInterval = null;
let presensiScanMode = '';
let presensiProcessedLabels = new Map();
let presensiIsProcessingRecognition = false;
let presensiLabeledFaceDescriptors = [];
let presensiIsPresensiSuccess = false;

function initPresensiPage() {
    presensiVideo = qs('#video-presensi');
    presensiCanvas = qs('#canvas-presensi');
    
    // Reset state
    presensiIsCameraActive = false;
    presensiVideoInterval = null;
    presensiScanMode = '';
    presensiProcessedLabels = new Map();
    presensiIsProcessingRecognition = false;
    presensiIsPresensiSuccess = false;
    
    // Hide video container initially
    const videoContainer = qs('#video-container-presensi');
    const statusDiv = qs('#presensi-status-presensi');
    const btnBack = qs('#btn-back-presensi');
    const btnStop = qs('#btn-stop-detection-presensi');
    const btnStart = qs('#btn-start-detection-presensi');
    
    if (videoContainer) videoContainer.classList.add('hidden');
    if (statusDiv) statusDiv.classList.add('hidden');
    if (btnBack) btnBack.classList.add('hidden');
    if (btnStop) btnStop.classList.add('hidden');
    if (btnStart) btnStart.classList.add('hidden');
    
    // Button handlers
    const btnMasuk = qs('#btn-presensi-masuk');
    const btnPulang = qs('#btn-presensi-pulang');
    
    if (btnMasuk) {
        btnMasuk.onclick = () => startPresensi('masuk');
    }
    if (btnPulang) {
        btnPulang.onclick = () => startPresensi('pulang');
    }
    if (btnBack) {
        btnBack.onclick = () => {
            stopPresensiCamera();
            videoContainer.classList.add('hidden');
            btnBack.classList.add('hidden');
            btnStop.classList.add('hidden');
            btnStart.classList.add('hidden');
            if (statusDiv) {
                statusDiv.classList.add('hidden');
                statusDiv.textContent = '';
            }
            // Return to employee presensi page (show the buttons again)
            // The page-presensi is already visible, we just need to ensure buttons are visible
            // The buttons are always visible when video container is hidden
        };
    }
    if (btnStop) {
        btnStop.onclick = () => {
            stopPresensiCamera();
            btnStop.classList.add('hidden');
            btnStart.classList.remove('hidden');
        };
    }
    if (btnStart) {
        btnStart.onclick = () => {
            if (!presensiScanMode) return;
            startPresensiCamera();
            btnStart.classList.add('hidden');
            btnStop.classList.remove('hidden');
        };
    }
}

async function startPresensi(mode) {
    presensiScanMode = mode;
    presensiIsPresensiSuccess = false;
    
    // Force request camera and location permissions BEFORE starting
    try {
        // Request camera permission explicitly
        const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
        // Stop it immediately - we just want to trigger the permission request
        cameraStream.getTracks().forEach(track => track.stop());
        
        // Request location permission explicitly  
        if (!navigator.geolocation) {
            showModalNotif('GPS tidak tersedia di perangkat Anda. Pastikan GPS aktif.', false, 'Izin Lokasi');
            return;
        }
        
        // Request location permission by trying to get position
        await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                () => resolve(true),
                (err) => {
                    if (err.code === err.PERMISSION_DENIED) {
                        showModalNotif('Izin lokasi diperlukan untuk presensi. Silakan aktifkan izin lokasi di pengaturan browser.', false, 'Izin Lokasi');
                        reject(new Error('Location permission denied'));
                    } else {
                        // Other errors are okay (timeout, etc) - we'll retry later
                        resolve(true);
                    }
                },
                { timeout: 5000, enableHighAccuracy: true }
            );
        });
    } catch (error) {
        if (error.name === 'NotAllowedError' || error.message === 'Location permission denied') {
            // Permission denied - user needs to enable it
            return; // Don't proceed
        } else if (error.name === 'NotFoundError') {
            showModalNotif('Kamera tidak ditemukan. Pastikan kamera terhubung.', false, 'Kamera Tidak Tersedia');
            return;
        } else {
            // Other errors - might be timeout, we'll proceed anyway
            console.warn('Permission check warning:', error);
        }
    }
    
    // Show video container
    const videoContainer = qs('#video-container-presensi');
    const btnBack = qs('#btn-back-presensi');
    const btnStop = qs('#btn-stop-detection-presensi');
    const btnStart = qs('#btn-start-detection-presensi');
    
    if (videoContainer) {
        videoContainer.classList.remove('hidden');
    }
    if (btnBack) btnBack.classList.remove('hidden');
    if (btnStop) btnStop.classList.remove('hidden');
    if (btnStart) btnStart.classList.add('hidden');
    
    // Load face recognition models and start camera
    await loadPresensiFaceModels();
    startPresensiCamera();
}

async function loadPresensiFaceModels() {
    // Load face-api.js models
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('/face-api-models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/face-api-models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/face-api-models');
        
        // Load face descriptors from database
        const res = await fetch('?ajax=get_members');
        const j = await res.json();
        const members = j.data || [];
        
        presensiLabeledFaceDescriptors = [];
        for (const member of members) {
            // Priority 1: Use pre-calculated face embedding if available
            if (member.face_embedding) {
                try {
                    const descriptor = new Float32Array(Object.values(JSON.parse(member.face_embedding)));
                    presensiLabeledFaceDescriptors.push(
                        new faceapi.LabeledFaceDescriptors(member.nim || '', [descriptor])
                    );
                } catch (e) {
                    console.error('Error parsing face_embedding for:', member.nim, e);
                }
            } 
            // Priority 2: Fallback to generating embedding from photo if available (and if we fetched it)
            else if (member.foto_base64) {
                try {
                    const img = await faceapi.fetchImage(member.foto_base64);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                    if (detection) {
                        presensiLabeledFaceDescriptors.push(
                            new faceapi.LabeledFaceDescriptors(member.nim || '', [detection.descriptor])
                        );
                    }
                } catch (e) {
                    console.error('Error processing photo for:', member.nim, e);
                }
            }
        }
    } catch (error) {
        console.error('Error loading face models:', error);
        showModalNotif('Gagal memuat sistem pengenalan wajah. Silakan refresh halaman.', false, 'Error');
    }
}

function startPresensiCamera() {
    if (presensiIsCameraActive) return;
    
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            presensiVideo.srcObject = stream;
            presensiIsCameraActive = true;
            
            presensiVideo.addEventListener('loadedmetadata', () => {
                presensiCanvas.width = presensiVideo.videoWidth;
                presensiCanvas.height = presensiVideo.videoHeight;
                startPresensiDetection();
            });
        })
        .catch(err => {
            console.error('Error accessing camera:', err);
            showModalNotif('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.', false, 'Error Kamera');
        });
}

function stopPresensiCamera() {
    if (presensiVideo && presensiVideo.srcObject) {
        presensiVideo.srcObject.getTracks().forEach(track => track.stop());
        presensiVideo.srcObject = null;
    }
    presensiIsCameraActive = false;
    if (presensiVideoInterval) {
        clearInterval(presensiVideoInterval);
        presensiVideoInterval = null;
    }
}

function startPresensiDetection() {
    if (!presensiIsCameraActive || presensiIsPresensiSuccess) return;
    if (presensiVideoInterval) clearInterval(presensiVideoInterval);
    
    presensiVideoInterval = setInterval(async () => {
        if (presensiIsPresensiSuccess || presensiIsProcessingRecognition) return;
        
        try {
            const detections = await faceapi
                .detectAllFaces(presensiVideo, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors();
            
            if (detections.length === 0 || presensiLabeledFaceDescriptors.length === 0) {
                const ctx = presensiCanvas.getContext('2d');
                ctx.clearRect(0, 0, presensiCanvas.width, presensiCanvas.height);
                return;
            }
            
            // Use adjusted threshold based on device type (more lenient for mobile)
            const adjustedThreshold = getAdjustedFaceMatcherThreshold();
            const faceMatcher = new faceapi.FaceMatcher(presensiLabeledFaceDescriptors, adjustedThreshold);
            const resizedDetections = faceapi.resizeResults(detections, {
                width: presensiVideo.videoWidth,
                height: presensiVideo.videoHeight
            });
            
            const ctx = presensiCanvas.getContext('2d');
            ctx.clearRect(0, 0, presensiCanvas.width, presensiCanvas.height);
            
            resizedDetections.forEach(detection => {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                
                if (bestMatch.label !== 'unknown' && bestMatch.distance < 0.4) {
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#00ff00';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    ctx.fillStyle = '#00ff00';
                    ctx.font = '16px Arial';
                    ctx.fillText(bestMatch.label, box.x, box.y - 5);
                    
                    // Process recognition
                    if (!presensiProcessedLabels.has(bestMatch.label)) {
                        processPresensiRecognition(bestMatch.label);
                    }
                }
            });
        } catch (error) {
            console.error('Detection error:', error);
        }
    }, 100);
}

async function processPresensiRecognition(nim) {
    if (presensiIsProcessingRecognition || presensiIsPresensiSuccess) return;
    if (presensiProcessedLabels.has(nim)) return;
    
    presensiIsProcessingRecognition = true;
    presensiProcessedLabels.set(nim, Date.now());
    
    try {
        // Get GPS location with better error handling
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    if (pos.coords.accuracy <= 50) {
                        resolve(pos);
                    } else {
                        // GPS accuracy accepted regardless of value
                        resolve(pos);
                    }
                },
                (error) => {
                    // Check permission state before rejecting
                    if (navigator.permissions) {
                        navigator.permissions.query({ name: 'geolocation' }).then(result => {
                            if (result.state === 'denied') {
                                reject(new Error('Izin lokasi ditolak'));
                            } else {
                                reject(error);
                            }
                        }).catch(() => reject(error));
                    } else {
                        reject(error);
                    }
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
        
        // Take screenshot
        const screenshot = await new Promise((resolve) => {
            try {
                const tmp = document.createElement('canvas');
                tmp.width = 240;
                tmp.height = 240;
                const tctx = tmp.getContext('2d');
                tctx.drawImage(presensiVideo, 0, 0, tmp.width, tmp.height);
                resolve(tmp.toDataURL('image/jpeg', 0.5));
            } catch (e) {
                resolve(null);
            }
        });
        
        // Submit attendance
        const data = {
            nim: nim,
            mode: presensiScanMode,
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            gps_accuracy: position.coords.accuracy,
            screenshot: screenshot
        };
        
        const response = await api('?ajax=save_attendance', data, { suppressModal: true });
        
        if (response.ok) {
            presensiIsPresensiSuccess = true;
            stopPresensiCamera();
            
            const btnStop = qs('#btn-stop-detection-presensi');
            const btnStart = qs('#btn-start-detection-presensi');
            
            if (btnStop) {
                btnStop.classList.add('hidden');
            }
            if (btnStart) {
                btnStart.classList.remove('hidden');
                // Remove existing listeners and add new one
                const newBtnStart = btnStart.cloneNode(true);
                btnStart.parentNode.replaceChild(newBtnStart, btnStart);
                newBtnStart.addEventListener('click', () => {
                    presensiIsPresensiSuccess = false;
                    presensiProcessedLabels.delete(nim);
                    startPresensiCamera();
                    newBtnStart.classList.add('hidden');
                    if (btnStop) btnStop.classList.remove('hidden');
                });
            }
            
            const statusDiv = qs('#presensi-status-presensi');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-green-100 text-green-700';
                statusDiv.textContent = response.message || 'Presensi berhasil!';
            }
        } else {
            const statusDiv = qs('#presensi-status-presensi');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-red-100 text-red-700';
                statusDiv.textContent = response.message || 'Presensi gagal. Silakan coba lagi.';
            }
            presensiProcessedLabels.delete(nim);
        }
    } catch (error) {
        console.error('Presensi error:', error);
        const statusDiv = qs('#presensi-status-presensi');
        if (statusDiv) {
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-red-100 text-red-700';
            let errorMsg = 'Presensi gagal. Silakan coba lagi.';
            
            if (error.message.includes('Izin lokasi ditolak')) {
                errorMsg = 'Izin lokasi ditolak. Silakan aktifkan izin lokasi di pengaturan browser.';
            } else if (error.message.includes('GPS accuracy') || error.message.includes('GPS')) {
                // Check if permission is granted but GPS accuracy is low
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        if (result.state === 'granted') {
                            statusDiv.textContent = errorMsg;
                            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                        } else {
                            statusDiv.textContent = errorMsg;
                        }
                    }).catch(() => {
                        statusDiv.textContent = errorMsg;
                    });
                } else {
                    statusDiv.textContent = errorMsg;
                    statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                }
            } else if (error.message.includes('timeout')) {
                // Check if permission is granted before showing timeout error
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        if (result.state === 'granted') {
                            statusDiv.textContent = 'Mendapatkan lokasi memakan waktu lama. Pastikan GPS aktif dan berada di area terbuka.';
                            statusDiv.className = 'mt-4 text-center font-medium text-lg p-3 rounded-md bg-yellow-100 text-yellow-700';
                        } else {
                            statusDiv.textContent = 'Izin lokasi diperlukan. Silakan aktifkan izin lokasi.';
                        }
                    }).catch(() => {
                        statusDiv.textContent = errorMsg;
                    });
                } else {
                    statusDiv.textContent = errorMsg;
                }
            } else {
                statusDiv.textContent = errorMsg;
            }
        }
        presensiProcessedLabels.delete(nim);
    } finally {
        presensiIsProcessingRecognition = false;
    }
}

// Face recognition functions are handled in the landing page section
// The logged-in app focuses on admin/employee dashboard functionality

// Members (Admin)
async function renderMembers(){
    const j = await api('?ajax=get_members', {}, { cache: false });
    const members = (j.data||[]);
    const term = (qs('#search-member')?.value||'').toLowerCase();
    const filtered = members.filter(m=> (m.nama||'').toLowerCase().includes(term) || (m.nim||'').toLowerCase().includes(term));
    const body = qs('#table-members-body'); if(!body) return; body.innerHTML='';
    if(filtered.length===0){ body.innerHTML = `<tr><td colspan="7" class="text-center py-4">Tidak ada data member.</td></tr>`; return; }
    filtered.forEach(m=>{
        const tr = document.createElement('tr'); tr.className='border-b hover:bg-gray-50';
        tr.innerHTML = `
            <td class="py-2 px-4"><img src="${m.foto_base64||''}" alt="Foto ${m.nama||''}" class="h-12 w-12 rounded-full" style="object-fit: contain;"></td>
            <td class="py-2 px-4">${m.nim||''}</td>
            <td class="py-2 px-4">${m.nama||''}</td>
            <td class="py-2 px-4">${m.prodi||''}</td>
            <td class="py-2 px-4">${m.startup||'-'}</td>
            <td class="py-2 px-4 text-center">
                <button class="btn-ga-qr bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition" data-id="${m.id}" data-email="${m.email || ''}" title="Lihat QR Code Google Authenticator">
                    <i class="fi fi-sr-qr-code mr-1"></i>QR Code
                </button>
            </td>
            <td class="py-2 px-4 text-center">
                <button class="btn-edit-member text-yellow-600 font-bold" data-id="${m.id}" data-json='${JSON.stringify(m).replace(/'/g,"&apos;")}' title="Edit"><i class="fi fi-sr-pen-square"></i></button>
                <button class="btn-work-schedule text-green-600 font-bold ml-2" data-id="${m.id}" data-name="${m.nama}" title="Kelola Jadwal Kerja"><i class="fi fi-sr-calendar"></i></button>
                <button class="btn-delete-member text-red-600 font-bold ml-2" data-id="${m.id}" title="Hapus"><i class="fi fi-ss-trash"></i></button>
            </td>`;
        body.appendChild(tr);
    });
}

qs('#search-member') && qs('#search-member').addEventListener('input', renderMembers);

const memberModal = qs('#member-modal');
const btnAddMember = qs('#btn-add-member');
const btnCancelModal = qs('#btn-cancel-modal');
const memberForm = qs('#member-form');

const modalVideoContainer = qs('#modal-video-container');
const modalVideo = qs('#modal-video');
const modalCanvas = qs('#modal-canvas');
const btnStartCamera = qs('#btn-start-camera');
const btnTakePhoto = qs('#btn-take-photo');
const btnUploadPhoto = qs('#btn-upload-photo');
const photoFileInput = qs('#photo-file-input');
const fotoPreview = qs('#foto-preview');
const fotoDataUrlInput = qs('#foto-data-url');
let modalStream = null;

function resetModalCamera(){ stopModalCamera(); modalVideoContainer.classList.add('hidden'); btnTakePhoto.classList.add('hidden'); btnStartCamera.classList.remove('hidden'); btnStartCamera.textContent='Buka Kamera untuk Foto'; fotoPreview.classList.add('hidden'); fotoDataUrlInput.value=''; }
function stopModalCamera(){ if(modalStream){ modalStream.getTracks().forEach(t=>t.stop()); modalStream=null; } }

btnStartCamera && btnStartCamera.addEventListener('click', async ()=>{
    try{ modalStream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } }); modalVideo.srcObject = modalStream; modalVideoContainer.classList.remove('hidden'); btnTakePhoto.classList.remove('hidden'); btnStartCamera.classList.add('hidden'); fotoPreview.classList.add('hidden'); }catch(err){ showNotif('Tidak bisa mengakses kamera.'); console.error(err); }
});

btnTakePhoto && btnTakePhoto.addEventListener('click', ()=>{
    const ctx = modalCanvas.getContext('2d'); modalCanvas.width = modalVideo.videoWidth; modalCanvas.height = modalVideo.videoHeight; ctx.drawImage(modalVideo,0,0,modalCanvas.width,modalCanvas.height);
    const dataUrl = modalCanvas.toDataURL('image/jpeg'); fotoPreview.src = dataUrl; fotoDataUrlInput.value = dataUrl; fotoPreview.classList.remove('hidden'); stopModalCamera(); modalVideoContainer.classList.add('hidden'); btnTakePhoto.classList.add('hidden'); btnStartCamera.classList.remove('hidden'); btnStartCamera.textContent='Ambil Ulang Foto';
});

btnUploadPhoto && btnUploadPhoto.addEventListener('click', ()=>{
    photoFileInput.click();
});

photoFileInput && photoFileInput.addEventListener('change', (e)=>{
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            fotoPreview.src = dataUrl;
            fotoDataUrlInput.value = dataUrl;
            fotoPreview.classList.remove('hidden');
            stopModalCamera();
            modalVideoContainer.classList.add('hidden');
            btnTakePhoto.classList.add('hidden');
            btnStartCamera.classList.remove('hidden');
            btnStartCamera.textContent='Ambil Ulang Foto';
        };
        reader.readAsDataURL(file);
    }
});

btnAddMember && btnAddMember.addEventListener('click', ()=>{
    memberForm.reset(); qs('#modal-title').textContent='Tambah Member Baru'; qs('#member-id').value=''; qs('#nim').readOnly=false; resetModalCamera(); btnStartCamera.textContent='Buka Kamera untuk Foto'; memberModal.classList.remove('hidden'); qs('#password-admin-wrapper').classList.remove('hidden');
});

btnCancelModal && btnCancelModal.addEventListener('click', ()=>{ stopModalCamera(); memberModal.classList.add('hidden'); });

// QR Code Modal
const gaQrModal = qs('#ga-qr-modal');
const btnCloseGaQr = qs('#btn-close-ga-qr');
if(btnCloseGaQr && gaQrModal){
    btnCloseGaQr.addEventListener('click', ()=>{
        gaQrModal.classList.add('hidden');
    });
    // Close modal when clicking outside
    gaQrModal.addEventListener('click', (e)=>{
        if(e.target === gaQrModal){
            gaQrModal.classList.add('hidden');
        }
    });
}

document.addEventListener('click', async (e)=>{
    const btnEdit = e.target.closest('.btn-edit-member');
    const btnDelete = e.target.closest('.btn-delete-member');
    const btnWorkSchedule = e.target.closest('.btn-work-schedule');
    const btnGaQr = e.target.closest('.btn-ga-qr');
    const btnViewDr = e.target.closest('.btn-view-dr-admin');
    const btnEditAtt = e.target.closest('.btn-edit-att');
    const btnDeleteLaporan = e.target.closest('.btn-delete-laporan');
    const btnViewMonth = e.target.closest('.btn-view-month');
    const btnAmApprove = e.target.closest('.btn-am-approve');
    const btnAmDisapprove = e.target.closest('.btn-am-disapprove');
    const btnViewMonthDetail = e.target.closest('.btn-view-month-detail');
    const btnViewKet = e.target.closest('.btn-view-ket');
    
    if(btnGaQr){
        const userId = btnGaQr.getAttribute('data-id');
        const email = btnGaQr.getAttribute('data-email');
        const qrModal = qs('#ga-qr-modal');
        const qrImage = qs('#ga-qr-image');
        const qrEmail = qs('#ga-qr-email');
        
        qrModal.classList.remove('hidden');
        qrEmail.textContent = 'Email: ' + email;
        qrImage.src = '';
        qrImage.alt = 'Loading QR Code...';
        
        try {
            const r = await api('?ajax=get_ga_qr&user_id=' + userId, {});
            if(r.ok && r.qr_url){
                qrImage.src = r.qr_url;
                qrImage.alt = 'QR Code Google Authenticator';
            } else {
                showNotif(r.message || 'Gagal memuat QR code', false);
                qrModal.classList.add('hidden');
            }
        } catch(err) {
            showNotif('Gagal memuat QR code', false);
            qrModal.classList.add('hidden');
        }
    }

    if(btnEdit){
        const data = JSON.parse(btnEdit.getAttribute('data-json').replace(/&apos;/g, "'"));
        resetModalCamera();
        qs('#modal-title').textContent='Edit Member';
        qs('#member-id').value = data.id;
        qs('#email').value = data.email || '';
        qs('#email').readOnly = false;
        qs('#nim').value = data.nim || '';
        qs('#nim').readOnly = true;
        qs('#nama').value = data.nama || '';
        qs('#prodi').value = data.prodi || '';
        qs('#startup').value = data.startup || '';
        fotoPreview.src = data.foto_base64 || '';
        if(data.foto_base64) fotoPreview.classList.remove('hidden');
        btnStartCamera.textContent='Ambil Ulang Foto';
        qs('#password-admin-wrapper').classList.add('hidden');
        memberModal.classList.remove('hidden');
    }

    if(btnDelete){
        const id = btnDelete.getAttribute('data-id');
        showConfirmModal('Apakah Anda yakin ingin menghapus member ini?', async ()=>{
            await api('?ajax=delete_member', { id });
            renderMembers(); 
            if (typeof loadLabeledFaceDescriptors === 'function') {
                loadLabeledFaceDescriptors();
            }
        });
    }

    if(btnWorkSchedule){
        const userId = btnWorkSchedule.getAttribute('data-id');
        const userName = btnWorkSchedule.getAttribute('data-name');
        await openWorkScheduleModal(userId, userName);
    }

    if(btnDeleteLaporan){
        const id = btnDeleteLaporan.getAttribute('data-id');
        showConfirmModal('Apakah Anda yakin ingin menghapus data kehadiran ini?', async ()=>{ await api('?ajax=delete_attendance', { id }); renderLaporan(); });
    }
    
        if(btnEditAtt){
        const att = JSON.parse(btnEditAtt.getAttribute('data-json').replace(/&apos;/g, "'"));
        qs('#edit-att-id').value = att.id;
        qs('#edit-att-user-id').value = att.user_id || '';
        qs('#edit-att-date').value = (att.jam_masuk_iso||'').slice(0,10);
        qs('#edit-att-nama').value = att.nama || '';
        qs('#edit-att-jam-masuk').value = att.jam_masuk ? att.jam_masuk.substring(0, 5) : '';
        qs('#edit-att-jam-pulang').value = att.jam_pulang ? att.jam_pulang.substring(0, 5) : '';
        qs('#edit-att-ket').value = att.ket || 'hadir';
        qs('#edit-att-status').value = att.status || 'ontime';
        
        // Handle existing screenshots
        if (att.foto_masuk) {
            editAttScreenshotMasuk = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-data').value = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-img').src = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-preview').classList.remove('hidden');
        } else {
            editAttScreenshotMasuk = null;
            qs('#edit-att-screenshot-masuk-data').value = '';
            qs('#edit-att-screenshot-masuk-preview').classList.add('hidden');
        }
        
        if (att.foto_pulang) {
            editAttScreenshotPulang = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-data').value = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-img').src = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-preview').classList.remove('hidden');
        } else {
            editAttScreenshotPulang = null;
            qs('#edit-att-screenshot-pulang-data').value = '';
            qs('#edit-att-screenshot-pulang-preview').classList.add('hidden');
        }
        
        editAttModal.classList.remove('hidden');
    }

    if(btnViewDr){
        const userId = btnViewDr.getAttribute('data-user'); const date = btnViewDr.getAttribute('data-date');
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date });
        const modal = qs('#dr-modal'); const content=qs('#dr-content'); const evalEl=qs('#dr-evaluation');
        modal.dataset.reportId = r?.data?.id || '';
        content.textContent = r?.data?.content || '(Belum ada laporan)';
        evalEl.value = r?.data?.evaluation || '';
        modal.classList.remove('hidden');
    }
    
        if(btnViewMonthDetail){
        const id = btnViewMonthDetail.getAttribute('data-id');
        const r = await api('?ajax=get_monthly_report_detail', { id });
        if(!r.ok) { showNotif(r.message || 'Laporan tidak ditemukan', false); return; }
        const item = r.data;
        if(!item) { showNotif('Laporan tidak ditemukan', false); return; }
        
        // Create modal if it doesn't exist
        let modal = qs('#monthly-detail-modal');
        if(!modal) {
            modal = document.createElement('div');
            modal.id = 'monthly-detail-modal';
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="monthly-detail-title" class="text-xl font-bold"></h3>
                        <button onclick="this.closest('#monthly-detail-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ringkasan Pekerjaan:</h4>
                            <div class="bg-gray-50 p-3 rounded border">
                                <p id="monthly-detail-summary" class="text-gray-600 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Pencapaian dan Hasil Kerja:</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left border-collapse">
                                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Pencapaian</th>
                                            <th class="py-2 px-4">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-detail-achievements-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Kendala:</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left border-collapse">
                                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Kendala</th>
                                            <th class="py-2 px-4">Solusi</th>
                                            <th class="py-2 px-4">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-detail-obstacles-table"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        const titleElement = qs('#monthly-detail-title');
        const summaryElement = qs('#monthly-detail-summary');
        
        if (titleElement) {
            titleElement.textContent = `Laporan Bulanan ${item.nama} - ${monthName(parseInt(item.month))} ${item.year}`;
        }
        if (summaryElement) {
            summaryElement.textContent = item.summary || '(Tidak ada ringkasan)';
        }
        
        // Parse achievements properly and fill table
        let achievements = [];
        try {
            achievements = JSON.parse(item.achievements || '[]');
        } catch (e) {
            achievements = [];
        }
        
        const achievementsTable = qs('#monthly-detail-achievements-table');
        if (achievementsTable) {
            if (achievements.length > 0) {
                achievementsTable.innerHTML = achievements.map((a, index) => {
                    const achievement = typeof a === 'object' ? (a.achievement || '') : a;
                    const detail = typeof a === 'object' ? (a.detail || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${achievement}</td>
                            <td class="py-2 px-4">${detail}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                achievementsTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="3" class="py-2 px-4 text-center text-gray-500">Tidak ada data pencapaian</td>
                    </tr>
                `;
            }
        }
        
        // Parse obstacles properly and fill table
        let obstacles = [];
        try {
            obstacles = JSON.parse(item.obstacles || '[]');
        } catch (e) {
            obstacles = [];
        }
        
        const obstaclesTable = qs('#monthly-detail-obstacles-table');
        if (obstaclesTable) {
            if (obstacles.length > 0) {
                obstaclesTable.innerHTML = obstacles.map((o, index) => {
                    const obstacle = typeof o === 'object' ? (o.obstacle || '') : o;
                    const solution = typeof o === 'object' ? (o.solution || '') : '';
                    const note = typeof o === 'object' ? (o.note || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${obstacle}</td>
                            <td class="py-2 px-4">${solution}</td>
                            <td class="py-2 px-4">${note}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                obstaclesTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="4" class="py-2 px-4 text-center text-gray-500">Tidak ada data kendala</td>
                </tr>
            `;
            }
        }
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    // Handle view monthly report for pegawai
    if(btnViewMonth){
        const data = JSON.parse(btnViewMonth.getAttribute('data-json').replace(/&apos;/g, "'"));
        if(!data) { showNotif('Data laporan tidak ditemukan', false); return; }
        
        // Create modal if it doesn't exist
        let modal = qs('#monthly-pegawai-view-modal');
        if(!modal) {
            modal = document.createElement('div');
            modal.id = 'monthly-pegawai-view-modal';
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
            modal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="monthly-pegawai-view-title" class="text-xl font-bold"></h3>
                        <button onclick="this.closest('#monthly-pegawai-view-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Status Laporan:</h4>
                                <div id="monthly-pegawai-view-status" class="text-sm"></div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">Tanggal Dibuat:</h4>
                                <div id="monthly-pegawai-view-created" class="text-sm text-gray-600"></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Ringkasan Pekerjaan:</h4>
                            <div class="bg-gray-50 p-3 rounded border">
                                <p id="monthly-pegawai-view-summary" class="text-gray-600 whitespace-pre-wrap"></p>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Pencapaian dan Hasil Kerja:</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left border-collapse">
                                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Pencapaian</th>
                                            <th class="py-2 px-4">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-pegawai-view-achievements-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Kendala:</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left border-collapse">
                                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                                        <tr>
                                            <th class="py-2 px-4">No</th>
                                            <th class="py-2 px-4">Kendala</th>
                                            <th class="py-2 px-4">Solusi</th>
                                            <th class="py-2 px-4">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="monthly-pegawai-view-obstacles-table"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button onclick="this.closest('#monthly-pegawai-view-modal').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Tutup</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        const monthName = (m) => ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m-1];
        
        // Fill modal data
        const titleElement = qs('#monthly-pegawai-view-title');
        const statusElement = qs('#monthly-pegawai-view-status');
        const createdElement = qs('#monthly-pegawai-view-created');
        const summaryElement = qs('#monthly-pegawai-view-summary');
        
        if (titleElement) {
            titleElement.textContent = `Laporan Bulanan - ${monthName(parseInt(data.month))} ${data.year}`;
        }
        
        if (statusElement) {
            const statusMap = {
                'draft': '<span class="badge badge-gray">Draft</span>',
                'belum di approve': '<span class="badge badge-blue">Belum di Approve</span>',
                'approved': '<span class="badge badge-green">Di-approve</span>',
                'disapproved': '<span class="badge badge-red">Tidak di-approve</span>'
            };
            statusElement.innerHTML = statusMap[data.status] || '<span class="badge badge-gray">Unknown</span>';
        }
        
        if (createdElement) {
            const createdDate = new Date(data.created_at || data.updated_at);
            createdElement.textContent = createdDate.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        if (summaryElement) {
            summaryElement.textContent = data.summary || '(Tidak ada ringkasan)';
        }
        
        // Parse achievements and fill table
        let achievements = [];
        try {
            achievements = JSON.parse(data.achievements || '[]');
        } catch (e) {
            achievements = [];
        }
        
        const achievementsTable = qs('#monthly-pegawai-view-achievements-table');
        if (achievementsTable) {
            if (achievements.length > 0) {
                achievementsTable.innerHTML = achievements.map((a, index) => {
                    const achievement = typeof a === 'object' ? (a.achievement || '') : a;
                    const detail = typeof a === 'object' ? (a.detail || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${achievement}</td>
                            <td class="py-2 px-4">${detail}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                achievementsTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="3" class="py-2 px-4 text-center text-gray-500">Tidak ada data pencapaian</td>
                    </tr>
                `;
            }
        }
        
        // Parse obstacles and fill table
        let obstacles = [];
        try {
            obstacles = JSON.parse(data.obstacles || '[]');
        } catch (e) {
            obstacles = [];
        }
        
        const obstaclesTable = qs('#monthly-pegawai-view-obstacles-table');
        if (obstaclesTable) {
            if (obstacles.length > 0) {
                obstaclesTable.innerHTML = obstacles.map((o, index) => {
                    const obstacle = typeof o === 'object' ? (o.obstacle || '') : o;
                    const solution = typeof o === 'object' ? (o.solution || '') : '';
                    const note = typeof o === 'object' ? (o.note || '') : '';
                    return `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 text-center">${index + 1}</td>
                            <td class="py-2 px-4">${obstacle}</td>
                            <td class="py-2 px-4">${solution}</td>
                            <td class="py-2 px-4">${note}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                obstaclesTable.innerHTML = `
                    <tr class="border-b">
                        <td colspan="4" class="py-2 px-4 text-center text-gray-500">Tidak ada data kendala</td>
                    </tr>
                `;
            }
        }
        
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    if(btnAmApprove){
        const id = btnAmApprove.getAttribute('data-id'); const status = 'approved';
        showConfirmModal('Yakin set status laporan bulanan?', async ()=>{ await api('?ajax=admin_set_monthly_status', { id, status }); renderAdminMonthly(); });
    }

    if(btnAmDisapprove){
        const id = btnAmDisapprove.getAttribute('data-id'); const status = 'disapproved';
        showConfirmModal('Yakin set status laporan bulanan?', async ()=>{ await api('?ajax=admin_set_monthly_status', { id, status }); renderAdminMonthly(); });
    }

    if(btnViewKet){
        const att = JSON.parse(btnViewKet.getAttribute('data-json').replace(/&apos;/g, "'"));
        const modal = qs('#ket-detail-modal');
        const title = qs('#ket-detail-title');
        const content = qs('#ket-detail-content');
        
        title.textContent = `Detail ${att.ket.toUpperCase()} - ${att.nama}`;
        
        if (att.ket === 'wfo' || att.ket === 'wfa') {
            // Show location map for WFO/WFA
            let mapContent = '';
            if (att.lat_masuk && att.lng_masuk && att.lokasi_masuk) {
                mapContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Presensi Masuk:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_masuk}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_masuk}, ${att.lng_masuk}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_masuk},${att.lng_masuk}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.lat_pulang && att.lng_pulang && att.lokasi_pulang) {
                mapContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Presensi Pulang:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_pulang}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_pulang}, ${att.lng_pulang}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_pulang},${att.lng_pulang}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.ket === 'wfa' && att.alasan_wfa) {
                mapContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Alasan WFA:</h4>
                        <p class="text-sm text-gray-600 p-3 bg-gray-50 rounded">${att.alasan_wfa}</p>
                    </div>
                `;
            }
            content.innerHTML = mapContent || '<p class="text-gray-500">Tidak ada data lokasi</p>';
        } else if (att.ket === 'overtime') {
            // Show location and reason for overtime
            let overtimeContent = '';
            if (att.lat_masuk && att.lng_masuk && att.lokasi_masuk) {
                overtimeContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Lokasi Overtime:</h4>
                        <p class="text-sm text-gray-600 mb-2">${att.lokasi_overtime || att.lokasi_masuk}</p>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">
                                <strong>Koordinat:</strong> ${att.lat_masuk}, ${att.lng_masuk}
                            </div>
                            <a href="https://www.google.com/maps?q=${att.lat_masuk},${att.lng_masuk}" target="_blank" class="inline-block bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                `;
            }
            if (att.alasan_overtime) {
                overtimeContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Alasan Overtime:</h4>
                        <p class="text-sm text-gray-600 p-3 bg-purple-50 rounded">${att.alasan_overtime}</p>
                    </div>
                `;
            }
            content.innerHTML = overtimeContent || '<p class="text-gray-500">Tidak ada data overtime</p>';
        } else if (att.ket === 'izin' || att.ket === 'sakit') {
            // Show proof and reason for izin/sakit
            let proofContent = '';
            if (att.has_bis || att.has_bukti || att.bukti_izin_sakit) {
                const proofId = att.is_note ? 'note_' + att.id : att.id;
                const proofType = att.is_note ? 'note' : 'izin_sakit';
                proofContent = `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Bukti ${att.ket.toUpperCase()}:</h4>
                        <div class="flex justify-center" id="lazy-proof-container-${att.id}">
                            ${att.bukti_izin_sakit ? 
                                `<img src="${att.bukti_izin_sakit}" alt="Bukti ${att.ket}" class="max-w-full max-h-96 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">` :
                                `<button type="button" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors" onclick="loadLazyProof('${proofId}', '${proofType}', 'lazy-proof-container-${att.id}')">
                                    <i class="fi fi-rr-picture mr-2"></i> Lihat Bukti Gambar
                                </button>`
                            }
                        </div>
                    </div>
                `;
            }
            if (att.alasan_izin_sakit) {
                proofContent += `
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Keterangan:</h4>
                        <p class="text-sm text-gray-600 p-3 bg-gray-50 rounded">${att.alasan_izin_sakit}</p>
                    </div>
                `;
            }
            content.innerHTML = proofContent || '<p class="text-gray-500">Tidak ada data bukti</p>';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
});

memberForm && memberForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = qs('#member-id').value;
    const payload = {
        id,
        email: qs('#email').value,
        nim: qs('#nim').value,
        nama: qs('#nama').value,
        prodi: qs('#prodi').value,
        startup: qs('#startup').value,
        foto: fotoDataUrlInput.value,
    };
    if(!id){ payload.password = qs('#password-new').value; const confirm = qs('#password-confirm').value; if(!payload.password || payload.password!==confirm){ showNotif('Password admin untuk member baru wajib dan harus cocok'); return; } }
    const r = await api('?ajax=save_member', payload);
    if(r.ok){ 
        renderMembers(); 
        if (typeof loadLabeledFaceDescriptors === 'function') {
            loadLabeledFaceDescriptors(); 
        }
        stopModalCamera(); 
        memberModal.classList.add('hidden'); 
    } else { 
        showNotif(r.message||'Gagal menyimpan'); 
    }
});

// Load startup options for filter
async function loadStartupOptions() {
    const filterStartup = qs('#filter-startup');
    if (filterStartup && filterStartup.options.length <= 1) {
        const res = await fetch('?ajax=get_startups');
        const j = await res.json();
        if (j.ok && j.data) {
            j.data.forEach(startup => {
                const o = document.createElement('option');
                o.value = startup;
                o.textContent = startup;
                filterStartup.appendChild(o);
            });
        }
    }
}

// Laporan
async function renderLaporan(){
    const j = await api('?ajax=get_attendance', {}, { cache: false });
    const list = (j.data||[]);
    const term = (qs('#search-laporan')?.value||'').toLowerCase();
    const startupFilter = qs('#filter-startup')?.value || '';
    const tglMulai = qs('#filter-tanggal-mulai')?.value || '';
    const tglSelesai = qs('#filter-tanggal-selesai')?.value || '';
    const sortBy = qs('#sort-presensi')?.value || 'tanggal-desc';
    
    // NEW: Get new filter values
    const statusFilter = qs('#filter-status')?.value || '';
    const ketFilter = qs('#filter-ket')?.value || '';
    const laporanFilter = qs('#filter-laporan')?.value || '';
    
    // NEW: Check if showing today only (using 5 AM reset)
    const btnToggleToday = qs('#btn-toggle-today');
    const showTodayOnly = btnToggleToday && btnToggleToday.textContent.includes('Hari Ini');
    
    // Calculate "today" with 5 AM reset (not midnight)
    const now = new Date();
    const currentHour = now.getHours();
    let todayDate;
    if (currentHour < 5) {
        // Before 5 AM = still yesterday
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        todayDate = yesterday.toISOString().slice(0, 10);
    } else {
        todayDate = now.toISOString().slice(0, 10);
    }
    
    const filtered = list.filter(a=>{
        const nameMatch = (a.nama||'').toLowerCase().includes(term);
        const nimMatch = (a.nim||'').toLowerCase().includes(term);
        const startupMatch = !startupFilter || (a.startup||'') === startupFilter;
        const recordDate = a.jam_masuk_iso ? a.jam_masuk_iso.slice(0,10) : '';
        const dateMatch = (!tglMulai || recordDate>=tglMulai) && (!tglSelesai || recordDate<=tglSelesai);
        
        // NEW: Today filter (5 AM reset)
        const todayMatch = !showTodayOnly || recordDate === todayDate;
        
        // NEW: Status filter
        const statusMatch = !statusFilter || (a.status||'').toLowerCase() === statusFilter.toLowerCase();
        
        // NEW: Ket filter
        const ketMatch = !ketFilter || (a.ket||'').toLowerCase() === ketFilter.toLowerCase();
        
        // NEW: Laporan filter
        let laporanMatch = true;
        if (laporanFilter === 'belum-ada') {
            laporanMatch = !a.daily_report_status || a.daily_report_status === '';
        } else if (laporanFilter === 'pending') {
            laporanMatch = a.daily_report_status === 'pending' || a.daily_report_status === 'disapproved';
        } else if (laporanFilter === 'approved') {
            laporanMatch = a.daily_report_status === 'approved';
        }
        
        return (nameMatch||nimMatch) && startupMatch && dateMatch && todayMatch && statusMatch && ketMatch && laporanMatch;
    });
    
    // Sorting
    filtered.sort((a,b) => {
        switch(sortBy) {
            case 'tanggal-asc':
                return new Date(a.jam_masuk_iso||0) - new Date(b.jam_masuk_iso||0);
            case 'tanggal-desc':
                return new Date(b.jam_masuk_iso||0) - new Date(a.jam_masuk_iso||0);
            case 'jam-masuk-asc':
                return (a.jam_masuk||'').localeCompare(b.jam_masuk||'');
            case 'jam-masuk-desc':
                return (b.jam_masuk||'').localeCompare(a.jam_masuk||'');
            case 'nama-asc':
                return (a.nama||'').localeCompare(b.nama||'');
            case 'nama-desc':
                return (b.nama||'').localeCompare(a.nama||'');
            default:
                return new Date(b.jam_masuk_iso||0) - new Date(a.jam_masuk_iso||0);
        }
    });
    
        const body = qs('#table-laporan-body'); if(!body) return; body.innerHTML='';
    if(filtered.length===0){ body.innerHTML = `<tr><td colspan="12" class="text-center py-4">Tidak ada data kehadiran.</td></tr>`; return; }
    filtered.forEach((att, idx)=>{
        // DEBUG: Log first few records
        if (idx < 3) {
            console.log(`[LAPORAN] ID ${att.id}, foto_masuk: ${att.foto_masuk ? att.foto_masuk.substring(0, 30) + '...' : 'NULL'}, screenshot_masuk: ${att.screenshot_masuk ? 'YES' : 'NO'}`);
        }
        
        const d = new Date(att.jam_masuk_iso);
        const tanggal = isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', { year:'numeric', month:'long', day:'numeric'});
        const statusClass = att.status === 'terlambat' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
        const statusText = att.status === 'terlambat' ? 'Terlambat' : 'On Time';

        let dailyReportStatus = 'Belum ada laporan';
        let dailyReportClass = 'badge-orange'; // Changed from badge-gray to badge-orange for "Belum ada laporan"
        if(att.daily_report_status) {
            dailyReportStatus = att.daily_report_status === 'approved' ? 'Sudah di-approve' : (att.daily_report_status === 'disapproved' ? 'Tidak di-approve' : 'Belum di-approve');
            dailyReportClass = att.daily_report_status === 'approved' ? 'badge-green' : (att.daily_report_status === 'disapproved' ? 'badge-red' : 'badge-blue');
        }

        const tr = document.createElement('tr'); tr.className='border-b hover:bg-gray-50';
        
        // Format jam untuk tampilan (hanya jam:menit)
        const formatTime = (timeStr) => {
            if (!timeStr || timeStr === '-') return '-';
            if (timeStr === 'izin' || timeStr === 'sakit' || timeStr === 'wfa') return timeStr;
            // Extract only HH:MM from HH:MM:SS
            return timeStr.substring(0, 5);
        };
        
        const jamMasuk = formatTime(att.jam_masuk);
        const jamPulang = formatTime(att.jam_pulang);
        
        // Render landmark canvas thumbnail inline in the table row
        const createBuktiDisplay = (attId, hasLandmark, landmarkData, ekspresi, mode, attKet, dateIso, photoData) => {
            if (attKet === 'izin' || attKet === 'sakit') {
                return '<div class="text-center">-</div>';
            }
            
            const isExpired = !isWithin10WorkingDays(dateIso);
            const label = ekspresi || 'Netral';
            
            // 1. Policy: If expired (>10 working days), show Expired button with expression
            if (isExpired) {
                return `<div class="text-center">
                    <button type="button" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-500 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase transition-all shadow-sm border border-gray-200 cursor-pointer"
                        onclick="showExpiredModal()"
                        title="Foto sudah dihapus (>10 hari kerja)">
                        ${label}
                    </button>
                </div>`;
            }

            // CASE 2: NOT EXPIRED - Show photo if available
            if (photoData) {
                let imgSrc;
                if (photoData.startsWith('data:image/')) {
                    imgSrc = photoData;
                } else if (photoData.startsWith('public/')) {
                    imgSrc = '/' + photoData.substring(7);
                } else if (photoData.startsWith('storage/')) {
                    imgSrc = '/' + photoData;
                } else if (photoData.startsWith('attendance/')) {
                    imgSrc = '/storage/' + photoData;
                } else {
                    // Assume it's a filename in attendance folder
                    imgSrc = '/storage/attendance/' + photoData;
                }
                return `<div class="flex justify-center group/img relative">
                    <img src="${imgSrc}" 
                         class="w-16 h-12 object-cover rounded-lg border-2 border-indigo-100 shadow-sm hover:scale-150 transition-transform duration-300 z-10 cursor-pointer"
                         onclick="showImageModal('${imgSrc}', 'Bukti ${mode === 'masuk' ? 'Masuk' : 'Pulang'} - ${attId}')"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Err&background=fee2e2&color=ef4444';">
                </div>`;
            }

            // CASE 3: NOT EXPIRED but has landmark data in payload
            if (hasLandmark && landmarkData) {
                // Return placeholder; canvas rendered after DOM insertion
                const canvasId = `lm-thumb-${attId}-${mode}`;
                return `<div class="flex justify-center">
                    <canvas id="${canvasId}" width="80" height="60"
                        style="border-radius:6px;cursor:pointer;border:2px solid #334155;background:#0f172a"
                        title="Klik untuk melihat detail landmark wajah"
                        onclick="showAdminLandmarkModal(this._lmData,'Bukti ${mode==='masuk'?'Masuk':'Pulang'} - ${attId}')"
                    ></canvas>
                </div>`;
            }
            
            // CASE 4: NOT EXPIRED but landmark flag is set (need to load)
            if (hasLandmark) {
                // has_sm/has_sp = 1 but data not in payload yet → use load button
                const title = mode === 'masuk' ? 'Bukti Masuk' : 'Bukti Pulang';
                return `<div class="text-center">
                    <button type="button" class="text-indigo-600 hover:text-indigo-800 flex flex-col items-center mx-auto"
                        onclick="loadAndShowEvidence('${attId}', '${mode}', '${title}')">
                        <i class="fi fi-rr-face-viewfinder text-xl"></i>
                        <span class="text-[10px] font-semibold uppercase">Lihat Wajah</span>
                    </button>
                </div>`;
            }
            
            // CASE 5: No data - show expression as button (clickable for info)
            return `<div class="flex justify-center">
                <button type="button" class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-semibold uppercase hover:bg-blue-100 transition-colors shadow-sm border border-blue-200 cursor-pointer"
                    onclick="showExpiredModal()">
                    ${ekspresi || '-'}
                </button>
            </div>`;
        };

        const buktiMasuk  = createBuktiDisplay(att.id, att.has_sm, att.landmark_masuk,  att.ekspresi_masuk_label || att.ekspresi_masuk,  'masuk',  att.ket, att.jam_masuk_iso, (att.foto_masuk && (att.foto_masuk.startsWith('data:image/') ? att.foto_masuk.length > 500 : true) ? att.foto_masuk : att.screenshot_masuk));
        const buktiPulang = createBuktiDisplay(att.id, att.has_sp, att.landmark_pulang, att.ekspresi_pulang_label || att.ekspresi_pulang, 'pulang', att.ket, att.jam_pulang_iso || att.jam_masuk_iso, (att.foto_pulang && (att.foto_pulang.startsWith('data:image/') ? att.foto_pulang.length > 500 : true) ? att.foto_pulang : att.screenshot_pulang));

        // Ket button logic with oval styling and colors
        let ketButton = '';
        if (att.ket && (att.ket === 'wfo' || att.ket === 'wfa' || att.ket === 'izin' || att.ket === 'sakit' || att.ket === 'overtime')) {
            const ketColors = {
                'wfo': 'bg-green-500 hover:bg-green-600 text-white',
                'wfa': 'bg-blue-500 hover:bg-blue-600 text-white', 
                'izin': 'bg-yellow-500 hover:bg-yellow-600 text-white',
                'sakit': 'bg-yellow-500 hover:bg-yellow-600 text-white',
                'overtime': 'bg-emerald-600 hover:bg-emerald-700 text-white'
            };
            const colorClass = ketColors[att.ket] || 'bg-gray-500 hover:bg-gray-600 text-white';
            ketButton = `<button class="btn-view-ket ${colorClass} px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200" data-json='${JSON.stringify(att).replace(/'/g,"&apos;")}' title="Lihat Detail ${att.ket.toUpperCase()}">${att.ket.toUpperCase()}</button>`;
        } else {
            ketButton = '<span class="text-gray-400">-</span>';
        }

        tr.innerHTML = `
            <td class="py-2 px-4">${tanggal}</td>
            <td class="py-2 px-4">${att.nim||''}</td>
            <td class="py-2 px-4">${att.nama||''}</td>
            <td class="py-2 px-4">${att.startup||'-'}</td>
            <td class="py-2 px-4">${jamMasuk}</td>
            <td class="py-2 px-4">${buktiMasuk}</td>
            <td class="py-2 px-4"><span class="badge ${statusClass}">${statusText}</span></td>
            <td class="py-2 px-4">${ketButton}</td>
            <td class="py-2 px-4">${jamPulang}</td>
            <td class="py-2 px-4">${buktiPulang}</td>
            <td class="py-2 px-4"><span class="badge ${dailyReportClass}">${dailyReportStatus}</span></td>
            <td class="py-2 px-4">
                <button title="Lihat Laporan" class="btn-view-dr-admin text-blue-600 font-bold" data-user="${att.user_id}" data-date="${(att.jam_masuk_iso||'').slice(0,10)}"><i class="fi fi-ss-eye"></i></button>
                <button title="Edit" class="btn-edit-att text-yellow-600 font-bold ml-1" data-json='${JSON.stringify(att).replace(/'/g,"&apos;")}'><i class="fi fi-sr-pen-square"></i></button>
                <button title="Hapus" class="btn-delete-laporan text-red-600 font-bold ml-1" data-id="${att.id}"><i class="fi fi-ss-trash"></i></button>
            </td>`;
        body.appendChild(tr);

        // After DOM insertion, render landmark canvases if data is available
        if (att.landmark_masuk) {
            const cMasuk = document.getElementById(`lm-thumb-${att.id}-masuk`);
            if (cMasuk) {
                renderLandmarkOnCanvas(cMasuk, att.landmark_masuk, 80, 60);
                cMasuk._lmData = att.landmark_masuk; // Store for modal onclick
            }
        }
        if (att.landmark_pulang) {
            const cPulang = document.getElementById(`lm-thumb-${att.id}-pulang`);
            if (cPulang) {
                renderLandmarkOnCanvas(cPulang, att.landmark_pulang, 80, 60);
                cPulang._lmData = att.landmark_pulang;
            }
        }
    });
}

[qs('#search-laporan'), qs('#filter-startup'), qs('#filter-tanggal-mulai'), qs('#filter-tanggal-selesai'), qs('#sort-presensi'), qs('#filter-status'), qs('#filter-ket'), qs('#filter-laporan')].forEach(el=>{ if(el) el.addEventListener('input', renderLaporan); });

// NEW: Toggle today/all button
qs('#btn-toggle-today') && qs('#btn-toggle-today').addEventListener('click', function() {
    const btn = this;
    if (btn.textContent.includes('Hari Ini')) {
        btn.textContent = '📊 Lihat Semua';
        btn.classList.remove('bg-indigo-500', 'hover:bg-indigo-600');
        btn.classList.add('bg-purple-500', 'hover:bg-purple-600');
    } else {
        btn.textContent = '📅 Hari Ini';
        btn.classList.remove('bg-purple-500', 'hover:bg-purple-600');
        btn.classList.add('bg-indigo-500', 'hover:bg-indigo-600');
    }
    renderLaporan();
});


qs('#btn-show-all') && qs('#btn-show-all').addEventListener('click', ()=>{
    if(qs('#search-laporan')) qs('#search-laporan').value = '';
    if(qs('#filter-startup')) qs('#filter-startup').value = '';
    if(qs('#filter-tanggal-mulai')) qs('#filter-tanggal-mulai').value = '';
    if(qs('#filter-tanggal-selesai')) qs('#filter-tanggal-selesai').value = '';
    if(qs('#sort-presensi')) qs('#sort-presensi').value = 'tanggal-desc';
    renderLaporan();
});

// Absence modal handlers
qs('#btn-open-absence') && qs('#btn-open-absence').addEventListener('click', async ()=>{
    const modal = qs('#absence-modal');
    const select = qs('#abs-user'); const search = qs('#abs-search');
    const r = await fetch('?ajax=get_members'); const j = await r.json(); const members=(j.data||[]);
    const fill = (term='')=>{ select.innerHTML=''; members.filter(m=> (m.nama||'').toLowerCase().includes(term)|| (m.nim||'').toLowerCase().includes(term)).forEach(m=>{ const o=document.createElement('option'); o.value=m.id; o.textContent=`${m.nama} (${m.nim})`; select.appendChild(o); }); };
    search.oninput = ()=> fill(search.value.toLowerCase()); fill('');
    modal.classList.remove('hidden');
});
// Manual holidays handlers
qs('#btn-manual-holidays') && qs('#btn-manual-holidays').addEventListener('click', async ()=>{
    await renderManualHolidays();
    qs('#manual-holidays-modal').classList.remove('hidden');
});
qs('#mh-close') && qs('#mh-close').addEventListener('click', ()=> qs('#manual-holidays-modal').classList.add('hidden'));

async function renderManualHolidays(){
    const start = new Date(new Date().getFullYear(),0,1).toISOString().slice(0,10);
    const end = new Date(new Date().getFullYear(),11,31).toISOString().slice(0,10);
    const j = await api(`?ajax=admin_get_manual_holidays&start=${start}&end=${end}`, {}, { cache: false });
    const list = j.data||[];
    const body = qs('#mh-body'); body.innerHTML='';
    if(list.length===0){ body.innerHTML = '<tr><td colspan="3" class="text-center py-3">Belum ada data.</td></tr>'; return; }
    list.forEach(it=>{
        const tr=document.createElement('tr'); tr.className='border-b';
        tr.innerHTML = `<td class="py-2 px-3">${it.date}</td><td class="py-2 px-3">${it.name}</td><td class="py-2 px-3 text-center"><button class="mh-del bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded" data-id="${it.id}">Hapus</button></td>`;
        body.appendChild(tr);
    });
}

document.addEventListener('click', async (e)=>{
    if(e.target && e.target.id==='mh-add'){
        const date = qs('#mh-date').value; const name = qs('#mh-name').value.trim();
        if(!date || !name){ showNotif('Isi tanggal dan keterangan', false); return; }
        
        try {
        const r = await api('?ajax=admin_add_manual_holiday', { date, name });
            if(r.ok){ 
                await renderManualHolidays(); 
                qs('#mh-name').value='';
                showNotif('Hari libur berhasil ditambahkan', true);
            } else {
                showNotif(r.message || 'Gagal menambahkan hari libur', false);
                console.error('API Error:', r);
            }
        } catch (error) {
            showNotif('Terjadi kesalahan: ' + error.message, false);
            console.error('Error adding manual holiday:', error);
        }
    }
    if(e.target && e.target.classList.contains('mh-del')){
        const id = e.target.getAttribute('data-id');
        showConfirmModal('Hapus hari libur ini?', async ()=>{ await api('?ajax=admin_delete_manual_holiday', { id }); await renderManualHolidays(); });
    }
});
qs('#abs-cancel') && qs('#abs-cancel').addEventListener('click', ()=> qs('#absence-modal').classList.add('hidden'));
// Add event listener for abs-type change
document.addEventListener('change', (e) => {
    if (e.target.id === 'abs-type') {
        const wfaForm = qs('#abs-wfa-form');
        const overtimeForm = qs('#abs-overtime-form');
        const type = e.target.value;
        
        // Hide all forms first
        wfaForm.classList.add('hidden');
        overtimeForm.classList.add('hidden');
        
        // Show appropriate form based on type
        if (type === 'wfa') {
            wfaForm.classList.remove('hidden');
        } else if (type === 'overtime') {
            overtimeForm.classList.remove('hidden');
        }
    }
});

qs('#abs-save') && qs('#abs-save').addEventListener('click', async ()=>{
    const type = qs('#abs-type').value;
    const payload = {
        user_id: qs('#abs-user').value,
        date: qs('#abs-date').value,
        type: type
    };
    
    // Add fields based on type
    if (type === 'wfa') {
        payload.jam_masuk = qs('#abs-jam-masuk')?.value;
        payload.jam_pulang = qs('#abs-jam-pulang')?.value;
        payload.alasan_wfa = qs('#abs-alasan-wfa')?.value;
    } else if (type === 'overtime') {
        payload.jam_masuk = qs('#abs-jam-masuk-ot')?.value;
        payload.jam_pulang = qs('#abs-jam-pulang-ot')?.value;
        payload.alasan_overtime = qs('#abs-alasan-overtime')?.value;
        payload.lokasi_overtime = qs('#abs-lokasi-overtime')?.value;
    } else if (type === 'izin' || type === 'sakit') {
        payload.alasan_izin_sakit = ''; // Can be empty for admin manual input
    }
    
    const r = await api('?ajax=admin_add_absence', payload);
    if(r.ok){
        qs('#absence-modal').classList.add('hidden');
        // Reset form
        qs('#abs-type').value = 'izin';
        qs('#abs-wfa-form').classList.add('hidden');
        qs('#abs-overtime-form').classList.add('hidden');
        qs('#abs-alasan-wfa').value = '';
        qs('#abs-alasan-overtime').value = '';
        qs('#abs-lokasi-overtime').value = '';
        renderLaporan();
        showNotif('Data berhasil disimpan', true);
    } else {
        showNotif(r.message||'Gagal simpan', false);
    }
});

// Update WFA locations button handler
qs('#btn-update-wfa-locations') && qs('#btn-update-wfa-locations').addEventListener('click', async ()=>{
    showConfirmModal('Apakah Anda yakin ingin memperbarui semua lokasi WFA yang masih dalam bentuk koordinat menjadi nama jalan? Proses ini mungkin memakan waktu beberapa saat.', async () => {
    
    const button = qs('#btn-update-wfa-locations');
    const originalText = button.textContent;
    button.textContent = 'Memproses...';
    button.disabled = true;
    
    try {
        const r = await api('?ajax=admin_update_wfa_locations', {});
        if (r.ok) {
            showNotif(r.message || 'Lokasi WFA berhasil diperbarui', true);
            renderLaporan(); // Refresh the table
        } else {
            showNotif(r.message || 'Gagal memperbarui lokasi WFA', false);
        }
    } catch (error) {
        showNotif('Terjadi kesalahan saat memperbarui lokasi WFA', false);
        console.error('Error updating WFA locations:', error);
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
    });
});

// Backup management handlers - moved to below for better integration with loadBackupFiles

qs('#btn-backup-status') && qs('#btn-backup-status').addEventListener('click', async ()=>{
    try {
        const r = await api('?ajax=get_backup_status', {});
        if (r.ok && r.data) {
            const data = r.data;
            let message = '';
            
            if (data.exists) {
                message = `Backup tersedia:\n`;
                message += `File: ${data.file}\n`;
                message += `Ukuran: ${data.size_formatted}\n`;
                message += `Dibuat: ${data.created}`;
            } else {
                message = 'Tidak ada file backup tersedia';
            }
            
            showNotif(message, false);
        } else {
            showNotif(r.message || 'Gagal mendapatkan status backup', false);
        }
    } catch (error) {
        showNotif('Terjadi kesalahan saat mendapatkan status backup', false);
        console.error('Error getting backup status:', error);
    }
});

// Load and render backup files list
async function loadBackupFiles() {
    const listContainer = qs('#backup-files-list');
    if (!listContainer) return;
    
    listContainer.innerHTML = `
        <div class="text-center text-gray-500 py-8">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
            <p class="mt-2">Memuat daftar file backup...</p>
        </div>
    `;
    
    try {
        const r = await api('?ajax=list_backup_files', {});
        if (r.ok && r.data) {
            const files = r.data;
            
            if (files.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fi fi-sr-database text-4xl mb-2"></i>
                        <p>Tidak ada file backup tersedia</p>
                        <p class="text-sm mt-2">Klik "Buat Backup Baru" untuk membuat backup pertama</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="space-y-2">';
            files.forEach(file => {
                html += `
                    <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">${file.name}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                <span class="mr-4"><i class="fi fi-sr-file"></i> ${file.size_formatted}</span>
                                <span><i class="fi fi-sr-calendar"></i> ${file.modified}</span>
                            </div>
                        </div>
                        <div>
                            <a href="?ajax=download_backup&file=${encodeURIComponent(file.name)}" 
                               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition inline-flex items-center">
                                <i class="fi fi-sr-download mr-2"></i> Download
                            </a>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            listContainer.innerHTML = html;
        } else {
            listContainer.innerHTML = `
                <div class="text-center text-red-500 py-8">
                    <i class="fi fi-sr-exclamation-triangle text-4xl mb-2"></i>
                    <p>Gagal memuat daftar file backup</p>
                    <p class="text-sm mt-2">${r.message || 'Terjadi kesalahan'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading backup files:', error);
        listContainer.innerHTML = `
            <div class="text-center text-red-500 py-8">
                <i class="fi fi-sr-exclamation-triangle text-4xl mb-2"></i>
                <p>Terjadi kesalahan saat memuat daftar file backup</p>
            </div>
        `;
    }
}

// Refresh backup list button
qs('#btn-refresh-backup-list') && qs('#btn-refresh-backup-list').addEventListener('click', () => {
    loadBackupFiles();
});

// Create backup button handler
qs('#btn-create-backup') && qs('#btn-create-backup').addEventListener('click', async () => {
    showConfirmModal('Apakah Anda yakin ingin membuat backup database? Proses ini mungkin memakan waktu beberapa saat.', async () => {
        const button = qs('#btn-create-backup');
        const originalText = button.textContent;
        button.textContent = 'Membuat Backup...';
        button.disabled = true;
        
        try {
            const r = await api('?ajax=create_backup', {});
            if (r.ok) {
                showNotif(r.message || 'Backup berhasil dibuat', true);
                // Refresh list after successful backup
                setTimeout(() => loadBackupFiles(), 500);
            } else {
                showNotif(r.message || 'Gagal membuat backup', false);
            }
        } catch (error) {
            showNotif('Terjadi kesalahan saat membuat backup', false);
            console.error('Error creating backup:', error);
        } finally {
            button.textContent = originalText;
            button.disabled = false;
        }
    });
});


// Daily report review modal
qs('#dr-close') && qs('#dr-close').addEventListener('click', ()=> qs('#dr-modal').classList.add('hidden'));
qs('#dr-approve') && qs('#dr-approve').addEventListener('click', ()=> handleDrApproveDisapprove('approved'));
qs('#dr-disapprove') && qs('#dr-disapprove').addEventListener('click', ()=> handleDrApproveDisapprove('disapproved'));
async function handleDrApproveDisapprove(status){
    const id = qs('#dr-modal').dataset.reportId; const evaluation = qs('#dr-evaluation').value;
    if(!id){ showNotif('Tidak ada laporan.'); return; }
    showConfirmModal('Yakin '+(status==='approved'?'approve':'disapprove')+'?', async ()=>{
        const r = await api('?ajax=admin_set_daily_status', { id, status, evaluation });
        if(r.ok){ qs('#dr-modal').classList.add('hidden'); renderLaporan(); } else { showNotif(r.message||'Gagal'); }
    });
}

const editAttModal = qs('#edit-att-modal');
qs('#edit-att-cancel') && qs('#edit-att-cancel').addEventListener('click', ()=> editAttModal.classList.add('hidden'));

// Handle change event for edit-att-ket to show/hide WFA and Overtime forms
document.addEventListener('change', (e) => {
    if (e.target.id === 'edit-att-ket') {
        const wfaForm = qs('#edit-att-wfa-form');
        const overtimeForm = qs('#edit-att-overtime-form');
        const ket = e.target.value;
        
        // Hide all forms first
        wfaForm.classList.add('hidden');
        overtimeForm.classList.add('hidden');
        
        // Show appropriate form based on ket
        if (ket === 'wfa') {
            wfaForm.classList.remove('hidden');
        } else if (ket === 'overtime') {
            overtimeForm.classList.remove('hidden');
        }
    }
});

// Handle screenshot upload for edit attendance modal
let editAttScreenshotMasuk = null;
let editAttScreenshotPulang = null;

// Upload screenshot masuk
qs('#edit-att-upload-masuk') && qs('#edit-att-upload-masuk').addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                editAttScreenshotMasuk = e.target.result;
                qs('#edit-att-screenshot-masuk-data').value = editAttScreenshotMasuk;
                qs('#edit-att-screenshot-masuk-img').src = editAttScreenshotMasuk;
                qs('#edit-att-screenshot-masuk-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
});

// Upload screenshot pulang
qs('#edit-att-upload-pulang') && qs('#edit-att-upload-pulang').addEventListener('click', () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                editAttScreenshotPulang = e.target.result;
                qs('#edit-att-screenshot-pulang-data').value = editAttScreenshotPulang;
                qs('#edit-att-screenshot-pulang-img').src = editAttScreenshotPulang;
                qs('#edit-att-screenshot-pulang-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
});

// Remove screenshot masuk
qs('#edit-att-remove-masuk') && qs('#edit-att-remove-masuk').addEventListener('click', () => {
    editAttScreenshotMasuk = null;
    qs('#edit-att-screenshot-masuk-data').value = '';
    qs('#edit-att-screenshot-masuk-preview').classList.add('hidden');
});

// Remove screenshot pulang
qs('#edit-att-remove-pulang') && qs('#edit-att-remove-pulang').addEventListener('click', () => {
    editAttScreenshotPulang = null;
    qs('#edit-att-screenshot-pulang-data').value = '';
    qs('#edit-att-screenshot-pulang-preview').classList.add('hidden');
});
qs('#edit-att-form') && qs('#edit-att-form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = qs('#edit-att-id').value;
    const jam_masuk = qs('#edit-att-jam-masuk').value || '';
    const jam_pulang = qs('#edit-att-jam-pulang').value || '';
    const ket = qs('#edit-att-ket').value || '';
    const status = qs('#edit-att-status').value || '';
    const foto_masuk = qs('#edit-att-screenshot-masuk-data').value || '';
    const foto_pulang = qs('#edit-att-screenshot-pulang-data').value || '';
    
    // Add seconds to time values
    const jam_masuk_with_seconds = jam_masuk ? jam_masuk + ':00' : '';
    const jam_pulang_with_seconds = jam_pulang ? jam_pulang + ':00' : '';
    
    const payload = { 
        id, 
        jam_masuk: jam_masuk_with_seconds, 
        jam_pulang: jam_pulang_with_seconds, 
        ket, 
        status,
        foto_masuk,
        foto_pulang
    };
    
    // Add WFA or Overtime fields based on ket
    if (ket === 'wfa') {
        payload.alasan_wfa = qs('#edit-att-alasan-wfa')?.value || '';
    } else if (ket === 'overtime') {
        payload.alasan_overtime = qs('#edit-att-alasan-overtime')?.value || '';
        payload.lokasi_overtime = qs('#edit-att-lokasi-overtime')?.value || '';
    }
    
    const r = await api('?ajax=admin_update_attendance', payload);
    showNotif(r.ok ? 'Berhasil disimpan.' : (r.message || 'Gagal menyimpan'), r.ok);
    if(r.ok){ 
        editAttModal.classList.add('hidden'); 
        renderLaporan(); 
    }
});

// Event listener untuk tombol "Tambahkan Laporan"
qs('#edit-att-add-report') && qs('#edit-att-add-report').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const nama = qs('#edit-att-nama').value;
    
    if (!userId || !date) {
        showNotif('Data tidak lengkap', false);
        return;
    }
    
    // Set info di modal laporan harian
    qs('#admin-dr-nama').textContent = nama;
    qs('#admin-dr-date').textContent = new Date(date).toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Cek apakah sudah ada laporan
    try {
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date: date });
        if (r.ok && r.data && r.data.content) {
            qs('#admin-dr-content').value = r.data.content;
        } else {
            qs('#admin-dr-content').value = '';
        }
    } catch (error) {
        console.error('Error checking daily report:', error);
        qs('#admin-dr-content').value = '';
    }
    
    // Sembunyikan modal edit kehadiran dan tampilkan modal laporan harian
    editAttModal.classList.add('hidden');
    qs('#admin-daily-report-modal').classList.remove('hidden');
});

// Event listener untuk modal laporan harian admin
qs('#admin-dr-cancel') && qs('#admin-dr-cancel').addEventListener('click', ()=>{
    qs('#admin-daily-report-modal').classList.add('hidden');
    editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
});

qs('#admin-dr-save') && qs('#admin-dr-save').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const content = qs('#admin-dr-content').value;
    
    if (!content.trim()) {
        showNotif('Isi laporan tidak boleh kosong', false);
        return;
    }
    
    try {
        const r = await api('?ajax=admin_save_daily_report', { 
            user_id: userId, 
            date: date, 
            content: content 
        });
        
        if (r.ok) {
            showNotif('Laporan harian berhasil disimpan');
            qs('#admin-daily-report-modal').classList.add('hidden');
            editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
        } else {
            showNotif(r.message || 'Gagal menyimpan laporan', false);
        }
    } catch (error) {
        console.error('Error saving daily report:', error);
        showNotif('Terjadi kesalahan saat menyimpan', false);
    }
});

// Event listener untuk tombol "Tambahkan Laporan"
qs('#edit-att-add-report') && qs('#edit-att-add-report').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const nama = qs('#edit-att-nama').value;
    
    if (!userId || !date) {
        showNotif('Data tidak lengkap', false);
        return;
    }
    
    // Set info di modal laporan harian
    qs('#admin-dr-nama').textContent = nama;
    qs('#admin-dr-date').textContent = new Date(date).toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
    });
    
    // Cek apakah sudah ada laporan
    try {
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date: date });
        if (r.ok && r.data && r.data.content) {
            qs('#admin-dr-content').value = r.data.content;
        } else {
            qs('#admin-dr-content').value = '';
        }
    } catch (error) {
        console.error('Error checking daily report:', error);
        qs('#admin-dr-content').value = '';
    }
    
    // Sembunyikan modal edit kehadiran dan tampilkan modal laporan harian
    editAttModal.classList.add('hidden');
    qs('#admin-daily-report-modal').classList.remove('hidden');
});

// Event listener untuk modal laporan harian admin
qs('#admin-dr-cancel') && qs('#admin-dr-cancel').addEventListener('click', ()=>{
    qs('#admin-daily-report-modal').classList.add('hidden');
    editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
});

qs('#admin-dr-save') && qs('#admin-dr-save').addEventListener('click', async ()=>{
    const userId = qs('#edit-att-user-id').value;
    const date = qs('#edit-att-date').value;
    const content = qs('#admin-dr-content').value;
    
    if (!content.trim()) {
        showNotif('Isi laporan tidak boleh kosong', false);
        return;
    }
    
    try {
        const r = await api('?ajax=admin_save_daily_report', { 
            user_id: userId, 
            date: date, 
            content: content 
        });
        
        if (r.ok) {
            showNotif('Laporan harian berhasil disimpan');
            qs('#admin-daily-report-modal').classList.add('hidden');
            editAttModal.classList.remove('hidden'); // Kembali ke modal edit kehadiran
        } else {
            showNotif(r.message || 'Gagal menyimpan laporan', false);
        }
    } catch (error) {
        console.error('Error saving daily report:', error);
        showNotif('Terjadi kesalahan saat menyimpan', false);
    }
});

document.addEventListener('click', async (e)=>{
    if(e.target.classList.contains('btn-delete-laporan')){
        const id = e.target.getAttribute('data-id');
        showConfirmModal('Apakah Anda yakin ingin menghapus data kehadiran ini?', async ()=>{ await api('?ajax=delete_attendance', { id }); renderLaporan(); });
    }
    if(e.target.classList.contains('btn-edit-att')){
        const att = JSON.parse(e.target.getAttribute('data-json').replace(/&apos;/g, "'"));
        qs('#edit-att-id').value = att.id;
        qs('#edit-att-user-id').value = att.user_id || '';
        qs('#edit-att-date').value = (att.jam_masuk_iso||'').slice(0,10);
        qs('#edit-att-nama').value = att.nama || '';
        qs('#edit-att-jam-masuk').value = att.jam_masuk ? att.jam_masuk.substring(0, 5) : '';
        qs('#edit-att-jam-pulang').value = att.jam_pulang ? att.jam_pulang.substring(0, 5) : '';
        qs('#edit-att-ket').value = att.ket || 'hadir';
        qs('#edit-att-status').value = att.status || 'ontime';
        
        // Handle WFA and Overtime fields
        const wfaForm = qs('#edit-att-wfa-form');
        const overtimeForm = qs('#edit-att-overtime-form');
        wfaForm.classList.add('hidden');
        overtimeForm.classList.add('hidden');
        
        if (att.ket === 'wfa') {
            wfaForm.classList.remove('hidden');
            qs('#edit-att-alasan-wfa').value = att.alasan_wfa || '';
        } else if (att.ket === 'overtime') {
            overtimeForm.classList.remove('hidden');
            qs('#edit-att-alasan-overtime').value = att.alasan_overtime || '';
            qs('#edit-att-lokasi-overtime').value = att.lokasi_overtime || '';
        }
        
        // Handle existing screenshots
        if (att.foto_masuk) {
            editAttScreenshotMasuk = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-data').value = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-img').src = att.foto_masuk;
            qs('#edit-att-screenshot-masuk-preview').classList.remove('hidden');
        } else {
            editAttScreenshotMasuk = null;
            qs('#edit-att-screenshot-masuk-data').value = '';
            qs('#edit-att-screenshot-masuk-preview').classList.add('hidden');
        }
        
        if (att.foto_pulang) {
            editAttScreenshotPulang = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-data').value = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-img').src = att.foto_pulang;
            qs('#edit-att-screenshot-pulang-preview').classList.remove('hidden');
        } else {
            editAttScreenshotPulang = null;
            qs('#edit-att-screenshot-pulang-data').value = '';
            qs('#edit-att-screenshot-pulang-preview').classList.add('hidden');
        }
        
        editAttModal.classList.remove('hidden');
    }
    if(e.target.classList.contains('btn-view-dr-admin')){
        const userId = e.target.getAttribute('data-user'); const date = e.target.getAttribute('data-date');
        const r = await api('?ajax=get_daily_report_detail', { user_id: userId, date });
        const modal = qs('#dr-modal'); const content=qs('#dr-content'); const evalEl=qs('#dr-evaluation');
        modal.dataset.reportId = r?.data?.id || '';
        content.textContent = r?.data?.content || '(Belum ada laporan)';
        evalEl.value = r?.data?.evaluation || '';
        modal.classList.remove('hidden');
    }
});

function showWFAModal(message) {
    // Create WFA modal if it doesn't exist
    let wfaModal = document.getElementById('wfaModal');
    if (!wfaModal) {
        wfaModal = document.createElement('div');
        wfaModal.id = 'wfaModal';
        wfaModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        wfaModal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Work From Anywhere (WFA)</h3>
                <p class="text-gray-600 mb-4">${message}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan WFA:</label>
                    <textarea id="wfaReason" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Masukkan alasan kerja di luar kantor..."></textarea>
                </div>
                <div class="flex space-x-3">
                    <button id="wfaSubmit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Submit
                    </button>
                    <button id="wfaCancel" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(wfaModal);
        
        // Add event listeners
        document.getElementById('wfaSubmit').addEventListener('click', () => {
            const reason = document.getElementById('wfaReason').value.trim();
            if (reason) {
                wfaModal.classList.add('hidden');
                // Store WFA reason for next attendance submission
                window.pendingWFAReson = reason;
                // Retry attendance submission
                if (window.pendingAttendanceData) {
                    submitAttendanceWithWFA(window.pendingAttendanceData, reason);
                }
            } else {
                showNotif('Harap isi alasan WFA terlebih dahulu.', false);
            }
        });
        
        document.getElementById('wfaCancel').addEventListener('click', () => {
            wfaModal.classList.add('hidden');
            window.pendingWFAReson = null;
            window.pendingAttendanceData = null;
        });
    }
    
    // Show modal
    wfaModal.classList.remove('hidden');
    document.getElementById('wfaReason').focus();
}

function submitAttendanceWithWFA(attendanceData, wfaReason) {
    // Add WFA reason to attendance data
    const dataWithWFA = {
        ...attendanceData,
        wfa_reason: wfaReason,
        is_wfa: true
    };
    
    // Submit attendance with WFA reason
    api('?ajax=save_attendance', dataWithWFA)
        .then(response => {
            if (response.ok) {
                statusMessage('Presensi berhasil dengan alasan WFA!', 'bg-green-100 text-green-700');
                // Clear pending data
                window.pendingWFAReson = null;
                window.pendingAttendanceData = null;
                isProcessingRecognition = false;
            } else {
                const errorMsg = response.message || 'Presensi gagal. Silakan coba lagi.';
                statusMessage('Gagal menyimpan presensi: ' + errorMsg, 'bg-red-100 text-red-700');
                isProcessingRecognition = false;
            }
        })
        .catch(error => {
            console.error('Error submitting attendance with WFA:', error);
            statusMessage('Terjadi kesalahan saat menyimpan presensi.', 'bg-red-100 text-red-700');
            isProcessingRecognition = false;
        });
}

function showConfirmModal(message, cb){
    const modal=qs('#confirm-modal');
    qs('#confirm-modal-message').textContent=message;
    onConfirmCallback=cb;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
qs('#btn-confirm-yes') && qs('#btn-confirm-yes').addEventListener('click', ()=>{
    if(typeof onConfirmCallback==='function') onConfirmCallback();
    qs('#confirm-modal').classList.add('hidden');
    qs('#confirm-modal').classList.remove('flex');
    onConfirmCallback=null;
});
qs('#btn-confirm-no') && qs('#btn-confirm-no').addEventListener('click', ()=>{
    qs('#confirm-modal').classList.add('hidden');
    qs('#confirm-modal').classList.remove('flex');
    onConfirmCallback=null;
});

// Pegawai app: setup Rekap and Monthly pages
const pageMonthlyList = qs('#page-laporan-bulanan');
const pageMonthlyForm = qs('#page-monthly-form');

function addAchievementRow(data = { achievement: '', detail: '' }) {
    const body = qs('#table-achievements-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.achievement}" placeholder="Capaian..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.detail}" placeholder="Detail capaian..."></td>
        <td class="p-1 text-center"><button type="button" class="btn-delete-row text-red-500 font-bold">Hapus</button></td>
    `;
    body.appendChild(tr);
}

function addObstacleRow(data = { obstacle: '', solution: '', note: '' }) {
    const body = qs('#table-obstacles-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.obstacle}" placeholder="Kendala..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.solution}" placeholder="Solusi..."></td>
        <td class="p-1"><input type="text" class="w-full p-2 border rounded" value="${data.note}" placeholder="Catatan..."></td>
        <td class="p-1 text-center"><button type="button" class="btn-delete-row text-red-500 font-bold">Hapus</button></td>
    `;
    body.appendChild(tr);
}

// Event listeners untuk tombol tambah baris
qs('#btn-add-achievement').addEventListener('click', () => addAchievementRow());
qs('#btn-add-obstacle').addEventListener('click', () => addObstacleRow());

// Event listener untuk hapus baris (delegation)
pageMonthlyForm.addEventListener('click', e => {
    if (e.target.classList.contains('btn-delete-row')) {
        e.target.closest('tr').remove();
    }
});

// Kembali ke daftar
qs('#btn-back-to-monthly-list').addEventListener('click', () => {
    pageMonthlyForm.classList.add('hidden');
    pageMonthlyList.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// Fungsi untuk menyimpan laporan (baik draft maupun submit)
async function saveMonthlyReport(isSubmit) {
    const year = qs('#monthly-report-year').value;
    const month = qs('#monthly-report-month').value;
    const summary = qs('#monthly-summary').value;

    const achievements = qsa('#table-achievements-body tr').map(tr => {
        const inputs = tr.querySelectorAll('input');
        return { achievement: inputs[0].value, detail: inputs[1].value };
    }).filter(item => item.achievement || item.detail);

    const obstacles = qsa('#table-obstacles-body tr').map(tr => {
        const inputs = tr.querySelectorAll('input');
        return { obstacle: inputs[0].value, solution: inputs[1].value, note: inputs[2].value };
    }).filter(item => item.obstacle || item.solution || item.note);

    const payload = {
        year: parseInt(year),
        month: parseInt(month),
        summary,
        achievements: JSON.stringify(achievements),
        obstacles: JSON.stringify(obstacles),
        submit: isSubmit
    };
    
    const r = await api('?ajax=save_monthly_report', payload);
    if (r.ok) {
        showNotif(isSubmit ? 'Laporan berhasil disubmit!' : 'Laporan berhasil disimpan sebagai draft.');
        pageMonthlyForm.classList.add('hidden');
        pageMonthlyList.scrollIntoView({ behavior: 'smooth', block: 'start' });
        renderMonthly(); // Refresh list
    } else {
        showNotif(r.message || 'Gagal menyimpan laporan.');
    }
}

qs('#btn-save-draft').addEventListener('click', () => saveMonthlyReport(false));
qs('#form-monthly-report').addEventListener('submit', (e) => {
    e.preventDefault();
    saveMonthlyReport(true);
});
// --- End Monthly Report Form Logic ---

function getWeekNumberInMonth(date) {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    const firstDayOfMonth = new Date(d.getFullYear(), d.getMonth(), 1);
    const firstDayOfWeek = firstDayOfMonth.getDay();
    const offsetDays = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1; // Monday = 0, Sunday = 6
    const weekNumber = Math.ceil((d.getDate() + offsetDays) / 7);
    return weekNumber;
}

// Flag to prevent multiple calls
    // Function declarations follow...

async function initRekapPage() {
    if (isInitRekapRunning) {
        console.log('initRekapPage already running, skipping...');
        return;
    }
    
    isInitRekapRunning = true;
    
    // Load settings for max days back for daily reports
    try {
        const settingsRes = await fetch('?ajax=get_settings');
        const settingsJson = await settingsRes.json();
        if (settingsJson.ok && settingsJson.data && settingsJson.data.max_daily_report_days_back) {
            window.maxDailyReportDaysBack = parseInt(settingsJson.data.max_daily_report_days_back.value) || 5;
        } else {
            window.maxDailyReportDaysBack = 5; // Default: 5 days
        }
    } catch (e) {
        window.maxDailyReportDaysBack = 5; // Default: 5 days on error
    }
    
    const m = parseInt(qs('#rekap-month')?.value || String(new Date().getMonth() + 1));
    const y = parseInt(qs('#rekap-year')?.value || String(new Date().getFullYear()));
    console.log('Loading rekap for month:', m, 'year:', y);
    const r = await api('?ajax=get_rekap', { month: m, year: y });
    console.log('Rekap data:', r);
    
    // Load missing daily reports
    await loadMissingDailyReports();

    const weekSel = qs('#rekap-week');
    if (weekSel) {
        weekSel.innerHTML = '';
        weekSel.classList.add('hidden');
        if (r.ok && r.data.length > 0) {
            const datesInMonth = r.data.map(d => new Date(d.date));
            const weeks = [...new Set(datesInMonth.map(d => getWeekNumberInMonth(d)))].sort((a, b) => a - b);
            console.log('Available weeks:', weeks);
            if (weeks.length >= 1) {
                // Always show week selector if there's data
                if (weeks.length > 1) {
                    // Add "All Weeks" option only if there are multiple weeks
                    const allOption = document.createElement('option');
                    allOption.value = '0';
                    allOption.textContent = 'Semua Minggu';
                    weekSel.appendChild(allOption);
                }
                
                weeks.forEach(w => {
                    const option = document.createElement('option');
                    option.value = w;
                    option.textContent = `Minggu ke-${w}`;
                    weekSel.appendChild(option);
                });
                weekSel.classList.remove('hidden');
                
                // Set default to current week if we're viewing current month and year
                const currentWeek = getWeekNumberInMonth(new Date());
                const currentMonth = new Date().getMonth() + 1;
                const currentYear = new Date().getFullYear();
                
                // Always set to current week if we're viewing current month and year
                if (m === currentMonth && y === currentYear) {
                    weekSel.value = currentWeek;
                    console.log('Setting default week to:', currentWeek);
                    console.log('Week selector value after setting:', weekSel.value);
                } else {
                    // For other months, set to first available week
                    if (weeks.length > 0) {
                        weekSel.value = weeks[0];
                        console.log('Setting to first available week:', weeks[0]);
                        console.log('Week selector value after setting:', weekSel.value);
                    }
                }
            }
        }
    }

    // Get selected week (use current week as default if no selection)
    const currentWeek = getWeekNumberInMonth(new Date());
    let selectedWeek = parseInt(qs('#rekap-week')?.value || currentWeek);
    
    // If week selector is hidden or no value, show all data
    if (!qs('#rekap-week') || qs('#rekap-week').classList.contains('hidden') || !qs('#rekap-week').value) {
        selectedWeek = 0; // Show all weeks
    }
    
    // Debug logging
    console.log('Current week:', currentWeek);
    console.log('Selected week:', selectedWeek);
    console.log('Week selector value:', qs('#rekap-week')?.value);
    console.log('Current month:', new Date().getMonth() + 1, 'Selected month:', m);
    console.log('Current year:', new Date().getFullYear(), 'Selected year:', y);
    
    const body = qs('#table-rekap-body');
    if (!body) {
        isInitRekapRunning = false;
        return;
    }
    body.innerHTML = '';
    if (!r.ok || !r.data || r.data.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data.</td></tr>`;
        isInitRekapRunning = false;
        return;
    }

    // Store current data globally for week filtering
    window.currentRekapData = r.data;
    
    // Render the data
    renderRekapData(r.data, m, y);
    
    // Load KPI data for employee
    loadEmployeeKPIData();
    
    // Reset flag
    isInitRekapRunning = false;
}

// Load KPI data for employee
async function loadEmployeeKPIData() {
    try {
        const response = await fetch('?ajax=get_kpi_data');
        const result = await response.json();
        
        if (result.ok && result.data) {
            renderEmployeeKPIChart(result.data);
        } else {
            console.error('Failed to load KPI data:', result.message);
        }
    } catch (error) {
        console.error('Error loading KPI data:', error);
    }
}

// Render KPI chart for employee
function renderEmployeeKPIChart(kpiData) {
    const ctx = qs('#kpi-chart');
    const summary = qs('#kpi-summary');
    
    if (!ctx || !summary) return;
    
    // Destroy existing chart if it exists
    if (window.employeeKPIChart) {
        try {
            window.employeeKPIChart.destroy();
        } catch (e) {
            console.log('Chart destroy error (ignored):', e);
        }
        window.employeeKPIChart = null;
    }
    
    // Create bar chart data
    const labels = ['Ontime', 'Terlambat', 'Izin/Sakit', 'Alpha', 'Overtime'];
    const data = [
        kpiData.ontime_count || 0,
        kpiData.late_count || 0,
        kpiData.izin_sakit_count || 0,
        kpiData.alpha_count || 0,
        kpiData.overtime_count || 0
    ];
    
    const colors = [
        '#22c55e', // Green for ontime
        '#ef4444', // Red for late
        '#eab308', // Yellow for izin/sakit
        '#6b7280', // Gray for alpha
        '#10b981'  // Emerald for overtime
    ];
    
    window.employeeKPIChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Hari',
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: `KPI Score: ${kpiData.kpi_score}% - ${kpiData.status}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Update summary cards
    summary.innerHTML = `
        <div class="bg-green-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">${kpiData.ontime_count || 0}</div>
            <div class="text-sm text-green-700">Ontime</div>
        </div>
        <div class="bg-red-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600">${kpiData.late_count || 0}</div>
            <div class="text-sm text-red-700">Terlambat</div>
        </div>
        <div class="bg-yellow-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-600">${kpiData.izin_sakit_count || 0}</div>
            <div class="text-sm text-yellow-700">Izin/Sakit</div>
        </div>
        <div class="bg-gray-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-gray-600">${kpiData.alpha_count || 0}</div>
            <div class="text-sm text-gray-700">Alpha</div>
        </div>
        <div class="bg-emerald-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-emerald-600">${kpiData.overtime_count || 0}</div>
            <div class="text-sm text-emerald-700">Overtime</div>
        </div>
        <div class="bg-indigo-100 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-indigo-600">${kpiData.kpi_score || 0}%</div>
            <div class="text-sm text-indigo-700">KPI Score</div>
        </div>
    `;
}

function renderRekapData(data, m, y) {
    // Get selected week (use current week as default if no selection)
    const currentWeek = getWeekNumberInMonth(new Date());
    let selectedWeek = parseInt(qs('#rekap-week')?.value || currentWeek);
    
    // If week selector is hidden or no value, show all data
    if (!qs('#rekap-week') || qs('#rekap-week').classList.contains('hidden') || !qs('#rekap-week').value) {
        selectedWeek = 0; // Show all weeks
    }
    
    // Debug logging
    console.log('Current week:', currentWeek);
    console.log('Selected week:', selectedWeek);
    console.log('Week selector value:', qs('#rekap-week')?.value);
    console.log('Current month:', new Date().getMonth() + 1, 'Selected month:', m);
    console.log('Current year:', new Date().getFullYear(), 'Selected year:', y);
    
    const body = qs('#table-rekap-body');
    if (!body) {
        return;
    }
    body.innerHTML = '';
    if (!data || data.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data.</td></tr>`;
        return;
    }

    // Show data for selected week, or all data if no week selector
    let dataToShow = data;
    if (selectedWeek > 0) {
        dataToShow = data.filter(row => {
            const rowWeek = getWeekNumberInMonth(new Date(row.date));
            console.log('Row date:', row.date, 'Row week:', rowWeek, 'Selected week:', selectedWeek);
            return rowWeek === selectedWeek;
        });
    } else {
        // Show all data when "Semua Minggu" is selected or no week selector
        dataToShow = data;
    }

    // Calculate past working days based on settings (default: 5 days) - only for current month/year
    const today = new Date();
    const currentMonth = today.getMonth() + 1;
    const currentYear = today.getFullYear();
    const past5WorkingDays = [];
    
    // Calculate past working days based on settings (default: 5 days)
    // Get max days back from settings or use default
    const maxDaysBack = window.maxDailyReportDaysBack || 5;
    if (m === currentMonth && y === currentYear) {
        let tempDate = new Date(today);
        let workingDaysFound = 0;
        let daysChecked = 0;
        const maxDaysToCheck = maxDaysBack * 2; // Check up to 2x maxDaysBack to find enough working days
        
        while (workingDaysFound < maxDaysBack && daysChecked < maxDaysToCheck) {
            const dayOfWeek = tempDate.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) and not Saturday (6)
                past5WorkingDays.push(tempDate.toISOString().slice(0, 10));
                workingDaysFound++;
            }
            tempDate.setDate(tempDate.getDate() - 1);
            daysChecked++;
        }
    }
    


    if (dataToShow.length === 0) {
        if (selectedWeek > 0) {
            body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data untuk minggu ke-${selectedWeek}.</td></tr>`;
        } else {
            body.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada data untuk periode ini.</td></tr>`;
        }
        return;
    }

    dataToShow.forEach(row => {
        const d = new Date(row.date);
        const tanggal = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const dayMap = { 
            Monday: 'Senin', 
            Tuesday: 'Selasa', 
            Wednesday: 'Rabu', 
            Thursday: 'Kamis', 
            Friday: 'Jumat',
            Saturday: 'Sabtu',
            Sunday: 'Minggu'
        };
        const day = dayMap[d.toLocaleDateString('en-US', { weekday: 'long' })] || '';
        const dr = row.daily_report;
        let reportBtns = '';
        
        // Check if attendance is complete (has entry time or is WFH or is overtime)
        const hasEntryTime = row.jam_masuk && row.jam_masuk !== '-';
        const isWFH = row.ket === 'wfh';
        const isOvertime = row.ket === 'overtime';
        const isAttendanceComplete = hasEntryTime || isWFH || isOvertime;
        
        // Check if within timeframe (only for current month/year) - use settings for max days
        // For now, allow all days that have attendance (including overtime)
        const isWithinTimeframe = (m === currentMonth && y === currentYear) ? past5WorkingDays.includes(row.date) : true;
        // Also allow overtime days and working days with attendance
        const canCreateReport = isAttendanceComplete && (isWithinTimeframe || isOvertime || hasEntryTime);
        
        // Check if can edit (not approved and within timeframe or is overtime)
        const canEdit = dr && dr.status !== 'approved' && (isWithinTimeframe || isOvertime);
        


        if (dr) {
            if (dr.status === 'approved') {
                // Only view button for approved reports
                reportBtns = `<button title="Lihat" class="btn-view-dr text-blue-600 font-bold" data-date="${row.date}"><i class="fi fi-ss-eye"></i></button>`;
            } else {
                // For disapproved and pending reports, always allow edit and view
                reportBtns = `<button title="Edit" class="btn-edit-dr text-yellow-600 font-bold ml-1" data-date="${row.date}"><i class="fi fi-sr-pen-square"></i></button>
                            <button title="Lihat" class="btn-view-dr text-blue-600 font-bold ml-1" data-date="${row.date}"><i class="fi fi-ss-eye"></i></button>`;
            }
        } else {
            // No report exists
            if (canCreateReport) {
                reportBtns = `<button class="btn-create-dr bg-emerald-500 hover:bg-emerald-600 text-white btn-pill" data-date="${row.date}">Buat</button>`;
            } else if (!isAttendanceComplete && isWithinTimeframe) {
                reportBtns = `<span class="text-gray-400">Belum presensi</span>`;
            } else if (!isAttendanceComplete && !isWithinTimeframe && !isOvertime) {
                reportBtns = `<span class="text-gray-400">Tidak tersedia</span>`;
            } else if (isOvertime && !dr) {
                // Allow creating report for overtime even if outside timeframe
                reportBtns = `<button class="btn-create-dr bg-emerald-500 hover:bg-emerald-600 text-white btn-pill" data-date="${row.date}">Buat</button>`;
            }
        }

        // Format time for display (only HH:MM)
        const formatTimeDisplay = (timeStr) => {
            if (!timeStr || timeStr === '-') return '-';
            if (timeStr === 'izin' || timeStr === 'sakit' || timeStr === 'wfh') return timeStr;
            return timeStr.substring(0, 5);
        };
        
        // Keterangan column logic
        let keteranganContent = '';
        const today = new Date().toISOString().slice(0, 10);
        const isToday = row.date === today;
        const isFuture = row.date > today;
        
        // Check if it's a manual holiday or before registration
        const isManualHoliday = row.is_manual_holiday || false;
        const isBeforeRegistration = row.is_before_registration || false;
        const isWorkingDay = row.is_working_day !== undefined ? row.is_working_day : true;
        
        // Check if date is weekend (Saturday = 6, Sunday = 0)
        const dayOfWeek = d.getDay();
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6; // Sunday or Saturday
        
        // Check if date is a holiday (manual holiday or not a working day or weekend)
        const isHoliday = isManualHoliday || !isWorkingDay || isWeekend;
        
        // Determine status label
        let statusLabel = '';
        if (isFuture || isHoliday) {
            // For future dates or holidays, show "Not Available" with gray badge
            statusLabel = '<span class="badge badge-gray">Not Available</span>';
        } else if (dr) {
            // If report exists, show status based on approval
            statusLabel = dr.status === 'approved' 
                ? `<span class="badge badge-green">Di-approve</span>` 
                : (dr.status === 'disapproved' 
                    ? `<span class="badge badge-red">Tidak di-approve</span>` 
                    : `<span class="badge badge-blue">Belum di-approve</span>`);
        } else {
            // No report exists and it's not a holiday/future date
            statusLabel = '<span class="badge badge-orange">Belum ada laporan</span>';
        }
        
        if (row.ket && (row.ket === 'wfo' || row.ket === 'wfa' || row.ket === 'izin' || row.ket === 'sakit' || row.ket === 'overtime' || row.ket === 'libur' || row.ket === 'na')) {
            // Show actual keterangan if exists
            let badgeClass = 'badge-gray';
            if (row.ket === 'wfo') badgeClass = 'badge-green';
            else if (row.ket === 'wfa') badgeClass = 'badge-blue';
            else if (row.ket === 'overtime') badgeClass = 'badge-emerald';
            else if (row.ket === 'izin') badgeClass = 'badge-yellow';
            else if (row.ket === 'sakit') badgeClass = 'badge-yellow';
            else if (row.ket === 'libur') badgeClass = 'badge-orange';
            else if (row.ket === 'na') badgeClass = 'badge-gray';
            
            let ketText = row.ket.toUpperCase();
            if (row.ket === 'na') ketText = 'NA';
            if (row.ket === 'libur') ketText = 'LIBUR';
            
            keteranganContent = `<span class="badge ${badgeClass}">${ketText}</span>`;
        } else if (isManualHoliday || (!isWorkingDay && !row.ket)) {
            // Manual holiday or weekend/holiday without attendance - show libur with orange badge
            keteranganContent = '<span class="badge badge-orange">LIBUR</span>';
        } else if (isBeforeRegistration) {
            // Before registration - show NA
            keteranganContent = '<span class="badge badge-gray">NA</span>';
        } else if (!isAttendanceComplete && isToday && isWorkingDay) {
            // Show input button only for today if no attendance and it's a working day
            keteranganContent = `<button class="btn-input-keterangan bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm" data-date="${row.date}">Input Keterangan</button>`;
        } else if (!isAttendanceComplete && isFuture) {
            // Show "Tidak Tersedia" for future days
            keteranganContent = '<span class="text-gray-400">Tidak Tersedia</span>';
        } else if (!isAttendanceComplete && !isToday && !isFuture && isWorkingDay) {
            // Mark past working days without attendance as alpha (only for working days)
            keteranganContent = '<span class="badge badge-red">ALPHA</span>';
        } else if (!isAttendanceComplete && !isToday && !isFuture && !isWorkingDay) {
            // For non-working days (weekend/holiday) without attendance, show nothing or "-"
            keteranganContent = '<span class="text-gray-400">-</span>';
        } else {
            keteranganContent = '<span class="text-gray-400">-</span>';
        }

        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50 text-center';
        tr.innerHTML = `
            <td class="py-2 px-4">${day}</td>
            <td class="py-2 px-4">${tanggal}</td>
            <td class="py-2 px-4">${formatTimeDisplay(row.jam_masuk)}</td>
            <td class="py-2 px-4">${formatTimeDisplay(row.jam_pulang)}</td>
            <td class="py-2 px-4">${keteranganContent}</td>
            <td class="py-2 px-4">${reportBtns}</td>
            <td class="py-2 px-4">${statusLabel}</td>`;
        body.appendChild(tr);
    });
    
    // Reset flag
    isInitRekapRunning = false;
    
    // Load and display KPI chart
    loadKPIChart(m, y);
}

// Global variable to store chart instance
let kpiChartInstance = null;

// Function to load and display KPI chart
async function loadKPIChart(month, year) {
    try {
        console.log('Loading KPI chart for month:', month, 'year:', year);
        
        // Get period start and end dates
        const periodStart = `${year}-${String(month).padStart(2, '0')}-01`;
        const lastDay = new Date(year, month, 0).getDate();
        const periodEnd = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
        
        console.log('KPI period:', periodStart, 'to', periodEnd);
        
        // Fetch KPI data - check if we're viewing a specific user's data
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id') || (window.currentUserId || '2'); // Default to user 2 for testing
        const kpiUrl = userId ? 
            `?ajax=get_kpi_data&period_start=${periodStart}&period_end=${periodEnd}&user_id=${userId}&t=${Date.now()}` :
            `?ajax=get_kpi_data&period_start=${periodStart}&period_end=${periodEnd}&t=${Date.now()}`;
        
        console.log('KPI URL:', kpiUrl);
        console.log('Using user_id:', userId);
        const response = await api(kpiUrl);
        
        console.log('KPI response:', response);
        
        if (response && response.ok && response.data) {
            const kpiData = response.data;
            console.log('KPI data received:', kpiData);
            console.log('Izin/Sakit count:', kpiData.izin_sakit_count);
            
            // Show KPI chart section
            const kpiSection = qs('#kpi-chart-section');
            if (kpiSection) {
                kpiSection.classList.remove('hidden');
                console.log('KPI section shown');
            } else {
                console.error('KPI section element not found');
            }
            
            // Render KPI chart
            renderKPIChart(kpiData);
            console.log('KPI chart rendered');
            
            // Render KPI summary
            renderKPISummary(kpiData);
            console.log('KPI summary rendered');
        } else {
            console.error('No KPI data in response:', response);
            // Hide KPI section if no data
            const kpiSection = qs('#kpi-chart-section');
            if (kpiSection) {
                kpiSection.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading KPI chart:', error);
        // Hide KPI section on error
        const kpiSection = qs('#kpi-chart-section');
        if (kpiSection) {
            kpiSection.classList.add('hidden');
        }
    }
}

// Function to render KPI chart
function renderKPIChart(kpiData) {
    const canvas = qs('#kpi-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (kpiChartInstance) {
        kpiChartInstance.destroy();
    }
    
    // Prepare data
    const labels = ['Hadir Ontime', 'Terlambat', 'Izin/Sakit', 'Alpha'];
    const data = [
        kpiData.ontime_count || 0,
        kpiData.late_count || 0,
        kpiData.izin_sakit_count || 0,
        kpiData.alpha_count || 0
    ];
    const colors = ['#10b981', '#f59e0b', '#3b82f6', '#ef4444'];
    
    console.log('Chart data:', { labels, data, izin_sakit: kpiData.izin_sakit_count });
    
    // Create chart
    kpiChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Hari',
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: `KPI Score: ${kpiData.kpi_score || 0} - Status: ${kpiData.status || 'N/A'}`,
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                }
            }
        }
    });
}

// Function to render KPI summary
function renderKPISummary(kpiData) {
    const summaryContainer = qs('#kpi-summary');
    if (!summaryContainer) {
        console.error('KPI summary container not found');
        return;
    }
    
    console.log('Rendering KPI summary with data:', kpiData);
    console.log('Izin/Sakit count for summary:', kpiData.izin_sakit_count);
    
    const statusColor = kpiData.status === 'Excellent' ? 'text-green-600' : 
                       kpiData.status === 'Good' ? 'text-blue-600' : 
                       kpiData.status === 'Average' ? 'text-yellow-600' : 'text-red-600';
    
    summaryContainer.innerHTML = `
        <div class="bg-green-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600">${kpiData.ontime_count || 0}</div>
            <div class="text-sm text-gray-600">Hadir Ontime</div>
        </div>
        <div class="bg-yellow-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-yellow-600">${kpiData.late_count || 0}</div>
            <div class="text-sm text-gray-600">Terlambat</div>
        </div>
        <div class="bg-blue-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600">${kpiData.izin_sakit_count || 0}</div>
            <div class="text-sm text-gray-600">Izin/Sakit</div>
        </div>
        <div class="bg-red-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-600">${kpiData.alpha_count || 0}</div>
            <div class="text-sm text-gray-600">Alpha</div>
        </div>
        <div class="bg-indigo-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold text-indigo-600">${kpiData.kpi_score || 0}</div>
            <div class="text-sm text-gray-600">KPI Score</div>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg text-center">
            <div class="text-2xl font-bold ${statusColor}">${kpiData.status || 'N/A'}</div>
            <div class="text-sm text-gray-600">Status</div>
        </div>
    `;
}

// Load missing daily reports for shortcut
async function loadMissingDailyReports() {
    try {
        const response = await fetch('?ajax=get_missing_daily_reports');
        const result = await response.json();
        
        if (!result.ok || !result.data) {
            qs('#missing-daily-reports-shortcut')?.classList.add('hidden');
            return;
        }
        
        const missingDates = result.data;
        const shortcutDiv = qs('#missing-daily-reports-shortcut');
        const countSpan = qs('#missing-reports-count');
        const listDiv = qs('#missing-reports-list');
        
        if (!shortcutDiv || !countSpan || !listDiv) return;
        
        if (missingDates.length === 0) {
            shortcutDiv.classList.add('hidden');
            return;
        }
        
        shortcutDiv.classList.remove('hidden');
        countSpan.textContent = missingDates.length;
        
        // Format dates and create buttons
        listDiv.innerHTML = missingDates.map(date => {
            const dateObj = new Date(date + 'T00:00:00');
            const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'short' });
            const day = dateObj.getDate();
            const month = dateObj.toLocaleDateString('id-ID', { month: 'short' });
            const formattedDate = `${dayName}, ${day} ${month}`;
            
            return `
                <button 
                    class="missing-report-btn bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2"
                    data-date="${date}"
                    title="Klik untuk mengisi laporan harian tanggal ${formattedDate}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    ${formattedDate}
                </button>
            `;
        }).join('');
        
        // Add event listeners to buttons
        listDiv.querySelectorAll('.missing-report-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const date = btn.getAttribute('data-date');
                await openDailyReportEditModal(date);
            });
        });
        
    } catch (error) {
        console.error('Error loading missing daily reports:', error);
        qs('#missing-daily-reports-shortcut')?.classList.add('hidden');
    }
}

// Initialize rekap page controls
const rekapControls = qs('#rekap-controls');
if (rekapControls) {
    console.log('Initializing rekap controls...');
    // Add event listeners for month, year, and week selectors
    qs('#rekap-month') && qs('#rekap-month').addEventListener('change', () => {
        console.log('Month changed to:', qs('#rekap-month').value);
        // Don't reset week selector, just reload data
        initRekapPage();
    });
    qs('#rekap-year') && qs('#rekap-year').addEventListener('change', () => {
        console.log('Year changed to:', qs('#rekap-year').value);
        // Don't reset week selector, just reload data
        initRekapPage();
    });
    qs('#rekap-week') && qs('#rekap-week').addEventListener('change', () => {
        console.log('Week selector changed to:', qs('#rekap-week').value);
        // Just reload the current data with new week filter
        const currentData = window.currentRekapData;
        if (currentData) {
            const m = parseInt(qs('#rekap-month')?.value || String(new Date().getMonth() + 1));
            const y = parseInt(qs('#rekap-year')?.value || String(new Date().getFullYear()));
            renderRekapData(currentData, m, y);
        }
    });
    qs('#btn-load-rekap') && qs('#btn-load-rekap').addEventListener('click', () => {
        console.log('Load rekap button clicked');
        initRekapPage();
    });
}

// Modal View Laporan Harian (hanya lihat, tidak bisa edit)
const drUserViewModal = document.createElement('div');
drUserViewModal.id='dr-user-view-modal';
drUserViewModal.className='fixed inset-0 bg-black/50 hidden items-center justify-center z-50';
drUserViewModal.innerHTML = `
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
        <h3 class="text-xl font-bold mb-2">Laporan Harian</h3>
        <div class="text-sm text-gray-500 mb-2" id="dr-user-view-date"></div>
        
        <!-- Bukti Izin/Sakit Section (View Only) -->
        <div id="dr-user-view-bukti-section" class="mb-4 hidden">
        <label class="block text-sm text-gray-600 mb-2">Bukti Izin/Sakit:</label>
            <div id="dr-user-view-bukti-container" class="mb-2">
            <!-- Bukti image will be inserted here -->
        </div>
        </div>
        
        <div id="dr-user-view-content" class="whitespace-pre-wrap border p-3 rounded bg-gray-50 mb-4 min-h-[200px]"></div>
        
        <div id="dr-user-view-evaluation-container" class="mt-4 hidden">
            <h4 class="text-sm font-bold text-gray-700 mb-1">Evaluasi Admin:</h4>
            <p id="dr-user-view-evaluation" class="whitespace-pre-wrap border p-3 rounded bg-gray-100"></p>
    </div>
    
        <div class="flex justify-end gap-2 mt-4">
            <button id="dr-user-view-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Tutup</button>
        </div>
    </div>`;
document.body.appendChild(drUserViewModal);

// Modal Edit Laporan Harian (bisa edit, tanpa tombol hapus bukti)
const drUserEditModal = document.createElement('div');
drUserEditModal.id='dr-user-edit-modal';
drUserEditModal.className='fixed inset-0 bg-black/50 hidden items-center justify-center z-50';
drUserEditModal.innerHTML = `
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-2xl">
        <h3 class="text-xl font-bold mb-2">Laporan Harian</h3>
        <div class="text-sm text-gray-500 mb-2" id="dr-user-edit-date"></div>
        
        <!-- Bukti Izin/Sakit Section (Edit Mode) -->
        <div id="dr-user-edit-bukti-section" class="mb-4 hidden">
            <label class="block text-sm text-gray-600 mb-2">Bukti Izin/Sakit:</label>
            <div id="dr-user-edit-bukti-container" class="mb-2">
                <!-- Bukti image will be inserted here -->
            </div>
            <div id="dr-user-edit-bukti-actions" class="flex gap-2 hidden">
                <button type="button" id="dr-user-edit-bukti-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">Ganti Bukti</button>
            </div>
        </div>
        
        <textarea id="dr-user-edit-content" class="w-full border rounded p-2" rows="8" placeholder="Tulis detail pekerjaan hari ini..."></textarea>
        
        <div id="dr-user-edit-evaluation-container" class="mt-4 hidden">
        <h4 class="text-sm font-bold text-gray-700 mb-1">Evaluasi Admin:</h4>
            <p id="dr-user-edit-evaluation" class="whitespace-pre-wrap border p-3 rounded bg-gray-100"></p>
    </div>
        
    <div class="flex justify-end gap-2 mt-4">
            <button id="dr-user-edit-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
            <button id="dr-user-edit-save" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Simpan</button>
    </div>
    </div>`;
document.body.appendChild(drUserEditModal);

// Izin/Sakit modal handlers
const izinSakitModal = qs('#izin-sakit-modal');
const izinSakitForm = qs('#izin-sakit-form');
const izinSakitBukti = qs('#izin-sakit-bukti');
const izinSakitPreview = qs('#izin-sakit-preview');
const izinSakitPreviewImg = qs('#izin-sakit-preview-img');

// File upload preview with size validation
izinSakitBukti && izinSakitBukti.addEventListener('change', (e) => {
    const file = e.target.files[0];
    const errorDiv = qs('#izin-sakit-error');
    
    if (file) {
        // Check file size (5MB = 5 * 1024 * 1024 bytes)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            errorDiv.textContent = `File terlalu besar. Maksimal 5MB. Ukuran saat ini: ${(file.size / (1024 * 1024)).toFixed(2)}MB`;
            errorDiv.classList.remove('hidden');
            izinSakitPreview.classList.add('hidden');
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            errorDiv.textContent = 'File harus berupa gambar (JPG, PNG, GIF)';
            errorDiv.classList.remove('hidden');
            izinSakitPreview.classList.add('hidden');
            return;
        }
        
        // Clear error and show preview
        errorDiv.classList.add('hidden');
        const reader = new FileReader();
        reader.onload = (e) => {
            izinSakitPreviewImg.src = e.target.result;
            izinSakitPreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        errorDiv.classList.add('hidden');
        izinSakitPreview.classList.add('hidden');
    }
});

// Cancel button
qs('#izin-sakit-cancel') && qs('#izin-sakit-cancel').addEventListener('click', () => {
    izinSakitModal.classList.add('hidden');
    izinSakitForm.reset();
    izinSakitPreview.classList.add('hidden');
});

// Form submit
izinSakitForm && izinSakitForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const type = qs('#izin-sakit-type').value;
    const alasan = qs('#izin-sakit-alasan').value;
    const file = izinSakitBukti.files[0];
    
    if (!type || !alasan || !file) {
        showNotif('Semua field harus diisi', false);
        return;
    }
    
    // Convert file to base64
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const r = await api('?ajax=submit_izin_sakit', {
                type: type,
                alasan: alasan,
                bukti: e.target.result
            });
            
            if (r.ok) {
                showNotif(r.message, true);
                izinSakitModal.classList.add('hidden');
                izinSakitForm.reset();
                izinSakitPreview.classList.add('hidden');
                initRekapPage(); // Refresh rekap
            } else {
                showNotif(r.message || 'Gagal menyimpan', false);
            }
        } catch (error) {
            console.error('Error submitting izin/sakit:', error);
            showNotif('Terjadi kesalahan', false);
        }
    };
    reader.readAsDataURL(file);
});

// Input keterangan button handler
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-input-keterangan')) {
        const date = e.target.getAttribute('data-date');
        izinSakitModal.classList.remove('hidden');
        izinSakitModal.classList.add('flex');
    }
});

// Fungsi untuk membuka modal view laporan harian
async function openDailyReportViewModal(date) {
    qs('#dr-user-view-date').textContent = 'Tanggal: ' + date;
        
        const r = await api('?ajax=get_rekap', { month: new Date(date).getMonth()+1, year: new Date(date).getFullYear() });
        const item = (r.data||[]).find(x=> x.date===date);
    
        if(item && item.daily_report){
        qs('#dr-user-view-content').textContent = item.daily_report.content||'';
                if (item.daily_report.evaluation) {
            qs('#dr-user-view-evaluation').textContent = item.daily_report.evaluation;
            qs('#dr-user-view-evaluation-container').classList.remove('hidden');
        } else {
            qs('#dr-user-view-evaluation-container').classList.add('hidden');
                }
            } else {
        qs('#dr-user-view-content').textContent = 'Belum ada laporan harian untuk tanggal ini.';
        qs('#dr-user-view-evaluation-container').classList.add('hidden');
    }
    
    // Cek apakah ada bukti izin/sakit untuk tanggal ini
    if (item && (item.ket === 'izin' || item.ket === 'sakit')) {
        // Get attendance data to find bukti
        const attendanceData = await api('?ajax=get_attendance');
        if (attendanceData.ok && attendanceData.data) {
            const todayRecord = attendanceData.data.find(att => 
                att.jam_masuk_iso && 
                att.jam_masuk_iso.slice(0, 10) === date &&
                (att.ket === 'izin' || att.ket === 'sakit') &&
                att.bukti_izin_sakit
            );
            
            if (todayRecord) {
                // Tampilkan bukti izin/sakit (view only)
                qs('#dr-user-view-bukti-section').classList.remove('hidden');
                qs('#dr-user-view-bukti-container').innerHTML = `
                    <div class="flex justify-center">
                        <img src="${todayRecord.bukti_izin_sakit}" alt="Bukti ${todayRecord.ket}" class="max-w-full max-h-64 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">
                    </div>
                    <p class="text-sm text-gray-600 mt-2 text-center">Bukti ${todayRecord.ket.toUpperCase()}</p>
                `;
            } else {
                qs('#dr-user-view-bukti-section').classList.add('hidden');
            }
        }
    } else {
        qs('#dr-user-view-bukti-section').classList.add('hidden');
    }
    
    qs('#dr-user-view-modal').classList.remove('hidden'); 
    qs('#dr-user-view-modal').classList.add('flex');
}

// Fungsi untuk membuka modal edit laporan harian
async function openDailyReportEditModal(date) {
    qs('#dr-user-edit-date').textContent = 'Tanggal: ' + date;
    qs('#dr-user-edit-modal').dataset.date = date;
    
    const r = await api('?ajax=get_rekap', { month: new Date(date).getMonth()+1, year: new Date(date).getFullYear() });
    const item = (r.data||[]).find(x=> x.date===date);
    
    if(item && item.daily_report){
        qs('#dr-user-edit-content').value = item.daily_report.content||'';
        if (item.daily_report.evaluation) {
            qs('#dr-user-edit-evaluation').textContent = item.daily_report.evaluation;
            qs('#dr-user-edit-evaluation-container').classList.remove('hidden');
        } else {
            qs('#dr-user-edit-evaluation-container').classList.add('hidden');
        }
    } else {
        qs('#dr-user-edit-content').value = '';
        qs('#dr-user-edit-evaluation-container').classList.add('hidden');
        }
        
        // Cek apakah ada bukti izin/sakit untuk tanggal ini
        if (item && (item.ket === 'izin' || item.ket === 'sakit')) {
            // Get attendance data to find bukti
            const attendanceData = await api('?ajax=get_attendance');
            if (attendanceData.ok && attendanceData.data) {
                const todayRecord = attendanceData.data.find(att => 
                    att.jam_masuk_iso && 
                    att.jam_masuk_iso.slice(0, 10) === date &&
                    (att.ket === 'izin' || att.ket === 'sakit') &&
                    att.bukti_izin_sakit
                );
                
                if (todayRecord) {
                // Tampilkan bukti izin/sakit (edit mode)
                qs('#dr-user-edit-bukti-section').classList.remove('hidden');
                qs('#dr-user-edit-bukti-container').innerHTML = `
                        <div class="flex justify-center">
                            <img src="${todayRecord.bukti_izin_sakit}" alt="Bukti ${todayRecord.ket}" class="max-w-full max-h-64 object-contain rounded border shadow-lg" style="max-width: 100%; height: auto;">
                        </div>
                        <p class="text-sm text-gray-600 mt-2 text-center">Bukti ${todayRecord.ket.toUpperCase()}</p>
                    `;
                // Show edit button
                qs('#dr-user-edit-bukti-actions').classList.remove('hidden');
                qs('#dr-user-edit-bukti-btn').dataset.date = date;
                } else {
                qs('#dr-user-edit-bukti-section').classList.add('hidden');
                qs('#dr-user-edit-bukti-actions').classList.add('hidden');
                }
            }
        } else {
        qs('#dr-user-edit-bukti-section').classList.add('hidden');
        qs('#dr-user-edit-bukti-actions').classList.add('hidden');
    }
    
    qs('#dr-user-edit-modal').classList.remove('hidden'); 
    qs('#dr-user-edit-modal').classList.add('flex');
}

// Event listener untuk tombol laporan harian
document.addEventListener('click', async (e)=>{
    const target = e.target.closest('.btn-create-dr, .btn-edit-dr, .btn-view-dr');
    if(target){
        const date = target.getAttribute('data-date');
        const isView = target.classList.contains('btn-view-dr');
        const isEdit = target.classList.contains('btn-edit-dr');
        
        if (isView) {
            await openDailyReportViewModal(date);
        } else if (isEdit) {
            await openDailyReportEditModal(date);
        } else {
            // Create new report - use edit modal
            await openDailyReportEditModal(date);
        }
    }
});
// Event handlers untuk modal view laporan harian
qs('#dr-user-view-cancel') && qs('#dr-user-view-cancel').addEventListener('click', ()=>{ 
    qs('#dr-user-view-modal').classList.add('hidden'); 
    qs('#dr-user-view-modal').classList.remove('flex'); 
});

// Event handlers untuk modal edit laporan harian
qs('#dr-user-edit-cancel') && qs('#dr-user-edit-cancel').addEventListener('click', ()=>{ 
    qs('#dr-user-edit-modal').classList.add('hidden'); 
    qs('#dr-user-edit-modal').classList.remove('flex'); 
});

qs('#dr-user-edit-save') && qs('#dr-user-edit-save').addEventListener('click', async ()=>{
    const date = qs('#dr-user-edit-modal').dataset.date; 
    const content = qs('#dr-user-edit-content').value;
    const r = await api('?ajax=save_daily_report', { date, content });
    if(r.ok){ 
        qs('#dr-user-edit-modal').classList.add('hidden'); 
        qs('#dr-user-edit-modal').classList.remove('flex'); 
        initRekapPage(); 
    } else { 
        showNotif(r.message||'Gagal simpan'); 
    }
});

// Event handler untuk ganti bukti izin/sakit (modal edit)
qs('#dr-user-edit-bukti-btn') && qs('#dr-user-edit-bukti-btn').addEventListener('click', () => {
    const date = qs('#dr-user-edit-bukti-btn').dataset.date;
    // Open edit bukti modal
    qs('#edit-bukti-modal').classList.remove('hidden');
    qs('#edit-bukti-modal').classList.add('flex');
    qs('#edit-bukti-save').dataset.date = date;
    
    // Show current bukti if exists
    const currentImg = qs('#dr-user-edit-bukti-container img');
    if (currentImg) {
        qs('#edit-bukti-current').classList.remove('hidden');
        qs('#edit-bukti-current-img').src = currentImg.src;
    } else {
        // If no current bukti, hide current section
        qs('#edit-bukti-current').classList.add('hidden');
    }
    
    // Reset file input and preview
    qs('#edit-bukti-file').value = '';
    qs('#edit-bukti-preview').classList.add('hidden');
});

// Event handler untuk modal edit bukti
qs('#edit-bukti-cancel') && qs('#edit-bukti-cancel').addEventListener('click', () => {
    qs('#edit-bukti-modal').classList.add('hidden');
    qs('#edit-bukti-modal').classList.remove('flex');
    qs('#edit-bukti-file').value = '';
    qs('#edit-bukti-preview').classList.add('hidden');
    qs('#edit-bukti-current').classList.add('hidden');
});

qs('#edit-bukti-save') && qs('#edit-bukti-save').addEventListener('click', async () => {
    const date = qs('#edit-bukti-save').dataset.date;
    const file = qs('#edit-bukti-file').files[0];
    
    if (!file) {
        showNotif('Pilih file gambar terlebih dahulu', false);
        return;
    }
    
    // Check file size (5MB = 5 * 1024 * 1024 bytes)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotif(`File terlalu besar. Maksimal 5MB. Ukuran saat ini: ${(file.size / (1024 * 1024)).toFixed(2)}MB`, false);
        return;
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
        showNotif('File harus berupa gambar (JPG, PNG, GIF)', false);
        return;
    }
    
    // Convert file to base64
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const r = await api('?ajax=update_bukti_izin_sakit', {
                date: date,
                action_type: 'update',
                bukti: e.target.result
            });
            
            if (r.ok) {
                showNotif('Bukti berhasil diperbarui');
                qs('#edit-bukti-modal').classList.add('hidden');
                qs('#edit-bukti-modal').classList.remove('flex');
                qs('#edit-bukti-file').value = '';
                qs('#edit-bukti-preview').classList.add('hidden');
                qs('#edit-bukti-current').classList.add('hidden');
                
                // Refresh the daily report modal to show updated bukti
                const drEditModal = qs('#dr-user-edit-modal');
                if (drEditModal && !drEditModal.classList.contains('hidden')) {
                    // Simply refresh the page to show updated data
                    location.reload();
                }
            } else {
                showNotif(r.message || 'Gagal memperbarui bukti', false);
            }
        } catch (error) {
            console.error('Error updating bukti:', error);
            showNotif('Terjadi kesalahan', false);
        }
    };
    reader.readAsDataURL(file);
});

// File upload preview for edit bukti modal
qs('#edit-bukti-file') && qs('#edit-bukti-file').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const preview = qs('#edit-bukti-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            qs('#edit-bukti-preview').src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

// Helper function for month names
function monthName(monthIndex) {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return months[monthIndex] || '';
}

// Tambahkan state untuk paginasi di atas fungsi renderMonthly
let currentMonthlyPageYear = new Date().getFullYear();

async function renderMonthly() {
    // Load settings for max months back and end year
    let monthlyReportEndYear = 2026; // Default: 2026
    try {
        const settingsJson = await api('?ajax=get_settings', {}, { cache: false });
        if (settingsJson.ok && settingsJson.data) {
            if (settingsJson.data.max_monthly_report_months_back) {
                window.maxMonthlyReportMonthsBack = parseInt(settingsJson.data.max_monthly_report_months_back.value) || 999;
            } else {
                window.maxMonthlyReportMonthsBack = 999; // Default: no limit
            }
            if (settingsJson.data.monthly_report_end_year) {
                monthlyReportEndYear = parseInt(settingsJson.data.monthly_report_end_year.value) || 2026;
            }
        } else {
            window.maxMonthlyReportMonthsBack = 999; // Default: no limit
        }
    } catch (e) {
        window.maxMonthlyReportMonthsBack = 999; // Default: no limit on error
    }
    
    // Validate currentMonthlyPageYear - should be between 2025 and monthlyReportEndYear
    if (currentMonthlyPageYear < 2025) {
        currentMonthlyPageYear = 2025;
    }
    if (currentMonthlyPageYear > monthlyReportEndYear) {
        currentMonthlyPageYear = monthlyReportEndYear;
    }
    
    const j = await api('?ajax=get_monthly_reports', {}, { cache: false });
    const list = (j.data || []);
    const body = qs('#table-monthly-body');
    if (!body) return;
    body.innerHTML = ''; // Kosongkan tabel body

    const monthName = (m) => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][m - 1];

    const year = currentMonthlyPageYear; // Gunakan tahun dari state
    const allMonths = Array.from({ length: 12 }, (_, i) => i + 1);

    // Logic untuk aturan waktu (2 bulan terakhir)
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // 1-12

    allMonths.forEach(m => {
        // Handle case where year/month might be 0 or invalid
        let item = list.find(it => {
            const itemYear = parseInt(it.year) || 0;
            const itemMonth = parseInt(it.month) || 0;
            return itemMonth === m && itemYear === year;
        });
        
        // If no item found for this month, check if there's a record with year=0 or month=0 for this month
        if (!item && m === 8 && year === 2025) {
            item = list.find(it => {
                const itemYear = parseInt(it.year) || 0;
                const itemMonth = parseInt(it.month) || 0;
                return (itemYear === 0 || itemMonth === 0) && it.status === 'approved';
            });
        }
        
        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50 text-center';
        const label = `${monthName(m)} ${year}`;

        let actionBtn;
        let statusBadge;

        // Cek apakah bulan ini valid untuk diedit/dibuat
        // Check settings for max months back (default: no limit, allow all months)
        // For now, allow all months - can be restricted via settings later
        const maxMonthsBack = window.maxMonthlyReportMonthsBack || 999; // Default: no limit
        const reportDate = new Date(year, m - 1, 1);
        const todayDate = new Date(currentYear, currentMonth - 1, 1);
        const monthsDiff = (todayDate.getFullYear() - reportDate.getFullYear()) * 12 + (todayDate.getMonth() - reportDate.getMonth());
        const isEditableTime = monthsDiff <= maxMonthsBack; // Allow all months by default

        if (item) { // Jika laporan sudah ada
            const isApproved = item.status === 'approved';
            const isDraft = item.status === 'draft';
            const isSubmitted = item.status === 'belum di approve';
            
            if (isApproved) {
                // Jika sudah di-approve, hanya bisa view (regardless of timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
            } else if (isDraft) {
                // Jika draft, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit Draft</button>`;
                }
            } else if (isSubmitted) {
                // Jika belum di approve, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit</button>`;
                }
            } else {
                // Jika disapproved, bisa view dan edit (jika dalam timeframe)
                actionBtn = `<button class="btn-view-month text-blue-600 font-bold" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-ss-eye"></i> Lihat</button>`;
                if (isEditableTime) {
                    actionBtn += ` <button class="btn-edit-month text-yellow-600 font-bold ml-2" data-json='${JSON.stringify(item).replace(/'/g, "&apos;")}'><i class="fi fi-sr-pen-square"></i> Edit</button>`;
                }
            }
            
            // Status badge
            if (isApproved) {
                statusBadge = `<span class="badge badge-green">Di-approve</span>`;
            } else if (item.status === 'disapproved') {
                statusBadge = `<span class="badge badge-red">Tidak di-approve</span>`;
            } else if (isDraft) {
                statusBadge = `<span class="badge badge-gray">Draft</span>`;
            } else if (isSubmitted) {
                statusBadge = `<span class="badge badge-blue">Belum di Approve</span>`;
            } else {
                statusBadge = `<span class="badge badge-gray">${item.status}</span>`;
            }
        } else { // Jika laporan belum ada
            if (isEditableTime) {
                actionBtn = `<button class="btn-create-month bg-emerald-500 hover:bg-emerald-600 text-white btn-pill" data-year="${year}" data-month="${m}">Buat</button>`;
            } else {
                actionBtn = `<span class="text-gray-400">Not Available</span>`;
            }
            statusBadge = `<span class="badge badge-orange">Belum ada laporan</span>`;
        }

        tr.innerHTML = `
            <td class="py-2 px-4">${label}</td>
            <td class="py-2 px-4">${actionBtn}</td>
            <td class="py-2 px-4">${statusBadge}</td>`;
        body.appendChild(tr);
    });
    
    // Hapus dan buat ulang tombol paginasi - generate from 2025 to monthlyReportEndYear
    let paginationDiv = qs('#monthly-pagination');
    if (paginationDiv) paginationDiv.remove();
    
    paginationDiv = document.createElement('div');
    paginationDiv.id = 'monthly-pagination';
    paginationDiv.className = 'mt-4 flex justify-center gap-2 flex-wrap';
    
    // Generate year buttons from 2025 to monthlyReportEndYear
    const yearButtons = [];
    for (let y = 2025; y <= monthlyReportEndYear; y++) {
        yearButtons.push(`<button data-year="${y}" class="page-btn px-4 py-2 rounded ${currentMonthlyPageYear === y ? 'bg-indigo-600 text-white' : 'bg-gray-200 hover:bg-gray-300'}">${y}</button>`);
    }
    paginationDiv.innerHTML = yearButtons.join('');
    body.closest('.overflow-x-auto').insertAdjacentElement('afterend', paginationDiv);
}


async function renderAdminMonthly(){
    const mSel = qs('#am-month'); const ySel = qs('#am-year'); const sSel = qs('#am-startup');
    if(mSel && mSel.options.length<=2){
        const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        months.forEach((m,i)=>{ const o=document.createElement('option'); o.value=String(i+1); o.textContent=m; mSel.appendChild(o); });
        const yNow=new Date().getFullYear(); for(let y=yNow-2;y<=yNow+1;y++){ const o=document.createElement('option'); o.value=String(y); o.textContent=String(y); ySel.appendChild(o);}
    }
    if(sSel && sSel.options.length<=1){
        const j = await api('?ajax=get_startups', {}, { cache: false });
        if(j.ok && j.data){
            j.data.forEach(startup => {
                const o = document.createElement('option');
                o.value = startup;
                o.textContent = startup;
                sSel.appendChild(o);
            });
        }
    }
}
</script>
<?php endif; ?>

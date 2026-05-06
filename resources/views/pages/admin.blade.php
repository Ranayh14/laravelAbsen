    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-all"></div>
    
    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed top-0 left-0 h-full w-72 bg-white shadow-2xl z-50 transform -translate-x-full transition-transform duration-300 md:hidden font-outfit">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-blue-500">Menu Admin</span>
            <button id="mobile-sidebar-close" class="p-2 hover:bg-gray-50 rounded-full text-gray-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <nav class="p-4 space-y-2">
            <button data-tab="dashboard" class="mobile-tab-link w-full text-left py-3 px-4 font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition duration-300 flex items-center gap-3">
                <i class="fi fi-sr-dashboard"></i> Dashboard
            </button>
            <button data-tab="members" class="mobile-tab-link w-full text-left py-3 px-4 font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition duration-300 flex items-center gap-3">
                <i class="fi fi-sr-users"></i> Kelola Member
            </button>
            <button data-tab="laporan" class="mobile-tab-link w-full text-left py-3 px-4 font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition duration-300 flex items-center gap-3">
                <i class="fi fi-sr-document"></i> Data Presensi
            </button>
            <button data-tab="admin-monthly" class="mobile-tab-link w-full text-left py-3 px-4 font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition duration-300 flex items-center gap-3">
                <i class="fi fi-sr-calendar"></i> Laporan Bulanan
            </button>
            <button data-tab="settings" class="mobile-tab-link w-full text-left py-3 px-4 font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition duration-300 flex items-center gap-3">
                <i class="fi fi-sr-settings"></i> Settings
            </button>
        </nav>
    </div>
    
    <header class="bg-white/80 backdrop-blur-md fixed top-0 left-0 right-0 z-30 border-b border-gray-100">
        <div class="w-full px-4 lg:px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-toggle" class="md:hidden p-2 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">
                        <i class="fi fi-sr-admin-alt text-sm"></i>
                    </div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600 tracking-tight hidden sm:block">SPBW <span class="text-gray-400 font-light">|</span> ADMIN</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Admin Notifications -->
                <div class="relative">
                    <button id="btn-notifications" class="relative p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all group">
                        <i class="fi fi-sr-bell text-xl"></i>
                        <span id="notif-badge" class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full hidden"></span>
                    </button>
                    <!-- Notifications Dropdown -->
                    <div id="dropdown-notifications" class="fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-16 sm:top-full mt-3 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 hidden z-50 overflow-hidden animate-fade-in-up">
                        <div class="p-4 border-b border-gray-50 flex items-center justify-between">
                            <h3 class="font-bold text-gray-800">Permintaan Bantuan</h3>
                            <span id="notif-count" class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-bold">0</span>
                        </div>
                        <div id="notif-items" class="max-h-96 overflow-y-auto p-2 space-y-1">
                            <!-- Items populated by JS -->
                            <div class="p-8 text-center text-gray-400">
                                <i class="fi fi-sr-inbox text-3xl mb-2 block"></i>
                                <p class="text-xs">Tidak ada permintaan baru</p>
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 border-t border-gray-100">
                            <button data-tab="help-requests" class="tab-link w-full text-center text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors uppercase tracking-wider py-2 bg-indigo-50 rounded-full">
                                Lihat Semua Riwayat
                            </button>
                        </div>
                    </div>
                </div>
                <div class="relative flex-shrink-0">
                    <button id="btn-profile" class="flex items-center gap-3 p-1.5 px-5 bg-white border border-gray-200 hover:border-indigo-300 rounded-full transition-all shadow-sm hover:shadow-md group whitespace-nowrap">
                        <?php 
                        $nama = $_SESSION['user']['nama'] ?? 'Admin';
                        $initials = '';
                        $words = explode(' ', $nama);
                        foreach ($words as $w) { if (!empty($w)) $initials .= strtoupper($w[0]); }
                        $initials = substr($initials, 0, 2);
                        
                        if (!empty($_SESSION['user']['foto_base64'])): ?>
                            <img src="<?php echo $_SESSION['user']['foto_base64']; ?>" class="profile-avatar-img ring-2 ring-white" alt="profile">
                        <?php else: ?>
                            <div class="avatar-initials"><?php echo $initials; ?></div>
                        <?php endif; ?>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600 transition-colors hidden sm:inline"><?php echo htmlspecialchars($nama); ?></span>
                        <i class="fi fi-sr-angle-small-down text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                    </button>
                    <div id="dropdown-profile" class="absolute right-4 sm:right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 hidden z-50 overflow-hidden animate-fade-in-up">
                        <?php if(isset($_SESSION['user'])): ?>
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Signed in as</p>
                                <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></p>
                            </div>
                            <a href="?page=logout" class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2">
                                <i class="fi fi-sr-sign-out-alt"></i> Logout
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin Navigation Tabs - Inside Header -->
        <div class="w-full px-4 lg:px-6 pb-3">
            <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                 <button data-tab="dashboard" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-dashboard"></i> <span class="hidden sm:inline">Dashboard</span>
                 </button>
                 <button data-tab="members" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-users"></i> <span class="hidden sm:inline">Member</span>
                 </button>
                 <button data-tab="laporan" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-document"></i> <span class="hidden sm:inline">Presensi</span>
                 </button>
                 <button data-tab="admin-monthly" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-calendar"></i> <span class="hidden sm:inline">Laporan</span>
                 </button>
                 <button data-tab="settings" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-settings"></i> <span class="hidden sm:inline">Settings</span>
                  </button>
                  <button data-tab="help-requests" class="tab-link flex-1 py-2.5 px-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-sr-interrogation"></i> <span class="hidden sm:inline">Requests</span>
                  </button>
            </div>
        </div>
    </header>

    
    <main class="w-full px-2 sm:px-4 lg:px-6 py-8 overflow-x-auto pt-32">
        <style>
            .tab-link.active-tab {
                background-color: #4f46e5 !important;
                color: white !important;
                box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
            }
            .tab-link.active-tab * {
                color: white !important;
            }
            .tab-link:not(.active-tab):hover {
                background-color: #eef2ff;
                color: #4f46e5;
            }
            .avatar-initials {
                width: 36px;
                height: 36px;
                flex-shrink: 0;
                background: linear-gradient(135deg, #4f46e5, #7c3aed);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 14px;
                border-radius: 50%;
                box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
                aspect-ratio: 1/1;
                object-fit: cover;
            }
            .profile-avatar-img {
                width: 36px;
                height: 36px;
                flex-shrink: 0;
                border-radius: 50%;
                object-fit: cover;
                aspect-ratio: 1/1;
            }
        </style>

        <?php if (isAdmin()): ?>
            <?php try { require __DIR__ . '/kelola_member.blade.php'; } catch (Throwable $e) { error_log("Error loading kelola_member: " . $e->getMessage()); } ?>
            <?php try { require __DIR__ . '/data_presensi.blade.php'; } catch (Throwable $e) { error_log("Error loading data_presensi: " . $e->getMessage()); } ?>
            <?php try { require __DIR__ . '/laporan_bulanan_admin.blade.php'; } catch (Throwable $e) { error_log("Error loading laporan_bulanan_admin: " . $e->getMessage()); } ?>
            <?php try { require __DIR__ . '/settings.blade.php'; } catch (Throwable $e) { error_log("Error loading settings: " . $e->getMessage()); } ?>
            <?php try { require __DIR__ . '/dashboard.blade.php'; } catch (Throwable $e) { error_log("Error loading dashboard: " . $e->getMessage()); } ?>
            <?php try { require __DIR__ . '/admin_requests.blade.php'; } catch (Throwable $e) { error_log("Error loading admin_requests: " . $e->getMessage()); } ?>
        <?php endif; ?>
    </main>

    <!-- Modals -->
    <?php try { require __DIR__ . '/components/modals_common.blade.php'; } catch (Throwable $e) {} ?>
    <?php try { require __DIR__ . '/components/modals_admin.blade.php'; } catch (Throwable $e) {} ?>
    <?php try { require __DIR__ . '/components/modals_pegawai.blade.php'; } catch (Throwable $e) {} ?>



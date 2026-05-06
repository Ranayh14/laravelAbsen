<?php
if (isset($_SESSION['user'])) {
    header('Location: ?page=dashboard');
    exit;
}
?>

<div class="min-h-screen flex w-full">
    <!-- Left Side - Form -->
    <div class="w-full lg:w-3/5 flex items-center justify-center p-8 bg-white relative z-10 overflow-y-auto">
         <a href="?page=landing" class="absolute top-8 left-8 text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-2">
            <i class="fi fi-sr-arrow-left"></i> Kembali
        </a>
        
        <div class="max-w-2xl w-full pt-16 lg:pt-0">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Buat Akun Pegawai 🚀</h1>
                <p class="text-slate-500">Lengkapi data diri Anda untuk mulai menggunakan sistem presensi.</p>
            </div>

            <form id="form-register" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-envelope"></i></span>
                        <input name="email" type="email" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="email@perusahaan.com" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">NIM / NIP</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-id-card-clip-alt"></i></span>
                        <input name="nim" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-user"></i></span>
                        <input name="nama" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Program Studi / Divisi</label>
                     <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-briefcase"></i></span>
                        <input name="prodi" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Startup (Opsional)</label>
                     <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-rocket-lunch"></i></span>
                        <input name="startup" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    </div>
                </div>

                <div class="md:col-span-2">
                     <label class="block text-sm font-medium text-slate-700 mb-2">Foto Wajah (Untuk Presensi)</label>
                     <div class="bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:border-emerald-500 transition-colors">
                        <div id="reg-video-container" class="relative bg-gray-900 rounded-lg w-full aspect-video mb-3 hidden overflow-hidden shadow-lg">
                            <video id="reg-video" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
                            <div class="absolute inset-0 border-2 border-emerald-500/50 pointer-events-none rounded-lg"></div>
                        </div>
                        <canvas id="reg-canvas" class="hidden"></canvas>
                        <img id="reg-foto-preview" class="mt-2 mb-4 h-32 w-32 object-cover rounded-full shadow-lg hidden mx-auto ring-4 ring-white">
                        
                        <input type="hidden" name="foto" id="reg-foto-data">
                        <input type="file" id="reg-photo-file-input" accept="image/*" class="hidden">
                        
                        <div class="grid grid-cols-2 gap-3" id="photo-actions">
                            <button type="button" id="reg-start-camera" class="bg-slate-800 hover:bg-slate-900 text-white font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 transition-all">
                                <i class="fi fi-sr-camera"></i> Buka Kamera
                            </button>
                            <button type="button" id="reg-upload-photo" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 transition-all">
                                <i class="fi fi-sr-picture"></i> Upload
                            </button>
                        </div>

                        <div class="flex gap-3 hidden" id="camera-actions">
                            <button type="button" id="reg-take-photo" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 transition-all">
                                <i class="fi fi-sr-aperture"></i> Ambil Foto
                            </button>
                            <button type="button" id="reg-remove-photo" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 transition-all hidden">
                                <i class="fi fi-sr-trash"></i> Hapus
                            </button>
                        </div>
                     </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-lock"></i></span>
                        <input name="password" type="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-check-circle"></i></span>
                        <input name="password2" type="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" required>
                    </div>
                </div>
                
                <div class="md:col-span-2 pt-2">
                    <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5">
                        Daftarkan Akun
                    </button>
                </div>
            </form>

            <div id="register-msg" class="text-center text-sm mt-6"></div>
            <p class="text-center text-slate-500 mt-6 pb-8">
                Sudah punya akun? <a class="text-emerald-600 font-bold hover:underline" href="?page=login">Login disini</a>
            </p>
        </div>
    </div>

    <!-- Right Side - Illustration -->
    <div class="hidden lg:flex w-2/5 bg-emerald-50 items-center justify-center p-12 relative overflow-hidden fixed right-0 h-screen">
         <div class="absolute inset-0 bg-gradient-to-bl from-emerald-100 to-teal-50 opacity-70"></div>
         <!-- Decorative blobs -->
         <div class="absolute top-1/4 right-1/4 w-80 h-80 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
         
         <div class="relative z-10 text-center max-w-sm">
             <img src="assets/photo/character_login.png" class="max-w-xs mx-auto drop-shadow-2xl mb-8 transform scale-x-[-1]" alt="Register Illustration">
             <h2 class="text-2xl font-bold text-emerald-900 mb-3">Bergabung Tim Kami</h2>
             <p class="text-emerald-700/80">Mulai catat kehadiran Anda dengan sistem yang modern dan efisien.</p>
         </div>
    </div>
</div>

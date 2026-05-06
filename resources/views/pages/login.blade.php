<?php
if (isset($_SESSION['user'])) {
    header('Location: ?page=dashboard');
    exit;
}
?>

<div class="min-h-screen flex w-full">
    <!-- Left Side - Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-8 md:p-12 bg-white relative z-10">
        <a href="?page=landing" class="absolute top-8 left-8 text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-2">
            <i class="fi fi-sr-arrow-left"></i> Kembali
        </a>
        
        <div class="max-w-md w-full animate-fade-in-up">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Selamat Datang! 👋</h1>
                <p class="text-slate-500">Silakan masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            <form id="form-login" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email / NIM</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-envelope"></i></span>
                        <input name="email" type="text" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium" placeholder="nama@email.com" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-lock"></i></span>
                        <input name="password" type="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium" placeholder="••••••••" required>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-500 hover:text-slate-700 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                        Ingat saya
                    </label>
                    <a href="?page=forgot-password" class="text-indigo-600 font-semibold hover:text-indigo-700 hover:underline">Lupa Password?</a>
                </div>

                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                    Masuk Sekarang
                </button>
            </form>

            <div id="login-msg" class="text-center text-sm mt-6"></div>

            <p class="text-center text-slate-500 mt-8">
                Belum punya akun? <a class="text-indigo-600 font-bold hover:underline" href="?page=register">Daftar sekarang</a>
            </p>
        </div>
    </div>

    <!-- Right Side - Illustration -->
    <div class="hidden md:flex md:w-1/2 bg-indigo-50 items-center justify-center p-12 relative overflow-hidden">
         <div class="absolute inset-0 bg-gradient-to-br from-indigo-100 to-blue-50 opacity-70"></div>
         <!-- Decorative blobs -->
         <div class="absolute top-20 right-20 w-64 h-64 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float"></div>
         <div class="absolute bottom-20 left-20 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float" style="animation-delay: 2s"></div>
         
         <div class="relative z-10 text-center max-w-lg">
             <img src="assets/photo/character_login.png" class="max-w-md w-full mx-auto drop-shadow-2xl animate-float" alt="Login Illustration">
             <h2 class="text-2xl font-bold text-indigo-900 mt-8 mb-2">Akses Presensi yang Aman</h2>
             <p class="text-indigo-700/80">Kelola kehadiran Anda dengan mudah dan aman menggunakan teknologi pengenalan wajah.</p>
         </div>
    </div>
</div>

<div class="min-h-screen flex items-center justify-center p-6 bg-slate-50 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 right-0 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-80 h-80 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 border border-white/20 relative z-10 animate-scale-in">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 mb-4 shadow-sm">
                <i class="fi fi-sr-lock text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Lupa Password?</h1>
            <p class="text-slate-500 text-sm">Jangan khawatir! Masukkan email Anda dan kami akan mengirimkan instruksi reset password.</p>
        </div>

        <form id="form-forgot-password" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Terdaftar</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-envelope"></i></span>
                    <input name="email" type="email" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium" placeholder="nama@email.com" required>
                </div>
            </div>
            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5">
                Kirim Link Reset
            </button>
        </form>

        <div id="forgot-password-msg" class="text-center text-sm mt-6"></div>

        <p class="text-center text-slate-500 mt-8 text-sm">
            Ingat password Anda? <a class="text-indigo-600 font-bold hover:underline" href="?page=login">Kembali Login</a>
        </p>
    </div>
</div>

<?php $tokenFromUrl = $_GET['token'] ?? ''; ?>
<div class="min-h-screen flex items-center justify-center p-6 bg-slate-50 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
         <div class="absolute top-1/3 left-1/4 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
         <div class="absolute bottom-1/3 right-1/4 w-72 h-72 bg-cyan-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 border border-white/20 relative z-10 animate-scale-in">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4 shadow-sm">
                <i class="fi fi-sr-key text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Reset Password</h1>
            <p class="text-slate-500 text-sm">Buat password baru untuk mengamankan akun Anda.</p>
        </div>

        <form id="form-reset-password" class="space-y-5">
            <input type="hidden" id="reset-token-final" name="token" value="<?php echo htmlspecialchars($tokenFromUrl); ?>">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-lock"></i></span>
                    <input name="password" type="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium" placeholder="••••••••" required>
                </div>
            </div>

             <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-slate-400"><i class="fi fi-sr-check-circle"></i></span>
                    <input name="password2" type="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium" placeholder="••••••••" required>
                </div>
            </div>

            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5">
                Simpan Password Baru
            </button>
        </form>

        <div id="reset-password-msg" class="text-center text-sm mt-6"></div>
    </div>
</div>

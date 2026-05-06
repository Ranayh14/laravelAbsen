<?php $tokenFromUrl = $_GET['token'] ?? ''; ?>
<div class="min-h-screen flex items-center justify-center p-6 bg-slate-50 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
         <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
         <div class="absolute top-20 left-20 w-72 h-72 bg-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    </div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 border border-white/20 relative z-10 animate-scale-in">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4 shadow-sm">
                <i class="fi fi-sr-shield-check text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Verifikasi OTP</h1>
            <p class="text-slate-500 text-sm">Masukkan 6 digit kode keamanan dari aplikasi Google Authenticator Anda.</p>
        </div>

        <form id="form-verify-otp" class="space-y-6">
            <input type="hidden" id="reset-token" name="token" value="<?php echo htmlspecialchars($tokenFromUrl); ?>">
            
            <div class="flex justify-center">
                <input name="otp" type="text" maxlength="6" pattern="[0-9]{6}" class="w-48 text-center text-3xl tracking-[0.5em] font-bold py-3 bg-slate-50 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-slate-700" placeholder="000000" required autocomplete="one-time-code">
            </div>
            
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5">
                Verifikasi Kode
            </button>
        </form>

        <div id="verify-otp-msg" class="text-center text-sm mt-6"></div>

        <p class="text-center text-slate-500 mt-8 text-sm">
            Terdapat masalah? <a class="text-emerald-600 font-bold hover:underline" href="?page=forgot-password">Kirim ulang link</a>
        </p>
    </div>
</div>

<?php
/**
 * Robot Cat Hero Banner Component for Pegawai Dashboard
 */
?>
<!-- Hero Banner for Pegawai -->
<div class="relative w-full rounded-2xl overflow-hidden shadow-lg mb-8 animate-fade-in-up">
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800"></div>
    <!-- Decorative Circles -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-8 -mt-8 blur-xl"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-400/20 rounded-full -ml-6 -mb-6 blur-lg"></div>
    
    <div class="relative p-6 flex flex-row items-center justify-between gap-6">
        <div class="text-white flex-1 z-10">
            <h2 class="text-2xl md:text-3xl font-bold mb-2 tracking-tight">
                Halo <?php echo explode(' ', $_SESSION['user']['nama'])[0]; ?>!
                <span class="text-blue-200 block text-lg md:text-xl font-normal mt-1">Jangan Lupa Laporan.</span>
            </h2>
            <p class="text-blue-100 text-sm mb-4 max-w-md">
                Pastikan presensi masuk dan pulang tercatat, serta laporan harian terisi dengan benar.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="?page=presensi-masuk" class="bg-white hover:bg-gray-50 text-indigo-600 font-semibold py-2 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 text-sm">
                    <i class="fi fi-sr-sign-in-alt"></i>
                    <span>Presensi Masuk</span>
                </a>
                <a href="?page=presensi-pulang" class="bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-2 px-4 rounded-xl shadow-md transition-all flex items-center gap-2 border border-white/20 text-sm">
                    <i class="fi fi-sr-sign-out-alt"></i>
                    <span>Presensi Pulang</span>
                </a>
            </div>
        </div>
        
        <!-- SVG Robot Cat Character -->
        <div class="relative w-24 h-24 md:w-28 md:h-28 flex items-center justify-center flex-shrink-0">
            <div class="w-full h-full bg-gradient-to-tr from-blue-400/20 to-indigo-400/20 backdrop-blur-sm rounded-2xl absolute border border-white/20"></div>
            <div id="robot-cat-character" class="relative z-10 w-full h-full emotion-happy">
                <svg viewBox="0 0 400 400" width="100%" height="100%">
                    <!-- DEFINITIONS -->
                    <defs>
                        <linearGradient id="metalGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#f8fafc;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#94a3b8;stop-opacity:1" />
                        </linearGradient>
                        <symbol id="heart-shape" viewBox="0 0 32 32">
                            <path d="M16 28.5L14.1 26.8C7.3 20.6 2.8 16.5 2.8 11.5C2.8 7.4 6 4.2 10.1 4.2C12.4 4.2 14.6 5.3 16 7C17.4 5.3 19.6 4.2 21.9 4.2C26 4.2 29.2 7.4 29.2 11.5C29.2 16.5 24.7 20.6 17.9 26.8L16 28.5Z" />
                        </symbol>
                    </defs>

                    <!-- SHADOW -->
                    <ellipse cx="200" cy="370" rx="90" ry="12" fill="rgba(0,0,0,0.15)" />

                    <!-- === EKOR (DI BELAKANG BADAN) === -->
                    <!-- Ekor Happy -->
                    <g id="tail-happy-state">
                        <g id="tail-happy-group">
                            <path d="M260 250 Q350 240 340 160" fill="none" stroke="url(#metalGrad)" stroke-width="14" stroke-linecap="round" />
                            <use href="#heart-shape" x="325" y="125" width="30" height="30" fill="url(#metalGrad)" transform="rotate(-15, 340, 140)" />
                        </g>
                    </g>

                    <!-- Ekor Sad -->
                    <g id="tail-sad-state" class="hidden">
                        <path d="M260 250 Q310 260 330 350" fill="none" stroke="url(#metalGrad)" stroke-width="14" stroke-linecap="round" />
                    </g>

                    <!-- Ekor Angry -->
                    <g id="tail-angry-state" class="hidden">
                        <path id="tail-angry-v3" d="M260 250 L290 220 L310 260 L330 210 L350 250 L370 180" fill="none" stroke="url(#metalGrad)" stroke-width="12" stroke-linecap="round" />
                    </g>

                    <!-- === TUBUH === -->
                    <g id="cat-body">
                        <!-- Kaki Belakang -->
                        <rect x="235" y="290" width="32" height="75" rx="16" fill="#64748b" stroke="#1e293b" stroke-width="3"/>
                        <rect x="135" y="290" width="32" height="75" rx="16" fill="#64748b" stroke="#1e293b" stroke-width="3"/>
                        
                        <!-- Kaki Depan -->
                        <rect x="210" y="300" width="38" height="70" rx="19" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                        <rect x="150" y="300" width="38" height="70" rx="19" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                        
                        <!-- Baut kaki -->
                        <circle cx="230" cy="315" r="3" fill="#1e293b" opacity="0.4"/>
                        <circle cx="170" cy="315" r="3" fill="#1e293b" opacity="0.4"/>

                        <!-- Badan Utama -->
                        <path d="M120 210 Q120 170 200 170 Q280 170 280 210 L280 300 Q280 330 200 330 Q120 330 120 300 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                        
                        <!-- Detail Panel -->
                        <path d="M135 230 L265 230" stroke="#1e293b" stroke-width="1" opacity="0.2"/>
                        <circle cx="260" cy="250" r="15" fill="#1e293b" opacity="0.1"/>
                        <circle cx="260" cy="250" r="8" fill="var(--glow-cyan)" class="glow-cyan" id="body-light"/>
                    </g>

                    <!-- === KEPALA === -->
                    <!-- Happy Head -->
                    <g id="head-happy-state">
                        <!-- Floating Hearts -->
                        <use href="#heart-shape" x="80" y="80" width="25" height="25" fill="#00f2ff" class="floating-heart" style="animation-delay: 0s" />
                        <use href="#heart-shape" x="290" y="100" width="20" height="20" fill="#00f2ff" class="floating-heart" style="animation-delay: 0.7s" />
                        
                        <g id="head-base">
                            <path d="M100 140 Q100 90 180 90 Q260 90 260 140 L260 195 Q260 235 180 235 Q100 235 100 195 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="5"/>
                            <!-- Telinga -->
                            <path d="M125 105 L95 45 L165 95 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <path d="M235 105 L265 45 L195 95 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <!-- Visor -->
                            <rect x="120" y="130" width="120" height="75" rx="37" fill="#1e293b"/>
                            <!-- Eyes Happy -->
                            <path d="M140 165 Q155 145 170 165" fill="none" stroke="var(--glow-cyan)" stroke-width="6" stroke-linecap="round" class="glow-cyan"/>
                            <path d="M190 165 Q205 145 220 165" fill="none" stroke="var(--glow-cyan)" stroke-width="6" stroke-linecap="round" class="glow-cyan"/>
                        </g>
                    </g>

                    <!-- Sad Head -->
                    <g id="head-sad-state" class="hidden">
                        <g id="head-sad-v3">
                            <path d="M100 140 Q100 90 180 90 Q260 90 260 140 L260 195 Q260 235 180 235 Q100 235 100 195 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="5"/>
                            <!-- Telinga Animated -->
                            <path id="ear-l-sad" d="M125 105 L85 125 L155 120 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <path id="ear-r-sad" d="M235 105 L275 125 L205 120 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <!-- Visor -->
                            <rect x="120" y="130" width="120" height="75" rx="37" fill="#1e293b"/>
                            <!-- Eyes Sad -->
                            <path d="M145 175 Q155 160 165 175" fill="none" stroke="var(--glow-cyan)" stroke-width="5" stroke-linecap="round" opacity="0.5"/>
                            <path d="M195 175 Q205 160 215 175" fill="none" stroke="var(--glow-cyan)" stroke-width="5" stroke-linecap="round" opacity="0.5"/>
                            <!-- Tears -->
                            <circle cx="155" cy="185" r="3" fill="var(--glow-cyan)" opacity="0.8">
                                <animate attributeName="cy" from="185" to="210" dur="2s" repeatCount="indefinite" />
                                <animate attributeName="opacity" from="0.8" to="0" dur="2s" repeatCount="indefinite" />
                            </circle>
                        </g>
                    </g>

                    <!-- Angry Head -->
                    <g id="head-angry-state" class="hidden">
                        <!-- Smoke -->
                        <circle cx="110" cy="60" r="12" fill="#cbd5e1" class="smoke-puff" />
                        <circle cx="250" cy="50" r="10" fill="#cbd5e1" class="smoke-puff" style="animation-delay: 0.8s"/>
                        
                        <g id="head-angry-v3">
                            <path d="M100 140 Q100 90 180 90 Q260 90 260 140 L260 195 Q260 235 180 235 Q100 235 100 195 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="5"/>
                            <path d="M125 105 L95 45 L165 95 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <path d="M235 105 L265 45 L195 95 Z" fill="url(#metalGrad)" stroke="#1e293b" stroke-width="4"/>
                            <!-- Visor -->
                            <rect x="120" y="130" width="120" height="75" rx="37" fill="#1e293b"/>
                            <!-- Eyes Angry (Red) -->
                            <path d="M140 170 L170 155" fill="none" stroke="var(--glow-red)" stroke-width="8" stroke-linecap="round" class="glow-red"/>
                            <path d="M190 155 L220 170" fill="none" stroke="var(--glow-red)" stroke-width="8" stroke-linecap="round" class="glow-red"/>
                            <!-- Electric Spark -->
                            <path d="M175 80 L185 60 L195 75" fill="none" stroke="#fbbf24" stroke-width="3" class="glow-cyan"/>
                        </g>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Modern Animated Robot Hero Banner Component for Pegawai Dashboard
 * Replaces the old cat robot with a clean, professional robot character.
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
        
        <!-- Modern Animated Robot (No Cat Ears) -->
        <div class="relative w-24 h-24 md:w-32 md:h-32 flex items-center justify-center flex-shrink-0">
            <div class="w-full h-full bg-gradient-to-tr from-blue-400/20 to-indigo-400/20 backdrop-blur-sm rounded-2xl absolute border border-white/20"></div>
            <div id="robot-container" class="relative z-10 w-full h-full">
                <!-- ================== ROBOT SENANG (Default) ================== -->
                <svg id="robot-senang" viewBox="0 0 120 120" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Robot Senang">
                    <defs>
                        <linearGradient id="robotBodyHero" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#F8FAFC" />
                            <stop offset="100%" stop-color="#E2E8F0" />
                        </linearGradient>
                        <linearGradient id="robotScreenHero" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#0F172A" />
                            <stop offset="100%" stop-color="#020617" />
                        </linearGradient>
                        <filter id="heroHeartGlow" x="-25%" y="-25%" width="150%" height="150%">
                            <feDropShadow dx="0" dy="0" stdDeviation="3.5" flood-color="#EC4899" flood-opacity="0.8" />
                        </filter>
                        <filter id="heroCyanGlow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="2" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>

                    <style>
                        @keyframes heroRobotFloat {
                            0%, 100% { transform: translateY(0) rotate(0deg); }
                            50% { transform: translateY(-3.5px) rotate(0.8deg); }
                        }
                        @keyframes heroHeartRise1 {
                            0% { transform: translateY(15px) translateX(0) scale(0.6); opacity: 0; }
                            25% { opacity: 0.9; }
                            100% { transform: translateY(-40px) translateX(10px) scale(1.1); opacity: 0; }
                        }
                        @keyframes heroHeartRise2 {
                            0% { transform: translateY(15px) translateX(0) scale(0.6); opacity: 0; }
                            25% { opacity: 0.9; }
                            100% { transform: translateY(-35px) translateX(-12px) scale(1.05); opacity: 0; }
                        }
                        .hero-robot-float { animation: heroRobotFloat 2.5s infinite ease-in-out; transform-origin: center bottom; }
                        .hero-heart-1 { animation: heroHeartRise1 2.8s infinite linear; transform-origin: center; }
                        .hero-heart-2 { animation: heroHeartRise2 3.2s infinite linear 1s; transform-origin: center; }
                    </style>

                    <g class="hero-heart-1" filter="url(#heroHeartGlow)">
                        <path d="M15 35 C15 31, 21 31, 21 35 C21 39, 15 43, 15 43 C15 43, 9 39, 9 35 C9 31, 15 31, 15 35 Z" fill="#EC4899" />
                    </g>
                    <g class="hero-heart-2" filter="url(#heroHeartGlow)">
                        <path d="M104 25 C104 21, 110 21, 110 25 C110 29, 104 33, 104 33 C104 33, 98 29, 98 25 C98 21, 104 21, 104 25 Z" fill="#F43F5E" />
                    </g>

                    <g class="hero-robot-float">
                        <ellipse cx="60" cy="112" rx="22" ry="5" fill="#020617" fill-opacity="0.15" />
                        <rect x="44" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="66" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="36" y="68" width="48" height="30" rx="10" fill="#FFFFFF" stroke="#64748B" stroke-width="3" />
                        <path d="M60 77 C60 75, 64 75, 64 77 C64 80, 60 83, 60 83 C60 83, 56 80, 56 77 C56 75, 60 75, 60 77 Z" fill="#EC4899" />
                        <rect x="54" y="62" width="12" height="8" fill="#F1F5F9" stroke="#64748B" stroke-width="3" />
                        <rect x="28" y="24" width="64" height="42" rx="14" fill="#FFFFFF" stroke="#64748B" stroke-width="3.5" />
                        <rect x="34" y="30" width="52" height="30" rx="8" fill="#0F172A" stroke="#64748B" stroke-width="2.5" />
                        
                        <path d="M47 43C47 47 39 47 39 43" stroke="#06B6D4" stroke-width="4" stroke-linecap="round" filter="url(#heroCyanGlow)" />
                        <path d="M81 43C81 47 73 47 73 43" stroke="#06B6D4" stroke-width="4" stroke-linecap="round" filter="url(#heroCyanGlow)" />
                        
                        <line x1="60" y1="24" x2="60" y2="13" stroke="#94A3B8" stroke-width="3" />
                        <path d="M60 11 C60 9, 63 9, 63 11 C63 13, 60 15, 60 15 C60 15, 57 13, 57 11 C57 9, 60 9, 60 11 Z" fill="#EC4899" filter="url(#heroHeartGlow)" />
                    </g>
                </svg>

                <!-- ================== ROBOT SEDIH ================== -->
                <svg id="robot-sedih" class="hidden" viewBox="0 0 120 120" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Robot Sedih">
                    <style>
                        @keyframes heroRobotDroop {
                            0%, 100% { transform: translateY(4px) rotate(0deg); }
                            50% { transform: translateY(6px) rotate(0.5deg); }
                        }
                        @keyframes heroTearDrop {
                            0% { transform: translateY(0); opacity: 0.8; }
                            50% { transform: translateY(15px); opacity: 1; }
                            100% { transform: translateY(25px); opacity: 0; }
                        }
                        .hero-robot-droop { animation: heroRobotDroop 3s infinite ease-in-out; transform-origin: center bottom; }
                        .hero-tear { animation: heroTearDrop 2.5s infinite ease-in; }
                    </style>

                    <g class="hero-robot-droop">
                        <ellipse cx="60" cy="112" rx="22" ry="5" fill="#020617" fill-opacity="0.15" />
                        <rect x="44" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="66" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="36" y="68" width="48" height="30" rx="10" fill="#FFFFFF" stroke="#64748B" stroke-width="3" />
                        <path d="M60 77 C60 75, 64 75, 64 77 C64 80, 60 83, 60 83 C60 83, 56 80, 56 77 C56 75, 60 75, 60 77 Z" fill="#94A3B8" opacity="0.6"/> <!-- Grey heart -->
                        <rect x="54" y="62" width="12" height="8" fill="#F1F5F9" stroke="#64748B" stroke-width="3" />
                        
                        <rect x="28" y="26" width="64" height="42" rx="14" fill="#FFFFFF" stroke="#64748B" stroke-width="3.5" /> <!-- Head slightly lower -->
                        <rect x="34" y="32" width="52" height="30" rx="8" fill="#0F172A" stroke="#64748B" stroke-width="2.5" />
                        
                        <!-- Sad eyes -->
                        <path d="M39 46L47 41" stroke="#06B6D4" stroke-width="3.5" stroke-linecap="round" filter="url(#heroCyanGlow)" opacity="0.6" />
                        <path d="M81 46L73 41" stroke="#06B6D4" stroke-width="3.5" stroke-linecap="round" filter="url(#heroCyanGlow)" opacity="0.6" />
                        
                        <!-- Tear -->
                        <circle cx="43" cy="49" r="2.5" fill="#06B6D4" filter="url(#heroCyanGlow)" class="hero-tear" />
                        <circle cx="77" cy="49" r="2.5" fill="#06B6D4" filter="url(#heroCyanGlow)" class="hero-tear" style="animation-delay: 1.2s;" />
                        
                        <!-- Drooping antenna -->
                        <path d="M60 26 Q60 18 53 14" fill="none" stroke="#94A3B8" stroke-width="3" stroke-linecap="round" />
                        <circle cx="51" cy="14" r="3" fill="#94A3B8" />
                    </g>
                </svg>

                <!-- ================== ROBOT MARAH ================== -->
                <svg id="robot-marah" class="hidden" viewBox="0 0 120 120" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Robot Marah">
                    <defs>
                        <filter id="heroRedGlow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="2" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>
                    <style>
                        @keyframes heroRobotShake {
                            0%, 100% { transform: translateX(0); }
                            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px) rotate(-1deg); }
                            20%, 40%, 60%, 80% { transform: translateX(2px) rotate(1deg); }
                        }
                        @keyframes heroSmoke {
                            0% { transform: translateY(0) scale(0.5); opacity: 0.8; }
                            100% { transform: translateY(-15px) scale(1.5); opacity: 0; }
                        }
                        .hero-robot-shake { animation: heroRobotShake 0.5s infinite; transform-origin: center; }
                        .hero-smoke-1 { animation: heroSmoke 1.5s infinite linear; }
                        .hero-smoke-2 { animation: heroSmoke 1.2s infinite linear 0.5s; }
                    </style>

                    <!-- Smoke puffs -->
                    <circle cx="30" cy="20" r="5" fill="#94A3B8" class="hero-smoke-1" />
                    <circle cx="90" cy="25" r="4" fill="#94A3B8" class="hero-smoke-2" />

                    <g class="hero-robot-shake">
                        <ellipse cx="60" cy="112" rx="22" ry="5" fill="#020617" fill-opacity="0.15" />
                        <rect x="44" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="66" y="94" width="10" height="14" rx="5" fill="#E2E8F0" stroke="#64748B" stroke-width="3" />
                        <rect x="36" y="68" width="48" height="30" rx="10" fill="#FFFFFF" stroke="#64748B" stroke-width="3" />
                        
                        <!-- Crack on chest -->
                        <path d="M55 75 L60 82 L58 88 L65 92" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        
                        <rect x="54" y="62" width="12" height="8" fill="#F1F5F9" stroke="#64748B" stroke-width="3" />
                        
                        <rect x="28" y="24" width="64" height="42" rx="14" fill="#FFFFFF" stroke="#64748B" stroke-width="3.5" />
                        <rect x="34" y="30" width="52" height="30" rx="8" fill="#0F172A" stroke="#64748B" stroke-width="2.5" />
                        
                        <!-- Angry eyes (red) -->
                        <path d="M38 41L49 46" stroke="#EF4444" stroke-width="4" stroke-linecap="round" filter="url(#heroRedGlow)" />
                        <path d="M82 41L71 46" stroke="#EF4444" stroke-width="4" stroke-linecap="round" filter="url(#heroRedGlow)" />
                        
                        <!-- Angry antenna -->
                        <path d="M60 24 L57 18 L63 12 L60 7" fill="none" stroke="#EF4444" stroke-width="3" stroke-linejoin="round" filter="url(#heroRedGlow)" />
                    </g>
                </svg>
            </div>
        </div>
    </div>
</div>

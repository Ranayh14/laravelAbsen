<!-- Hidden SVG Gradient Definitions for Modern Emoji Icons -->
<svg width="0" height="0" style="position:absolute;overflow:hidden" aria-hidden="true">
  <defs>
    <linearGradient id="lg-happy" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#38BDF8"/><stop offset="100%" stop-color="#0284C7"/></linearGradient>
    <linearGradient id="lg-sleeping" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#C084FC"/><stop offset="100%" stop-color="#7E22CE"/></linearGradient>
    <linearGradient id="lg-energetic" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#34D399"/><stop offset="100%" stop-color="#059669"/></linearGradient>
    <linearGradient id="lg-bored" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#F87171"/><stop offset="100%" stop-color="#DC2626"/></linearGradient>
    <linearGradient id="lg-unknown" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#FBBF24"/><stop offset="100%" stop-color="#D97706"/></linearGradient>
    <linearGradient id="lg-future" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#94A3B8"/><stop offset="100%" stop-color="#64748B"/></linearGradient>
    <linearGradient id="lg-overtime-side" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#FB923C"/><stop offset="100%" stop-color="#EA580C"/></linearGradient>
  </defs>
</svg>
<div id="page-rekap" class="<?php echo isAdmin() ? 'hidden' : '';?> animate-fade-in-up">
    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Rekap Daftar Hadir</h2>
            <div id="rekap-controls" class="flex flex-wrap items-center gap-2">
                <select id="rekap-view-mode" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors">
                    <option value="monthly">Bulanan</option>
                    <option value="weekly">Mingguan</option>
                </select>
                <select id="rekap-month" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors"></select>
                <select id="rekap-year" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-colors"></select>
                <select id="rekap-week" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 hidden transition-colors"></select>
                <button id="btn-load-rekap" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fi fi-sr-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div id="pegawai-info" class="text-sm text-gray-600 mb-6 bg-blue-50 p-4 rounded-2xl hidden user-info-box"></div>
        
        <!-- KPI Chart Section -->
        <div id="kpi-chart-section" class="mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                Penilaian KPI Absen
            </h3>
            <!-- Score Summary Header -->
            <div id="kpi-score-header" class="mb-6 bg-indigo-50 p-4 rounded-2xl flex items-center justify-between shadow-sm border border-indigo-100 hidden">
                <div>
                    <div class="text-sm text-indigo-600 font-medium">KPI Score</div>
                    <div id="kpi-score-value" class="text-3xl font-bold text-indigo-700">0</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-indigo-600 font-medium">Status</div>
                    <div id="kpi-status-value" class="text-lg font-bold text-indigo-700">-</div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 h-80 relative">
                <canvas id="kpi-chart"></canvas>
            </div>
            
            <!-- Hidden container for raw values if needed later -->
            <div id="kpi-summary" class="hidden"></div>
        </div>
        
        
        <style>
            .calendar-section-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: 1fr 300px;
                gap: 1.5rem;
                align-items: start;
            }
            .calendar-mood-board {
                background: white;
                border-radius: 24px;
                padding: 1.5rem;
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
                width: 100%;
            }
            .calendar-sidebar {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .sidebar-card {
                background: white;
                border-radius: 24px;
                padding: 1.5rem;
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            }
            .stat-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.75rem;
                border-radius: 16px;
                transition: all 0.3s ease;
            }
            .stat-item:hover {
                background: #f8fafc;
                transform: translateX(5px);
            }
            .stat-icon {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .stat-info {
                flex: 1;
            }
            .stat-label {
                font-size: 0.65rem;
                font-weight: 800;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .stat-value {
                font-size: 1.1rem;
                font-weight: 900;
                color: #1e293b;
            }

            .calendar-header-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.75rem;
                margin-bottom: 1rem;
                border-bottom: 2px solid #f8fafc;
                padding-bottom: 0.75rem;
            }
            .day-header {
                text-align: center;
                font-size: 0.75rem;
                font-weight: 900;
                color: #cbd5e1;
                text-transform: uppercase;
                letter-spacing: 0.1em;
            }
            .mood-board-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 0.75rem;
            }
            .mood-item {
                aspect-ratio: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                position: relative;
                border-radius: 18px;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                background: #f8fafc;
                border: 2px solid transparent;
            }
            .mood-item:not(.empty-slot):hover {
                background: white;
                border-color: #f1f5f9;
                transform: translateY(-3px);
                box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.05);
            }
            .mood-button {
                width: 68%;
                aspect-ratio: 1;
                border-radius: 30%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                border: none;
                cursor: pointer;
                background: transparent;
                padding: 0;
                overflow: hidden;
            }
            .mood-button svg {
                width: 100%;
                height: 100%;
                border-radius: 28%;
                transition: transform 0.3s ease;
                filter: drop-shadow(0 3px 6px rgba(0,0,0,0.12));
            }
            .mood-button:hover svg {
                transform: scale(1.1) rotate(3deg);
            }
            .mood-status-dot {
                width: 25%;
                height: 25%;
                min-width: 8px;
                min-height: 8px;
                border-radius: 50%;
                border: 2px solid white;
                position: absolute;
                bottom: -5%;
                right: -5%;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
                z-index: 5;
            }
            .mood-bubble {
                position: absolute;
                bottom: 110%;
                left: 50%;
                transform: translateX(-50%) translateY(-10px);
                background: #1e293b;
                color: white;
                padding: 0.4rem 0.8rem;
                border-radius: 10px;
                font-size: 0.65rem;
                font-weight: 800;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 30;
                pointer-events: none;
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            }
            .mood-bubble::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border-width: 5px;
                border-style: solid;
                border-color: #1e293b transparent transparent transparent;
            }
            .mood-item:hover .mood-bubble {
                opacity: 1;
                visibility: visible;
                transform: translateX(-50%) translateY(-5px);
            }
            .mood-date {
                font-size: 0.75rem;
                font-weight: 800;
                color: #94a3b8;
                margin-top: 8%;
            }
            
            /* Updated Mood Colors - Subtle Glow for Modern SVG Emojis */
            .mood-blue-bright { box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2); } /* Holi-yay: Sky Blue */
            .mood-purple-dark { box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.2); } /* On Leave: Purple */
            .mood-green { box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.2); } /* WFO: Emerald */
            .mood-yellow { box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2); } /* WFA: Amber */
            .mood-orange { box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.2); } /* Overtime: Orange */
            .mood-red { box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.2); } /* Missing: Red */
            .mood-gray { box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15); } /* Gray/Future */
            .mood-today-empty { border: 2px dashed #cbd5e1; background: white; opacity: 0.8; }


            /* Responsive Adjustments */
            @media (max-width: 1024px) {
                .calendar-section-wrapper {
                    grid-template-columns: 1fr;
                    max-width: 850px;
                }
                .calendar-sidebar {
                    flex-direction: row;
                    flex-wrap: wrap;
                }
                .sidebar-card { flex: 1; min-width: 250px; }
            }

            @media (max-width: 640px) {
                .mood-board-grid, .calendar-header-grid { gap: 0.4rem; }
                .mood-item { border-radius: 12px; }
                .mood-date { font-size: 0.65rem; }
                .day-header { font-size: 0.65rem; letter-spacing: 0.05em; }
                .calendar-mood-board, .sidebar-card { border-radius: 20px; padding: 0.75rem; }
                .mood-bubble { font-size: 0.65rem; padding: 0.3rem 0.6rem; }
            }

            /* Today Highlight */
            .mood-item.today-active {
                background: white;
                border: 2px solid #3b82f6;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            }
            .mood-item.today-active .mood-date {
                color: #3b82f6;
            }

            /* Angry Cat Overlay */
            #angry-cat-overlay {
                position: fixed;
                inset: 0;
                z-index: 10005;
                display: none;
                align-items: center;
                justify-content: center;
                background: rgba(0,0,0,0.2);
                backdrop-filter: blur(2px);
                pointer-events: none;
            }
            .angry-cat-container {
                width: 300px;
                height: 300px;
                position: relative;
                animation: bounce-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            /* Sidebar Scrollable List */
            #missing-reports-list {
                max-height: 250px;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #fbd38d transparent;
            }
            #missing-reports-list::-webkit-scrollbar {
                width: 4px;
            }
            #missing-reports-list::-webkit-scrollbar-thumb {
                background-color: #fbd38d;
                border-radius: 10px;
            }
            .missing-report-date-btn {
                width: 100%;
                text-align: left;
                padding: 0.75rem 1rem;
                background: #fffaf0;
                border: 1px solid #feebc8;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 700;
                color: #9c4221;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .missing-report-date-btn:hover {
                background: #feebc8;
                transform: translateX(4px);
            }
            @keyframes bounce-in {
                0% { transform: scale(0); }
                70% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        </style>

        <div class="calendar-section-wrapper">
            <div id="mood-board-container" class="calendar-mood-board">
                <div class="calendar-header-grid">
                    <div class="day-header">Sen</div>
                    <div class="day-header">Sel</div>
                    <div class="day-header">Rab</div>
                    <div class="day-header">Kam</div>
                    <div class="day-header">Jum</div>
                    <div class="day-header">Sab</div>
                    <div class="day-header">Min</div>
                </div>
                <div id="table-rekap-body" class="mood-board-grid"></div>
            </div>

            <div class="calendar-sidebar">
                <div class="sidebar-card">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Mood Summary</h4>
                    <div class="space-y-2">
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-happy)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <rect x="20" y="32" width="26" height="18" rx="6" fill="#0F172A"/>
                                    <rect x="54" y="32" width="26" height="18" rx="6" fill="#0F172A"/>
                                    <path d="M46 38H54" stroke="#0F172A" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M38 64C38 70.6 43.4 76 50 76C56.6 76 62 70.6 62 64" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">Holi-yay!</p>
                                <p id="stat-happy-count" class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-sleeping)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <path d="M22 45C25 41 31 41 34 45" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>
                                    <path d="M66 45C69 41 75 41 78 45" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>
                                    <path d="M42 62H58" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round"/>
                                    <path d="M78 16C75 16 71 18 70 21C70 24 72 27 75 27C78 27 80 25 80 22C80 18.5 78 16 78 16Z" fill="#FDE047" opacity="0.8"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">On Leave</p>
                                <p id="stat-leave-count" class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-energetic)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <rect x="18" y="34" width="28" height="20" rx="6" stroke="#FFFFFF" stroke-width="4" fill="none"/>
                                    <rect x="54" y="34" width="28" height="20" rx="6" stroke="#FFFFFF" stroke-width="4" fill="none"/>
                                    <path d="M46 42H54" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round"/>
                                    <circle cx="32" cy="44" r="4.5" fill="#FFFFFF"/>
                                    <circle cx="68" cy="44" r="4.5" fill="#FFFFFF"/>
                                    <path d="M38 66C42 70 58 70 62 66" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">WFO</p>
                                <p id="stat-wfo-count" class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-unknown)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <!-- Headset arc -->
                                    <path d="M14 44C14 24 28 16 50 16C72 16 86 24 86 44" stroke="#334155" stroke-width="5" stroke-linecap="round" fill="none"/>
                                    <!-- Earcups -->
                                    <rect x="10" y="38" width="8" height="20" rx="4" fill="#334155"/>
                                    <rect x="82" y="38" width="8" height="20" rx="4" fill="#334155"/>
                                    <!-- Eyes -->
                                    <circle cx="34" cy="46" r="5" fill="#FFFFFF"/>
                                    <circle cx="66" cy="46" r="5" fill="#FFFFFF"/>
                                    <!-- Smile -->
                                    <path d="M40 64C40 68 60 68 60 64" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">WFA</p>
                                <p id="stat-wfa-count" class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <defs><linearGradient id="lg-overtime-side" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#FB923C"/><stop offset="100%" stop-color="#EA580C"/></linearGradient></defs>
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-overtime-side)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <path d="M22 42L36 44" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round"/>
                                    <path d="M78 42L64 44" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round"/>
                                    <circle cx="29" cy="49" r="4" fill="#FFFFFF"/>
                                    <circle cx="71" cy="49" r="4" fill="#FFFFFF"/>
                                    <path d="M35 65C40 61 60 61 65 65" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                                    <path d="M84 14L78 22H85L79 30" stroke="#FEF08A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">Overtime</p>
                                <p id="stat-overtime-count" class="stat-value">0</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon" style="background:transparent;padding:0">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                                    <rect x="5" y="5" width="90" height="90" rx="30" fill="url(#lg-bored)"/>
                                    <path d="M5 35C5 20 20 5 35 5H65C80 5 95 20 95 35C65 30 35 30 5 35Z" fill="white" fill-opacity="0.12"/>
                                    <path d="M22 46H36" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"/>
                                    <path d="M64 46H78" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"/>
                                    <circle cx="50" cy="66" r="7" stroke="#FFFFFF" stroke-width="4" fill="none"/>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <p class="stat-label">Missing</p>
                                <p id="stat-alpha-count" class="stat-value">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Laporan Belum Diisi -->
                <div id="missing-daily-reports-shortcut" class="sidebar-card bg-orange-50 border-none hidden">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-black text-orange-800 uppercase tracking-widest">Reports Needed</h4>
                        <span id="missing-reports-count" class="bg-orange-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">0</span>
                    </div>
                    <div id="missing-reports-list" class="space-y-2 pr-1">
                        <!-- List populated by JS -->
                    </div>
                    <p class="text-[10px] text-orange-600 font-bold mt-4 italic text-center">Tap to fill report</p>
                </div>
            </div>
        </div>

        <!-- Angry Cat Overlay -->
        <div id="angry-cat-overlay">
            <div class="angry-cat-container">
                <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExNHYzeHExbmpxNmR4Znd5N3R4eG54eG54eG54eG54eG54eG54eG54JmVwPXYxX2ludGVybmFsX2dpZl9ieV9pZCZjdD1z/26n6WywWKAODN3uHC/giphy.gif" alt="Angry Cat" class="w-full h-full object-contain">
            </div>
        </div>
    </div>
</div>

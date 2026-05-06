<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Aplikasi Presensi Wajah</title>
    <script src="assets/js/tailwind.js"></script>

    <script src="assets/js/face-api.min.js" defer></script>
    <script>
        // Expose model URL for optimizers
        window.FACEAPI_MODEL_URL = 'assets/face-models';
        window.USER_ROLE = '<?php echo $_SESSION['user']['role'] ?? 'guest'; ?>';
    </script>
    <script src="assets/js/performance-optimizer.js" defer></script>
    
    <!-- Cache optimization enabled -->
    
    <script src="assets/js/chart.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="assets/css/inter.css">
    <link rel='stylesheet' href='assets/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='assets/css/uicons-solid-straight.css'>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensi App">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap');
        
        :root {
            /* Primary Brand Colors */
            --primary: #3b82f6; /* Blue 500 */
            --primary-dark: #2563eb; /* Blue 600 */
            --secondary: #1e3a8a; /* Dark Blue */
            
            /* Accent Colors */
            --accent-success: #10b981; /* Emerald 500 */
            --accent-warning: #f59e0b; /* Amber 500 */
            --accent-danger: #ef4444; /* Red 500 */
            
            /* Usage Backgrounds */
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-glass: rgba(255, 255, 255, 0.8);
            
            /* Text Colors */
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            
            /* Robot Cat Colors */
            --glow-cyan: #00f2ff;
            --glow-red: #ff3131;
            --body-silver: #e2e8f0;
            --body-shadow: #94a3b8;
        }

        body { 
            font-family: 'Outfit', 'Inter', sans-serif; 
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Modern Utilities */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        /* Loader */
        .loader {
            border-top-color: var(--primary);
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Video Container */
        #video-container { 
            position: relative; 
            width: 100%; 
            max-width: 720px; 
            margin: auto; 
            border-radius: 1.5rem; 
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        #video, #canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        /* Header account button - Simplified to let Tailwind handle styling */
        #btn-profile .avatar { width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; flex-shrink: 0; }

        .mirror-video { transform: scaleX(-1); }
        #video { object-fit: cover; object-position: center center; }

        /* Tables */
        table.bordered { 
            border-collapse: separate; 
            border-spacing: 0; 
            width: 100%; 
        }
        table.bordered th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        table.bordered td { 
            padding: 1rem; 
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-main);
        }
        table.bordered tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge { padding: 0.35em 0.8em; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block; letter-spacing: 0.025em; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-orange { background: #ffedd5; color: #9a3412; }
        
        /* Buttons */
        .btn-attendance { 
            cursor: pointer; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border: none; 
            width: 100%; 
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .btn-attendance:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .btn-attendance.blue { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }
        .btn-attendance.red { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.5);
        }

        /* Pill Buttons */
        .btn-pill { 
            border-radius: 9999px !important; 
            padding: 0.35em 0.8em !important; 
            font-weight: 600; 
            font-size: 0.75rem !important;
            letter-spacing: 0.025em;
            display: inline-block;
        }

        /* Suggestion Customization */
        .suggestion-item { padding: 0.75rem 1rem; cursor: pointer; transition: all 0.2s; }
        .suggestion-item:hover, .suggestion-item.active { background-color: #f1f5f9; color: var(--primary); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    </style>

<!-- Global Helper Script for Exports (Generated to ensure availability) -->
<script>
// Utility for quick selection
function qs(sel) { return document.querySelector(sel); }
function qsa(sel) { return document.querySelectorAll(sel); }

// Global Export Wrappers
window.openExportDailyModal = function() {
    const d = document.getElementById('export-presensi-modal');
    if(d) {
        d.classList.remove('hidden');
        d.style.display = 'flex';
        // Sync visibility of monthly options
        const range = document.getElementById('export-p-range');
        const opts = document.getElementById('export-p-monthly-opts');
        if (range && opts) {
            if (range.value === 'monthly') {
                opts.style.display = 'block';
                opts.classList.remove('hidden');
            } else {
                opts.style.display = 'none';
                opts.classList.add('hidden');
            }
        }
    } else alert('Modal not found!');
};

window.closeExportDailyModal = function() {
    const d = document.getElementById('export-presensi-modal');
    if(d) {
        d.classList.add('hidden');
        d.style.display = 'none';
    }
};

window.triggerExportMonthly = function() {
    const startup = qs('#am-startup')?.value || '';
    const month = qs('#am-month')?.value || '';
    const year = qs('#am-year')?.value || '';
    const term = qs('#am-search')?.value || '';
    
    // Default to 'per_employee' format
    const params = new URLSearchParams({
        startup: startup,
        month: month,
        year: year,
        term: term,
        format: 'per_employee'
    });
    
    window.location.href = '?ajax=export_monthly&' + params.toString();
};

window.triggerExportKPI = function() {
    // We try to find the filters. If not found, use defaults.
    const fType = qs('#kpi-filter-type');
    const fMonth = qs('#kpi-filter-month');
    const fYear = qs('#kpi-filter-year');

    const type = fType ? fType.value : 'period';
    const month = fMonth ? fMonth.value : '';
    const year = fYear ? fYear.value : '';
    
    const params = new URLSearchParams();
    params.append('filter_type', type);
    if (type === 'monthly' && month && year) {
        params.append('month', month);
        params.append('year', year);
    }
    window.location.href = '?ajax=export_kpi&' + params.toString();
};
</script>
</head>
<body class="bg-gray-50 text-gray-800">

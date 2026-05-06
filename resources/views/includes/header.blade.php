<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Aplikasi Presensi Wajah</title>
    <!-- Tailwind CSS -->
    <script src="assets/js/tailwind.js"></script>

    <!-- Face API -->
    <script src="assets/js/face-api.min.js" defer></script>
    <script>
        // Expose model URL for optimizers
        window.FACEAPI_MODEL_URL = 'assets/js/face-api-models';
    </script>
    <script src="assets/js/performance-optimizer.js" defer></script>
    
    <!-- Chart JS -->
    <script src="assets/js/chart.min.js" defer></script>

    <!-- UI Icons -->
    <link rel="stylesheet" href="assets/css/inter.css">
    <link rel='stylesheet' href='assets/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='assets/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='assets/css/uicons-brands.css'>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensi App">

    <!-- Fonts -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    </style>

    <!-- Global Styles -->
    <style>
        body { font-family: 'Inter', sans-serif; }
        .loader {
            border-top-color: #3498db;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #video-container { position: relative; width: 100%; max-width: 720px; margin: auto; }
        #video, #canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        /* Header account button - keep avatar inside and tidy on mobile */
        #btn-profile { max-width: 160px; }
        #btn-profile .avatar { width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; flex-shrink: 0; }
        @media (max-width: 400px) {
            #btn-profile { max-width: 140px; padding-left: 0.5rem; padding-right: 0.5rem; gap: 0.5rem; }
            #btn-profile span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80px; display: inline-block; }
        }
        
        /* Mirror hanya video agar UI & teks tidak terbalik */
        .mirror-video { transform: scaleX(-1); }
        
        /* Ensure video crops from center on tall mobile cameras */
        #video { object-fit: cover; object-position: center center; }
        
        /* Bordered tables */
        table.bordered { border-collapse: collapse; width: 100%; table-layout: auto; }
        table.bordered th, table.bordered td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: center; vertical-align: middle; }
        
        /* Status badges */
        .badge { padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fde68a; color: #854d0e; }
        .badge-emerald { background: #a7f3d0; color: #064e3b; }
        .badge-orange { background: #fed7aa; color: #9a3412; }
        .badge-purple { background: #e9d5ff; color: #6b21a8; }
        .btn-pill { 
            border-radius: 9999px !important; 
            padding: 0.5rem 1.5rem !important; 
            font-weight: 600; 
            font-size: 0.875rem;
            min-width: 80px;
            text-align: center;
        }
        .z-60 { z-index: 60; }
        .z-70 { z-index: 70; }
        .max-w-7xl { max-width: 80rem; }
        
        /* Address search suggestions */
        .suggestion-item {
            transition: background-color 0.2s ease;
        }
        .suggestion-item:hover {
            background-color: #f3f4f6;
        }
        .suggestion-item.active {
            background-color: #dbeafe;
        }
        
        /* Landing page custom styles */
        .landing-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

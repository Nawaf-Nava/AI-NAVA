<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8"> 
    <title>NAVA | LIVE_CORE</title>
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="Style.css">
    <style>
        body { background: var(--void-bg); overflow: hidden; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .live-wrapper { display: flex; flex-direction: column; align-items: center; }
        .back-btn { position: absolute; top: 30px; right: 30px; background: var(--cyan-transparent); border: 1px solid var(--cyber-cyan); color: var(--cyber-cyan); padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: 0.3s; z-index: 100; }
        .back-btn:hover { background: var(--cyber-cyan); color: #000; }
        #live-visualizer { display: flex !important; position: relative; background: none; }
    </style>
</head>
<body class="live-active">
    <button class="back-btn" onclick="window.location.href='index.php'">
        <i class="fa-solid fa-arrow-right"></i> إغلاق والمغادرة
    </button>

    <div class="live-wrapper">
        <div id="live-visualizer">
            <div class="central-core">
                <div class="frequency-wave"></div>
                <div class="frequency-wave"></div>
                <div class="frequency-wave"></div>
                <div class="core-pulse-ring"></div>
                <div class="core-pulse-ring"></div>
                <div class="core-pulse-ring"></div>
                <div class="bot-avatar-frame thinking-avatar" style="width: 180px !important; height: 180px !important; z-index: 10;">
                     <img src="images/ooo.png" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
        <p id="live-status-text" style="margin-top: 40px; font-family: 'Fira Code', monospace; color: var(--cyber-cyan); letter-spacing: 3px; font-size: 1.2rem;">INITIALIZING_LIVE_LINK...</p>
    </div>

    <!-- Script.js سيقوم بالتعامل مع تهيئة وضع LIVE تلقائياً -->
    <script src="Script.js" defer></script>
</body>
</html>
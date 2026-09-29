<?php
/* ========================================================================
   PROJECT: NAVA AI v9.5 - MULTIMODAL_CORE (FINAL_SYNC)
   ENGINEER: NAWAF_ROOT (Cybersecurity Specialist)
   STATUS: UI_ENHANCED (Guest Alerts & Safe Routing)
   ======================================================================== */

// إعدادات أمان الجلسة قبل البدء
$lifetime = 2592000; // 30 يوماً
ini_set('session.gc_maxlifetime', $lifetime);

// تحديد ما إذا كان الاتصال آمناً (يدعم HTTPS وخلف بروكسي)
$isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// التحقق من حالة تسجيل الدخول
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : 'GUEST_USER';
$user_id = $is_logged_in ? $_SESSION['user_id'] : '0000000000';
$bio = $is_logged_in ? ($_SESSION['bio'] ?? '') : '';

// منطق جلب الصورة الشخصية بأمان
$profile_img = 'images/default-avatar.png'; // المسار الافتراضي الآمن
if ($is_logged_in) {
    $session_pic = $_SESSION['profile_pic'] ?? '';
    if (!empty($session_pic) && $session_pic !== 'default-avatar.png') {
        $profile_img = "images/uploads/" . htmlspecialchars($session_pic);
    } else {
        $profile_img = 'images/default-avatar.png';
    }
}

// توليد CSRF Token لكل طلب، أو التحقق مما إذا كان موجودًا في الجلسة
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#020609">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="NAVA">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- SEO Optimization -->
    <title>NAVA | نافا</title>
    <meta name="description" content="NAVA - نافا: المساعد الذكي المتقدم للخدمات التقنية والسيبرانية.">
    <meta name="keywords" content="nava, نافا, مساعد ذكي, تقنية, سكيورتي">

    <!-- Favicon & Identity Icons -->
    <link rel="icon" type="image/png" href="images/ooo.png">
    <link rel="shortcut icon" href="images/ooo.png">
    <link rel="apple-touch-icon" href="images/ooo.png">
    <link rel="manifest" href="manifest.json">

    <!-- التحميل المسبق للأيقونات لضمان الظهور الفوري -->
    <link rel="preload" as="image" href="images/ooo.png">
    <link rel="preload" as="image" href="images/icons/nava-normal.png">
    <link rel="preload" as="image" href="images/icons/nava-deep.png">

    <!-- Windows Desktop & Start Menu Support -->
    <meta name="msapplication-TileImage" content="images/ooo.png">
    <meta name="msapplication-TileColor" content="#020609">
    
    <!-- Core Icon and Font Libraries -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/bunny-fonts.css">

    <script src="assets/vendor/marked/marked.min.js"></script>
    <link rel="stylesheet" href="assets/vendor/highlightjs/vs2015.min.css">
    <script src="assets/vendor/highlightjs/highlight.min.js"></script>
    <link rel="stylesheet" href="Style.css">
    
    <script>
        const IS_LOGGED_IN = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const CURRENT_USER_NAME = "<?php echo $is_logged_in ? htmlspecialchars($username) : ''; ?>";
        let isDeepMode = false; // متغير حالة الوضع العميق
    </script>
    <script src="Script.js" defer></script>
</head>

<body class="<?php echo !$is_logged_in ? 'guest-mode' : 'user-mode'; ?>">
    <!-- شاشة الإقلاع الاحترافية -->
    <div id="app-splash-screen"> 
        <img src="images/ooo.png" alt="NAVA">
        <div class="loading-bar-container">
            <div class="loading-bar-fill" id="splash-bar"></div>
        </div>
        <p style="margin-top: 15px; font-family: 'Fira Code', monospace; font-size: 0.7rem; color: var(--cyber-cyan); letter-spacing: 2px;">INITIALIZING_CORE...</p>
    </div>

    <div class="space-engine"></div>

    <!-- نظام التنبيهات المدمج -->
    <div id="notification-zone"></div>

    <!-- نافذة انقطاع الاتصال (Floating System Alert) -->
    <div id="offline-screen" class="cyber-offline-overlay">
        <div class="offline-alert-card">
            <div class="alert-mini-icon"><i class="fa-solid fa-wifi-slash"></i></div>
            <div class="alert-info-text">
                <h3>خطأ في الاتصال</h3>
                <p>أنت الآن خارج التغطية، يرجى التحقق من الشبكة للمزامنة.</p>
            </div>
            <button onclick="checkConnectionAndReload()" class="alert-refresh-btn" title="إعادة المزامنة">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>
    </div>

    <main class="app-shell">
        <header class="top-nav">
            <div class="nav-left" style="display: flex; align-items: center; flex:1;">
                <?php if ($is_logged_in): ?>
                    <button id="history-toggle-btn" class="nav-item-btn" title="سجلات الجلسات">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </button>
                <?php endif; ?>
                <button id="ui-zoom-btn" class="nav-zoom-btn" title="تغيير حجم الواجهة">
                    <i class="fa-solid fa-maximize"></i>
                </button>
                <button id="power-save-btn" class="nav-power-btn" title="وضعية توفير الطاقة">
                    <i class="fa-solid fa-microchip"></i>
                </button>
            </div>

            <div class="cyber-brand">NAVA</div>

            <div class="nav-right" style="display: flex; align-items: center; gap: 20px; justify-content: flex-end; flex:1;">
                <?php if ($is_logged_in): ?>
                    <button id="save-chat-btn" class="nav-item-btn" title="تشفير وحفظ الجلسة">
                        <i class="fa-solid fa-shield-halved"></i>
                    </button>
                <?php else: ?>
                    <button onclick="Utils.showNotification('يجب تسجيل الدخول لحفظ الجلسات', 'error')" class="nav-item-btn" title="تشفير وحفظ الجلسة" style="color: var(--neon-red) !important; border-color: rgba(255,0,0,0.2) !important;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </button>
                <?php endif; ?>

                <button id="new-chat-btn" class="nav-item-btn" title="مزامنة جلسة جديدة">
                    <i class="fa-solid fa-plus"></i>
                </button>

                <?php if ($is_logged_in): ?>
                    <div id="profile-trigger-btn" class="profile-trigger">
                        <img src="<?php echo $profile_img; ?>?v=<?php echo time(); ?>" alt="User Avatar" onerror="this.onerror=null; this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';">
                        <i class="fa-solid fa-user" style="display:none; color: var(--cyber-cyan); font-size: 1.2rem;"></i>
                    </div>
                <?php else: ?>
                    <a href="login.php" target="_self" class="login-trigger" title="تسجيل الدخول">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <aside id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                    <h3><i class="fa-solid fa-vault"></i> SECURE_ARCHIVE</h3>
                    <i class="fa-solid fa-xmark" id="close-sidebar" style="cursor:pointer; color:#fff; font-size: 1.2rem;"></i>
                </div>
            </div>
            <div id="history-list" class="history-content"></div>
        </aside>

        <!-- واجهة المحادثة المباشرة (Live Mode) -->
        <div id="live-visualizer">
            <div class="central-core">
                <div class="core-pulse-ring"></div>
                <div class="core-pulse-ring"></div>
                <div class="core-pulse-ring"></div>
                <div class="frequency-wave"></div>
                <div class="frequency-wave"></div>
                <div class="frequency-wave"></div>
                <div class="bot-avatar-frame" style="width: 150px !important; height: 150px !important;"><img src="images/icons/nava-normal.png" alt="NAVA Core" id="live-avatar" style="width:100%; height:100%; object-fit:cover; border-radius:50%;"></div>
            </div>
            <p id="live-status-text" style="margin-top: 30px; font-family: var(--font-code); font-size: 1rem; color: var(--cyber-cyan); letter-spacing: 2px;">SYSTEM_LISTENING...</p>
        </div>

        <section id="welcome-hero" class="welcome-hero">
            <div class="hero-header">
                <!-- أيقونة النواة المركزية للترحيب -->
                <div class="bot-avatar-frame" id="welcome-avatar" style="width: 70px !important; height: 70px !important; margin: 0 auto 15px; cursor: pointer; border: none !important; box-shadow: none !important;"></div>

                <h1 class="hero-logo"><span>NAVA</span></h1>
                <p class="hero-subtitle" style="color: var(--cyber-cyan); opacity:0.8; letter-spacing:2px;">أهلاً بك، أنا NAVA. تم تطويري بواسطة NAWAF للاختراق، لذلك احرص على أن لا تؤذي أحداً.</p>
            </div>
            
            <div class="suggestion-grid" id="main-suggestion-grid">
                <!-- يتم بناؤها برمجياً عبر Script.js لتوحيد الواجهة -->
            </div>
        </section>

        <div id="chat-container" class="conversation-zone" style="display: none;"></div>

        <footer class="input-anchor">
            <div class="pill-container">
                <div id="file-preview-container" class="preview-wrapper">
                    <div class="preview-box">
                        <img id="visual-preview" src="" alt="preview">
                        <i id="file-icon" class="fa-solid fa-file-shield" style="color:var(--cyber-cyan); display:none; font-size: 1.5rem;"></i>
                        <div id="file-name-display" style="color:var(--cyber-cyan); font-size:0.85rem; font-family:monospace; word-break: break-all;"></div>
                        <i class="fa-solid fa-circle-xmark remove-preview" id="remove-file"></i>
                    </div>
                </div>

                <textarea id="user-input" placeholder="اسال NAVA؟" rows="1"></textarea>

                <div id="voice-input-bar" class="voice-input-bar">
                    <div class="waveform-line"></div>
                    <div class="waveform-line delay-1"></div>
                    <div class="waveform-line delay-2"></div>
                    <div class="waveform-line delay-3"></div>
                </div>

                <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <div class="left-actions" style="position: relative; display: flex; align-items: center; gap: 8px;">
                        <!-- قائمة خيارات الرفع (Gemini Style) -->
                        <div id="attach-menu" class="attach-popover">
                            <div class="attach-item" id="opt-camera"><i class="fa-solid fa-camera"></i> <span>فتح الكاميرا</span></div>
                            <div class="attach-item" id="opt-photos"><i class="fa-solid fa-image"></i> <span>الصور</span></div>
                            <div class="attach-item" id="opt-files"><i class="fa-solid fa-file-lines"></i> <span>مستندات وأكواد</span></div>
                        </div>

                        <!-- مدخلات الملفات المخفية -->
                        <input type="file" id="input-camera" accept="image/*" capture="user" style="display:none;">
                        <input type="file" id="input-photos" accept="image/*" style="display:none;">
                        <input type="file" id="input-files" accept=".php,.py,.js,.json,.html,.css,.cpp,.c,.java,.txt,.md,.doc,.docx,.pdf" style="display:none;">

                    <?php if ($is_logged_in): ?>
                        <button id="attach-toggle" class="input-action-btn" title="إضافة">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    <?php else: ?>
                        <button class="input-action-btn" onclick="Utils.showNotification('[ ACCESS_DENIED ]: يرجى تسجيل الدخول لرفع المرفقات', 'error')">
                            <i class="fa-solid fa-plus" style="color: var(--text-muted);"></i>
                        </button>
                    <?php endif; ?>

                    <button id="deep-mode-toggle" class="input-action-btn" title="تفعيل الوضع العميق">
                        <i class="fa-solid fa-user-secret"></i>
                    </button>
                    </div>
                    <div class="right-actions" style="display: flex; align-items: center; gap: 18px;">
                        <button id="live-mode-btn" class="online-status-indicator">
                            <i class="fa-solid fa-braille"></i>
                        </button>
                        <button id="main-action-btn" class="main-action-btn" title="إرسال">
                            <i class="fa-solid fa-microphone" id="action-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </footer>

        <div id="overlay" class="overlay"></div>
    </main>
    
    <script>
        // This script block will run after Script.js is loaded (due to defer)
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            const type = urlParams.get('type');

            if (msg && type) {
                if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
                    Utils.showNotification(msg, type);
                    history.replaceState({}, document.title, window.location.pathname); // Clean URL
                }
            }
        });
    </script>

    <script>
        // محرك التحكم في حجم الواجهة (UI Zoom Engine)
        document.addEventListener('DOMContentLoaded', () => {
            // منطق الزووم
            const zoomBtn = document.getElementById('ui-zoom-btn');
            const sizes = ['0.9rem', '1.05rem', '1.25rem', '1.4rem']; // مستويات التكبير
            let currentIdx = 1; // المستوى الافتراضي (1.05rem)

            if (zoomBtn) {
                zoomBtn.addEventListener('click', () => {
                    currentIdx = (currentIdx + 1) % sizes.length;
                    document.documentElement.style.setProperty('--font-size-chat', sizes[currentIdx]);
                });
            }

            // منطق توفير الطاقة (Power Saving Mode)
            const powerBtn = document.getElementById('power-save-btn');
            const isPowerSave = localStorage.getItem('nava_power_save') === 'true';
            
            if (isPowerSave) document.body.classList.add('power-saving-mode');

            if (powerBtn) {
                powerBtn.addEventListener('click', () => {
                    const active = document.body.classList.toggle('power-saving-mode');
                    localStorage.setItem('nava_power_save', active);
                    if (typeof Utils !== 'undefined') Utils.showNotification(active ? 'تم تفعيل وضع توفير الطاقة' : 'تم تعطيل وضع توفير الطاقة', active ? 'success' : 'info');
                });
            }
        });
    </script>

    <!-- نافذة تأكيد الوضع العميق (Deep Mode Confirmation Modal) -->
    <div id="deep-mode-modal" class="modal">
        <div class="modal-header" style="padding:15px; border-bottom:1px solid #111; display:flex; justify-content:space-between; align-items:center;">
            <span style="color:var(--neon-red); font-size:0.75rem; font-family:monospace;">[ AUTH_REQUIRED: DEEP_CORE_PROTOCOL ]</span>
            <i class="fa-solid fa-xmark" id="close-deep-mode-modal" style="cursor:pointer; color:#fff;"></i>
        </div>
        <div class="modal-body" style="text-align:center; padding: 30px 20px;">
            <h4 style="color:#fff; margin:0 0 15px; font-family: monospace; letter-spacing: 2px;">CONFIRM_IDENTITY_AUTHORIZATION</h4>
            <p style="color: var(--neon-red); font-size:0.85rem; margin-bottom:20px; font-family: monospace; line-height: 1.6; text-align: left;">
                > CAUTION: Unrestricted access to offensive tools.
                <br>> TRACING: Disabled.
                <br>> LIABILITY: User-Only.
                <br><br>> هل تود المتابعة؟
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <button id="confirm-deep-mode" style="background: var(--neon-red); color: #fff; border: none; padding: 10px 30px; border-radius: 2px; font-weight: bold; cursor: pointer; font-family: monospace; text-transform: uppercase;">
                    PROCEED
                </button>
                <button id="cancel-deep-mode" style="background: transparent; border: 1px solid #444; color: #888; padding: 10px 30px; border-radius: 2px; cursor: pointer; font-family: monospace; text-transform: uppercase;">
                    ABORT
                </button>
            </div>
        </div>
    </div>
</body>
</html>

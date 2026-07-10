<?php
/**
 * PROJECT: NAVA AI / CyberFlux v9.5 - DATABASE_CORE
 * MODULE: User Authentication (Login & Sync)
 * DATABASE: PostgreSQL
 * ENGINEER: NAWAF_ROOT
 */

// تأمين إعدادات الجلسة
$lifetime = 2592000;
ini_set('session.gc_maxlifetime', $lifetime);
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

require_once 'config/database.php';

// --- [فحص الاتصال ومحرك الإعداد التلقائي] ---

if ($pdo === null) {
    // إذا فشل الاتصال في config/database.php، نعرض رسالة خطأ مفصلة هنا.
    // هذا هو المكان الذي يراه المستخدم، لذا يجب أن تكون الرسالة واضحة.

    // [جديد] عرض الخطأ التقني الفعلي للمساعدة في التشخيص الدقيق
    $technical_error_html = '';
    if (!empty($db_connection_error)) {
        $technical_error_html = "<div style='background: #3e2121; border: 1px solid #ff6c6b; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: left; direction: ltr; font-family: monospace;'><strong>Technical Error Details:</strong><br>" . htmlspecialchars($db_connection_error) . "</div>";
    }

    die("
    <body style='background-color: #0d1117; color: #c9d1d9; font-family: sans-serif; padding: 20px; direction: rtl;'>
    <div style='max-width: 800px; margin: 40px auto; border: 1px solid #ff6c6b; border-radius: 8px; padding: 25px; background-color: #282c34;'>
        <h1 style='color: #ff6c6b; text-align: center;'>🔴 فشل الاتصال بقاعدة البيانات</h1>
        {$technical_error_html}
        <p>لم يتمكن النظام من الاتصال بقاعدة البيانات. هذا يعني أن الجداول المطلوبة لم يتم إنشاؤها، والموقع لن يعمل.</p>
        <p>بما أنك تستخدم استضافة InfinityFree، فالسبب غالباً واحد من التالي:</p>
        
        <h3 style='color: #79c0ff;'>1. خطأ في معلومات الاتصال</h3>
        <p>تأكد 100% أن هذه المعلومات صحيحة ومطابقة لما هو موجود في لوحة تحكم InfinityFree:</p>
        <pre style='background: #161b22; padding: 15px; border-radius: 5px; border: 1px solid #30363d; color: #c9d1d9; font-family: monospace; text-align: left; direction: ltr;'>
Host:     sql210.infinityfree.com
Database: if0_42300177_nava_db
Username: if0_42300177
Password: (كلمة المرور التي أعطيتها)
        </pre>
        <p><strong>ملاحظة:</strong> اسم المستخدم واسم قاعدة البيانات ليسا نفس اسم حسابك في InfinityFree.</p>
    
        <h3 style='color: #79c0ff;'>2. صلاحيات الوصول عن بعد (السبب الأكثر شيوعاً)</h3>
        <p>استضافة InfinityFree تتطلب منك السماح لخادم الويب بالوصول إلى خادم قاعدة البيانات. هذا يتم عبر قسم <strong>\"Remote MySQL\"</strong>.</p>
        <ul>
            <li>اذهب إلى لوحة التحكم (cPanel) في InfinityFree.</li>
            <li>ابحث عن أيقونة باسم \"Remote MySQL\".</li>
            <li>في خانة \"Host (% wildcard is allowed)\"، اكتب عنوان IP الخاص بموقعك.</li>
            <li><strong>كيف تجد IP موقعك؟</strong> في لوحة التحكم، على اليمين، ستجد قسماً باسم \"Account Details\" أو \"General Information\". ابحث عن \"Website IP\" أو \"Server IP\" وانسخه.</li>
            <li>الصق الـ IP في خانة Remote MySQL واضغط \"Add Host\".</li>
        </ul>
        <p>إذا لم تكن متأكداً من الـ IP، يمكنك استخدام الرمز <code>%</code> للسماح بالاتصال من أي مكان، لكن هذا أقل أماناً.</p>
        
        <hr style='border-color: #30363d; margin: 20px 0;'>
        <p style='text-align: center; color: #d29922;'><strong>بعد إصلاح المشكلة في لوحة التحكم، قم بتحديث هذه الصفحة.</strong></p>
    </div>
    </body>");
}

// --- [نهاية الفحص ومحرك الإعداد] ---

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // تم التغيير لاستخدام البريد الإلكتروني لتسجيل الدخول
    $username_input = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // [1] البحث عن المستخدم عبر البريد الإلكتروني في Schema الجديد
        $stmt = $pdo->prepare("SELECT id, name, password, bio, profile_pic, access_level FROM users WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$username_input]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target_user) {
            $error = "خطأ: اسم المستخدم أو كلمة المرور غير صحيحة.";
        } 
        // [2] التحقق من كلمة المرور (العمود الجديد اسمه 'password')
        elseif (!password_verify($password, $target_user['password'])) {
            $error = "خطأ: البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        } 
        else {
            // النجاح
            session_regenerate_id(true);
            
            // [3] تعيين بيانات الجلسة من Schema الجديد
            $_SESSION['user_id']     = $target_user['id']; // المعرف الرقمي الجديد
            $_SESSION['user_db_id']  = $target_user['id']; // معرف إضافي للاستخدام في api.php
            $_SESSION['username']    = $target_user['name']; // اسم المستخدم
            $_SESSION['bio']         = $target_user['bio'];
            $_SESSION['profile_pic'] = $target_user['profile_pic'];
            $_SESSION['access_level']= $target_user['access_level'];

            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        // خطأ تقني في قاعدة البيانات
        error_log("LOGIN_DATABASE_ERROR: " . $e->getMessage());
        $error = "خطأ تقني في قاعدة البيانات. راجع السجلات لمعرفة السبب.";
    }
}
?>
<?php if ($error): ?>
    <script>
        window.location.href = 'index.php?msg=<?php echo urlencode($error); ?>&type=error';
    </script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberFlux | AUTH_REQUIRED</title>

    <!-- Favicon & Identity Icons -->
    <link rel="icon" type="image/png" href="images/ooo.png">
    <link rel="apple-touch-icon" href="images/ooo.png">

    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="Style.css">
    <style>
        body { background: var(--void-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; overflow: hidden; }
        .auth-container { 
            background: var(--panel-glass); 
            padding: 40px 30px; 
            border: 1px solid var(--border-color); 
            border-radius: var(--radius-md); 
            width: 90%; 
            max-width: 400px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), var(--neon-glow); 
            backdrop-filter: blur(var(--blur-val));
            animation: heroFadeIn 0.8s ease-out;
            z-index: 10;
        }
        input { 
            width: 100%; padding: 14px; margin-bottom: 20px; 
            background: var(--input-bg); border: 1px solid var(--border-color); 
            color: white; border-radius: var(--radius-sm); box-sizing: border-box; 
            transition: var(--transition-smooth);
            font-family: var(--font-code);
        }
        input:focus { border-color: var(--cyber-cyan); box-shadow: 0 0 15px rgba(0,243,255,0.2); }
        button { 
            width: 100%; padding: 14px; background: var(--cyber-cyan); color: #000; 
            font-weight: bold; border: none; cursor: pointer; text-transform: uppercase; 
            transition: 0.3s; border-radius: var(--radius-sm);
            font-family: var(--font-main);
        }
        button:hover { box-shadow: 0 0 25px var(--cyber-cyan); background: #fff; }
        .error-box { color: var(--neon-red); background: rgba(255,49,49,0.1); padding: 12px; border: 1px solid var(--neon-red); border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .auth-footer { margin-top: 20px; text-align: center; font-size: 0.8rem; color: #888; }
        .cyber-link { color: var(--cyber-cyan); text-decoration: none; }
    </style>
</head>
<body>
    <div class="space-engine"></div>
    <div class="auth-container">
        <h2 style="color: var(--cyber-cyan); text-align: center; margin-bottom: 30px; font-family: var(--font-code); letter-spacing: 2px;">AUTH_REQUIRED</h2>
        
        <?php if($error): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <!-- تم التغيير إلى البريد الإلكتروني -->
            <input type="text" name="username" placeholder="اسم المستخدم" required autocomplete="off">
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">INITIALIZE_SESSION</button>
        </form>

        <div class="auth-footer">
            ليس لديك تصريح؟ <a href="register.php" class="cyber-link">أنشئ هويتك الجديدة</a>
        </div>
    </div>
</body>
</html>

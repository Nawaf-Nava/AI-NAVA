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

// --- [محرك الإعداد التلقائي لقاعدة البيانات] ---
// هذا الجزء يقوم بإنشاء الجداول المطلوبة تلقائياً عند زيارة الصفحة لأول مرة.
try {
    if ($pdo) { // التأكد من أن الاتصال بقاعدة البيانات ناجح
        $sql_statements = [
            "users" => "CREATE TABLE IF NOT EXISTS `users` (
                          `id` INT AUTO_INCREMENT PRIMARY KEY,
                          `name` VARCHAR(255) NOT NULL UNIQUE,
                          `password` VARCHAR(255) NOT NULL,
                          `bio` TEXT,
                          `profile_pic` VARCHAR(255) DEFAULT 'default-avatar.png',
                          `access_level` VARCHAR(50) DEFAULT 'USER',
                          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "chats" => "CREATE TABLE IF NOT EXISTS `chats` (
                          `id` INT AUTO_INCREMENT PRIMARY KEY,
                          `user_id` INT NOT NULL,
                          `title` VARCHAR(255) NOT NULL,
                          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "messages" => "CREATE TABLE IF NOT EXISTS `messages` (
                             `id` INT AUTO_INCREMENT PRIMARY KEY,
                             `chat_id` INT NOT NULL,
                             `sender_type` VARCHAR(50) NOT NULL,
                             `content` TEXT NOT NULL,
                             `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                             FOREIGN KEY (`chat_id`) REFERENCES `chats`(`id`) ON DELETE CASCADE
                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];

        foreach ($sql_statements as $sql) {
            $pdo->exec($sql);
        }
    }
} catch (PDOException $e) {
    // في حال فشل إنشاء الجداول، يتم إيقاف البرنامج وعرض الخطأ
    die("خطأ حرج في إعداد قاعدة البيانات: " . $e->getMessage());
}
// --- [نهاية محرك الإعداد التلقائي] ---

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

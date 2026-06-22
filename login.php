<?php
/**
 * PROJECT: NAVA AI / CyberFlux v9.5 - DATABASE_CORE
 * MODULE: User Authentication (Login & Sync)
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // [1] البحث عن المستخدم
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target_user) {
            $error = "خطأ: اسم المستخدم أو كلمة المرور غير صحيحة."; // رسالة خطأ عامة لمنع تعداد أسماء المستخدمين
        } 
        // [2] التحقق من كلمة المرور
        elseif (!password_verify($password, $target_user['password_hash'])) {
            $error = "خطأ: اسم المستخدم أو كلمة المرور غير صحيحة."; // رسالة خطأ عامة لمنع تعداد أسماء المستخدمين
        } 
        else {
            // النجاح
            session_regenerate_id(true);
            
            // [3] صيانة المجلدات: التأكد من وجود مسارات التخزين للحسابات المنتقلة لـ PostgreSQL
            // تغيير الصلاحيات من 0777 إلى 0755
            $default_dir_permissions = 0755;
            $upload_dir = __DIR__ . '/images/uploads/';

            if (!is_dir($upload_dir)) mkdir($upload_dir, $default_dir_permissions, true);
            // [4] تعيين بيانات الجلسة مباشرة من قاعدة البيانات
            $user_pic = !empty($target_user['profile_pic']) ? $target_user['profile_pic'] : 'default-avatar.png';

            $_SESSION['user_id']     = $target_user['user_id'];
            $_SESSION['username']    = $target_user['username'];
            $_SESSION['role']        = $target_user['access_level'];
            $_SESSION['bio']         = $target_user['bio'];
            $_SESSION['profile_pic'] = $user_pic;

            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        // خطأ تقني في قاعدة البيانات
        $error = "خطأ تقني في قاعدة البيانات: " . $e->getMessage();
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
            <input type="text" name="username" placeholder="Username (NODE_ID)" required autocomplete="off">
            <input type="password" name="password" placeholder="Password (ACCESS_KEY)" required>
            <button type="submit">INITIALIZE_SESSION</button>
        </form>

        <div class="auth-footer">
            ليس لديك تصريح؟ <a href="register.php" class="cyber-link">أنشئ هويتك الجديدة</a>
        </div>
    </div>
</body>
</html>
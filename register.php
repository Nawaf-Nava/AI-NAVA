<?php
/* ========================================================================
   PROJECT: NAVA AI / CyberFlux v9.5 - REGISTRATION_CORE (STABILIZED v2)
   DATABASE: PostgreSQL
   ENGINEER: NAWAF_ROOT (Cybersecurity Specialist)
   STATUS: FIXED BUG #48 | PATHINFO STRING TYPECASTED | VAULT INTERNAL
   ======================================================================== */

ini_set('session.cookie_lifetime', 2592000);
ini_set('session.gc_maxlifetime', 2592000);
session_start();
error_reporting(E_ALL);

require_once 'config/database.php';

$msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $result = false;

    try {
        // التحقق من تكرار اسم المستخدم
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(name) = LOWER(?)");
        $stmt->execute([$username]);
        $username_exists = $stmt->fetchColumn() > 0;

        if ($username_exists) {
            $msg = "خطأ: اسم المستخدم مسجل مسبقاً.";
        } else {
            // 2. إدخال البيانات في Schema الجديد
            $sql = "INSERT INTO users (name, password) VALUES (:name, :pass)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'  => $username,
                    ':pass'  => password_hash($password, PASSWORD_BCRYPT),
                ]);
                $user_id = $pdo->lastInsertId(); // جلب المعرف الرقمي الجديد
                $result = $user_id > 0;
            } catch (PDOException $e) {
                error_log("REGISTER_DATABASE_ERROR: " . $e->getMessage());
                $msg = "DATABASE_INSERT_ERROR: فشل في إنشاء الحساب. راجع السجلات.";
            }

            if ($result) {
                session_regenerate_id(true);
                // تفعيل الجلسة بالبيانات الجديدة
                $_SESSION['user_id']     = $user_id;
                $_SESSION['user_db_id']  = $user_id;
                $_SESSION['username']    = $username;
                $_SESSION['bio']         = ''; // Bio is empty on registration
                $_SESSION['profile_pic'] = 'default-avatar.png'; // Default avatar
                $_SESSION['access_level']= 'USER'; // Default access level
                $success_msg = "تمت مزامنة العقدة وإنشاء ملف الهوية بنجاح! جاري الانتقال للوحة التحكم...";
                
                echo "<script>window.location.href = 'index.php?msg=" . urlencode($success_msg) . "&type=success';</script>";
                exit();
            } elseif (empty($msg)) {
                $msg = "فشل إنشاء الحساب لسبب غير معروف.";
            } 
        }
    } catch (PDOException $e) {
        error_log("REGISTER_DB_OPERATION_ERROR: " . $e->getMessage());
        $msg = "خطأ في قاعدة البيانات. يرجى المحاولة لاحقاً.";
    }

    // إذا حدث خطأ، أعد التوجيه مع الرسالة
    if ($msg) {
        echo "<script>window.location.href = 'index.php?msg=" . urlencode($msg) . "&type=error';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVA | REGISTER</title>

    <!-- Favicon & Identity Icons -->
    <link rel="icon" type="image/png" href="images/ooo.png">
    <link rel="apple-touch-icon" href="images/ooo.png">
    
    <!-- PWA Config -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#00f3ff">
    <script>if('serviceWorker' in navigator) { navigator.serviceWorker.register('sw.js'); }</script>
    <!-- المكتبات المحلية -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/bunny-fonts.css">
    <link rel="stylesheet" href="Style.css">
    <style>
        body { background: var(--void-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; overflow-y: auto; padding: 40px 0; }
        .register-container { 
            background: var(--panel-glass); 
            padding: 40px 30px; 
            border: 1px solid var(--border-color); 
            border-radius: var(--radius-md); 
            width: 92%;
            max-width: 450px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), var(--neon-glow);
            backdrop-filter: blur(var(--blur-val));
            z-index: 10;
            animation: heroFadeIn 0.8s ease-out;
        }
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; font-size: 0.75rem; color: var(--cyber-cyan); font-family: var(--font-code); margin-bottom: 6px; }
        .input-group i { position: absolute; right: 12px; bottom: 12px; color: var(--text-muted); }
        input, textarea { 
            width: 100%; padding: 12px 35px 12px 12px; 
            background: var(--input-bg); border: 1px solid var(--border-color); 
            color: #fff; border-radius: var(--radius-sm); box-sizing: border-box;
            transition: var(--transition-smooth);
        }
        input:focus, textarea:focus { border-color: var(--cyber-cyan); box-shadow: 0 0 10px rgba(0,243,255,0.2); }
        .btn-submit { 
            width: 100%; padding: 15px; background: var(--cyber-cyan); border: none; 
            color: #000; font-weight: bold; cursor: pointer; text-transform: uppercase; 
            transition: 0.3s; border-radius: var(--radius-sm); font-family: var(--font-main);
        }
        .btn-submit:hover { box-shadow: 0 0 20px var(--cyber-cyan); background: #fff; }
        .error-msg { background: rgba(255, 51, 102, 0.1); border: 1px solid var(--neon-red); color: var(--neon-red); padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-size: 0.85rem; }
        .success-msg { background: rgba(0, 255, 136, 0.1); border: 1px solid var(--neon-green); color: var(--neon-green); padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="space-engine"></div>
    <div class="register-container">
        <h2 style="text-align: center; color: var(--cyber-cyan); font-family: var(--font-code); letter-spacing: 2px; margin-bottom: 25px;">NAVA_REGISTRATION</h2>
        
        <?php if($msg): ?>
            <div class="error-msg"><i class="fa-solid fa-shield-halved"></i> <?php echo $msg; ?></div>
        <?php endif; ?>

        <?php if($success_msg): ?>
            <div class="success-msg"><i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="input-group">
                <label>[ USERNAME ]</label>
                <i class="fa-solid fa-user-gear"></i>
                <input type="text" name="username" placeholder="اسم المستخدم" required autocomplete="off">
            </div>
            <div class="input-group">
                <label>[ PASSWORD ]</label>
                <i class="fa-solid fa-key"></i>
                <input type="password" name="password" placeholder="كلمة المرور التشفيرية" required>
            </div>
            <button type="submit" class="btn-submit">INITIALIZE_CORE_ACCOUNT</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
            لديك عقدة نشطة بالفعل؟ <a href="login.php" style="color: var(--cyber-cyan); text-decoration: none;">تسجيل الدخول من هنا</a>
        </p>
    </div>
</body>
</html>

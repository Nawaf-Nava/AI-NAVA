<?php
/* ========================================================================
   PROJECT: NAVA AI / CyberFlux v9.5 - REGISTRATION_CORE (STABILIZED v2)
   ENGINEER: NAWAF_ROOT (Cybersecurity Specialist)
   STATUS: FIXED BUG #48 | PATHINFO STRING TYPECASTED | VAULT INTERNAL
   ======================================================================== */

ini_set('session.cookie_lifetime', 2592000);
ini_set('session.gc_maxlifetime', 2592000);
session_start();
// يجب إزالة هذا السطر في بيئة الإنتاج
// ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once 'config/database.php';

$msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $bio      = trim($_POST['bio']);
    
    // 1. تحديد مسارات تخزين الملحقات (الصور والمحادثات) فقط
    $upload_dir = __DIR__ . '/images/uploads/';
    
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) { // تغيير الصلاحيات من 0777 إلى 0755
        if (empty($msg)) { // لا نستبدل رسالة خطأ سابقة إذا كانت موجودة
            $msg = "CRITICAL_ERROR: فشل في إنشاء مجلد رفع الملفات. تحقق من الصلاحيات.";
        }
    }
    $profile_pic = "default-avatar.png"; 

    // 2. التحقق من تكرار اسم المستخدم في قاعدة بيانات MySQL
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = LOWER(?)");
    $stmt->execute([$username]);
    $user_exists = $stmt->fetchColumn() > 0;

    if ($user_exists) {
        $msg = "خطأ: اسم المستخدم مسجل مسبقاً في قاعدة البيانات.";
    } else {
        // 3. توليد معرف مستخدم فريد والتحقق من عدم وجوده في MySQL لمنع التضارب
        do {
            $user_id = strval(rand(1000000000, 1999999999));
            $check_id = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
            $check_id->execute([$user_id]);
            $id_exists = $check_id->fetchColumn() > 0;
        } while ($id_exists);
        
        // 4. معالجة رفع الصورة الشخصية (إصلاح الخطأ الحاصل في السطر 48)
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
            finfo_close($finfo);
            
            // التأكد من جلب الاسم كسلسلة نصية نقية لتجنب TypeError
            $raw_filename = $_FILES['profile_pic']['name'];
            $filename = is_array($raw_filename) ? $raw_filename[0] : $raw_filename;
            
            $ext = strtolower(pathinfo((string)$filename, PATHINFO_EXTENSION));

            // تحقق إضافي من نوع MIME الفعلي للملف
            if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $msg = "خطأ: نوع الملف غير مدعوم أو غير صالح.";
            }

            if (in_array($ext, $allowed)) {
                // الهيكلة الإجبارية للاسم لتطابق فحص الـ glob في index.php
                $new_name = "user_" . $user_id . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                if (!is_writable($upload_dir)) { $msg = "CRITICAL_ERROR: مجلد الرفع غير قابل للكتابة."; }
                else if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $new_name)) {
                    $profile_pic = $new_name; 
                }
            }
        }

        // Check if this is the first user to assign ROOT automatically
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $access_level = ($countStmt->fetchColumn() == 0) ? 'ROOT' : 'USER';

        // 5. إدخال البيانات في MySQL
        $sql = "INSERT INTO users (user_id, username, password_hash, bio, profile_pic, access_level) 
                VALUES (:uid, :uname, :pass, :bio, :pic, :lvl)";
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':uid'   => $user_id,
                ':uname' => $username,
                ':pass'  => password_hash($password, PASSWORD_BCRYPT),
                ':bio'   => $bio,
                ':pic'   => $profile_pic,
                ':lvl'   => $access_level
            ]);
        } catch (PDOException $e) {
            $msg = "DATABASE_INSERT_ERROR: " . $e->getMessage();
            $result = false;
        }

        if ($result) {
            session_regenerate_id(true); // منع تثبيت الجلسة
            // تفعيل الجلسة الآمنة تلقائياً للمستخدم الموثق
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $access_level;
            $_SESSION['bio'] = $bio;
            $_SESSION['profile_pic'] = $profile_pic;

            $success_msg = "تمت مزامنة العقدة وإنشاء ملف الهوية بنجاح! جاري الانتقال للوحة التحكم...";
            
            // التأكد من إرسال الـ Headers قبل أي مخرجات
            echo "<script>window.location.href = 'index.php?msg=" . urlencode($success_msg) . "&type=success';</script>";
            exit();
        } else {
            echo "<script>window.location.href = 'index.php?msg=" . urlencode($msg) . "&type=error';</script>";
            exit();
        }
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
        .error-msg { background: rgba(255, 51, 102, 0.1); border: 1px solid var(--neon-red); color: var(--neon-red); padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-size: 0.9rem; }
        .success-msg { background: rgba(0, 255, 136, 0.1); border: 1px solid var(--neon-green); color: var(--neon-green); padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px; font-size: 0.9rem; }
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
                <label>[ INITIALIZE_NODE_NAME ]</label>
                <i class="fa-solid fa-user-gear"></i>
                <input type="text" name="username" placeholder="اسم المستخدم" required>
            </div>
            <div class="input-group">
                <label>[ ASSIGN_ACCESS_KEY ]</label>
                <i class="fa-solid fa-key"></i>
                <input type="password" name="password" placeholder="كلمة المرور التشفيرية" required>
            </div>
            <div class="input-group">
                <label>[ CRYPTO_BIO_IDENTIFIER ]</label>
                <i class="fa-solid fa-fingerprint" style="bottom: 45px;"></i>
                <textarea name="bio" placeholder="وصف مهاراتك السيبرانية أو تخصصك..." rows="2"></textarea>
            </div>
            <div class="input-group">
                <label>[ AVATAR_VECTOR_UPLOAD ]</label>
                <input type="file" name="profile_pic" accept="image/*" style="border: 1px dashed var(--border-color); padding: 8px 10px; color: var(--text-muted);">
            </div>
            <button type="submit" class="btn-submit">INITIALIZE_CORE_ACCOUNT</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
            لديك عقدة نشطة بالفعل؟ <a href="login.php" style="color: var(--cyber-cyan); text-decoration: none;">تسجيل الدخول من هنا</a>
        </p>
    </div>
</body>
</html>
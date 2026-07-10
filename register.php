<?php
/**
 * PROJECT: NAVA AI / CyberFlux v9.5 - DATABASE_CORE
 * MODULE: User Registration
 * ENGINEER: NAWAF_ROOT (Modified by Gemini Code Assist)
 */

session_start();
require_once 'config/database.php';

// [تحسين] التحقق من الاتصال بقاعدة البيانات قبل أي شيء
if ($pdo === null) {
    die("
    <body style='background-color: #0d1117; color: #c9d1d9; font-family: sans-serif; padding: 20px; direction: rtl;'>
    <div style='max-width: 800px; margin: 40px auto; border: 1px solid #ff6c6b; border-radius: 8px; padding: 25px; background-color: #282c34;'>
        <h1 style='color: #ff6c6b; text-align: center;'>🔴 فشل الاتصال بقاعدة البيانات</h1>
        <p>لا يمكن إنشاء حساب جديد لأن النظام غير قادر على الاتصال بقاعدة البيانات. يرجى مراجعة مسؤول النظام.</p>
    </div>
    </body>");
}

$error = '';
$success = '';

// دالة لمعالجة رفع الصورة
function handleProfilePictureUpload() {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['profile_pic'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_exts)) {
            return ['error' => 'صيغة الصورة غير مسموح بها. استخدم jpg, jpeg, png, gif.'];
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            return ['error' => 'حجم الصورة كبير جداً. الحد الأقصى 5 ميجابايت.'];
        }

        $new_filename = uniqid('user_', true) . '.' . $file_ext;
        $target_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['filename' => $new_filename];
        } else {
            return ['error' => 'حدث خطأ أثناء رفع الصورة.'];
        }
    }
    return ['filename' => 'default-avatar.png']; // الصورة الافتراضية
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 1. التحقق من تطابق كلمات المرور
    if ($password !== $password_confirm) {
        $error = 'كلمتا المرور غير متطابقتين.';
    } 
    // 2. التحقق من قوة كلمة المرور (مثال بسيط)
    elseif (strlen($password) < 6) {
        $error = 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.';
    } 
    else {
        try {
            // 3. التحقق من أن اسم المستخدم غير موجود مسبقاً
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $error = 'اسم المستخدم هذا محجوز بالفعل.';
            } else {
                // 4. معالجة رفع الصورة
                $upload_result = handleProfilePictureUpload();
                if (isset($upload_result['error'])) {
                    $error = $upload_result['error'];
                } else {
                    $profile_pic_filename = $upload_result['filename'];
                    
                    // 5. تشفير كلمة المرور
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // 6. إدخال المستخدم الجديد في قاعدة البيانات
                    $stmt = $pdo->prepare("INSERT INTO users (name, password, profile_pic) VALUES (?, ?, ?)");
                    if ($stmt->execute([$name, $hashed_password, $profile_pic_filename])) {
                        $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
                        header("Location: login.php?msg=" . urlencode($success) . "&type=success");
                        exit();
                    } else {
                        $error = 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("REGISTER_DATABASE_ERROR: " . $e->getMessage());
            $error = "خطأ تقني في قاعدة البيانات. راجع السجلات.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberFlux | إنشاء هوية جديدة</title>
    <link rel="icon" type="image/png" href="images/ooo.png">
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
            max-width: 450px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), var(--neon-glow); 
            backdrop-filter: blur(var(--blur-val));
            animation: heroFadeIn 0.8s ease-out;
            z-index: 10;
        }
        input, .file-input-wrapper { 
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
        .error-box, .success-box { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .error-box { color: var(--neon-red); background: rgba(255,49,49,0.1); border: 1px solid var(--neon-red); }
        .success-box { color: var(--neon-green); background: rgba(0,255,136,0.1); border: 1px solid var(--neon-green); }
        .auth-footer { margin-top: 20px; text-align: center; font-size: 0.8rem; color: #888; }
        .cyber-link { color: var(--cyber-cyan); text-decoration: none; }
        .file-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-input-wrapper .file-input-label {
            color: #888;
            flex-grow: 1;
        }
        .file-input-wrapper i {
            color: var(--cyber-cyan);
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="space-engine"></div>
    <div class="auth-container">
        <h2 style="color: var(--cyber-cyan); text-align: center; margin-bottom: 30px; font-family: var(--font-code); letter-spacing: 2px;">CREATE_IDENTITY</h2>
        
        <?php if($error): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="success-box"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="اسم المستخدم" required autocomplete="off" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <input type="password" name="password_confirm" placeholder="تأكيد كلمة المرور" required>
            
            <div class="file-input-wrapper">
                <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*">
                <span class="file-input-label" id="file-input-text">اختر صورة شخصية (اختياري)</span>
                <i class="fa-solid fa-image"></i>
            </div>

            <button type="submit">REGISTER_NODE</button>
        </form>

        <div class="auth-footer">
            لديك هوية بالفعل؟ <a href="login.php" class="cyber-link">قم بتسجيل الدخول</a>
        </div>
    </div>

    <script>
        document.getElementById('profile_pic_input').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'اختر صورة شخصية (اختياري)';
            document.getElementById('file-input-text').textContent = fileName;
            document.getElementById('file-input-text').style.color = this.files[0] ? '#fff' : '#888';
        });
    </script>
</body>
</html>
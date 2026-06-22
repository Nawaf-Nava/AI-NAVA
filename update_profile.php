<?php
/**
 * PROJECT: CyberFlux v9.5 - PROFILE_UPDATE_CORE
 * ENGINEER: NAWAF_ROOT
 */

session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// [أمان] التحقق من توكن CSRF لمنع الهجمات العابرة للمواقع
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: index.php?status=error&msg=" . urlencode("خطأ في التحقق من صحة الطلب (CSRF)."));
    exit();
}

$user_id = $_SESSION['user_id'];
$new_username = !empty($_POST['username']) ? trim($_POST['username']) : $_SESSION['username'];
$new_bio = trim($_POST['bio']);
$upload_dir = __DIR__ . '/images/uploads/';
$profile_pic = $_SESSION['profile_pic']; // القيمة الافتراضية هي الصورة القديمة

if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) { // تغيير الصلاحيات
    // يجب تسجيل الخطأ هنا وعدم عرضه للمستخدم مباشرة
    error_log("CRITICAL_ERROR: فشل في إنشاء مجلد رفع الملفات. تحقق من الصلاحيات.");
    header("Location: index.php?status=error&msg=" . urlencode("فشل في إنشاء مجلد الرفع."));
    exit();
}

try {
    // جلب اسم الصورة القديمة من قاعدة البيانات لضمان الدقة
    $stmt_old_pic = $pdo->prepare("SELECT profile_pic FROM users WHERE user_id = :uid");
    $stmt_old_pic->execute([':uid' => $user_id]);
    $old_profile_pic_filename = $stmt_old_pic->fetchColumn();
    $old_data = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $old_data->execute([$user_id]);
    $user_record = $old_data->fetch();

    // --- [1] معالجة طلبات الحذف الفردي للمدخلات ---
    if (isset($_POST['clear_field'])) {
        if ($_POST['clear_field'] === 'bio') {
            $pdo->prepare("UPDATE users SET bio = '' WHERE user_id = ?")->execute([$user_id]);
            $_SESSION['bio'] = '';
            header("Location: index.php?msg=" . urlencode('تم حذف السيرة الذاتية.') . "&type=success");
            exit();
        }
        if ($_POST['clear_field'] === 'avatar') {
            if ($old_profile_pic_filename !== 'default-avatar.png' && file_exists($upload_dir . $old_profile_pic_filename)) {
                unlink($upload_dir . $old_profile_pic_filename);
            }
            $pdo->prepare("UPDATE users SET profile_pic = 'default-avatar.png' WHERE user_id = ?")->execute([$user_id]);
            $_SESSION['profile_pic'] = 'default-avatar.png';
            header("Location: index.php?msg=" . urlencode('تم استعادة الصورة الافتراضية.') . "&type=success");
            exit();
        }
    }

    // --- [2] معالجة رفع الصورة الجديدة ---
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = "user_" . $user_id . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $new_name)) {
                // [تحسين أمان] التحقق من نوع MIME الفعلي وحذف الملف فوراً إذا كان مشبوهاً
                if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    unlink($upload_dir . $new_name);
                    throw new Exception("نوع الملف غير مدعوم أو غير صالح.");
                }
                
                // [تحسين أداء] حذف الصورة القديمة من السيرفر لتوفير المساحة
                if ($old_profile_pic_filename !== 'default-avatar.png' && file_exists($upload_dir . $old_profile_pic_filename)) {
                    unlink($upload_dir . $old_profile_pic_filename);
                }
                $profile_pic = $new_name;
                $_SESSION['profile_pic'] = $profile_pic;
            }
        }
    }

    // --- [3] معالجة تغيير كلمة المرور ---
    $password_sql = "";
    $params = [':bio' => $new_bio, ':pic' => $profile_pic, ':uid' => $user_id, ':uname' => $new_username];
    
    if (!empty($_POST['new_password']) && !empty($_POST['current_password'])) {
        if (password_verify($_POST['current_password'], $user_record['password_hash'])) {
            $password_sql = ", password_hash = :pass";
            $params[':pass'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        } else {
            header("Location: index.php?msg=" . urlencode("كلمة المرور الحالية غير صحيحة.") . "&type=error");
            exit();
        }
    }

    // --- [4] تحديث البيانات النهائية في MySQL ---
    $sql = "UPDATE users SET username = :uname, bio = :bio, profile_pic = :pic $password_sql WHERE user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // تحديث بيانات الجلسة
    $_SESSION['bio'] = $new_bio;
    $_SESSION['username'] = $new_username;
    $_SESSION['profile_pic'] = $profile_pic;

    // النجاح - العودة لصفحة الملف الشخصي مع إشعار نجاح بسيط
    header("Location: index.php?msg=" . urlencode('تم تحديث الملف الشخصي بنجاح.') . "&type=success");
    exit();

} catch (Exception $e) {
    // في حال الفشل
    error_log("CRITICAL_UPDATE_ERROR for user " . $user_id . ": " . $e->getMessage());
    header("Location: index.php?msg=" . urlencode("حدث خطأ أثناء تحديث الملف الشخصي.") . "&type=error");
    exit();
}
?>
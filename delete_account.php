<?php
session_start();

ini_set('display_errors', 0);
error_reporting(0);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_dir = __DIR__ . '/images/uploads/';

require_once 'config/database.php';

// جلب اسم الصورة قبل حذف السجل من قاعدة البيانات
$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$profile_pic_filename = $stmt->fetchColumn();

try {
    $pdo->beginTransaction(); // بدء معاملة قاعدة البيانات

    // 1. حذف السجل من قاعدة البيانات أولاً
    // حذف المستخدم من جدول users سيؤدي تلقائياً إلى حذف جلساته ورسائله بفضل ON DELETE CASCADE
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    // حذف الصورة الشخصية إذا لم تكن الصورة الافتراضية وموجودة في المسار الجديد
    if ($profile_pic_filename && $profile_pic_filename !== 'default-avatar.png' && file_exists($upload_dir . $profile_pic_filename)) {
        unlink($upload_dir . $profile_pic_filename);
    }

    // 2. تدمير الجلسة الحالية وإعادة التوجيه
    session_unset();
    session_destroy();

    $pdo->commit(); // تأكيد المعاملة إذا نجحت جميع العمليات

    header("Location: index.php?msg=" . urlencode('تم حذف الحساب والبيانات المرتبطة بنجاح. نأسف لمغادرتك CyberFlux.') . "&type=success");
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack(); // التراجع عن المعاملة إذا حدث أي خطأ
    error_log("CRITICAL_ACCOUNT_DELETION_ERROR for user " . $user_id . ": " . $e->getMessage());
    header("Location: profile.php?msg=" . urlencode('حدث خطأ تقني أثناء حذف الحساب. يرجى المحاولة لاحقًا.') . "&type=error");
    exit();
}
?>
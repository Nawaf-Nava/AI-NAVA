<?php
/**
 * PROJECT: NAVA AI - DATABASE_CONNECTION
 * تم تحديث الكود للعمل مع Railway Environment Variables
 */

// جلب المتغيرات من بيئة العمل في Railway
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

// التحقق من وجود كافة المتغيرات المطلوبة
if (!$host || !$db || !$user || !$pass || !$port) {
    die("CRITICAL_ERROR: متغيرات قاعدة البيانات غير مضبوطة بشكل صحيح في Railway.");
}

// إعداد سلسلة الاتصال (DSN)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    // إنشاء اتصال PDO
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // تسجيل الخطأ في سجلات Railway (Logs) دون كشف البيانات الحساسة للمستخدم
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    die("CRITICAL_ERROR: فشل الاتصال بقاعدة البيانات. تأكد من إعدادات المتغيرات في Railway.");
}
?>
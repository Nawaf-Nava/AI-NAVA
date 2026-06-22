<?php
/**
 * PROJECT: NAVA AI - DATABASE_CONNECTION
 * تم تعديل المتغيرات لتطابق إعدادات Railway
 */

// Railway توفر المتغيرات تلقائياً، نحن نقرأها هنا
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

// التأكد من وجود البيانات قبل المحاولة
if (!$host || !$db || !$user || !$pass) {
    die("CRITICAL_ERROR: متغيرات قاعدة البيانات غير مضبوطة في بيئة العمل.");
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // تسجيل الخطأ مع إخفاء كلمة المرور والبيانات الحساسة
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    die("CRITICAL_ERROR: فشل الاتصال بقاعدة البيانات.");
}
?>
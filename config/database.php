<?php
/**
 * PROJECT: NAVA AI - DYNAMIC DATABASE CONNECTION
 * تعديل لضمان الاتصال المباشر عبر Railway
 */

// 1. سحب المتغيرات (Railway تقوم بحقنها تلقائياً)
$host   = getenv('MYSQLHOST');
$port   = getenv('MYSQLPORT');
$dbname = getenv('MYSQLDATABASE');
$user   = getenv('MYSQLUSER');
$pass   = getenv('MYSQLPASSWORD');

// 2. التحقق من وجود القيم (في حال فشل السحب)
// إذا كانت هذه القيم فارغة، فهذا يعني أنك لست في بيئة Railway، 
// أو أن خدمة MySQL غير مربوطة بخدمة الموقع.
if (!$host || !$dbname || !$user) {
    die("CRITICAL_ERROR: متغيرات قاعدة البيانات غير موجودة. تأكد من ربط خدمة MySQL بـ AI-NAVA في لوحة التحكم.");
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
 
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
 
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // تسجيل الخطأ بدقة
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    die("CRITICAL_ERROR: فشل الاتصال بقاعدة البيانات. تأكد من إعدادات الربط في Railway.");
}
?>
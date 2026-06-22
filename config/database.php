<?php
/**
 * PROJECT: NAVA AI - DYNAMIC DATABASE CONNECTION
 * هذا الملف مهيأ للعمل ديناميكياً مع استضافة Railway وبيئة التطوير المحلية.
 */

// --- [ Dynamic Environment Configuration ] ---
// يقرأ متغيرات Railway، وفي حال عدم وجودها، يعود للإعدادات المحلية الافتراضية.
$host = getenv('MYSQLHOST') ?: '127.0.0.1';      // استبدل '127.0.0.1' إذا كان خادمك المحلي مختلفاً
$port = getenv('MYSQLPORT') ?: '3306';           // المنفذ الافتراضي لـ MySQL
$dbname = getenv('MYSQLDATABASE') ?: 'navadb';   // اسم قاعدة بياناتك المحلية
$user = getenv('MYSQLUSER') ?: 'root';           // اسم مستخدم قاعدة بياناتك المحلية
$pass = getenv('MYSQLPASSWORD') ?: '';           // كلمة مرور قاعدة بياناتك المحلية (اتركها فارغة إذا لم تكن موجودة)
 
// --- [ PDO Connection Setup ] ---
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
 
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
 
try {
    // إنشاء اتصال PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // في بيئة الإنتاج (مثل Railway)، سيتم تسجيل الخطأ فقط.
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    // إيقاف التنفيذ مع رسالة عامة وآمنة.
    // استخدام throw يجعله متوافقاً مع ملف debug_system.php
    throw new PDOException("CRITICAL: فشل الاتصال بنواة قاعدة البيانات.", (int)$e->getCode());
}
?>
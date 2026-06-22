<?php
/**
 * PROJECT: NAVA AI - DYNAMIC DATABASE CONNECTION
 * جاهز للعمل بنسبة 100% على استضافة Railway
 */

// --- [ إعدادات الاتصال ] ---
// الكود سيحاول قراءة المتغيرات من الاستضافة أولاً، وإذا لم يجدها سيستخدم بياناتك المباشرة
$host = getenv('MYSQLHOST')     ?: 'mysql'; // 'mysql' هو الهوست الداخلي الافتراضي في Railway
$port = getenv('MYSQLPORT')     ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway'; // اسم قاعدة بياناتك في Railway
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'WTELrleofnbQdyuSTxYqgcwSJJHKlZLS'; // كلمة المرور الخاصة بك
 
// --- [ بناء الاتصال بـ PDO ] ---
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
 
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
 
try {
    // إنشاء الاتصال
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // (اختياري): يمكنك إزالة الشرطتين (//) من السطر القادم لاختبار نجاح الاتصال عند فتح الموقع
    // echo "NAVA AI: تم الاتصال بقاعدة البيانات بنجاح!"; 
    
} catch (PDOException $e) {
    // تسجيل الخطأ في سجلات السيرفر (Logs)
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    
    // إيقاف التنفيذ وإظهار رسالة للمستخدم
    die("CRITICAL_ERROR: فشل الاتصال بنواة قاعدة البيانات. يرجى مراجعة سجلات النظام.");
}
?>
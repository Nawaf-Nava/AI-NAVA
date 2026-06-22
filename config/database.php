
<?php
// سحب المتغيرات من Railway
$host   = getenv('MYSQLHOST');
$port   = getenv('MYSQLPORT');
$dbname = getenv('MYSQLDATABASE');
$user   = getenv('MYSQLUSER');
$pass   = getenv('MYSQLPASSWORD');

// بناء رابط الاتصال
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

// إعدادات الاتصال (تم حذف السطر المسبب للخطأ)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // إنشاء الاتصال
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // تسجيل الخطأ وإيقاف التنفيذ
    error_log("DATABASE_ERROR: " . $e->getMessage());
    die("خطأ في الاتصال بقاعدة البيانات. راجع سجلات النظام.");
}
?>

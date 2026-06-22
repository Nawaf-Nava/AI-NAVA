<?php
/**
 * PROJECT: NAVA AI - PostgreSQL Connection
 */

// سحب المتغيرات تماماً كما هي مكتوبة في لوحة تحكم Railway الخاصة بك
$host   = getenv('PGHOST');
$dbname = getenv('PGDATABASE');
$user   = getenv('POSTGRES_USER');
$pass   = getenv('POSTGRES_PASSWORD');

// المنفذ الافتراضي لـ PostgreSQL هو 5432 (أضفته كقيمة احتياطية لأنه لم يظهر في الصورة)
$port   = getenv('PGPORT') ?: '5432';

// التحقق من وصول المتغيرات لتجنب الأخطاء
if (!$host || !$dbname || !$user) {
    die("CRITICAL_ERROR: بيانات الاتصال غير مكتملة. تأكد من تحميل المتغيرات بشكل صحيح.");
}

// بناء رابط الاتصال (لاحظ استخدام pgsql بدلاً من mysql)
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // إنشاء الاتصال
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // (اختياري) أزل الشرطتين في السطر التالي لاختبار نجاح الاتصال
    // echo "تم الاتصال بقاعدة بيانات PostgreSQL بنجاح!";
    
} catch (PDOException $e) {
    error_log("DATABASE_ERROR: " . $e->getMessage());
    die("فشل الاتصال بقاعدة البيانات. راجع سجلات النظام لمعرفة السبب.");
}
?>

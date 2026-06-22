<?php
/**
 * PROJECT: NAVA AI - PostgreSQL Connection (Enhanced)
 * DATABASE: Railway PostgreSQL
 * ENHANCED DIAGNOSTICS VERSION
 */

// 1. محاولة الحصول على متغيرات البيئة
$host   = getenv('PGHOST') ?: $_ENV['PGHOST'] ?? null;
$dbname = getenv('PGDATABASE') ?: $_ENV['PGDATABASE'] ?? null;
$user   = getenv('POSTGRES_USER') ?: $_ENV['POSTGRES_USER'] ?? null;
$pass   = getenv('POSTGRES_PASSWORD') ?: $_ENV['POSTGRES_PASSWORD'] ?? null;
$port   = getenv('PGPORT') ?: $_ENV['PGPORT'] ?? '5432';

// 2. التشخيص - حفظ معلومات الاتصال (للتصحيح فقط - احذفها بعد الإصلاح)
$debug_log = __DIR__ . '/database_debug.log';
$debug_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'host_set' => !empty($host) ? '✓ نعم' : '✗ لا',
    'dbname_set' => !empty($dbname) ? '✓ نعم' : '✗ لا',
    'user_set' => !empty($user) ? '✓ نعم' : '✗ لا',
    'pass_set' => !empty($pass) ? '✓ نعم (مخفي)' : '✗ لا',
    'port_value' => $port,
];
file_put_contents($debug_log, json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// 3. التحقق من وصول المتغيرات
if (!$host || !$dbname || !$user || !$pass) {
    $error_msg = "❌ بيانات الاتصال غير مكتملة:\n";
    if (!$host) $error_msg .= "- PGHOST غير موجود\n";
    if (!$dbname) $error_msg .= "- PGDATABASE غير موجود\n";
    if (!$user) $error_msg .= "- POSTGRES_USER غير موجود\n";
    if (!$pass) $error_msg .= "- POSTGRES_PASSWORD غير موجود\n";
    
    file_put_contents($debug_log, "ERROR: " . $error_msg . "\n", FILE_APPEND);
    die($error_msg . "\n\n⚠️ تأكد من متغيرات البيئة في لوحة تحكم Railway");
}

// 4. بناء رابط الاتصال
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

// 5. خيارات الاتصال
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false, // استخدام fresh connection
];

// 6. محاولة الاتصال مع تسجيل تفصيلي
try {
    file_put_contents($debug_log, "Attempting connection to: $host:$port/$dbname\n", FILE_APPEND);
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // اختبار الاتصال
    $test_query = $pdo->query("SELECT 1");
    if ($test_query) {
        file_put_contents($debug_log, "✓ SUCCESS: الاتصال بقاعدة البيانات نجح!\n\n", FILE_APPEND);
    }
    
} catch (PDOException $e) {
    $error_details = [
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage(),
        'host' => $host,
        'port' => $port,
        'database' => $dbname,
        'user' => $user,
    ];
    
    file_put_contents($debug_log, "❌ DATABASE CONNECTION ERROR:\n" . json_encode($error_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);
    
    error_log("DATABASE_ERROR: " . $e->getMessage());
    
    // رسالة خطأ مفيدة للمستخدم
    $error_message = "فشل الاتصال بقاعدة البيانات:\n\n";
    $error_message .= "• Host: " . $host . "\n";
    $error_message .= "• Database: " . $dbname . "\n";
    $error_message .= "• Error Code: " . $e->getCode() . "\n";
    $error_message .= "• Error: " . substr($e->getMessage(), 0, 100) . "...\n\n";
    $error_message .= "تأكد من:\n";
    $error_message .= "1. متغيرات البيئة في Railway مطبوعة بشكل صحيح\n";
    $error_message .= "2. قاعدة البيانات تعمل وقابلة للوصول\n";
    $error_message .= "3. بيانات المستخدم صحيحة\n";
    
    die($error_message);
}
?>

<?php
/**
 * PROJECT: NAVA AI - Local Database Connection
 * DATABASE: MySQL (Local Apache/MariaDB)
 * USER: nava_user (or as configured)
 * ENGINEER: NAWAF_ROOT
 */

// 1. إنشاء مجلد logs إذا لم يكن موجوداً
$logs_dir = dirname(__DIR__) . '/logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

// 2. ملف السجل
$debug_log = $logs_dir . '/database_connection_' . date('Y-m-d') . '.log';

function log_debug($message) {
    global $debug_log;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($debug_log, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

log_debug("=== بدء اختبار الاتصال ===");

$pdo = null; // تهيئة المتغير لضمان وجوده

try {
    // 3. [محلي] إعدادات الاتصال بقاعدة بيانات MySQL المحلية
    $host    = 'sql210.infinityfree.com'; // أو 'localhost' - عنوان السيرفر المحلي
    $port    = '3306';      // المنفذ الافتراضي لـ MySQL/MariaDB
    $dbname  = 'if0_42300177_nava_db';   // اسم قاعدة البيانات التي أنشأتها
    $user    = 'if0_42300177'; // اسم المستخدم الذي أنشأته لقاعدة البيانات
    $pass    = '446SYxdlOQWn';    // كلمة المرور للمستخدم الذي أنشأته
    $charset = 'utf8mb4';

    // بناء DSN للاتصال بـ MySQL المحلي
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

    // 4. تسجيل المتغيرات المستخرجة للتأكد
    log_debug("Host: $host");
    log_debug("Database: $dbname");
    log_debug("User: $user");
    log_debug("Port: $port");
    log_debug("DSN: " . $dsn);

    // 5. خيارات الاتصال
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // اختبار الاتصال بـ query بسيطة
    $pdo->query("SELECT 1"); // أبسط وأسرع اختبار للاتصال
    
    log_debug("✓ الاتصال نجح!");
    // لم نعد بحاجة لجلب الوقت، فقط نتأكد من أن الاستعلام يعمل
    log_debug("=== انتهى الاختبار بنجاح ===\n");

} catch (Exception $e) {
    log_debug("❌ فشل الاتصال!");
    log_debug("Error Code: " . $e->getCode());
    log_debug("Error Message: " . $e->getMessage());
    log_debug("=== انتهى الاختبار بفشل ===\n");

    if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'api.php') {
        $pdo = null; // تم تعيينه مسبقاً، هذا للتأكيد فقط
    } else {
        die("<pre style='background-color: #282c34; color: #ff6c6b; padding: 20px; border-radius: 5px; font-family: monospace;'>🔴 Database Connection Failed: " . htmlspecialchars($e->getMessage()) . "</pre>");
    }
}

// تم الاتصال بنجاح ويمكن لبقية كود المشروع استخدام كائن $pdo الآن!
?>

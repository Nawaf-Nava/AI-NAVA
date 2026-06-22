<?php
/**
 * PROJECT: NAVA AI - DYNAMIC DATABASE CONNECTION
 * إعدادات محسّنة وموثوقة لقاعدة البيانات
 */

// 1. سحب المتغيرات البيئية من Railway / Hosting Provider
$host     = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$port     = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
$dbname   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME');
$user     = getenv('MYSQLUSER') ?: getenv('DB_USER');
$pass     = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';

// 2. التحقق من القيم الحرجة
$missing_vars = [];
if (!$dbname) $missing_vars[] = 'MYSQLDATABASE (DB_NAME)';
if (!$user) $missing_vars[] = 'MYSQLUSER (DB_USER)';

if (!empty($missing_vars)) {
    $error_msg = "CRITICAL_ERROR: المتغيرات التالية غير موجودة:\n" . 
                 implode("\n", $missing_vars) . 
                 "\n\nتأكد من:\n" .
                 "1. ربط خدمة MySQL بالتطبيق في Railway\n" .
                 "2. إضافة متغيرات البيئة في ملف .env أو لوحة التحكم";
    die($error_msg);
}

// 3. بناء DSN (Database Source Name)
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

// 4. خيارات PDO الأمنة
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
    PDO::ATTR_PERSISTENT           => false, // تجنب الاتصالات المستمرة في بيئات مشتركة
    PDO::MYSQL_ATTR_INIT_COMMAND   => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::ATTR_TIMEOUT              => 10, // مهلة زمنية للاتصال
];

// 5. محاولة الاتصال مع معالجة الأخطاء
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // اختبار الاتصال
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // تسجيل تفاصيل الخطأ
    $error_msg = "DB_CONNECTION_FAILED: " . $e->getMessage();
    error_log($error_msg);
    
    // عرض رسالة آمنة للمستخدم
    header("HTTP/1.1 503 Service Unavailable");
    die("خطأ: لا يمكن الاتصال بقاعدة البيانات. الرجاء المحاولة لاحقاً.");
}

// 6. دوال مساعدة
/**
 * اختبار الاتصال
 */
function test_db_connection($pdo) {
    try {
        $result = $pdo->query("SELECT 1")->fetch();
        return $result ? true : false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * الحصول على معلومات الاتصال (للتشخيص)
 */
function get_db_info($pdo) {
    try {
        $result = $pdo->query("SELECT VERSION() as version, USER() as user")->fetch();
        return $result;
    } catch (Exception $e) {
        return null;
    }
}

?>

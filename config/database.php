<?php
/**
 * PROJECT: NAVA AI - PostgreSQL Connection (FULL DIAGNOSTIC)
 * DATABASE: Railway PostgreSQL
 * VERSION: DIRECT URL CONNECTION (FIXED)
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
    file_put_contents($debug_log, "[$timestamp] $message\n", FILE_APPEND);
}

log_debug("=== بدء اختبار الاتصال ===");

$pdo = null; // تهيئة المتغير لضمان وجوده

try {
    // 3. [محسن] محاولة قراءة الرابط من متغيرات البيئة أولاً (الأفضل للإنتاج)
    $db_url = getenv('DATABASE_URL');
    if ($db_url === false) {
        log_debug("متغير البيئة DATABASE_URL غير موجود، سيتم استخدام الرابط المباشر كخيار احتياطي.");
        // استخدام الرابط المباشر الذي قدمته كخيار احتياطي
        $db_url = "postgresql://postgres:JisCbmnkfhOBosZSMkpaEatkGxkRsYXO@thomas.proxy.rlwy.net:43959/railway";
    }

log_debug("استخراج إعدادات الاتصال من متغير البيئة DATABASE_URL...");
$parsed_url = parse_url($db_url);

$host   = $parsed_url['host'];
$port   = $parsed_url['port'];
$dbname = ltrim($parsed_url['path'], '/'); 
$user   = $parsed_url['user'];
$pass   = $parsed_url['pass'];

// 4. تسجيل المتغيرات المستخرجة للتأكد
log_debug("PGHOST: $host");
log_debug("PGDATABASE: $dbname");
log_debug("POSTGRES_USER: $user");
log_debug("PGPORT: $port");

// 5. التحقق من وجود البيانات المستخرجة
$missing = [];
if (!$host) $missing[] = 'HOST';
if (!$dbname) $missing[] = 'DATABASE';
if (!$user) $missing[] = 'USER';
if (!$pass) $missing[] = 'PASSWORD';

if (!empty($missing)) {
    $error_msg = "❌ فشل استخراج بيانات الاتصال من الرابط المباشر: " . implode(', ', $missing) . "\n\n";
    log_debug("ERROR: بيانات مفقودة في الرابط");
    die("<pre>" . htmlspecialchars($error_msg) . "</pre>");
}

// 6. بناء DSN للاتصال المباشر عبر المنفذ الخارجي لـ Railway
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
log_debug("DSN المبني: pgsql:host=$host;port=$port;dbname=$dbname");

// 7. خيارات الاتصال
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

    log_debug("جاري محاولة الاتصال...");
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // اختبار الاتصال بـ query بسيطة
    $test = $pdo->query("SELECT NOW()");
    $result = $test->fetch();
    
    log_debug("✓ الاتصال نجح!");
    log_debug("✓ الوقت من قاعدة البيانات: " . $result['now']);
    log_debug("=== انتهى الاختبار بنجاح ===\n");

} catch (Exception $e) { // تم التغيير إلى Exception لالتقاط كافة الأخطاء بما فيها متغير البيئة المفقود
    log_debug("❌ فشل الاتصال!");
    log_debug("Error Code: " . $e->getCode());
    log_debug("Error Message: " . $e->getMessage());
    log_debug("=== انتهى الاختبار بفشل ===\n");

    // [مهم] إذا كان الملف الذي يستدعي هذا الكود هو api.php، لا تقم بإيقاف التنفيذ الكامل
    // بدلاً من ذلك، قم بتعريف $pdo كـ null للسماح للنموذج بالرد دون حفظ.
    if (basename($_SERVER['SCRIPT_NAME']) === 'api.php') {
        $pdo = null; // تم تعيينه مسبقاً، هذا للتأكيد فقط
    } else {
        // للملفات الأخرى (مثل test_db.php)، أظهر رسالة الخطأ الكاملة
        die("<pre>🔴 فشل الاتصال بقاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</pre>");
    }
}

// تم الاتصال بنجاح ويمكن لبقية كود المشروع استخدام كائن $pdo الآن!
?>

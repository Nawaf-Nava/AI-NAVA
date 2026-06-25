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

// 3. [محسن] قراءة رابط الاتصال من متغيرات البيئة (الأفضل للإنتاج) أو استخدام رابط مباشر كخيار احتياطي للتطوير
$db_url = getenv('DATABASE_URL');
if ($db_url === false) {
    log_debug("متغير البيئة DATABASE_URL غير موجود، سيتم استخدام الرابط المباشر كخيار احتياطي.");
    $db_url = "postgresql://postgres:UPhWsKbmLKGewiYEKjgfekLyvbDWHhdS@reseau.proxy.rlwy.net:34072/railway"; // ⚠️ للتطوير المحلي فقط
}

log_debug("استخراج إعدادات الاتصال من الرابط: " . (getenv('DATABASE_URL') ? 'DATABASE_URL' : 'Fallback URL'));
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

// 8. محاولة الاتصال
try {
    log_debug("جاري محاولة الاتصال...");
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // اختبار الاتصال بـ query بسيطة
    $test = $pdo->query("SELECT NOW()");
    $result = $test->fetch();
    
    log_debug("✓ الاتصال نجح!");
    log_debug("✓ الوقت من قاعدة البيانات: " . $result['now']);
    log_debug("=== انتهى الاختبار بنجاح ===\n");
    
} catch (PDOException $e) {
    $error_code = $e->getCode();
    $error_msg_raw = $e->getMessage();
    
    log_debug("❌ فشل الاتصال!");
    log_debug("Error Code: $error_code");
    log_debug("Error Message: $error_msg_raw");
    log_debug("=== انتهى الاختبار بفشل ===\n");
    
    // تشخيص الخطأ تلقائياً بناءً على رسالة PostgreSQL الأصلية
    $diagnosis = "";
    if (strpos($error_msg_raw, 'password authentication failed') !== false) {
        $diagnosis = "❌ كلمة المرور في الرابط غير صحيحة أو تم تغييرها من Railway.\n";
    } elseif (strpos($error_msg_raw, 'Connection refused') !== false) {
        $diagnosis = "❌ السيرفر الخارجي لا يستجيب، قد يكون المنفذ (Port) تغير من Railway.\n";
    } elseif (strpos($error_msg_raw, 'database') !== false && strpos($error_msg_raw, 'does not exist') !== false) {
        $diagnosis = "❌ اسم قاعدة البيانات المكتوب في الرابط غير موجود داخل السيرفر.\n";
    } else {
        $diagnosis = "❌ خطأ في الاتصال بالشبكة أو إعدادات خادم PHP.\n";
    }
    
    $error_message = "🔴 فشل الاتصال بقاعدة البيانات\n\n";
    $error_message .= "📊 التفاصيل المستخرجة من الرابط:\n";
    $error_message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $error_message .= "Host: $host\n";
    $error_message .= "Database: $dbname\n";
    $error_message .= "User: $user\n";
    $error_message .= "Port: $port\n\n";
    $error_message .= "🔍 التشخيص:\n";
    $error_message .= $diagnosis . "\n";
    $error_message .= "📝 الخطأ الأصلي المتلقى من السيرفر:\n";
    $error_message .= $error_msg_raw . "\n\n";
    $error_message .= "📄 ملف السجل الكامل: $debug_log\n";
    
    die("<pre>" . htmlspecialchars($error_message) . "</pre>");
}

// تم الاتصال بنجاح ويمكن لبقية كود المشروع استخدام كائن $pdo الآن!
?>

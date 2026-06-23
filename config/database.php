<?php
/**
 * PROJECT: NAVA AI - PostgreSQL Connection (FULL DIAGNOSTIC)
 * DATABASE: Railway PostgreSQL
 * VERSION: WITH COMPLETE DEBUGGING (MODIFIED FOR INTERNAL LINK)
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

// 3. جلب متغيرات البيئة - الأولوية لرابط الاتصال الموحد (DATABASE_URL)
$database_url = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;

if ($database_url) {
    log_debug("تم العثور على DATABASE_URL. جاري تحليل الرابط...");
    $db_parts = parse_url($database_url);
    $host   = $db_parts['host'] ?? null;
    $port   = $db_parts['port'] ?? '5432';
    $user   = $db_parts['user'] ?? null;
    $pass   = $db_parts['pass'] ?? null;
    $dbname = ltrim($db_parts['path'] ?? '', '/');
} else {
    log_debug("لم يتم العثور على DATABASE_URL. العودة للمتغيرات الفردية...");
    // الطريقة القديمة كخيار احتياطي
    $host   = getenv('PGHOST') ?: $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? null;
    $dbname = getenv('PGDATABASE') ?: $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? null;
    $user   = getenv('POSTGRES_USER') ?: $_ENV['POSTGRES_USER'] ?? $_SERVER['POSTGRES_USER'] ?? null;
    $pass   = getenv('POSTGRES_PASSWORD') ?: $_ENV['POSTGRES_PASSWORD'] ?? $_SERVER['POSTGRES_PASSWORD'] ?? null;
    $port   = getenv('PGPORT') ?: $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? '5432';
}

// 4. تسجيل المتغيرات المستلمة
log_debug("PGHOST: " . ($host ? "موجود ($host)" : "غير موجود"));
log_debug("PGDATABASE: " . ($dbname ? "موجود ($dbname)" : "غير موجود"));
log_debug("POSTGRES_USER: " . ($user ? "موجود ($user)" : "غير موجود"));
log_debug("POSTGRES_PASSWORD: " . ($pass ? "موجود (مخفي)" : "غير موجود"));
log_debug("PGPORT: $port");

// 5. التحقق من وجود كل المتغيرات المطلوبة
$missing = [];
if (!$host) $missing[] = 'PGHOST';
if (!$dbname) $missing[] = 'PGDATABASE';
if (!$user) $missing[] = 'POSTGRES_USER';
if (!$pass) $missing[] = 'POSTGRES_PASSWORD';

if (!empty($missing)) {
    $error_msg = "❌ متغيرات بيئة مفقودة: " . implode(', ', $missing) . "\n\n";
    $error_msg .= "📋 المتغيرات المطلوبة في Railway:\n";
    $error_msg .= "• PGHOST\n";
    $error_msg .= "• PGDATABASE\n";
    $error_msg .= "• POSTGRES_USER\n";
    $error_msg .= "• POSTGRES_PASSWORD\n";
    $error_msg .= "• PGPORT (اختياري)\n\n";
    $error_msg .= "🔗 اذهب إلى: https://railway.app -> Variables\n";
    $error_msg .= "ثم انسخ واللصق القيم بدقة\n\n";
    $error_msg .= "📝 ملف السجل: " . str_replace(__DIR__, '', $debug_log) . "\n";
    
    log_debug("ERROR: " . implode(', ', $missing) . " غير موجودة");
    log_debug("=== انتهى الاختبار بفشل ===\n");
    
    // التحقق من بيئة التشغيل قبل طباعة الأخطاء التفصيلية
    if (getenv('APP_ENV') === 'production') {
        http_response_code(500);
        die("Database configuration error. Please contact the administrator.");
    }
    die("<pre>" . htmlspecialchars($error_msg) . "</pre>");
}

// 6. بناء DSN (تم تعديل هذا السطر وإزالة لضمان التوافق الداخلي مع سيرفر Railway)
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
    
    // الاتصال نجح - نتابع العمل
    
} catch (PDOException $e) {
    $error_code = $e->getCode();
    $error_msg_raw = $e->getMessage();
    
    log_debug("❌ فشل الاتصال!");
    log_debug("Error Code: $error_code");
    log_debug("Error Message: $error_msg_raw");
    log_debug("=== انتهى الاختبار بفشل ===\n");
    
    // تشخيص الخطأ بناءً على رمز الخطأ
    $diagnosis = "";
    
    if (strpos($error_msg_raw, 'could not translate host name') !== false) {
        $diagnosis = "❌ Hostname غير صحيح أو غير موجود\n";
        $diagnosis .= "   تأكد من قيمة PGHOST من Railway\n";
    } elseif (strpos($error_msg_raw, 'Connection refused') !== false) {
        $diagnosis = "❌ قاعدة البيانات لا ترد على الطلب\n";
        $diagnosis .= "   تحقق من أن PostgreSQL مشغل في Railway\n";
    } elseif (strpos($error_msg_raw, 'password authentication failed') !== false) {
        $diagnosis = "❌ كلمة المرور خاطئة\n";
        $diagnosis .= "   تأكد من POSTGRES_PASSWORD\n";
    } elseif (strpos($error_msg_raw, 'database') !== false && strpos($error_msg_raw, 'does not exist') !== false) {
        $diagnosis = "❌ اسم قاعدة البيانات خاطئ\n";
        $diagnosis .= "   تأكد من PGDATABASE\n";
    } elseif (strpos($error_msg_raw, 'SSL') !== false) {
        $diagnosis = "❌ مشكلة في SSL/TLS\n";
        $diagnosis .= "   جرب إضافة sslmode=disable في DSN\n";
    } else {
        $diagnosis = "❌ خطأ غير معروف - انظر التفاصيل أدناه\n";
    }
    
    $error_message = "🔴 فشل الاتصال بقاعدة البيانات\n\n";
    $error_message .= "📊 التفاصيل:\n";
    $error_message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $error_message .= "Host: $host\n";
    $error_message .= "Database: $dbname\n";
    $error_message .= "User: $user\n";
    $error_message .= "Port: $port\n\n";
    $error_message .= "🔍 التشخيص:\n";
    $error_message .= $diagnosis . "\n";
    $error_message .= "📝 الخطأ الأصلي:\n";
    $error_message .= substr($error_msg_raw, 0, 200) . "...\n\n";
    $error_message .= "📄 ملف السجل الكامل:\n";
    $error_message .= $debug_log . "\n\n";
    $error_message .= "🚀 الحل:\n";
    $error_message .= "1. اذهب إلى https://railway.app\n";
    $error_message .= "2. افتح المشروع الخاص بك\n";
    $error_message .= "3. انسخ جميع متغيرات البيئة\n";
    $error_message .= "4. حدثها في ملف .env أو في إعدادات الخادم\n";
    
    if (getenv('APP_ENV') === 'production') {
        http_response_code(500);
        die("Database connection failed. Please contact the administrator.");
    }
    die("<pre>" . htmlspecialchars($error_message) . "</pre>");
}

// تم الاتصال بنجاح!
?>
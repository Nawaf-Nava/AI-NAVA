<?php
/**
 * NAVA AI - Diagnostic Tool
 * يهدف هذا الملف لمعرفة سبب عدم فتح الموقع
 */
header('Content-Type: text/html; charset=utf-8');
echo "<body style='background-color: #0d1117; color: #c9d1d9; font-family: monospace; padding: 20px;'>";
echo "<h2 style='color: #00f3ff;'>NAVA AI - System Diagnostics</h2>";

// 0. فحص متغيرات البيئة
echo "<h3>[0] Environment Variables Check</h3>";
if (getenv('DATABASE_URL')) {
    echo "<p style='color:green;'>✅ متغير البيئة DATABASE_URL: موجود.</p>";
} else {
    echo "<p style='color:red;'>❌ متغير البيئة DATABASE_URL: غير موجود. هذا سيمنع الاتصال بالقاعدة.</p>";
}

// 1. فحص قاعدة البيانات
echo "<h3>[1] Database Connection Check</h3>";
try {
   require_once 'config/database.php';
    echo "<p style='color:green;'>✅ الاتصال بقاعدة البيانات: سليم.</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage() . "</p>";
}

// 2. فحص المجلدات
$folders = ['vault/chats/', 'images/uploads/', 'scripts/', 'logs/'];
foreach ($folders as $f) {
    $path = __DIR__ . '/' . $f;
    if (is_writable($path)) {
        echo "<p style='color:green;'>✅ مجلد $f: قابل للكتابة</p>";
    } else {
        echo "<p style='color:red;'>❌ مجلد $f: غير موجود أو لا يملك صلاحيات (Permission Denied)</p>";
    }
}

// 3. فحص إضافات PHP
echo "<h3>[3] PHP Extensions Check</h3>";
if (extension_loaded('pdo_pgsql')) {
    echo "<p style='color:green;'>✅ إضافة PostgreSQL (pdo_pgsql): مفعلة.</p>";
} else {
    echo "<p style='color:red;'>❌ إضافة PostgreSQL (pdo_pgsql): غير مفعلة. هذا هو السبب الرئيسي لخطأ 'could not find driver'.</p>";
    echo "<p style='color:yellow;'>الحل: تأكد من وجود ملف 'nixpacks.toml' في مشروعك يحتوي على 'aptPkgs = [\"php-pgsql\"]'.</p>";
}

if (extension_loaded('curl')) {
    echo "<p style='color:green;'>✅ إضافة cURL: مفعلة (ضرورية لـ API).</p>";
} else {
    echo "<p style='color:red;'>❌ إضافة cURL: غير مفعلة في السيرفر.</p>";
}

// 4. فحص دوال التنفيذ
echo "<h3>[4] Execution Functions Check</h3>";
if (function_exists('shell_exec')) {
    echo "<p style='color:green;'>✅ دالة shell_exec: مفعلة (ضرورية للوضع العميق).</p>";
} else {
    echo "<p style='color:red;'>❌ دالة shell_exec: معطلة في إعدادات PHP (php.ini).</p>";
}

echo "<hr style='border-color: #30363d;'>";
echo "<h4>الخلاصة:</h4>";
echo "<p>إذا رأيت أي خطأ بالأحمر أعلاه، فهذا هو سبب المشكلة. الخطأ الأكثر شيوعاً هو عدم تفعيل إضافة 'pdo_pgsql'.</p>";
echo "<p style='color:yellow;'>بعد إصلاح الأخطاء وإعادة النشر، قم بحذف هذا الملف من السيرفر للأمان.</p>";
echo "</body>";
?>
<?php
/**
 * NAVA AI - Diagnostic Tool
 * يهدف هذا الملف لمعرفة سبب عدم فتح الموقع
 */
header('Content-Type: text/html; charset=utf-8');
echo "<h2>فحص أنظمة NAVA AI...</h2>";

// 1. فحص قاعدة البيانات
try {
   require_once 'config/database.php';
    echo "<p style='color:green;'>✅ الاتصال بقاعدة البيانات: سليم</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage() . "</p>";
}

// 2. فحص المجلدات
$folders = ['vault/chats/', 'images/uploads/'];
foreach ($folders as $f) {
    $path = __DIR__ . '/' . $f;
    if (is_writable($path)) {
        echo "<p style='color:green;'>✅ مجلد $f: قابل للكتابة</p>";
    } else {
        echo "<p style='color:red;'>❌ مجلد $f: غير موجود أو لا يملك صلاحيات (Permission Denied)</p>";
    }
}

// 3. فحص إضافات PHP
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color:green;'>✅ إضافة MySQL: مفعلة</p>";
} else {
    echo "<p style='color:red;'>❌ إضافة MySQL: غير مفعلة في السيرفر</p>";
}

if (extension_loaded('curl')) {
    echo "<p style='color:green;'>✅ إضافة cURL: مفعلة (ضرورية لـ API)</p>";
} else {
    echo "<p style='color:red;'>❌ إضافة cURL: غير مفعلة في السيرفر</p>";
}

if (function_exists('shell_exec')) {
    echo "<p style='color:green;'>✅ دالة shell_exec: مفعلة (ضرورية للوضع العميق)</p>";
} else {
    echo "<p style='color:red;'>❌ دالة shell_exec: معطلة في إعدادات PHP (php.ini)</p>";
}

echo "<hr><p>بعد إصلاح الأخطاء أعلاه، قم بحذف هذا الملف للأمان.</p>";
?>
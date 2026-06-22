<?php
session_start();

// 1. مسح جميع متغيرات الجلسة
$_SESSION = array();

// 2. تدمير ملف تعريف الارتباط الخاص بالجلسة (Cookie)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. تدمير الجلسة نهائياً
session_destroy();

// 4. التوجيه لصفحة تسجيل الدخول أو الرئيسية
header("Location: index.php");
exit();
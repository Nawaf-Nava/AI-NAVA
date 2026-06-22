<?php
/**
 * PROJECT: NAVA AI - DATABASE_CONNECTION
 * LOCATION: /config/database.php
 */

$host = getenv('DB_HOST') ?: "127.0.0.1";
$db   = getenv('DB_NAME') ?: "navadb";
$user = getenv('DB_USER') ?: "navadb";
$pass = getenv('DB_PASS') ?: "NavaPass123!";
$port = getenv('DB_PORT') ?: "3306";

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log("NAVA_DB_FAILURE: " . $e->getMessage());
    // عرض رسالة خطأ عامة للمستخدم وتسجيل التفاصيل في السجلات
    die("CRITICAL_ERROR: فشل الاتصال بعقدة البيانات الأساسية. يرجى مراجعة سجلات النظام.");
}
?>
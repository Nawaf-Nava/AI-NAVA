<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$sessions = [];
$user_id = $_SESSION['user_id'];

try {
    // الاستعلام من جدول 'sessions' لجلب بيانات الجلسات
    // نستخدم user_id (المعرف الفريد VARCHAR) وليس id (المفتاح الأساسي SERIAL)
    $query = "SELECT session_uuid, title FROM sessions WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل session_uuid إلى session_id ليتوافق مع JavaScript
    $formattedSessions = array_map(function($session) {
        return ["session_id" => $session['session_uuid'], "title" => $session['title']];
    }, $sessions);

    echo json_encode($formattedSessions);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
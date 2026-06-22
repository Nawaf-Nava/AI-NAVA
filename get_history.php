<?php
session_start();
// 1. منع خروج أي أخطاء نصية قد تفسد صيغة الـ JSON
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once 'config/database.php';

// 2. التحقق من جلسة المستخدم (معرف المستخدم 7 كما في الصورة)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // استعلام مباشر وسريع باستخدام الفهرس (Index) على user_id
    $query = "SELECT session_uuid, title 
              FROM sessions 
              WHERE user_id = :user_id 
              ORDER BY created_at DESC LIMIT 25";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $history = [];
    foreach ($result as $row) {
        // تأمين معالجة اللغة العربية لـ Nawaflux
        $history[] = [
            "session_id" => $row['session_uuid'],
            "title" => $row['title'] ?: 'Untitled Session',
            "username" => $_SESSION['username'] // استخدام اسم المستخدم من الجلسة لتوفير وقت الـ JOIN
        ];
    }

    // 4. إرسال مصفوفة نظيفة ليفهمها كود الـ JavaScript
    echo json_encode($history);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
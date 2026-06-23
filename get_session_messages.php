<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config/database.php';

// التحقق من صلاحية الوصول وجودة الطلب
if (!isset($_SESSION['user_id']) || !isset($_GET['session_id'])) {
    echo json_encode(["status" => "error", "message" => "UNAUTHORIZED_OR_MISSING_ID"]);
    exit;
}

$chat_id = filter_var($_GET['session_id'], FILTER_VALIDATE_INT);

// إعدادات التحميل المتأخر
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // جلب 20 رسالة فقط في كل مرة
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0; // نقطة البداية

try {
    // [REFACTORED]: تم التحديث ليتوافق مع Schema الجديد
    $stmt = $pdo->prepare("SELECT sender_type as role, content FROM messages WHERE chat_id = :cid ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    
    $stmt->bindValue(':cid', $chat_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إعادة ترتيب الرسائل لتظهر بشكل صحيح (من الأقدم للأحدث) في الواجهة
    $messages = array_reverse($messages);

    echo json_encode(["status" => "success", "history" => $messages]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "DATABASE_QUERY_FAILED"]);
}
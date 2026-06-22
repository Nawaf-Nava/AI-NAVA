<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config/database.php';

// التحقق من صلاحية الوصول وجودة الطلب
if (!isset($_SESSION['user_id']) || !isset($_GET['session_id'])) {
    echo json_encode(["status" => "error", "message" => "UNAUTHORIZED_OR_MISSING_ID"]);
    exit;
}

$session_uuid = preg_replace('/[^A-Za-z0-9_]/', '', $_GET['session_id']);

// إعدادات التحميل المتأخر
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // جلب 20 رسالة فقط في كل مرة
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0; // نقطة البداية

try {
    // [تحسين هندسي]: الربط الصريح للأنواع (Explicit Type Binding) لضمان أفضل أداء لمحرك MySQL
    $stmt = $pdo->prepare("SELECT role, content FROM messages WHERE session_uuid = :sid ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    
    $stmt->bindValue(':sid', $session_uuid, PDO::PARAM_STR);
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
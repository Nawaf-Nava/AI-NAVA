<?php
/**
 * PROJECT: NAVA AI v9.5 - PURGE_ENGINE
 * ENGINEER: NAWAF_ROOT
 * STATUS: PERMANENT_DESTRUCTION_ENABLED
 */

session_start();
require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

// 1. التحقق من الهوية (ROOT_AUTHORITY_CHECK)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "ACCESS_DENIED"]);
    exit;
}

$user_id = $_SESSION['user_id'];
// تنظيف المعرف المستلم لمنع ثغرات الحقن
$session_uuid = isset($_GET['session_id']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_GET['session_id']) : null;

if (!$session_uuid) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "SESSION_ID_REQUIRED"]);
    exit;
}

try {
    // بدء معاملة لضمان حذف كل شيء أو لا شيء (Atomicity)
    $pdo->beginTransaction();

    // 2. حذف الرسائل المرتبطة بالعقدة أولاً
    // نستخدم استعلام فرعي للتأكد من أن الرسائل تتبع لجلسة يملكها المستخدم الحالي
    $deleteMessages = $pdo->prepare("
        DELETE FROM messages 
        WHERE session_uuid = ? 
        AND session_uuid IN (SELECT session_uuid FROM sessions WHERE user_id = ?)
    ");
    $deleteMessages->execute([$session_uuid, $user_id]);

    // 3. حذف سجل الجلسة نفسه
    $deleteSession = $pdo->prepare("DELETE FROM sessions WHERE session_uuid = ? AND user_id = ?");
    $deleteSession->execute([$session_uuid, $user_id]);

    $pdo->commit();
    
    echo json_encode(["status" => "success", "message" => "NODE_PURGED_PERMANENTLY"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("PURGE_ERROR for user $user_id on node $session_uuid: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "PURGE_FAILURE"]);
}
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
$chat_id = isset($_GET['session_id']) ? filter_var($_GET['session_id'], FILTER_VALIDATE_INT) : null;

if (!$chat_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "SESSION_ID_REQUIRED"]);
    exit;
}

try {
    // [REFACTORED]: الحذف المباشر من جدول chats سيقوم بحذف الرسائل تلقائياً بفضل ON DELETE CASCADE
    $deleteChat = $pdo->prepare("DELETE FROM chats WHERE id = ? AND user_id = ?");
    $deleteChat->execute([$chat_id, $user_id]);
    
    echo json_encode(["status" => "success", "message" => "NODE_PURGED_PERMANENTLY"]);

} catch (Exception $e) {
    error_log("PURGE_ERROR for user $user_id on chat $chat_id: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "PURGE_FAILURE"]);
}
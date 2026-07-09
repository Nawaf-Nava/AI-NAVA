<?php
/**
 * PROJECT: NAVA AI v9.5 - SESSION_NAMING_ENGINE
 * This script updates the title of a given session.
 */

session_start();
require_once 'config/database.php';
header('Content-Type: application/json; charset=utf-8');

// 1. Security Check: Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "ACCESS_DENIED"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$chat_id = isset($data['session_id']) ? filter_var($data['session_id'], FILTER_VALIDATE_INT) : null;
$title = trim($data['title'] ?? '');

// 2. Validation: Ensure all required data is present.
if (!$chat_id || empty($title)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "SESSION_ID_OR_TITLE_MISSING"]);
    exit;
}

try {
    // 3. [REFACTORED] تحديث عنوان المحادثة في جدول chats
    $stmt = $pdo->prepare("UPDATE chats SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $chat_id, $user_id]);

    echo json_encode(["status" => "success", "message" => "Session title updated."]);
} catch (Exception $e) {
    error_log("SESSION_TITLE_UPDATE_ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DATABASE_ERROR"]);
}
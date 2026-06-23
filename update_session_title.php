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
$session_uuid = $data['session_id'] ?? null;
$title = trim($data['title'] ?? '');

// 2. Validation: Ensure all required data is present.
if (!$session_uuid || empty($title)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "SESSION_ID_OR_TITLE_MISSING"]);
    exit;
}

try {
    // 3. Update the session title in the database, ensuring the user owns the session.
    $stmt = $pdo->prepare("UPDATE sessions SET title = ? WHERE session_uuid = ? AND user_id = ?");
    $stmt->execute([$title, $session_uuid, $user_id]);

    echo json_encode(["status" => "success", "message" => "Session title updated."]);
} catch (Exception $e) {
    error_log("SESSION_TITLE_UPDATE_ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DATABASE_ERROR"]);
}
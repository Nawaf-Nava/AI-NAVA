<?php
/* --- CyberFlux Production Core: Secure Vault v8.7 --- */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

// التحقق من أن المستخدم مسجل دخوله في الجلسة
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// [1] استقبال البيانات
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['messages'])) {
    // يجب تسجيل الخطأ داخليًا
    error_log("Secure Vault: No data received for user " . ($user_id ?? 'N/A'));
    ob_end_clean(); echo json_encode(["status" => "error", "message" => "No Data Received"]); exit;
}

$user_id    = $_SESSION['user_id'];
$session_id = $input['session_id'] ?? time();
$title      = $input['title'] ?? 'Session_' . date("H:i");

// تنظيف session_id لمنع ثغرات تجاوز المسار
$session_id = preg_replace('/[^A-Za-z0-9_]/', '', $session_id);

// [2] تحديد المسارات
$userFolder = __DIR__ . '/vault/chats/' . $user_id;
$sessionFolder = $userFolder . '/' . $session_id;
$filePath = $sessionFolder . '/active_session.json';

try {
    $pdo->beginTransaction();
    
    // 1. تحديث أو إدخال الجلسة في جدول sessions
    $sqlSession = "INSERT INTO sessions (user_id, session_id, session_uuid, title) 
                   VALUES (:uid, :sid, :suid, :title)
                   ON DUPLICATE KEY UPDATE title = VALUES(title)";
    $stmtS = $pdo->prepare($sqlSession);
    $stmtS->execute([
        ':uid'   => $user_id,
        ':sid'   => $session_id,
        ':suid'  => $session_id,
        ':title' => $title
    ]);

    // 2. مسح الرسائل القديمة لهذه الجلسة (اختياري) وحفظ الجديدة
    $pdo->prepare("DELETE FROM messages WHERE session_uuid = ?")->execute([$session_id]);
    
    $insMsg = $pdo->prepare("INSERT INTO messages (session_uuid, role, content) VALUES (?, ?, ?)");
    foreach ($input['messages'] as $msg) {
        $insMsg->execute([$session_id, $msg['role'], $msg['content']]);
    }

    $pdo->commit();
    $res = ["status" => "success", "session_id" => $session_id];

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $res = ["status" => "error", "message" => $e->getMessage()];
}

ob_end_clean();
echo json_encode($res);
exit;
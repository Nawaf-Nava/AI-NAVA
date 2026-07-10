<?php
/**
 * PROJECT: NAVA AI v9.5
 * MODULE: Session Loader
 */

// 1. بدء الجلسة فوراً للتمكن من قراءة user_id
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
header('Content-Type: application/json; charset=utf-8');

// 2. التحقق من الصلاحيات
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'unauthorized', 
        'message' => 'Session expired or not logged in',
        'sessions' => []
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    /**
     * 3. الاستعلام المجهز
     * تم التحديث ليتوافق مع Schema الجديد
     */
    $query = "SELECT id, title FROM chats WHERE user_id = ? AND title NOT LIKE 'UNSAVED_SESSION_%' ORDER BY created_at DESC LIMIT 50";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. الرد بنجاح
    echo json_encode([
        'status' => 'success',
        'sessions' => $sessions
    ]);

} catch (Exception $e) {
    // تسجيل الخطأ داخلياً وعدم عرضه للمستخدم بالكامل لأسباب أمنية
    error_log("CyberFlux Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Internal Server Error' 
    ]);
}
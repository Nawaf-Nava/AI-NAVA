<?php
/**
 * NAVA AI - SHELL_EXECUTION_BRIDGE
 * ENGINEER: NAWAF_ROOT
 */
session_start();
header('Content-Type: application/json');

// [أمان] السماح بالتشغيل فقط للمستخدمين المسجلين الذين يملكون صلاحية ADMIN
if (!isset($_SESSION['user_id']) || (isset($_SESSION['access_level']) && $_SESSION['access_level'] !== 'ADMIN')) {
    http_response_code(403); // Forbidden
    exit(json_encode(['error' => 'ACCESS_DENIED']));
}

$data = json_decode(file_get_contents('php://input'), true);
$command = $data['code'] ?? '';

if (empty($command)) {
    exit(json_encode(['error' => 'NO_COMMAND_PROVIDED']));
}

// تنفيذ الأمر وجلب المخرجات والخطأ (2>&1) لضمان رؤية كل شيء
$output = shell_exec($command . " 2>&1");

echo json_encode(['output' => $output ?: "Command executed with no return output."]);
?>
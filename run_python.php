<?php
/**
 * NAVA AI - PYTHON_EXECUTION_BRIDGE
 */
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';

// [أمان] السماح بالتشغيل فقط للمستخدمين المسجلين الذين يملكون صلاحية ADMIN
if (!isset($_SESSION['user_id']) || (isset($_SESSION['access_level']) && $_SESSION['access_level'] !== 'ADMIN')) {
    http_response_code(403); // Forbidden
    exit(json_encode(['error' => 'ACCESS_DENIED: AUTH_REQUIRED']));
}

if (empty($code)) {
    exit(json_encode(['error' => 'NO_CODE_PROVIDED']));
}

function executePython($code) {
    $baseDir = __DIR__;
    $venvPython = $baseDir . "/venv/bin/python3";
    $scriptsDir = $baseDir . '/scripts/';
    
    // التأكد من وجود مجلد السكربتات وصلاحية الكتابة
    if (!is_dir($scriptsDir)) {
        @mkdir($scriptsDir, 0755, true);
    }
    
    if (!is_writable($scriptsDir)) {
        return "ERROR: Scripts directory is not writable. Check permissions.";
    }

    $filePath = $scriptsDir . 'exec_' . uniqid() . '.py';
    // إضافة سطر الترميز لدعم اللغة العربية في المخرجات
    file_put_contents($filePath, "# -*- coding: utf-8 -*-\n" . $code);

    // التحقق من وجود البيئة الافتراضية أو استخدام بايثون النظام
    $pythonCmd = file_exists($venvPython) ? $venvPython : "python3";
    $venvBin = dirname($pythonCmd);

    // تحسين الأمر لضمان استخدام ترميز UTF-8 في المخرجات والمدخلات
    $command = "export PATH='$venvBin:\$PATH' && export PYTHONIOENCODING=utf-8 && $pythonCmd $filePath 2>&1";
    $output = shell_exec($command);

    if (file_exists($filePath)) unlink($filePath);
    
    return ($output !== null && $output !== "") ? $output : "Execution completed with no output.";
}

echo json_encode(['output' => executePython($code)]);
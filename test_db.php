<?php
/**
 * PROJECT: CyberFlux - DATABASE_CONNECTION_TESTER
 * ENGINEER: NAWAF_ROOT
 * PURPOSE: Test PostgreSQL connection independently.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "PHP_VERSION: " . PHP_VERSION . "\n";

echo "--- DEBUG INFO ---\n";
echo "Available PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "\n";
echo "------------------\n\n";

echo "Attempting to connect to MySQL...\n";

require_once 'config/database.php'; // تضمين ملف الاتصال بقاعدة البيانات

try {
    // محاولة تنفيذ استعلام بسيط للتأكد من أن الاتصال يعمل
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "SUCCESS: Connected to MySQL successfully!\n";
    echo "Database Version: " . $version . "\n";
} catch (PDOException $e) {
    echo "FAILURE: Could not connect to MySQL.\n";
    echo "Error: " . $e->getMessage() . "\n";
}
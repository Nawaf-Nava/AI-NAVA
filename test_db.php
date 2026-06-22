<?php
/**
 * PROJECT: NAVA AI - ADVANCED DATABASE CONNECTION TESTER
 * ENGINEER: NAWAF_ROOT
 * PURPOSE: Test MySQL connection with detailed diagnostics for Railway.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

echo "<pre style='background-color: #0d1117; color: #c9d1d9; padding: 20px; border-radius: 10px; font-family: monospace; line-height: 1.6;'>";
echo "<h2 style='color: #00f3ff;'>NAVA AI - Database Connection Diagnostics</h2>";

echo "PHP Version: " . PHP_VERSION . "\n";

echo "\n<strong style='color: #ffcc00;'>--- [1] System Checks ---</strong>\n";
$drivers = PDO::getAvailableDrivers();
echo "Available PDO Drivers: " . implode(", ", $drivers) . "\n";
if (in_array('mysql', $drivers)) {
    echo "<span style='color: #00ff88;'>[+] pdo_mysql extension is loaded. OK.</span>\n";
} else {
    echo "<span style='color: #ff3366;'>[-] CRITICAL: pdo_mysql extension is NOT loaded. Connection will fail.</span>\n";
    echo "    <span style='color: #ffcc00;'>SOLUTION: Ensure 'pdo_mysql' is in your nixpacks.toml or Dockerfile.</span>\n";
}

// --- [2] قراءة إعدادات الاتصال بنفس منطق config/database.php ---
$host = getenv('MYSQLHOST')     ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT')     ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'NAVADB';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '69y87fuworf';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

echo "\n<strong style='color: #ffcc00;'>--- [2] Connection Parameters ---</strong>\n";
echo "Host      : " . $host . (getenv('MYSQLHOST') ? " (from Railway ENV)" : " (local default)") . "\n";
echo "Port      : " . $port . (getenv('MYSQLPORT') ? " (from Railway ENV)" : " (local default)") . "\n";
echo "Database  : " . $dbname . (getenv('MYSQLDATABASE') ? " (from Railway ENV)" : " (local default)") . "\n";
echo "User      : " . $user . (getenv('MYSQLUSER') ? " (from Railway ENV)" : " (local default)") . "\n";
echo "Password  : " . (getenv('MYSQLPASSWORD') ? "******** (from Railway ENV)" : "******** (local default)") . "\n";
echo "DSN String: " . $dsn . "\n";

echo "\n<strong style='color: #ffcc00;'>--- [3] Connection Attempt ---</strong>\n";
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "<span style='color: #00ff88;'>[SUCCESS] Connected to MySQL successfully!</span>\n";
    echo "Database Version: " . $version . "\n\n";
    echo "<strong style='color: #00ff88;'>✅ All checks passed. Your application should be able to connect.</strong>\n";
} catch (PDOException $e) {
    echo "<span style='color: #ff3366;'>[FAILURE] Could not connect to the database.</span>\n";
    echo "<strong style='color: #ff3366;'>Error Details: " . htmlspecialchars($e->getMessage()) . "</strong>\n\n";
    echo "<strong style='color: #ffcc00;'>Possible Causes & Solutions:</strong>\n";
    echo "1. Incorrect credentials (Host, DB Name, User, Password) in Railway variables.\n";
    echo "2. The database service is not running or is still deploying.\n";
    echo "3. Network policies on Railway are blocking the connection between services.\n";
}
echo "</pre>";
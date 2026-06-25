<?php
/**
 * PROJECT: NAVA AI - ADVANCED DATABASE CONNECTION TESTER
 * DATABASE: PostgreSQL
 * PURPOSE: Test PostgreSQL connection with detailed diagnostics for Railway.
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
if (in_array('pgsql', $drivers)) {
    echo "<span style='color: #00ff88;'>[+] pdo_pgsql extension is loaded. OK.</span>\n";
} else {
    echo "<span style='color: #ff3366;'>[-] CRITICAL: pdo_pgsql extension is NOT loaded. Connection will fail.</span>\n";
    echo "    <span style='color: #ffcc00;'>SOLUTION: Ensure 'php-pgsql' is in your nixpacks.toml or Dockerfile.</span>\n";
}

echo "\n<strong style='color: #ffcc00;'>--- [2] Connection Parameters ---</strong>\n";
try {
    // --- [2] قراءة إعدادات الاتصال بنفس منطق config/database.php ---
    $db_url = getenv('DATABASE_URL');
    if ($db_url === false) {
        echo "DATABASE_URL: Not found. Using hardcoded fallback URL.\n";
        $db_url = "postgresql://postgres:JisCbmnkfhOBosZSMkpaEatkGxkRsYXO@thomas.proxy.rlwy.net:43959/railway";
    } else {
        echo "DATABASE_URL: Found. (Value is hidden for security)\n";
    }
    
    $parsed_url = parse_url($db_url);
    $host   = $parsed_url['host'];
    $port   = $parsed_url['port'];
    $dbname = ltrim($parsed_url['path'], '/'); 
    $user   = $parsed_url['user'];
    $pass   = $parsed_url['pass'];
    
    echo "Host      : " . $host . "\n";
    echo "Port      : " . $port . "\n";
    echo "Database  : " . $dbname . "\n";
    echo "User      : " . $user . "\n";
    echo "Password  : ********\n";
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    echo "DSN String: " . $dsn . "\n";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    echo "\n<strong style='color: #ffcc00;'>--- [3] Connection Attempt ---</strong>\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<span style='color: #00ff88;'>[SUCCESS] Connected to PostgreSQL successfully!</span>\n";
    echo "Database Version: " . $version . "\n\n";
    echo "<strong style='color: #00ff88;'>✅ All checks passed. Your application should be able to connect.</strong>\n";

} catch (PDOException $e) {
    echo "<span style='color: #ff3366;'>[FAILURE] Could not connect to the database.</span>\n";
    echo "<strong style='color: #ff3366;'>Error Details: " . htmlspecialchars($e->getMessage()) . "</strong>\n\n";
    echo "<strong style='color: #ffcc00;'>Possible Causes & Solutions:</strong>\n";
    echo "1. Incorrect 'DATABASE_URL' in Railway variables.\n";
    echo "2. The database service is not running or is still deploying.\n";
    echo "3. Network policies on Railway are blocking the connection between services.\n";
}
echo "</pre>";
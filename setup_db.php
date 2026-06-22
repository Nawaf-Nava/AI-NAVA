<?php
/**
 * NAVA AI - DATABASE_INITIALIZER
 * يقوم هذا السكربت بإنشاء قاعدة البيانات والجداول المطلوبة للنظام.
 */

require_once 'config/database.php';

echo "--- [ NAVA AI DB SETUP ] ---\n";

try {
    // 1. إنشاء جدول المستخدمين
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        user_id VARCHAR(20) PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        bio TEXT,
        profile_pic VARCHAR(255) DEFAULT 'default-avatar.png',
        access_level ENUM('ROOT', 'USER') DEFAULT 'USER',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql_users);
    echo "[+] Table 'users' created or already exists.\n";

    // 2. إنشاء جدول الجلسات (Sessions)
    $sql_sessions = "CREATE TABLE IF NOT EXISTS sessions (
        session_uuid VARCHAR(100) PRIMARY KEY,
        user_id VARCHAR(20),
        title VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql_sessions);
    echo "[+] Table 'sessions' created or already exists.\n";

    // 3. إنشاء جدول الرسائل (Messages)
    $sql_messages = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_uuid VARCHAR(100),
        role ENUM('user', 'model') NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_uuid) REFERENCES sessions(session_uuid) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql_messages);
    echo "[+] Table 'messages' created or already exists.\n";

    echo "\n[SUCCESS] قاعدة البيانات جاهزة للعمل بنسبة 100%.\n";
    echo "يمكنك الآن البدء بالتسجيل عبر register.php\n";

} catch (PDOException $e) {
    echo "\n[ERROR] فشل إعداد قاعدة البيانات: " . $e->getMessage() . "\n";
    echo "تأكد من وجود قاعدة بيانات باسم 'navadb' أو صلاحيات المستخدم.\n";
}
?>
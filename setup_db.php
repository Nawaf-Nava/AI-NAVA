<?php
/**
 * NAVA AI - DATABASE_INITIALIZER (Web Interface)
 * يقوم هذا السكربت بإنشاء قاعدة البيانات والجداول المطلوبة للنظام عبر المتصفح.
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVA AI - Database Setup</title>
    <link rel="icon" type="image/png" href="images/ooo.png">
    <link rel="stylesheet" href="assets/vendor/fonts/bunny-fonts.css">
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
            font-family: 'IBM Plex Sans Arabic', monospace;
            line-height: 1.7;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #161b22;
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        h1 {
            color: #00f3ff;
            text-align: center;
            margin-bottom: 25px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            letter-spacing: 2px;
        }
        pre {
            background-color: #010409;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 0.9rem;
            border: 1px solid #222;
        }
        .success { color: #00ff88; }
        .error { color: #ff3366; }
        .warning { color: #ffcc00; margin-top: 20px; text-align: center; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>NAVA AI DB SETUP</h1>
        <pre><?php
            ob_start(); // Start output buffering

            try {
                require_once 'config/database.php';
                echo "<span class='success'>[+] Connection to Database successful.</span>\n";

                // 1. إنشاء جدول المستخدمين (users) - بصيغة MySQL
                $sql_users = "CREATE TABLE IF NOT EXISTS users (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                name VARCHAR(100) UNIQUE NOT NULL,
                                password VARCHAR(255) NOT NULL,
                                bio TEXT,
                                profile_pic VARCHAR(255) DEFAULT 'default-avatar.png',
                                access_level VARCHAR(50) DEFAULT 'USER' NOT NULL,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                              );";
                $pdo->exec($sql_users);
                echo "<span class='success'>[+] Table 'users' created or already exists.</span>\n";

                // 2. إنشاء جدول المحادثات (chats) - بصيغة MySQL
                $sql_chats = "CREATE TABLE IF NOT EXISTS chats (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                user_id INT NOT NULL,
                                title VARCHAR(255) DEFAULT 'محادثة جديدة',
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                              );";
                $pdo->exec($sql_chats);
                echo "<span class='success'>[+] Table 'chats' created or already exists.</span>\n";

                // 3. إنشاء جدول الرسائل (messages) - بصيغة MySQL
                $sql_messages = "CREATE TABLE IF NOT EXISTS messages (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    chat_id INT NOT NULL,
                                    sender_type VARCHAR(50) NOT NULL, -- 'user' أو 'ai'
                                    content TEXT NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
                                 );";
                $pdo->exec($sql_messages);
                echo "<span class='success'>[+] Table 'messages' created or already exists.</span>\n";

                echo "\n<span class='success'>[SUCCESS] قاعدة البيانات جاهزة للعمل بنسبة 100%.</span>\n";
                echo "يمكنك الآن البدء بالتسجيل عبر register.php\n";

            } catch (PDOException $e) {
                echo "\n<span class='error'>[ERROR] فشل إعداد قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</span>\n";
                echo "تأكد من صحة إعدادات الاتصال في 'config/database.php' وأن قاعدة البيانات موجودة.\n";
            }
            ob_end_flush(); // End buffering and output everything
        ?></pre>
        <p class="warning">⚠️ تحذير أمني: يرجى حذف هذا الملف (`setup_db.php`) من الخادم فور الانتهاء من الإعداد.</p>
    </div>
</body>
</html>

<?php
/**
 * PROJECT: CyberFlux v7.0 - FLAT-FILE INTERFACE
 * MODULE: User Profile & Control Panel
 * ENGINEER: NAWAF_ROOT
 */

session_start();

// تضمين ملف الاتصال بقاعدة البيانات
require_once 'config/database.php';

// التحقق من حالة تسجيل الدخول
$is_logged_in = isset($_SESSION['user_id']);
$user_data = [];

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    // جلب البيانات المحدثة من قاعدة البيانات
    $stmt = $pdo->prepare("SELECT user_id, username, bio, profile_pic, access_level, created_at FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        // في حال عدم وجود المستخدم في قاعدة البيانات
        session_destroy();
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

$username = $user_data['username'];
$bio = $user_data['bio'];
$profile_pic = $user_data['profile_pic'];

// توليد CSRF Token لكل طلب، أو التحقق مما إذا كان موجودًا في الجلسة
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>CyberFlux | Profile: <?php echo htmlspecialchars($username); ?></title>

    <!-- Favicon & Identity Icons -->
    <link rel="icon" type="image/png" href="images/ooo.png">
    <link rel="apple-touch-icon" href="images/ooo.png">
    
    <!-- Core Icon and Font Libraries -->
    <!-- المكتبات المحلية -->
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/bunny-fonts.css">

    <script src="Script.js" defer></script>
    <link rel="stylesheet" href="Style.css">  

    <style>
        body { background: var(--void-bg); margin: 0; padding-bottom: 50px; overflow-x: hidden; }
        .profile-container { 
            max-width: 800px; 
            margin: 80px auto 40px; 
            padding: 20px; 
            position: relative;
            z-index: 10;
        }

        .profile-header { 
            background: rgba(13, 17, 23, 0.98); /* تعتيم كامل للخلفية لمنع التشويش */
            border: 1px solid var(--border-color); 
            padding: 40px; 
            border-radius: var(--radius-md); 
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.8);
            backdrop-filter: none; /* إلغاء الضباب في الهيدر */
        }

        .avatar-frame {
            width: 120px; 
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--cyber-cyan);
            margin: 0 auto 20px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.8);
        }

        .avatar-frame img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 20px; 
            margin-top: 30px; 
        }

        .stat-card { 
            background: #161b22; /* لون صلب وواضح */
            padding: 15px; 
            border-radius: 10px; 
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--cyber-cyan); 
            transition: 0.3s;
        }
        
        .stat-card:hover {
            background: rgba(0, 243, 255, 0.1);
        }

        .btn-logout { 
            background: #ff3131; 
            color: white; 
            border: none; 
            padding: 10px 25px; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            margin-top: 20px;
            transition: 0.3s;
        }
        
        .btn-logout:hover {
            background: #c51616;
            box-shadow: 0 0 10px rgba(255, 49, 49, 0.5);
        }

        .history-list { 
            margin-top: 40px; 
        }

        .session-item { 
            background: var(--card-bg); 
            margin-bottom: 10px; 
            padding: 15px; 
            border-radius: 8px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-right: 1px solid var(--cyber-cyan);
        }
        
        .session-item:hover {
            background: rgba(255,255,255,0.08);
        }

        @media (max-width: 600px) {
            .profile-container { margin: 15px auto; padding: 10px; }
            .profile-header { padding: 25px 15px; }
            .avatar-frame { width: 95px; height: 95px; }
            h1 { font-size: 1.6rem; }
            .stats-grid { grid-template-columns: 1fr; gap: 12px; }
            .session-item { flex-direction: column; align-items: flex-start; gap: 5px; }
        }
    </style>
</head>
<body>
    <div class="space-engine"></div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="avatar-frame">
                <?php
                $upload_path = 'images/uploads/' . $profile_pic;
                if ($profile_pic !== 'default-avatar.png' && file_exists(__DIR__ . '/' . $upload_path)) {
                    $display_pic = $upload_path;
                } else {
                    $display_pic = 'images/default-avatar.png';
                }
                ?>
                <img src="<?php echo $display_pic; ?>" alt="Profile">
            </div>
            <h1 style="color: var(--cyber-cyan); margin: 0; font-size: 2rem; /* تم تقليل حجم الخط */"><?php echo htmlspecialchars($username); ?></h1>
            <p style="color: #888; font-family: 'Fira Code', monospace;">[ NODE_ID: <?php echo htmlspecialchars($user_id); ?> ]</p>
            <div style="margin-top: 15px; font-style: italic; color: #eee;">"<?php echo htmlspecialchars($bio); ?>"</div>
            
            <!-- قسم تعديل الملف الشخصي -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
                <button onclick="document.getElementById('edit-form').style.display='block'; this.style.display='none'" 
                        style="background: transparent; border: 1px solid var(--cyber-cyan); color: var(--cyber-cyan); padding: 5px 15px; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                    <i class="fa-solid fa-user-pen"></i> EDIT_PROFILE_DATA
                </button>
                
                <form id="edit-form" action="update_profile.php" method="POST" enctype="multipart/form-data" style="display: none; text-align: right; margin-top: 20px;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--cyber-cyan); margin-bottom: 5px;">[ UPDATE_USERNAME ]</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" style="width: 100%; background: #000; border: 1px solid var(--border-color); color: white; padding: 10px; border-radius: 5px;" placeholder="اسم المستخدم الجديد">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--cyber-cyan); margin-bottom: 5px;">[ UPDATE_BIO ]</label>
                        <div style="display: flex; gap: 10px;">
                            <textarea name="bio" style="flex: 1; background: #000; border: 1px solid var(--border-color); color: white; padding: 10px; border-radius: 5px;" placeholder="اكتب سيرتك الذاتية هنا..."><?php echo htmlspecialchars($bio); ?></textarea>
                            <button type="submit" name="clear_field" value="bio" style="background: rgba(255,49,49,0.2); border: 1px solid var(--neon-red); color: var(--neon-red); border-radius: 5px; padding: 0 15px;" title="مسح السيرة الذاتية">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--cyber-cyan); margin-bottom: 5px;">[ CHANGE_AVATAR ]</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="file" name="profile_pic" accept="image/*" style="flex: 1; font-size: 0.8rem; color: #888;">
                            <button type="submit" name="clear_field" value="avatar" style="background: rgba(255,49,49,0.2); border: 1px solid var(--neon-red); color: var(--neon-red); border-radius: 5px; padding: 8px 15px;" title="إزالة الصورة واستخدام الافتراضية">
                                <i class="fa-solid fa-image-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px; border-top: 1px solid #222; padding-top: 15px;">
                        <label style="display: block; font-size: 0.8rem; color: var(--neon-gold); margin-bottom: 5px;">[ CHANGE_PASSWORD ]</label>
                        <input type="password" name="current_password" placeholder="كلمة المرور الحالية" style="width: 100%; background: #000; border: 1px solid var(--border-color); color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <input type="password" name="new_password" placeholder="كلمة المرور الجديدة" style="width: 100%; background: #000; border: 1px solid var(--border-color); color: white; padding: 10px; border-radius: 5px;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: var(--cyber-cyan); color: #000; border: none; padding: 8px 20px; border-radius: 5px; font-weight: bold; cursor: pointer;">SAVE_CHANGES</button>
                        <button type="button" onclick="location.reload()" style="background: transparent; border: 1px solid #666; color: #666; padding: 8px 20px; border-radius: 5px; cursor: pointer;">CANCEL</button>
                    </div>
                </form>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: center; gap: 20px;">
                <a href="logout.php" class="btn-logout" style="margin-top:0;">
                    <i class="fa-solid fa-right-from-bracket"></i> LOGOUT
                </a>
                <button onclick="if(confirm('⚠️ تنبيه أمني: هل أنت متأكد من حذف الحساب نهائياً؟ سيتم مسح جميع الجلسات والبيانات من قاعدة البيانات.')) window.location.href='delete_account.php';" 
                        style="background: rgba(255,49,49,0.1); border: 1px solid var(--neon-red); color: var(--neon-red); padding: 10px 25px; border-radius: 5px; cursor: pointer; transition: 0.3s; font-weight: bold;">
                    <i class="fa-solid fa-burst"></i> TERMINATE_ACCOUNT
                </button>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
                <script>
                    // This will be replaced by Utils.showNotification
                    // Utils.showNotification('تم تحديث الملف الشخصي بنجاح.', 'success');
                </script>
            <?php endif; ?>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div style="font-size: 0.8rem; color: #7f8c8d; font-family: 'Fira Code', monospace;">SECURITY_ROLE</div>
                <div style="font-weight: bold; color: var(--cyber-cyan);"><?php echo htmlspecialchars($user_data['access_level'] ?? 'USER'); ?></div>
            </div>
            <div class="stat-card">
                <div style="font-size: 0.8rem; color: #7f8c8d; font-family: 'Fira Code', monospace;">INITIALIZED_AT</div>
                <div style="font-weight: bold;"><?php echo htmlspecialchars($user_data['created_at'] ?? 'N/A'); ?></div>
            </div>
        </div>
        <div class="history-list">
            <h3 style="color: var(--cyber-cyan); border-bottom: 1px solid rgba(0, 243, 255, 0.2); padding-bottom: 10px; font-family: 'Fira Code', monospace;">
                RECENT_CHATS
            </h3>
            <?php
            // جلب جلسات المحادثة من قاعدة البيانات
            $stmt = $pdo->prepare("SELECT session_uuid, title, created_at FROM sessions WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->execute();
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($sessions)) {
                foreach ($sessions as $session) {
                    echo "<div class='session-item'>
                                    <span><i class='fa-regular fa-comment'></i> Session: " . htmlspecialchars($session['title'] ?? 'Untitled Session') . "</span>
                                    <small style=\"color:var(--cyber-cyan); font-family:'Fira Code', monospace;\">ACTIVE</small>
                                  </div>";
                }
            } else {
                echo "<p style='text-align:center; color:#444; font-family:'Fira Code', monospace;'>[ NO_SESSIONS_FOUND ]</p>";
            }
            ?>
        </div>
    </div>
    <div id="notification-zone"></div>
    <script>
        // This script block will run after Script.js is loaded (due to defer)
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            const type = urlParams.get('type');

            if (msg && type) {
                if (typeof Utils !== 'undefined' && typeof Utils.showNotification === 'function') {
                    Utils.showNotification(msg, type);
                    history.replaceState({}, document.title, window.location.pathname); // Clean URL
                }
            }
        });
    </script>
</body>
</html>
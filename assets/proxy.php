<?php
/**
 * NAVA_PROXY_GATEWAY v1.0
 * ENGINEER: NAWAF_ROOT
 * PURPOSE: BYPASS GEO-BLOCKING FOR ASSETS
 */

if (!isset($_GET['file'])) {
    die("[ACCESS_DENIED]: No target specified.");
}

$file_type = $_GET['file'];
$base_url = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/";

// تحديد المسار ونوع المحتوى بناءً على الطلب
if ($file_type === 'css') {
    $target_url = $base_url . "css/all.min.css";
    header("Content-Type: text/css");
} elseif ($file_type === 'font') {
    $font_name = $_GET['name'] ?? 'fa-solid-900.woff2';
    $target_url = $base_url . "webfonts/" . $font_name;
    header("Content-Type: font/woff2");
} else {
    die("[ERROR]: Invalid file type.");
}

// جلب المحتوى من السيرفر العالمي وإرساله للمستخدم
$content = file_get_contents($target_url);

// إذا كان الملف CSS، نحتاج لتعديل روابط الخطوط بداخله لتمر عبر الوكيل أيضاً
if ($file_type === 'css') {
    $proxy_path = "assets/proxy.php?file=font&name=";
    $content = str_replace("../webfonts/", $proxy_path, $content);
}

echo $content;
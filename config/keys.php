<?php
/**
 * PROJECT: NAVA AI - API KEYS & SECRETS
 * LOCATION: /config/keys.php
 * SECURITY: This file should be added to .gitignore and managed via environment variables in production.
 */

// مفتاح الـ API الخاص بنواف - ⚠️ استبدله بمفتاح صالح أو استخدم متغير بيئة
$normal_gemini_api_key = getenv('GEMINI_API_KEY') ?: ""; // القيمة الافتراضية فارغة لفرض استخدام متغيرات البيئة
$deep_mode_gemini_api_key = getenv('DEEP_MODE_GEMINI_API_KEY') ?: ""; // القيمة الافتراضية فارغة

// --- [إعدادات محرك البحث المستقل] ---
$google_search_api_key = getenv('GOOGLE_SEARCH_API_KEY') ?: ""; // القيمة الافتراضية فارغة
$google_search_cx = getenv('GOOGLE_SEARCH_CX') ?: ""; // القيمة الافتراضية فارغة

?>

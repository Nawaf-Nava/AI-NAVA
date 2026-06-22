<?php
/**
 * PROJECT: NAVA AI - API KEYS & SECRETS
 * LOCATION: /config/keys.php
 * SECURITY: This file should be added to .gitignore and managed via environment variables in production.
 */

// --- [Production & Railway Environment] ---
// قراءة المفاتيح بشكل آمن من متغيرات البيئة.
// استخدام 'false' كقيمة افتراضية لضمان عدم وجود مفاتيح وهمية في الكود.
$normal_gemini_api_key = getenv('GEMINI_API_KEY') ?: false;
$deep_mode_gemini_api_key = getenv('DEEP_MODE_GEMINI_API_KEY') ?: false;

// --- [إعدادات محرك البحث المستقل] ---
$google_search_api_key = getenv('GOOGLE_SEARCH_API_KEY') ?: false;
$google_search_cx = getenv('GOOGLE_SEARCH_CX') ?: false;

// --- [Security Check] ---
// التحقق من وجود المفتاح الأساسي على الأقل لمنع فشل التطبيق.
if ($normal_gemini_api_key === false) {
    // في بيئة الإنتاج، من الأفضل تسجيل هذا الخطأ بدلاً من إيقاف التنفيذ.
    error_log("CRITICAL: GEMINI_API_KEY is not set in the environment variables.");
}

?>
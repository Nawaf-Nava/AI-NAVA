<?php
/**
 * PROJECT: NAVA AI - API KEYS & SECRETS
 * LOCATION: /config/keys.php
 * SECURITY: This file should be added to .gitignore and managed via environment variables in production.
 */

// مفتاح الـ API الخاص بنواف - ⚠️ استبدله بمفتاح صالح أو استخدم متغير بيئة
$normal_gemini_api_key = getenv('GEMINI_API_KEY') ?: "AIzaSyAmQK7bPBru3h3W_Xl5Kw3-cGavkbWXNLQ";
$deep_mode_gemini_api_key = getenv('DEEP_MODE_GEMINI_API_KEY') ?: "AQ.Ab8RN6IdpQ-EYTAl64m_TJejbC0iYTW-gjqu8nJoDOz7QfpoQg"; // مفتاح وضع الهكر الخاص بنواف

// --- [إعدادات محرك البحث المستقل] ---
$google_search_api_key = getenv('GOOGLE_SEARCH_API_KEY') ?: "AIzaSyCuvVc2YNdZN8r7jlflNScQTsSl3G8iGu4"; // مفتاح الـ API الذي حصلت عليه
$google_search_cx = getenv('GOOGLE_SEARCH_CX') ?: "51bbe2743dd174106"; // معرف محرك البحث المخصص (CX ID)

?>
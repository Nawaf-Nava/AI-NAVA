<?php
/**
 * PROJECT: NAVA AI - Google Custom Search API Tester
 * ENGINEER: NAWAF_ROOT
 * PURPOSE: Test Google Custom Search API Key and CX ID independently.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "--- [ Google Custom Search API Tester ] ---\n\n";

// ⚠️ استبدل هذه القيم بمفاتيحك الحقيقية
$apiKey = "AIzaSyCuvVc2YNdZN8r7jlflNScQTsSl3G8iGu4"; // مفتاح الـ API الذي حصلت عليه
$cx = "51bbe2743dd174106"; // معرف محرك البحث المخصص (CX ID) الصحيح
$testQuery = "موقع نواف طسطاس"; // استعلام اختباري

echo "[CHECK] API Key Format: " . (strpos($apiKey, 'AIzaSy') === 0 ? "OK ✅" : "WRONG ❌ (Must start with AIzaSy)") . "\n";
echo "[CHECK] CX ID Config: " . (strpos($cx, 'ضع_') === false ? "OK ✅" : "MISSING ❌ (Please replace the placeholder)") . "\n";
echo "Test Query: \"$testQuery\"\n\n";

if (strpos($cx, 'ضع_') !== false) {
    echo "ERROR: Please replace 'ضع_معرف_CX_هنا' with your actual CX ID.\n";
    echo "       Also ensure your API Key is correctly set.\n";
    exit;
}

$url = "https://www.googleapis.com/customsearch/v1?key=" . $apiKey . "&cx=" . $cx . "&q=" . urlencode($testQuery);

echo "Fetching URL: $url\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 10 // مهلة أطول للاختبار
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "--- [ Diagnostic Result ] ---\n";
echo "HTTP Status Code: $httpCode\n";

if ($error) {
    echo "CRITICAL: Connection Error: $error\n";
    exit;
}

$data = json_decode($response, true);

if ($httpCode === 200) {
    if (isset($data['items'])) {
        echo "SUCCESS: Connection established and results found! ✅\n";
        echo "Total Results Found: " . $data['searchInformation']['totalResults'] . "\n\n";
        foreach ($data['items'] as $index => $item) {
            echo ($index + 1) . ". " . $item['title'] . "\n   URL: " . $item['link'] . "\n\n";
        }
    } else {
        echo "WARNING: Connection OK, but NO RESULTS found. ⚠️\n";
        echo "SUGGESTION: Go to Google PSE settings and enable 'Search the entire web'.\n";
    }
} else {
    echo "FAILURE: Google API returned an error. ❌\n";
    if (isset($data['error'])) {
        $reason = $data['error']['message'];
        $status = $data['error']['status'];
        echo "Error Type: $status\n";
        echo "Message: $reason\n";
        
        if ($status === "PERMISSION_DENIED") {
            echo "SUGGESTION: Your API Key might be invalid or Restricted. Check Google Cloud Console.\n";
        } elseif ($status === "RESOURCE_EXHAUSTED") {
            echo "SUGGESTION: You have reached your daily free limit (100 searches/day).\n";
        }
    }
}
echo "\n--- [ Test Complete ] ---\n";
?>
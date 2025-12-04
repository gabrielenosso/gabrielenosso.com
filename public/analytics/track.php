<?php
header('HTTP/1.1 204 No Content');
header('Content-Length: 0');
error_reporting(0);

$dir = __DIR__ . '/data';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$file = $dir . '/' . date('Y-m') . '.json';

// Get visitor IP (anonymized - last octet zeroed for privacy)
$rawIp = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
$ipParts = explode(',', $rawIp);
$realIp = trim($ipParts[0]);

// Anonymize for visitor ID: zero last octet
$parts = explode('.', $realIp);
$anonIp = (count($parts) == 4) ? $parts[0].'.'.$parts[1].'.'.$parts[2].'.0' : $realIp;
$vid = substr(md5($anonIp . date('Y-m')), 0, 8);

// Try to get country
$country = '?';
if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'];
} elseif (function_exists('geoip_country_code_by_name')) {
    $c = @geoip_country_code_by_name($realIp);
    if ($c) $country = $c;
} elseif (function_exists('curl_init')) {
    $ch = @curl_init("http://ip-api.com/json/$realIp?fields=countryCode");
    if ($ch) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $json = @curl_exec($ch);
        @curl_close($ch);
        if ($json) {
            $r = @json_decode($json, true);
            if ($r && isset($r['countryCode'])) $country = $r['countryCode'];
        }
    }
}

$data = array(
    't' => date('d H:i'),
    'p' => isset($_POST['page']) ? $_POST['page'] : '/',
    'c' => $country,
    'l' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2) : '?',
    'v' => $vid
);

@file_put_contents($file, json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
exit;

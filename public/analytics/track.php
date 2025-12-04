<?php
$dir = __DIR__ . '/data';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$file = $dir . '/' . date('Y-m') . '.json';

// Get visitor IP (anonymized - last octet zeroed for privacy)
$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR'];
$ip = trim($ip);
// Anonymize: zero last octet (192.168.1.100 -> 192.168.1.0)
$parts = explode('.', $ip);
if (count($parts) == 4) {
    $parts[3] = '0';
    $ip = implode('.', $parts);
}
$vid = substr(md5($ip . date('Y-m')), 0, 8); // visitor ID (hashed, resets monthly)

// Try to get country from GeoIP if available
$country = '?';
if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
    $country = $_SERVER['HTTP_CF_IPCOUNTRY'];
} elseif (function_exists('geoip_country_code_by_name')) {
    $c = @geoip_country_code_by_name($ip);
    if ($c) $country = $c;
}

$data = array(
    't' => date('d H:i'),
    'p' => isset($_POST['page']) ? $_POST['page'] : '/',
    'c' => $country,
    'l' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2) : '?',
    'v' => $vid
);

file_put_contents($file, json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
http_response_code(204);

<?php
$dir = __DIR__ . '/data';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$file = $dir . '/' . date('Y-m') . '.json';

$data = array(
    't' => date('d H:i'),
    'p' => isset($_POST['page']) ? $_POST['page'] : '/',
    'c' => isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $_SERVER['HTTP_CF_IPCOUNTRY'] : '?',
    'l' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2) : '?'
);

file_put_contents($file, json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
http_response_code(204);

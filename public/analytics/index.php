<?php
$pass = 'art2025';
session_start();

if (isset($_POST['p']) && $_POST['p'] === $pass) $_SESSION['ok'] = 1;
if (isset($_GET['logout'])) unset($_SESSION['ok']);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Analytics</title>
<style>
body { font-family: system-ui, sans-serif; background: #0a1628; color: #e0e0e0; padding: 2rem; max-width: 800px; margin: 0 auto; }
h1 { color: #d4af37; }
h2 { color: #6b9bd1; border-bottom: 1px solid #333; padding-bottom: 0.5rem; }
a { color: #6b9bd1; }
form { text-align: center; margin-top: 100px; }
input { padding: 0.75rem; border: 1px solid #6b9bd1; background: #162337; color: #fff; border-radius: 4px; }
button { padding: 0.75rem 1.5rem; background: #d4af37; color: #0a1628; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.stat { display: inline-block; background: #162337; padding: 0.5rem 1rem; margin: 0.25rem; border-radius: 4px; }
.stat b { color: #d4af37; }
details { background: #162337; padding: 1rem; border-radius: 4px; margin: 1rem 0; }
summary { cursor: pointer; color: #6b9bd1; }
ul { margin: 1rem 0; padding-left: 1.5rem; }
li { margin: 0.25rem 0; font-size: 0.9rem; color: #999; }
.total { font-size: 1.5rem; color: #d4af37; margin-top: 2rem; }
</style>
</head>
<body>
<?php
if (!isset($_SESSION['ok'])) {
    echo '<form method="post"><input name="p" type="password" placeholder="Password"><button>Login</button></form></body></html>';
    exit;
}

$dir = __DIR__ . '/data';
$files = glob($dir . '/*.json');
rsort($files);

echo '<h1>🎨 Analytics</h1><a href="?logout=1">Logout</a>';

$total = 0;
foreach ($files as $file) {
    $month = basename($file, '.json');
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $count = count($lines);
    $total += $count;
    
    // Count countries and languages
    $countries = array();
    $languages = array();
    $pages = array();
    $entries = array();
    foreach ($lines as $line) {
        $d = json_decode($line, true);
        if (!$d) continue;
        $entries[] = $d;
        $c = isset($d['c']) ? $d['c'] : '?';
        $l = isset($d['l']) ? $d['l'] : '?';
        $pg = isset($d['p']) ? $d['p'] : '/';
        $countries[$c] = isset($countries[$c]) ? $countries[$c] + 1 : 1;
        $languages[$l] = isset($languages[$l]) ? $languages[$l] + 1 : 1;
        $pages[$pg] = isset($pages[$pg]) ? $pages[$pg] + 1 : 1;
    }
    arsort($countries);
    arsort($languages);
    arsort($pages);
    
    echo "<h2>📅 $month <span style='color:#d4af37'>($count views)</span></h2>";
    
    echo '<p>🌍 <b>Countries:</b> ';
    foreach (array_slice($countries, 0, 5, true) as $k => $v) echo "<span class='stat'><b>$v</b> $k</span>";
    echo '</p>';
    
    echo '<p>🗣️ <b>Languages:</b> ';
    foreach (array_slice($languages, 0, 5, true) as $k => $v) echo "<span class='stat'><b>$v</b> $k</span>";
    echo '</p>';
    
    echo '<p>📄 <b>Pages:</b> ';
    foreach (array_slice($pages, 0, 5, true) as $k => $v) echo "<span class='stat'><b>$v</b> $k</span>";
    echo '</p>';
    
    echo '<details><summary>📋 Last 20 visits</summary><ul>';
    foreach (array_reverse(array_slice($entries, -20)) as $d) {
        $time = isset($d['t']) ? $d['t'] : '';
        $page = isset($d['p']) ? $d['p'] : '';
        $country = isset($d['c']) ? $d['c'] : '?';
        $lang = isset($d['l']) ? $d['l'] : '?';
        echo '<li>' . htmlspecialchars("$time - $page [$country/$lang]") . '</li>';
    }
    echo '</ul></details>';
}

if ($total == 0) {
    echo '<p style="color:#888">No data yet. Visit the site to start tracking!</p>';
}
echo "<p class='total'>📊 Total: $total views</p>";
?>
</body>
</html>

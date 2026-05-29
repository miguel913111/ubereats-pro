<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

$host = '127.0.0.1';
$db   = 'multi_food_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "DB Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$stmt = $pdo->query("SELECT id, `value` FROM translations WHERE locale = 'pt'");
$rows = $stmt->fetchAll();

echo "Total pt rows to translate: " . count($rows) . PHP_EOL;

$updateStmt = $pdo->prepare("UPDATE translations SET `value` = ? WHERE id = ?");
$translated = 0;
$failed = 0;

foreach ($rows as $idx => $row) {
    $id = $row['id'];
    $value = $row['value'];
    
    $cleanValue = trim($value);
    if ($cleanValue === '' || is_numeric($cleanValue)) {
        continue;
    }
    
    $encoded = urlencode($cleanValue);
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=pt&dt=t&q={$encoded}";
    
    $attempt = 0;
    $success = false;
    $ptValue = null;
    
    while ($attempt < 3 && !$success) {
        $response = @file_get_contents($url);
        if ($response !== false) {
            $json = json_decode($response, true);
            if (isset($json[0][0][0])) {
                $ptValue = $json[0][0][0];
                $success = true;
            }
        }
        if (!$success) {
            $attempt++;
            sleep(2);
        }
    }
    
    if ($success && $ptValue !== null) {
        $updateStmt->execute([$ptValue, $id]);
        $translated++;
    } else {
        $failed++;
    }
    
    if (($idx + 1) % 10 === 0) {
        echo "Progress: " . ($idx + 1) . "/" . count($rows) . " (translated: {$translated}, failed: {$failed})\n";
    }
    
    usleep(100000);
}

echo "=== DONE ===\n";
echo "Translated: {$translated}\n";
echo "Failed: {$failed}\n";

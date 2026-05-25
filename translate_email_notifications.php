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

function translateText($text) {
    if (trim($text) === '' || is_numeric($text)) {
        return $text;
    }
    
    // Protect placeholders like {userName}, {orderId}, etc.
    $placeholders = [];
    $counter = 0;
    $protected = preg_replace_callback('/\{[^}]+\}/', function($matches) use (&$placeholders, &$counter) {
        $key = "___PH{$counter}___";
        $placeholders[$key] = $matches[0];
        $counter++;
        return $key;
    }, $text);
    
    $encoded = urlencode($protected);
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
        // Restore placeholders
        foreach ($placeholders as $key => $original) {
            $ptValue = str_replace($key, $original, $ptValue);
        }
        return $ptValue;
    }
    
    return $text;
}

// Translate email_templates
$stmt = $pdo->query("SELECT id, title, body, body_2, button_name, footer_text, copyright_text FROM email_templates");
$emails = $stmt->fetchAll();

echo "Email templates to translate: " . count($emails) . PHP_EOL;

$updateEmail = $pdo->prepare("UPDATE email_templates SET title = ?, body = ?, body_2 = ?, button_name = ?, footer_text = ?, copyright_text = ? WHERE id = ?");
$translated = 0;

foreach ($emails as $idx => $row) {
    $title = translateText($row['title']);
    $body = translateText($row['body']);
    $body2 = translateText($row['body_2']);
    $button = translateText($row['button_name']);
    $footer = translateText($row['footer_text']);
    $copyright = translateText($row['copyright_text']);
    
    $updateEmail->execute([$title, $body, $body2, $button, $footer, $copyright, $row['id']]);
    $translated++;
    
    if (($idx + 1) % 5 === 0) {
        echo "Email progress: " . ($idx + 1) . "/" . count($emails) . PHP_EOL;
    }
    usleep(100000);
}

echo "Emails translated: {$translated}" . PHP_EOL;

// Translate notification_messages
$stmt2 = $pdo->query("SELECT id, message FROM notification_messages");
$notifs = $stmt2->fetchAll();

echo "Notification messages to translate: " . count($notifs) . PHP_EOL;

$updateNotif = $pdo->prepare("UPDATE notification_messages SET message = ? WHERE id = ?");
$translated2 = 0;

foreach ($notifs as $idx => $row) {
    $message = translateText($row['message']);
    $updateNotif->execute([$message, $row['id']]);
    $translated2++;
    
    if (($idx + 1) % 10 === 0) {
        echo "Notif progress: " . ($idx + 1) . "/" . count($notifs) . PHP_EOL;
    }
    usleep(100000);
}

echo "Notifications translated: {$translated2}" . PHP_EOL;
echo "=== ALL DONE ===" . PHP_EOL;

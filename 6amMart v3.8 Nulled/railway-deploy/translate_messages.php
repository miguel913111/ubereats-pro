<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

$inputFile = __DIR__ . '/resources/lang/en/messages.php';
$outputFile = __DIR__ . '/resources/lang/pt/messages.php';
$cacheFile = __DIR__ . '/translate_cache.json';
$logFile = __DIR__ . '/translate_log.txt';

function logMsg($msg) {
    global $logFile;
    $line = date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

logMsg("Starting translation...");

$english = include $inputFile;
if (!is_array($english)) {
    logMsg("ERROR: Could not load messages.php");
    exit(1);
}

$translated = [];
$translatedCount = 0;
$skippedCount = 0;
$failedKeys = [];

if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true);
    if (is_array($cache)) {
        $translated = $cache;
        $translatedCount = count(array_filter($translated, fn($v, $k) => $v !== $english[$k] && trim($v) !== '', ARRAY_FILTER_USE_BOTH));
        logMsg("Resuming from cache: " . count($translated) . " already processed");
    }
}

$total = count($english);
$idx = 0;

foreach ($english as $key => $value) {
    $idx++;
    
    if (isset($translated[$key])) {
        continue;
    }
    
    $cleanValue = trim((string)$value);
    if ($cleanValue === '' || is_numeric($cleanValue)) {
        $translated[$key] = $value;
        $skippedCount++;
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
        $translated[$key] = $ptValue;
        $translatedCount++;
    } else {
        $translated[$key] = $value;
        $failedKeys[] = $key;
    }
    
    if ($idx % 50 === 0) {
        file_put_contents($cacheFile, json_encode($translated, JSON_UNESCAPED_UNICODE));
        logMsg("Progress: {$idx}/{$total} (translated: {$translatedCount}, skipped: {$skippedCount}, failed: " . count($failedKeys) . ")");
    }
    
    usleep(50000);
}

file_put_contents($cacheFile, json_encode($translated, JSON_UNESCAPED_UNICODE));

$output = "<?php\n\nreturn " . var_export($translated, true) . ";\n";
file_put_contents($outputFile, $output);

logMsg("=== DONE ===");
logMsg("Total keys: {$total}");
logMsg("Translated: {$translatedCount}");
logMsg("Skipped: {$skippedCount}");
logMsg("Failed: " . count($failedKeys));
logMsg("Output: {$outputFile}");

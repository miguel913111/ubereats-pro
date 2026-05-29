<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

$part = (int)($argv[1] ?? 1);
$totalParts = 3;

$inputFile = __DIR__ . '/resources/lang/en/messages.php';
$cacheFile = __DIR__ . '/translate_cache_part' . $part . '.json';
$logFile = __DIR__ . '/translate_part' . $part . '_log.txt';

function logMsg($msg, $logFile) {
    $line = date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

logMsg("Starting part {$part}/{$totalParts}", $logFile);

$english = include $inputFile;
$keys = array_keys($english);
$total = count($keys);

$perPart = ceil($total / $totalParts);
$start = ($part - 1) * $perPart;
$end = min($start + $perPart, $total);

logMsg("Range: {$start} - {$end} (total keys: {$total})", $logFile);

$translated = [];
if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true);
    if (is_array($cache)) {
        $translated = $cache;
        logMsg("Resuming from cache: " . count($translated) . " already processed", $logFile);
    }
}

$translatedCount = 0;
$skippedCount = 0;
$failedKeys = [];

for ($i = $start; $i < $end; $i++) {
    $key = $keys[$i];
    
    if (isset($translated[$key])) {
        continue;
    }
    
    $value = $english[$key];
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
    
    if (($i - $start) % 50 === 0) {
        file_put_contents($cacheFile, json_encode($translated, JSON_UNESCAPED_UNICODE));
        logMsg("Progress part{$part}: " . ($i - $start) . "/" . ($end - $start) . " (translated: {$translatedCount}, skipped: {$skippedCount}, failed: " . count($failedKeys) . ")", $logFile);
    }
    
    usleep(50000);
}

file_put_contents($cacheFile, json_encode($translated, JSON_UNESCAPED_UNICODE));

logMsg("=== PART {$part} DONE ===", $logFile);
logMsg("Translated: {$translatedCount}", $logFile);
logMsg("Skipped: {$skippedCount}", $logFile);
logMsg("Failed: " . count($failedKeys), $logFile);

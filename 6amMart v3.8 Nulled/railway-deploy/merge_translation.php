<?php
// Script to merge 3 translation cache files into pt/messages.php

$basePath = __DIR__ . '/resources/lang/';
$enFile = $basePath . 'en/messages.php';
$ptFile = $basePath . 'pt/messages.php';

$cacheFiles = [
    __DIR__ . '/translate_cache_part1.json',
    __DIR__ . '/translate_cache_part2.json',
    __DIR__ . '/translate_cache_part3.json',
];

// Load all translations from caches
$translations = [];
foreach ($cacheFiles as $file) {
    if (!file_exists($file)) {
        echo "Cache file not found: $file\n";
        continue;
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON decode error in $file: " . json_last_error_msg() . "\n";
        continue;
    }
    $translations = array_merge($translations, $data);
    echo "Loaded " . count($data) . " translations from " . basename($file) . "\n";
}

echo "Total unique translations: " . count($translations) . "\n";

// Load original English messages
if (!file_exists($enFile)) {
    die("English messages file not found: $enFile\n");
}
$messages = require $enFile;

if (!is_array($messages)) {
    die("Failed to load English messages array\n");
}

$totalKeys = count($messages);
$translatedCount = 0;
$skippedCount = 0;

foreach ($messages as $key => $value) {
    if (isset($translations[$key])) {
        $messages[$key] = $translations[$key];
        $translatedCount++;
    } else {
        $skippedCount++;
    }
}

echo "Keys in en/messages.php: $totalKeys\n";
echo "Translated keys: $translatedCount\n";
echo "Skipped keys (not in cache): $skippedCount\n";

// Build PHP array export manually to preserve order and formatting
function buildPhpArray($array, $indent = 0) {
    $result = "[\n";
    foreach ($array as $key => $value) {
        $escapedKey = var_export((string)$key, true);
        if (is_array($value)) {
            $result .= str_repeat("    ", $indent + 1) . "$escapedKey => " . buildPhpArray($value, $indent + 1) . ",\n";
        } else {
            $escapedValue = var_export((string)$value, true);
            $result .= str_repeat("    ", $indent + 1) . "$escapedKey => $escapedValue,\n";
        }
    }
    $result .= str_repeat("    ", $indent) . "]";
    return $result;
}

$phpContent = "<?php\nreturn " . buildPhpArray($messages, 0) . ";\n";

// Write to pt/messages.php
if (!is_dir($basePath . 'pt')) {
    mkdir($basePath . 'pt', 0755, true);
}

file_put_contents($ptFile, $phpContent);
echo "Successfully wrote pt/messages.php (" . strlen($phpContent) . " bytes)\n";

// Verify
$ptMessages = require $ptFile;
echo "Verification: pt/messages.php loaded with " . count($ptMessages) . " keys\n";

// Show some samples
$samples = array_slice($ptMessages, 0, 5, true);
echo "\nSample translations:\n";
foreach ($samples as $k => $v) {
    echo "  $k => $v\n";
}

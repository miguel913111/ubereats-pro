<?php
$cacheFiles = [
    __DIR__ . '/translate_cache_part1.json',
    __DIR__ . '/translate_cache_part2.json',
    __DIR__ . '/translate_cache_part3.json',
];

$translations = [];
foreach ($cacheFiles as $file) {
    $data = json_decode(file_get_contents($file), true);
    $translations = array_merge($translations, $data);
}

$messages = require __DIR__ . '/resources/lang/en/messages.php';

$skipped = [];
foreach ($messages as $key => $value) {
    if (!isset($translations[$key])) {
        $skipped[$key] = $value;
    }
}

echo "Skipped keys (" . count($skipped) . "):\n";
foreach ($skipped as $k => $v) {
    echo "  [$k] => " . json_encode($v) . "\n";
}

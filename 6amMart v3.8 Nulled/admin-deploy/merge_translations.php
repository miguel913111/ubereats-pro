<?php
$inputFile = __DIR__ . '/resources/lang/en/messages.php';
$cacheFiles = [
    __DIR__ . '/translate_cache_part1.json',
    __DIR__ . '/translate_cache_part2.json',
    __DIR__ . '/translate_cache_part3.json',
];
$outputFile = __DIR__ . '/resources/lang/pt/messages.php';

$english = include $inputFile;
$merged = [];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        $cache = json_decode(file_get_contents($file), true);
        if (is_array($cache)) {
            $merged = array_merge($merged, $cache);
            echo "Loaded " . count($cache) . " entries from " . basename($file) . PHP_EOL;
        }
    } else {
        echo "Warning: " . basename($file) . " not found" . PHP_EOL;
    }
}

echo "Total merged entries: " . count($merged) . PHP_EOL;

// Fill missing keys with english fallback
$missing = 0;
foreach ($english as $key => $value) {
    if (!isset($merged[$key])) {
        $merged[$key] = $value;
        $missing++;
    }
}

echo "Missing keys filled with english: {$missing}" . PHP_EOL;
echo "Final total: " . count($merged) . PHP_EOL;

$output = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
file_put_contents($outputFile, $output);

echo "Output written to: {$outputFile}" . PHP_EOL;

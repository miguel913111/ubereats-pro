<?php
$baseFile = file_get_contents("database/migrations/0000_00_00_000000_create_base_tables.php");
$baseColumns = [];

preg_match_all("/Schema::create\('(\w+)'/", $baseFile, $tableMatches, PREG_OFFSET_CAPTURE);

echo "Found " . count($tableMatches[1]) . " tables\n";
foreach ($tableMatches[1] as $i => $match) {
    $tableName = $match[0];
    $start = $match[1];
    
    // Find the opening brace of the closure
    $bracePos = strpos($baseFile, '{', $start);
    if ($bracePos === false) continue;
    
    // Find the matching closing brace
    $depth = 0;
    $endPos = $bracePos;
    for ($j = $bracePos; $j < strlen($baseFile); $j++) {
        if ($baseFile[$j] == '{') $depth++;
        if ($baseFile[$j] == '}') {
            $depth--;
            if ($depth == 0) {
                $endPos = $j;
                break;
            }
        }
    }
    
    $body = substr($baseFile, $bracePos + 1, $endPos - $bracePos - 1);
    
    preg_match_all('/\$table->(\w+)\([\'\"]?(\w+)[\'\"]?/', $body, $cols);
    foreach ($cols[2] as $col) {
        $baseColumns[$tableName][$col] = true;
    }
}

echo "\nColumns:\n";
foreach ($baseColumns as $table => $cols) {
    echo "$table: " . implode(", ", array_keys($cols)) . "\n";
}

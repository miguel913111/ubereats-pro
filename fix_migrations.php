<?php
// Parse base migration to find which columns are created in each table
$baseFile = file_get_contents("database/migrations/0000_00_00_000000_create_base_tables.php");
$baseColumns = [];

preg_match_all("/Schema::create\('(\w+)'/", $baseFile, $tableMatches, PREG_OFFSET_CAPTURE);

foreach ($tableMatches[1] as $match) {
    $tableName = $match[0];
    $start = $match[1];
    
    $bracePos = strpos($baseFile, '{', $start);
    if ($bracePos === false) continue;
    
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

// Now fix each add-column migration
$files = glob("database/migrations/*add*.php");
$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!preg_match("/Schema::table\(['\"](\w+)['\"]/", $content, $m)) continue;
    $table = $m[1];
    if (!isset($baseColumns[$table])) continue;
    
    $lines = file($file);
    $newLines = [];
    $insideClosure = false;
    $wasModified = false;
    
    foreach ($lines as $line) {
        if (preg_match("/Schema::table\(['\"](\w+)['\"]/", $line, $m)) {
            $insideClosure = true;
            $newLines[] = $line;
            continue;
        }
        
        if ($insideClosure && preg_match('/^(\s+)\$table->(\w+)\([\'\"](\w+)[\'\"]/', $line, $m)) {
            $indent = $m[1];
            $type = $m[2];
            $col = $m[3];
            if (in_array($type, ["dropColumn", "dropIfExists", "dropForeign"])) {
                $newLines[] = $line;
                continue;
            }
            if (isset($baseColumns[$table][$col])) {
                $newLines[] = $indent . "if (!Schema::hasColumn('{$table}', '{$col}')) {" . "\n";
                $newLines[] = $indent . "    " . ltrim($line);
                $newLines[] = $indent . "}" . "\n";
                $wasModified = true;
                continue;
            }
        }
        
        if ($insideClosure && strpos($line, '});') !== false) {
            $insideClosure = false;
        }
        
        $newLines[] = $line;
    }
    
    file_put_contents($file, implode('', $newLines));
    if ($wasModified) $fixed++;
}

echo "Fixed $fixed migrations\n";

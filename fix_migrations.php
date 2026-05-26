<?php
$files = glob("database/migrations/*.php");
$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!preg_match("/Schema::table\(['\"](\w+)['\"]/", $content, $m)) continue;
    $table = $m[1];
    
    $lines = file($file);
    $newLines = [];
    $insideTable = false;
    $indent = '';
    $wasModified = false;
    
    foreach ($lines as $i => $line) {
        if (preg_match('/^(\s+)Schema::table\([\'\"]' . $table . '[\'\"]/', $line, $m)) {
            $indent = $m[1];
            // Add hasTable wrapper if not already present
            if (strpos(implode('', array_slice($lines, max(0, $i-2), 2)), "Schema::hasTable('{$table}')") === false) {
                $newLines[] = $indent . "if (Schema::hasTable('{$table}')) {" . "\n";
                $newLines[] = $indent . "    " . ltrim($line);
                $insideTable = true;
                $wasModified = true;
                continue;
            }
            $newLines[] = $line;
            $insideTable = true;
            continue;
        }
        
        if ($insideTable && trim($line) === '});') {
            if ($wasModified) {
                $newLines[] = $indent . "    " . ltrim($line);
                $newLines[] = $indent . "}" . "\n";
            } else {
                $newLines[] = $line;
            }
            $insideTable = false;
            continue;
        }
        
        if ($insideTable && preg_match('/^(\s+)\$table->(\w+)\([\'\"](\w+)[\'\"]/', $line, $m)) {
            $colIndent = $m[1];
            $type = $m[2];
            $col = $m[3];
            if (in_array($type, ["dropColumn", "dropIfExists", "dropForeign"])) {
                $newLines[] = $line;
                continue;
            }
            if (strpos($line, 'Schema::hasColumn') !== false) {
                $newLines[] = $line;
                continue;
            }
            $newLines[] = $colIndent . "if (!Schema::hasColumn('{$table}', '{$col}')) {" . "\n";
            $newLines[] = $colIndent . "    " . ltrim($line);
            $newLines[] = $colIndent . "}" . "\n";
            $wasModified = true;
            continue;
        }
        
        if ($insideTable && $wasModified) {
            if (trim($line) !== '' && strpos($line, $indent . "    ") !== 0) {
                $newLines[] = $indent . "    " . ltrim($line);
            } else {
                $newLines[] = $line;
            }
            continue;
        }
        
        $newLines[] = $line;
    }
    
    file_put_contents($file, implode('', $newLines));
    if ($wasModified) $fixed++;
}

echo "Fixed $fixed migrations\n";

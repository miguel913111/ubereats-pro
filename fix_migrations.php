<?php
$files = glob("database/migrations/*add*.php");
$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!preg_match("/Schema::table\(['\"](\w+)['\"]/", $content, $m)) continue;
    $table = $m[1];
    
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
            if (strpos($line, 'Schema::hasColumn') !== false) {
                $newLines[] = $line;
                continue;
            }
            $newLines[] = $indent . "if (Schema::hasTable('{$table}') && !Schema::hasColumn('{$table}', '{$col}')) {" . "\n";
            $newLines[] = $indent . "    " . ltrim($line);
            $newLines[] = $indent . "}" . "\n";
            $wasModified = true;
            continue;
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

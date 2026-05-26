<?php
$files = glob("database/migrations/*.php");
$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!preg_match("/Schema::table\(['\"](\w+)['\"]/", $content, $m)) continue;
    $table = $m[1];
    
    // Skip if already wrapped
    if (strpos($content, "Schema::hasTable('{$table}')") !== false) continue;
    
    $lines = file($file);
    $newLines = [];
    $insideTable = false;
    $indent = '';
    $wasModified = false;
    
    foreach ($lines as $i => $line) {
        if (preg_match('/^(\s+)Schema::table\([\'\"]' . $table . '[\'\"]/', $line, $m)) {
            $indent = $m[1];
            $newLines[] = $indent . "if (Schema::hasTable('{$table}')) {" . "\n";
            $newLines[] = $indent . "    " . ltrim($line);
            $insideTable = true;
            $wasModified = true;
            continue;
        }
        
        if ($insideTable && trim($line) === '});') {
            $newLines[] = $indent . "    " . ltrim($line);
            $newLines[] = $indent . "}" . "\n";
            $insideTable = false;
            continue;
        }
        
        if ($insideTable) {
            // Only indent lines that are not blank and not already more indented
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

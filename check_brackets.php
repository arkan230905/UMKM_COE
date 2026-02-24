<?php

$content = file_get_contents('c:\UMKM_COE\resources\views\laporan\stok\index.blade.php');

$lines = explode("\n", $content);
$braceStack = [];
$parenStack = [];
$lineNumber = 0;

foreach ($lines as $line) {
    $lineNumber++;
    $trimmed = trim($line);
    
    // Skip comments and empty lines
    if (empty($trimmed) || strpos($trimmed, '//') === 0 || strpos($trimmed, '/*') === 0) {
        continue;
    }
    
    // Count braces
    $openBraces = substr_count($line, '{');
    $closeBraces = substr_count($line, '}');
    
    // Count parentheses
    $openParens = substr_count($line, '(');
    $closeParens = substr_count($line, ')');
    
    // Track braces
    for ($i = 0; $i < $openBraces; $i++) {
        array_push($braceStack, $lineNumber);
    }
    for ($i = 0; $i < $closeBraces; $i++) {
        if (empty($braceStack)) {
            echo "Unmatched closing brace at line $lineNumber: $line\n";
        } else {
            array_pop($braceStack);
        }
    }
    
    // Track parentheses
    for ($i = 0; $i < $openParens; $i++) {
        array_push($parenStack, $lineNumber);
    }
    for ($i = 0; $i < $closeParens; $i++) {
        if (empty($parenStack)) {
            echo "Unmatched closing parenthesis at line $lineNumber: $line\n";
        } else {
            array_pop($parenStack);
        }
    }
    
    // Show lines with braces around line 206
    if ($lineNumber >= 200 && $lineNumber <= 210) {
        echo "Line $lineNumber: $line\n";
        echo "  Open braces: $openBraces, Close braces: $closeBraces\n";
        echo "  Stack count: " . count($braceStack) . "\n\n";
    }
}

echo "\nFinal stack counts:\n";
echo "Open braces remaining: " . count($braceStack) . "\n";
echo "Open parentheses remaining: " . count($parenStack) . "\n";

if (!empty($braceStack)) {
    echo "\nUnmatched opening braces at lines: " . implode(', ', $braceStack) . "\n";
}

if (!empty($parenStack)) {
    echo "\nUnmatched opening parentheses at lines: " . implode(', ', $parenStack) . "\n";
}

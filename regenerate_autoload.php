<?php
// Regenerate composer autoload
echo "Regenerating Composer autoload files...\n";
exec('composer dump-autoload 2>&1', $output, $returnCode);

foreach ($output as $line) {
    echo $line . "\n";
}

if ($returnCode === 0) {
    echo "\nAutoload regenerated successfully!\n";
} else {
    echo "\nError regenerating autoload. Return code: " . $returnCode . "\n";
}

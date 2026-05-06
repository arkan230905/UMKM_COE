<?php
// Clear opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!\n";
} else {
    echo "OPcache is not enabled.\n";
}

// Clear realpath cache
clearstatcache(true);
echo "Realpath cache cleared!\n";

echo "\nPlease refresh your browser now.\n";

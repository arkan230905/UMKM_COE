<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING USER_ID FIELDS ===\n";

$tables = ['bahan_bakus', 'bahan_pendukungs'];

foreach ($tables as $table) {
    echo "\n{$table}:\n";
    try {
        $columns = DB::select("SHOW COLUMNS FROM {$table}");
        $hasUserId = false;
        
        foreach ($columns as $column) {
            if ($column->Field === 'user_id') {
                $hasUserId = true;
                echo "  ✅ user_id field exists ({$column->Type})\n";
                break;
            }
        }
        
        if (!$hasUserId) {
            echo "  ❌ user_id field NOT found\n";
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}
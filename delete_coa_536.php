<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Hapus COA 536 untuk user_id 2
DB::table('coas')->where('kode_akun', '536')->where('user_id', 2)->delete();

echo "✅ COA 536 berhasil dihapus untuk user_id 2\n";

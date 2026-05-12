<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    echo "=== CLEANUP PEMBAYARAN BEBAN ===\n\n";
    
    // Hapus jurnal umum yang terkait dengan pembayaran beban
    echo "Menghapus jurnal umum untuk pembayaran beban...\n";
    $deletedJurnals = DB::table('jurnal_umum')
        ->where('tipe_referensi', 'pembayaran_beban')
        ->where('user_id', 1) // Ganti dengan user_id yang sesuai
        ->delete();
    
    echo "Jurnal terhapus: " . $deletedJurnals . " records\n\n";
    
    // Soft delete data pembayaran beban
    echo "Melakukan soft delete pembayaran beban...\n";
    $deletedPembayaran = DB::table('pembayaran_bebans')
        ->where('user_id', 1) // Ganti dengan user_id yang sesuai
        ->update(['deleted_at' => now()]);
    
    echo "Pembayaran beban di-update: " . $deletedPembayaran . " records\n\n";
    
    // Tampilkan data tersisa
    echo "Data pembayaran beban tersisa:\n";
    $remaining = DB::table('pembayaran_bebans')
        ->where('user_id', 1)
        ->whereNull('deleted_at')
        ->count();
    
    echo "Jumlah tersisa: " . $remaining . " records\n\n";
    
    echo "=== CLEANUP SELESAI ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

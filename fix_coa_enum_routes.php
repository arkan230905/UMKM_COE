<?php
/**
 * Tambahkan route ini ke routes/web.php untuk web tool fix enum
 */

// Route untuk fix COA enum - EMERGENCY
Route::post('/fix-coa-enum-check', function() {
    try {
        $result = DB::select("SHOW COLUMNS FROM coas LIKE 'tipe_akun'");
        if (!empty($result)) {
            return response("Current enum: " . $result[0]->Type);
        }
        return response("Tidak dapat memeriksa enum", 500);
    } catch (Exception $e) {
        return response("Error: " . $e->getMessage(), 500);
    }
});

Route::post('/fix-coa-enum-update', function() {
    try {
        // Update existing BEBAN values to Expense first
        DB::table('coas')->where('tipe_akun', 'BEBAN')->update(['tipe_akun' => 'Expense']);
        
        // Alter enum to include all values
        $sql = "ALTER TABLE coas MODIFY COLUMN tipe_akun ENUM(
            'Asset', 'Aset', 'ASET',
            'Liability', 'Kewajiban', 'KEWAJIBAN', 
            'Equity', 'Ekuitas', 'Modal', 'MODAL',
            'Revenue', 'Pendapatan', 'PENDAPATAN',
            'Expense', 'Beban', 'BEBAN', 'Biaya',
            'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
            'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
            'BOP Tidak Langsung Lainnya'
        ) NOT NULL";
        
        DB::statement($sql);
        
        return response("Enum berhasil diupdate untuk mendukung semua nilai termasuk 'BEBAN'");
    } catch (Exception $e) {
        return response("Error: " . $e->getMessage(), 500);
    }
});

Route::post('/fix-coa-enum-test', function() {
    try {
        $updated = DB::table('coas')
            ->where('id', 166)
            ->update([
                'nama_akun' => 'Biaya TENAGA KERJA TIDAK LANGSUNG',
                'tipe_akun' => 'BEBAN',
                'tanggal_saldo_awal' => '2026-04-01 00:00:00',
                'updated_at' => now()
            ]);
        
        if ($updated) {
            return response("Test update berhasil! COA ID 166 berhasil diupdate dengan tipe 'BEBAN'");
        } else {
            return response("Test update gagal - tidak ada record yang diupdate", 500);
        }
    } catch (Exception $e) {
        return response("Test update gagal: " . $e->getMessage(), 500);
    }
});
?>
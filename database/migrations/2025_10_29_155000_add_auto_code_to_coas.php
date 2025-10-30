<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAutoCodeToCoas extends Migration
{
    public function up()
    {
        // 1. Tambahkan kolom kode_akun jika belum ada
        if (!Schema::hasColumn('coas', 'kode_akun')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kode_akun')->unique()->after('id');
            });
        }

        // 2. Generate kode akun otomatis untuk data yang sudah ada
        $coas = DB::table('coas')->orderBy('id')->get();
        $codeMap = [];
        
        foreach ($coas as $coa) {
            if (empty($codeMap[$coa->tipe_akun])) {
                $codeMap[$coa->tipe_akun] = 1;
            } else {
                $codeMap[$coa->tipe_akun]++;
            }
            
            $prefix = $this->getPrefixByType($coa->tipe_akun);
            $newCode = $prefix . str_pad($codeMap[$coa->tipe_akun], 2, '0', STR_PAD_LEFT);
            
            // Update kode akun
            DB::table('coas')
                ->where('id', $coa->id)
                ->update(['kode_akun' => $newCode]);
        }
    }

    public function down()
    {
        // Tidak perlu melakukan apa-apa saat rollback
        // Karena kita tidak ingin menghapus data yang sudah ada
    }

    private function getPrefixByType($type)
    {
        $type = strtolower($type);
        
        switch ($type) {
            case 'asset':
                return '1';
            case 'liability':
                return '2';
            case 'equity':
                return '3';
            case 'revenue':
                return '4';
            case 'expense':
                return '5';
            default:
                return '9';
        }
    }
}

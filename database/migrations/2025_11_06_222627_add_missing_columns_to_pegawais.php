<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Add kode_pegawai if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }
            
            // Add no_telepon if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'no_telepon')) {
                $table->string('no_telepon', 20)->nullable()->after('email');
            }
            
            // Add nama_bank if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'nama_bank')) {
                $table->string('nama_bank', 100)->nullable()->after('alamat');
            }
            
            // Add no_rekening if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'no_rekening')) {
                $table->string('no_rekening', 50)->nullable()->after('nama_bank');
            }
            
            // Add kategori if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'kategori')) {
                $table->enum('kategori', ['BTKL', 'BTKTL'])->default('BTKL')->after('jabatan');
            }
            
            // Add asuransi if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('kategori');
            }
            
            // Add tarif if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0)->after('asuransi');
            }
            
            // Add tunjangan if it doesn't exist
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('tarif');
            }
        });
        
        // Generate kode_pegawai for existing records
        $pegawais = DB::table('pegawais')->get();
        foreach ($pegawais as $index => $pegawai) {
            $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('pegawais')
                ->where('id', $pegawai->id)
                ->update(['kode_pegawai' => $kode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $columnsToDrop = [
                'kode_pegawai',
                'no_telepon',
                'nama_bank',
                'no_rekening',
                'kategori',
                'asuransi',
                'tarif',
                'tunjangan'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

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
            // Add new columns if they don't exist
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }
            if (!Schema::hasColumn('pegawais', 'no_telepon')) {
                $table->string('no_telepon', 20)->after('email');
            }
            if (!Schema::hasColumn('pegawais', 'nama_bank')) {
                $table->string('nama_bank', 100)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('pegawais', 'no_rekening')) {
                $table->string('no_rekening', 50)->nullable()->after('nama_bank');
            }
            if (!Schema::hasColumn('pegawais', 'kategori')) {
                $table->enum('kategori', ['BTKL', 'BTKTL'])->default('BTKL')->after('jabatan');
            }
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('kategori');
            }
            if (!Schema::hasColumn('pegawais', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0)->after('asuransi');
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('tarif');
            }
            
            // Update existing columns if needed
            if (Schema::hasColumn('pegawais', 'no_telp') && !Schema::hasColumn('pegawais', 'no_telepon')) {
                $table->renameColumn('no_telp', 'no_telepon');
            }
            
            // Add index for better performance
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->unique('kode_pegawai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We'll only drop the columns that were added in this migration
        // to avoid accidentally dropping existing data
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
            
            $table->dropColumn(array_filter($columnsToDrop, function($column) use ($table) {
                return Schema::hasColumn('pegawais', $column);
            }));
            
            // Rename back if needed
            if (Schema::hasColumn('pegawais', 'no_telepon') && !Schema::hasColumn('pegawais', 'no_telp')) {
                $table->renameColumn('no_telepon', 'no_telp');
            }
        });
    }
};

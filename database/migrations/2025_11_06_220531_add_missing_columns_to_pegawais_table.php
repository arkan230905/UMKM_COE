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
        if (!Schema::hasTable('pegawais')) {
            return;
        }

        Schema::table('pegawais', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                if (Schema::hasColumn('pegawais', 'id')) {
                    $table->string('kode_pegawai', 20)->nullable()->after('id');
                } else {
                    $table->string('kode_pegawai', 20)->nullable();
                }
            }
            if (!Schema::hasColumn('pegawais', 'no_telepon')) {
                $table->string('no_telepon', 20)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'nama_bank')) {
                $table->string('nama_bank', 100)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'no_rekening')) {
                $table->string('no_rekening', 50)->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'kategori')) {
                $table->enum('kategori', ['BTKL', 'BTKTL'])->default('BTKL')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('pegawais', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0);
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

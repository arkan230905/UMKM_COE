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
        Schema::table('bops', function (Blueprint $table) {
            // Pastikan kolom-kolom yang diperlukan ada
            if (!Schema::hasColumn('bops', 'aktual')) {
                $table->decimal('aktual', 15, 2)->default(0)->after('budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            // Hapus indeks
            $table->dropIndex(['kode_akun']);
            $table->dropIndex(['is_active']);
            
            // Hapus kolom aktual jika rollback
            if (Schema::hasColumn('bops', 'aktual')) {
                $table->dropColumn('aktual');
            }
        });
    }
};

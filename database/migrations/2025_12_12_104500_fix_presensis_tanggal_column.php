<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            // Jika ada kolom 'tanggal', ubah menjadi nullable atau hapus
            if (Schema::hasColumn('presensis', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
        });
    }

    public function down(): void
    {
        // Tidak perlu rollback
    }
};

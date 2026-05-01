<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bops')) {
            try {
                Schema::table('bops', function (Blueprint $table) {
                    $table->dropForeign(['kode_akun']);
                });
            } catch (\Exception $e) {
                // FK may not exist, continue
            }
        }
    }

    public function down(): void
    {
        // No-op: FK was already removed
    }
};

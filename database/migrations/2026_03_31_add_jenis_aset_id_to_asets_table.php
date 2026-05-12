<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Add jenis_aset_id field if it doesn't exist
            if (!Schema::hasColumn('asets', 'jenis_aset_id')) {
                $table->foreignId('jenis_aset_id')->nullable()->after('kategori_aset_id')->constrained('jenis_asets')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'jenis_aset_id')) {
                $table->dropForeign(['jenis_aset_id']);
                $table->dropColumn('jenis_aset_id');
            }
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'nama_asset')) {
                $table->string('nama_asset')->nullable()->after('nama_aset');
            }
            if (!Schema::hasColumn('assets', 'tanggal_beli')) {
                $table->date('tanggal_beli')->nullable()->after('tanggal_perolehan');
            }
            if (!Schema::hasColumn('assets', 'id_perusahaan')) {
                $table->unsignedBigInteger('id_perusahaan')->nullable()->after('umur_ekonomis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'nama_asset')) {
                $table->dropColumn('nama_asset');
            }
            if (Schema::hasColumn('assets', 'tanggal_beli')) {
                $table->dropColumn('tanggal_beli');
            }
            if (Schema::hasColumn('assets', 'id_perusahaan')) {
                $table->dropColumn('id_perusahaan');
            }
        });
    }
};

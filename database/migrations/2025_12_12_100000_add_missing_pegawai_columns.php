<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'no_telp')) {
                $table->string('no_telp')->nullable()->after('email');
            }
            if (!Schema::hasColumn('pegawais', 'alamat')) {
                $table->text('alamat')->nullable()->after('no_telp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'no_telp')) {
                $table->dropColumn('no_telp');
            }
            if (Schema::hasColumn('pegawais', 'alamat')) {
                $table->dropColumn('alamat');
            }
        });
    }
};

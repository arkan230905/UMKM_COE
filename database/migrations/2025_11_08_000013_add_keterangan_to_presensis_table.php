<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('presensis', 'keterangan')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('jumlah_jam');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('presensis', 'keterangan')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};

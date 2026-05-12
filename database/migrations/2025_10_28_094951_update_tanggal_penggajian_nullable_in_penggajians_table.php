<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->date('tanggal_penggajian')->default(DB::raw('CURRENT_DATE'))->change();
        });
    }

    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->date('tanggal_penggajian')->nullable(false)->change();
        });
    }
};

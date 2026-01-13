<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->string('kode', 10)->nullable()->unique()->after('id');
        });

        // Generate kode untuk perusahaan yang sudah ada
        $perusahaans = DB::table('perusahaan')->whereNull('kode')->get();
        foreach ($perusahaans as $p) {
            $kode = strtoupper(substr(md5($p->id . time()), 0, 6));
            DB::table('perusahaan')->where('id', $p->id)->update(['kode' => $kode]);
        }
    }

    public function down(): void
    {
        Schema::table('perusahaan', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};

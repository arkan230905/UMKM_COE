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
        Schema::table('penggajians', function (Blueprint $table) {
            $table->string('nomor_penggajian', 20)->nullable()->after('id')->unique();
        });

        // Update existing records with auto-generated numbers
        $penggajians = \DB::table('penggajians')->orderBy('id')->get();
        $counter = 1;
        foreach ($penggajians as $penggajian) {
            \DB::table('penggajians')
                ->where('id', $penggajian->id)
                ->update(['nomor_penggajian' => 'PGJ' . str_pad($counter, 6, '0', STR_PAD_LEFT)]);
            $counter++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn('nomor_penggajian');
        });
    }
};

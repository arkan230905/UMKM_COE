<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Add jabatan_id column
            $table->unsignedBigInteger('jabatan_id')->nullable()->after('jenis_kelamin');
            
            // Add foreign key constraint
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('set null');
        });

        // Update existing data to map jabatan string to jabatan_id
        $pegawais = DB::table('pegawais')->whereNotNull('jabatan')->get();
        
        foreach ($pegawais as $pegawai) {
            $jabatan = DB::table('jabatans')->where('nama', $pegawai->jabatan)->first();
            
            if ($jabatan) {
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update(['jabatan_id' => $jabatan->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn('jabatan_id');
        });
    }
};
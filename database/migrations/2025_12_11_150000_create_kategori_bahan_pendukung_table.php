<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_bahan_pendukung', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('kategori_bahan_pendukung')->insert([
            ['nama' => 'Gas', 'keterangan' => 'Gas LPG, dll', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Bumbu', 'keterangan' => 'Bumbu masak', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Minyak', 'keterangan' => 'Minyak goreng, dll', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Air', 'keterangan' => 'Air bersih', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Listrik', 'keterangan' => 'Biaya listrik', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pembersih', 'keterangan' => 'Sabun, deterjen, dll', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lainnya', 'keterangan' => 'Kategori lainnya', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Add foreign key to bahan_pendukungs table
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('kategori')->constrained('kategori_bahan_pendukung')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });
        Schema::dropIfExists('kategori_bahan_pendukung');
    }
};

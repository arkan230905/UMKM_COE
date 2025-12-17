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
        if (Schema::hasTable('jabatans')) {
            return;
        }

        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->enum('kategori', ['btkl', 'btktl']);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('asuransi', 15, 2)->default(0);
            $table->decimal('gaji', 15, 2)->default(0);
            $table->decimal('tarif', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert default data
        DB::table('jabatans')->insert([
            [
                'nama' => 'Manajer',
                'kategori' => 'btktl',
                'tunjangan' => 1000000,
                'asuransi' => 200000,
                'gaji' => 8000000,
                'tarif' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Supervisor',
                'kategori' => 'btktl',
                'tunjangan' => 800000,
                'asuransi' => 150000,
                'gaji' => 6000000,
                'tarif' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Operator Produksi',
                'kategori' => 'btkl',
                'tunjangan' => 300000,
                'asuransi' => 100000,
                'gaji' => 0,
                'tarif' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Admin',
                'kategori' => 'btktl',
                'tunjangan' => 500000,
                'asuransi' => 100000,
                'gaji' => 4500000,
                'tarif' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};

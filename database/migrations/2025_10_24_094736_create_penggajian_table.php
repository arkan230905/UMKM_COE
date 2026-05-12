<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penggajians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->onDelete('cascade');
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('tunjangan', 12, 2)->default(0);
            $table->decimal('potongan', 12, 2)->default(0);
            $table->decimal('total_jam_kerja', 12, 2)->default(0);
            $table->decimal('total_gaji', 12, 2)->default(0);
            $table->date('tanggal_penggajian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajians');
    }
};

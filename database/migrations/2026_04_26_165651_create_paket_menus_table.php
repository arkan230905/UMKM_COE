<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->decimal('harga_normal', 15, 2)->default(0);
            $table->decimal('harga_paket', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('paket_menu_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_menu_id')->constrained('paket_menus')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->decimal('jumlah', 10, 2)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_menu_details');
        Schema::dropIfExists('paket_menus');
    }
};

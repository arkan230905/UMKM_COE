<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create catalog photos table for hero slider management
     */
    public function up(): void
    {
        Schema::create('catalog_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan');
            $table->string('judul')->nullable();
            $table->string('foto');
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['perusahaan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_photos');
    }
};

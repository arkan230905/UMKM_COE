<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->index();
            $table->decimal('asuransi', 15, 2)->default(0);
            $table->decimal('tarif', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('jabatans');
    }
};

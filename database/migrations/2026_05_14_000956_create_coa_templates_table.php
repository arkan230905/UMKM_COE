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
        if (!Schema::hasTable('coa_templates')) {
            Schema::create('coa_templates', function (Blueprint $table) {
                $table->id();
                
                /**
                 * PERBAIKAN: Menyesuaikan nama kolom dengan CoaTemplateSeeder
                 * 'code', 'name', dan 'type'
                 */
                $table->string('code')->unique(); // 1110, 1120, dll
                $table->string('name');           // Kas, Bank, dll
                $table->string('type');           // ASSET, LIABILITY, dll
                
                // Tambahkan ini jika sistem Anda membutuhkan kategori tambahan nanti
                $table->string('category')->nullable(); 
                $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
                
                $table->timestamps();

                $table->index('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_templates');
    }
};
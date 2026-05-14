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
        // Menggunakan check agar tidak terjadi error "table already exists"
        if (!Schema::hasTable('coa_periods')) {
            Schema::create('coa_periods', function (Blueprint $table) {
                $table->id();
                
                /**
                 * Relasi ke Owner/User. 
                 * Penting agar setiap UMKM memiliki kalender periode akuntansinya sendiri.
                 */
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                
                $table->string('periode', 7); // Format: YYYY-MM
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                
                // Status periode ditutup atau belum
                $table->boolean('is_closed')->default(false); 
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamps();
                
                // Indexes untuk performa laporan keuangan
                $table->index('user_id');
                $table->index('periode');
                $table->index('is_closed');

                /**
                 * Unique Constraint: 
                 * Satu user tidak boleh memiliki dua entri periode yang sama (misal: 2026-05)
                 */
                $table->unique(['user_id', 'periode'], 'unique_user_period');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_periods');
    }
};
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
        // Drop old foreign keys that reference 'accounts' table
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'pembayaran_beban' 
                AND TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'accounts'
            ");
            
            foreach ($foreignKeys as $fk) {
                try {
                    DB::statement("ALTER TABLE pembayaran_beban DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    echo "Dropped foreign key: {$fk->CONSTRAINT_NAME}\n";
                } catch (\Exception $e) {
                    echo "Could not drop {$fk->CONSTRAINT_NAME}: " . $e->getMessage() . "\n";
                }
            }
        });
        
        // Add new foreign keys that reference 'coas' table
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            // Check if columns exist
            if (Schema::hasColumn('pembayaran_beban', 'akun_beban_id')) {
                try {
                    $table->foreign('akun_beban_id')
                        ->references('id')
                        ->on('coas')
                        ->onDelete('restrict')
                        ->onUpdate('cascade');
                    echo "Added foreign key: akun_beban_id -> coas(id)\n";
                } catch (\Exception $e) {
                    echo "Could not add akun_beban_id FK: " . $e->getMessage() . "\n";
                }
            }
            
            if (Schema::hasColumn('pembayaran_beban', 'akun_kas_id')) {
                try {
                    $table->foreign('akun_kas_id')
                        ->references('id')
                        ->on('coas')
                        ->onDelete('restrict')
                        ->onUpdate('cascade');
                    echo "Added foreign key: akun_kas_id -> coas(id)\n";
                } catch (\Exception $e) {
                    echo "Could not add akun_kas_id FK: " . $e->getMessage() . "\n";
                }
            }
        });
        
        echo "✅ Fixed pembayaran_beban foreign keys to reference coas table\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            try {
                $table->dropForeign(['akun_beban_id']);
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
            
            try {
                $table->dropForeign(['akun_kas_id']);
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
        });
    }
};

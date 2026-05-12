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
        // Disable foreign key checks to avoid issues during migration
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. Make sure nomor_induk_pegawai exists and is unique
            if (!Schema::hasColumn('pegawais', 'nomor_induk_pegawai')) {
                Schema::table('pegawais', function (Blueprint $table) {
                    $table->string('nomor_induk_pegawai')->unique()->after('id');
                });
            } else {
                // Check if the unique constraint already exists
                $hasUnique = DB::selectOne(
                    "SELECT COUNT(*) as count 
                     FROM information_schema.table_constraints 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'pegawais' 
                     AND constraint_name = 'pegawais_nomor_induk_pegawai_unique'"
                );
                
                // Only add the unique constraint if it doesn't exist
                if ($hasUnique->count == 0) {
                    Schema::table('pegawais', function (Blueprint $table) {
                        $table->string('nomor_induk_pegawai')->unique()->change();
                    });
                }
            }

            // 2. Fix the presensis table to reference the correct column
            Schema::table('presensis', function (Blueprint $table) {
                // Drop existing foreign key constraints
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME 
                     FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'presensis' 
                     AND COLUMN_NAME = 'pegawai_id' 
                     AND REFERENCED_TABLE_NAME IS NOT NULL"
                );
                
                foreach ($foreignKeys as $key) {
                    $table->dropForeign($key->CONSTRAINT_NAME);
                }

                // Change pegawai_id to string if it's not already
                if (Schema::hasColumn('presensis', 'pegawai_id')) {
                    $table->string('pegawai_id', 50)->change();
                } else {
                    $table->string('pegawai_id', 50)->after('id');
                }
            });

            // 3. Add the new foreign key constraint
            Schema::table('presensis', function (Blueprint $table) {
                $table->foreign('pegawai_id')
                      ->references('nomor_induk_pegawai')
                      ->on('pegawais')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Log the error and continue
            \Log::error('Migration error: ' . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a complex migration, so the down method will be minimal
        // as rolling back might cause data loss
        try {
            Schema::table('presensis', function (Blueprint $table) {
                // Drop the foreign key constraint
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME 
                     FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'presensis' 
                     AND COLUMN_NAME = 'pegawai_id' 
                     AND REFERENCED_TABLE_NAME IS NOT NULL"
                );
                
                foreach ($foreignKeys as $key) {
                    $table->dropForeign($key->CONSTRAINT_NAME);
                }

                // Revert pegawai_id back to bigint if needed
                if (Schema::hasColumn('presensis', 'pegawai_id')) {
                    $table->unsignedBigInteger('pegawai_id')->change();
                }
            });

            // Re-add the old foreign key constraint if needed
            if (Schema::hasColumn('pegawais', 'id') && Schema::hasColumn('presensis', 'pegawai_id')) {
                Schema::table('presensis', function (Blueprint $table) {
                    $table->foreign('pegawai_id')
                          ->references('id')
                          ->on('pegawais')
                          ->onDelete('cascade');
                });
            }
        } catch (\Exception $e) {
            // Log the error and continue
            \Log::error('Migration rollback error: ' . $e->getMessage());
            throw $e;
        }
    }
};

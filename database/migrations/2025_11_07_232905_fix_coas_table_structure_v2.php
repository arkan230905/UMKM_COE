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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Drop foreign key constraints that reference the coas table
            $this->dropForeignKeys();

            // Add the missing columns to the coas table
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->string('kategori_akun')->after('tipe_akun')->nullable();
                });
            }

            if (!Schema::hasColumn('coas', 'kode_induk')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->string('kode_induk')->after('kategori_akun')->nullable();
                });
            }

            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk')->default('debit');
                });
            }

            if (!Schema::hasColumn('coas', 'keterangan')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->text('keterangan')->after('saldo_normal')->nullable();
                });
            }

            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->boolean('is_akun_header')->default(false)->after('keterangan');
                });
            }

            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->decimal('saldo_awal', 20, 2)->default(0)->after('is_akun_header');
                });
            }

            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->date('tanggal_saldo_awal')->after('saldo_awal')->nullable();
                });
            }

            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->boolean('posted_saldo_awal')->default(false)->after('tanggal_saldo_awal');
                });
            }

            // Re-add foreign key constraints
            $this->addForeignKeys();

        } catch (\Exception $e) {
            // Log the error and re-enable foreign key checks
            \Log::error('Migration error: ' . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            throw $e;
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration, so we won't implement down()
        // to avoid data loss. If you need to rollback, create a new migration.
    }

    /**
     * Drop all foreign key constraints that reference the coas table
     */
    private function dropForeignKeys(): void
    {
        $tables = [
            'bops' => ['kode_akun'],
            'coas' => ['kode_induk']
            // Add other tables that have foreign keys to coas table
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $constraintName = $this->getConstraintName($table, $column);
                        if ($constraintName) {
                            try {
                                $table->dropForeign($constraintName);
                            } catch (\Exception $e) {
                                // Log the error but continue
                                \Log::warning("Failed to drop foreign key {$constraintName} on {$table}.{$column}: " . $e->getMessage());
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * Add back all foreign key constraints
     */
    private function addForeignKeys(): void
    {
        // Add foreign key for kode_induk in coas table (self-referential)
        if (Schema::hasTable('coas') && Schema::hasColumn('coas', 'kode_induk')) {
            try {
                Schema::table('coas', function (Blueprint $table) {
                    $table->foreign('kode_induk')
                          ->references('kode_akun')
                          ->on('coas')
                          ->onDelete('set null')
                          ->onUpdate('cascade');
                });
            } catch (\Exception $e) {
                \Log::warning('Failed to add foreign key for coas.kode_induk: ' . $e->getMessage());
            }
        }

        // Add foreign key for bops table
        if (Schema::hasTable('bops') && Schema::hasColumn('bops', 'kode_akun')) {
            try {
                Schema::table('bops', function (Blueprint $table) {
                    $table->foreign('kode_akun')
                          ->references('kode_akun')
                          ->on('coas')
                          ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                \Log::warning('Failed to add foreign key for bops.kode_akun: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get the constraint name for a foreign key
     */
    private function getConstraintName(string $table, string $column): ?string
    {
        try {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table, $column]);

            return $constraints[0]->CONSTRAINT_NAME ?? null;
        } catch (\Exception $e) {
            \Log::warning("Failed to get constraint name for {$table}.{$column}: " . $e->getMessage());
            return null;
        }
    }
};

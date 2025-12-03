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

        // Drop all foreign key constraints that reference coas table
        $this->dropForeignKeys();

        // Add the missing columns to coas table
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun')->after('tipe_akun')->nullable();
            }
            
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk')->after('kategori_akun')->nullable();
            }
            
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk')->default('debit');
            }
            
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->after('saldo_normal')->nullable();
            }
            
            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                $table->boolean('is_akun_header')->default(false)->after('keterangan');
            }
            
            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                $table->decimal('saldo_awal', 20, 2)->default(0)->after('is_akun_header');
            }
            
            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                $table->date('tanggal_saldo_awal')->after('saldo_awal')->nullable();
            }
            
            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                $table->boolean('posted_saldo_awal')->default(false)->after('tanggal_saldo_awal');
            }
        });

        // Re-add foreign key constraints
        $this->addForeignKeys();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Drop the columns we added
        Schema::table('coas', function (Blueprint $table) {
            $columns = [
                'kategori_akun',
                'kode_induk',
                'saldo_normal',
                'keterangan',
                'is_akun_header',
                'saldo_awal',
                'tanggal_saldo_awal',
                'posted_saldo_awal'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('coas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
                            $table->dropForeign($constraintName);
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
        if (Schema::hasTable('coas')) {
            Schema::table('coas', function (Blueprint $table) {
                if (Schema::hasColumn('coas', 'kode_induk')) {
                    $table->foreign('kode_induk')
                          ->references('kode_akun')
                          ->on('coas')
                          ->onDelete('set null')
                          ->onUpdate('cascade');
                }
            });
        }

        // Add foreign key for bops table
        if (Schema::hasTable('bops') && Schema::hasColumn('bops', 'kode_akun')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->foreign('kode_akun')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('cascade');
            });
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
            return null;
        }
    }
};

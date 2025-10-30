<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, drop the foreign key constraints that might cause issues
        Schema::table('bom_details', function (Blueprint $table) {
            // This will remove the foreign key constraint if it exists
            $table->dropForeign(['bom_id']);
        });

        // Now modify the boms table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop the foreign key constraint first
        if (Schema::hasTable('boms')) {
            // Get the foreign key constraint name
            $constraint = DB::select(
                "SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'boms' 
                AND COLUMN_NAME = 'bahan_baku_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL"
            );

            if (!empty($constraint)) {
                $constraintName = $constraint[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE boms DROP FOREIGN KEY `$constraintName`");
            }

            // Now drop the columns
            Schema::table('boms', function (Blueprint $table) {
                $columnsToCheck = [
                    'bahan_baku_id',
                    'jumlah',
                    'satuan_resep',
                    'btkl_per_unit',
                    'bop_rate',
                    'bop_per_unit',
                    'total_btkl',
                    'total_bop',
                    'periode'
                ];

                foreach ($columnsToCheck as $column) {
                    if (Schema::hasColumn('boms', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Add new columns if they don't exist
        Schema::table('boms', function (Blueprint $table) {
            // First, make sure the table has the required columns
            if (!Schema::hasColumn('boms', 'total_biaya')) {
                $table->decimal('total_biaya', 15, 2)->default(0);
            }
            
            if (!Schema::hasColumn('boms', 'kode_bom')) {
                $table->string('kode_bom')->unique()->after('id');
            }
            
            if (!Schema::hasColumn('boms', 'persentase_keuntungan')) {
                $table->decimal('persentase_keuntungan', 5, 2)->default(0)->after('total_biaya');
            }
            
            // Add catatan at the end of the table since we're not sure about harga_jual
            if (!Schema::hasColumn('boms', 'catatan')) {
                $table->text('catatan')->nullable();
            }
        });

        // Re-add the foreign key constraint for bom_details
        Schema::table('bom_details', function (Blueprint $table) {
            $table->foreign('bom_id')
                  ->references('id')
                  ->on('boms')
                  ->onDelete('cascade');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        // This is a simplified rollback - you might need to adjust based on your needs
        Schema::table('boms', function (Blueprint $table) {
            // Add back the dropped columns
            if (!Schema::hasColumn('boms', 'bahan_baku_id')) {
                $table->foreignId('bahan_baku_id')->nullable()->constrained('bahan_bakus');
            }
            if (!Schema::hasColumn('boms', 'jumlah')) {
                $table->decimal('jumlah', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'satuan_resep')) {
                $table->string('satuan_resep')->nullable();
            }
            if (!Schema::hasColumn('boms', 'btkl_per_unit')) {
                $table->decimal('btkl_per_unit', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'bop_rate')) {
                $table->decimal('bop_rate', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'bop_per_unit')) {
                $table->decimal('bop_per_unit', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'total_btkl')) {
                $table->decimal('total_btkl', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'total_bop')) {
                $table->decimal('total_bop', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('boms', 'periode')) {
                $table->date('periode')->nullable();
            }

            // Drop the new columns
            $table->dropColumn(['kode_bom', 'persentase_keuntungan', 'catatan']);
        });
    }
};

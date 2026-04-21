<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix BTKL (code 52) and BOP (code 53) journal lines
        // Move them from debit to credit for production_labor_overhead transactions
        
        DB::statement("
            UPDATE journal_lines jl
            INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
            SET jl.credit = jl.debit, jl.debit = 0
            WHERE je.ref_type = 'production_labor_overhead'
            AND jl.coa_code IN ('52', '53')
            AND jl.debit > 0
        ");
    }

    public function down(): void
    {
        // Revert the changes - move credit back to debit
        DB::statement("
            UPDATE journal_lines jl
            INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
            SET jl.debit = jl.credit, jl.credit = 0
            WHERE je.ref_type = 'production_labor_overhead'
            AND jl.coa_code IN ('52', '53')
            AND jl.credit > 0
        ");
    }
};

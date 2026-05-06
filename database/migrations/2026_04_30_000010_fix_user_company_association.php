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
        // Fix users without company association
        $usersWithoutCompany = DB::table('users')
            ->whereNull('company_id')
            ->whereNull('perusahaan_id')
            ->get();
        
        foreach ($usersWithoutCompany as $user) {
            // Assign to default company (UMKM COE - ID: 1)
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'company_id' => 1,
                    'perusahaan_id' => 1,
                    'updated_at' => now(),
                ]);
            
            echo "Assigned user '{$user->name}' to default company\n";
        }
        
        echo "Fixed user-company associations\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove company associations for users that were added in this migration
        // This is optional - we can keep the associations
    }
};

<?php

echo "=== CREATE HPP COMPONENTS MIGRATION ===\n\n";

echo "Creating migration to add selected component IDs to bom_job_costings...\n";

$migrationContent = '<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(\'bom_job_costings\', function (Blueprint $table) {
            // Add columns to store selected component IDs
            $table->json(\'selected_bbb_ids\')->nullable()->comment(\'Selected BBB component IDs\');
            $table->json(\'selected_btkl_ids\')->nullable()->comment(\'Selected BTKL process IDs\');
            $table->json(\'selected_bop_ids\')->nullable()->comment(\'Selected BOP component IDs\');
            
            // Add flags for component selection
            $table->boolean(\'include_bbb\')->default(true)->comment(\'Include Biaya Bahan Baku\');
            $table->boolean(\'include_btkl\')->default(true)->comment(\'Include Biaya Tenaga Kerja Langsung\');
            $table->boolean(\'include_bop\')->default(true)->comment(\'Include Biaya Overhead Pabrik\');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(\'bom_job_costings\', function (Blueprint $table) {
            $table->dropColumn([
                \'selected_bbb_ids\',
                \'selected_btkl_ids\',
                \'selected_bop_ids\',
                \'include_bbb\',
                \'include_btkl\',
                \'include_bop\'
            ]);
        });
    }
};
';

// Create migration file
$timestamp = date('Y_m_d_His');
$migrationFile = "c:\\UMKM_COE\\database\\migrations\\{$timestamp}_add_selected_components_to_bom_job_costings.php";

file_put_contents($migrationFile, $migrationContent);

echo "✅ Created migration file: " . basename($migrationFile) . "\n";
echo "✅ Added columns:\n";
echo "  - selected_bbb_ids (JSON): Selected BBB component IDs\n";
echo "  - selected_btkl_ids (JSON): Selected BTKL process IDs\n";
echo "  - selected_bop_ids (JSON): Selected BOP component IDs\n";
echo "  - include_bbb (boolean): Include Biaya Bahan Baku flag\n";
echo "  - include_btkl (boolean): Include BTKL flag\n";
echo "  - include_bop (boolean): Include BOP flag\n\n";

echo "To run migration:\n";
echo "php artisan migrate\n\n";

echo "=== MIGRATION CREATED ===\n";

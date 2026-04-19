public function up(): void
{
    if (Schema::hasTable('journal_lines')) {

        Schema::table('journal_lines', function (Blueprint $table) {

            // kalau belum ada coa_id → buat
            if (!Schema::hasColumn('journal_lines', 'coa_id')) {
                $table->unsignedBigInteger('coa_id')->nullable();
            }

        });
    }
}

public function down(): void
{
    if (Schema::hasTable('journal_lines')) {

        Schema::table('journal_lines', function (Blueprint $table) {

            if (Schema::hasColumn('journal_lines', 'coa_id')) {
                $table->dropColumn('coa_id');
            }

        });
    }
}
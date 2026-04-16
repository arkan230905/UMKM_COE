<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\CoaService();

// Test creating COA for a new company using current database template
$count = $service->createDefaultCoaForCompany(996);
echo "Created {$count} COA accounts using current database template for test company\n";

// Verify the created COA data matches current database
echo "\nVerification - First 5 accounts from template vs created:\n";
echo "Template vs Created\n";

$templateCoas = App\Models\Coa::withoutGlobalScopes()->whereNull('company_id')->orderBy('kode_akun')->limit(5)->get();
$createdCoas = App\Models\Coa::withoutGlobalScopes()->where('company_id', 996)->orderBy('kode_akun')->limit(5)->get();

foreach($templateCoas as $index => $template) {
    $created = $createdCoas[$index] ?? null;
    if ($created) {
        echo "Template: {$template->kode_akun} - {$template->nama_akun} - {$template->tipe_akun} - {$template->saldo_awal}\n";
        echo "Created:  {$created->kode_akun} - {$created->nama_akun} - {$created->tipe_akun} - {$created->saldo_awal}\n";
        echo "Match: " . ($template->kode_akun == $created->kode_akun && $template->nama_akun == $created->nama_akun ? "YES" : "NO") . "\n\n";
    }
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$company = \App\Models\Perusahaan::first();
echo "Company: " . $company->nama . " (ID: " . $company->id . ")\n\n";

$newCompanyDesc = "Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.";
$newTeamDesc = "Didukung oleh fullstack developer yang kompeten dan pembimbing berpengalaman, tim ini menghadirkan solusi digital terintegrasi dengan pendekatan strategis, presisi teknis, dan standar kualitas tinggi.";

// Update cover section
$coverSection = \DB::table('catalog_sections')
    ->where('perusahaan_id', $company->id)
    ->where('section_type', 'cover')
    ->first();

if ($coverSection) {
    $content = json_decode($coverSection->content, true);
    $content['company_description'] = $newCompanyDesc;
    \DB::table('catalog_sections')
        ->where('id', $coverSection->id)
        ->update(['content' => json_encode($content), 'updated_at' => now()]);
    echo "✅ Cover description updated\n";
} else {
    echo "❌ Cover section not found\n";
}

// Update team section
$teamSection = \DB::table('catalog_sections')
    ->where('perusahaan_id', $company->id)
    ->where('section_type', 'team')
    ->first();

if ($teamSection) {
    $content = json_decode($teamSection->content, true);
    $content['description'] = $newTeamDesc;
    \DB::table('catalog_sections')
        ->where('id', $teamSection->id)
        ->update(['content' => json_encode($content), 'updated_at' => now()]);
    echo "✅ Team description updated\n";
} else {
    echo "❌ Team section not found\n";
}

// Also update company catalog_description
$company->catalog_description = $newCompanyDesc;
$company->save();
echo "✅ Company catalog_description updated\n\n";

// Verify
echo "--- VERIFIKASI ---\n";
$cover = \DB::table('catalog_sections')->where('perusahaan_id', $company->id)->where('section_type', 'cover')->first();
$team  = \DB::table('catalog_sections')->where('perusahaan_id', $company->id)->where('section_type', 'team')->first();
$coverContent = json_decode($cover->content, true);
$teamContent  = json_decode($team->content, true);
echo "Cover desc: " . $coverContent['company_description'] . "\n\n";
echo "Team desc:  " . $teamContent['description'] . "\n";

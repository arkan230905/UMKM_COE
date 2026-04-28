<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$company = \App\Models\Perusahaan::first();
if (!$company) { echo "No company found\n"; exit; }

echo "Company ID: " . $company->id . "\n";
echo "Company Name: " . $company->nama . "\n\n";

$sections = \DB::table('catalog_sections')->where('perusahaan_id', $company->id)->orderBy('order')->get();
echo "Existing sections: " . $sections->count() . "\n";
foreach ($sections as $s) {
    $content = json_decode($s->content, true);
    echo "  [" . $s->section_type . "] title: " . $s->title . "\n";
    if ($s->section_type === 'cover') {
        echo "    company_description: " . ($content['company_description'] ?? 'N/A') . "\n";
    }
    if ($s->section_type === 'team') {
        echo "    description: " . ($content['description'] ?? 'N/A') . "\n";
        echo "    members: " . count($content['members'] ?? []) . " orang\n";
    }
}

<?php
/**
 * Debug script untuk melihat data yang dikirim form BOP
 * 
 * Jalankan dengan: php debug_bop_submission.php
 */

// Simulasi data yang dikirim form
$formData = [
    'komponen_bop' => [
        0 => ['component' => 'Listrik Mixer', 'rate_per_hour' => '1000'],
        1 => ['component' => 'Mesin Ringan', 'rate_per_hour' => '0'],
        2 => ['component' => 'Penyusutan Alat', 'rate_per_hour' => '0'],
        3 => ['component' => 'Drum / Mixer', 'rate_per_hour' => '0'],
        4 => ['component' => 'Maintenace', 'rate_per_hour' => '0'],
        5 => ['component' => 'Rutin', 'rate_per_hour' => '0'],
        6 => ['component' => 'Kebersihan', 'rate_per_hour' => '0']
    ]
];

echo "=== DATA YANG DIKIRIM FORM ===\n";
print_r($formData);

echo "\n=== FILTER KOMPONEN VALID (rate > 0) ===\n";
$validComponents = array_filter($formData['komponen_bop'], function($component) {
    return !empty($component['component']) && floatval($component['rate_per_hour']) > 0;
});

print_r($validComponents);

echo "\n=== JUMLAH KOMPONEN VALID ===\n";
echo "Count: " . count($validComponents) . "\n";

if (empty($validComponents)) {
    echo "\n❌ ERROR: Tidak ada komponen dengan rate > 0\n";
    echo "Pesan error: Harap isi minimal satu komponen BOP dengan nominal lebih dari 0.\n";
} else {
    echo "\n✅ SUCCESS: Ada " . count($validComponents) . " komponen valid\n";
}

echo "\n=== TEST DENGAN DATA TERISI ===\n";
$formDataFilled = [
    'komponen_bop' => [
        0 => ['component' => 'Listrik Mixer', 'rate_per_hour' => '1000'],
        1 => ['component' => 'Mesin Ringan', 'rate_per_hour' => '500'],
        2 => ['component' => 'Penyusutan Alat', 'rate_per_hour' => '0'],
        3 => ['component' => 'Drum / Mixer', 'rate_per_hour' => '0'],
        4 => ['component' => 'Maintenace', 'rate_per_hour' => '0'],
        5 => ['component' => 'Rutin', 'rate_per_hour' => '600'],
        6 => ['component' => 'Kebersihan', 'rate_per_hour' => '0']
    ]
];

$validComponentsFilled = array_filter($formDataFilled['komponen_bop'], function($component) {
    return !empty($component['component']) && floatval($component['rate_per_hour']) > 0;
});

echo "Komponen terisi:\n";
print_r($validComponentsFilled);
echo "Count: " . count($validComponentsFilled) . "\n";

if (empty($validComponentsFilled)) {
    echo "❌ ERROR\n";
} else {
    echo "✅ SUCCESS: " . count($validComponentsFilled) . " komponen valid\n";
}

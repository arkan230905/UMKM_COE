<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK COA ASET KENDARAAN ===\n\n";

$aset = \App\Models\Aset::find(5);

echo "Aset: {$aset->nama_aset}\n";
echo "ID: {$aset->id}\n";
echo "expense_coa_id: {$aset->expense_coa_id}\n";
echo "accum_depr_coa_id: {$aset->accum_depr_coa_id}\n\n";

if ($aset->expense_coa_id) {
    $expenseCoa = \App\Models\Coa::find($aset->expense_coa_id);
    if ($expenseCoa) {
        echo "Expense COA (Beban Penyusutan):\n";
        echo "  ID: {$expenseCoa->id}\n";
        echo "  Kode: {$expenseCoa->kode_akun}\n";
        echo "  Nama: {$expenseCoa->nama_akun}\n";
        echo "  Tipe: {$expenseCoa->tipe_akun}\n\n";
    }
}

if ($aset->accum_depr_coa_id) {
    $accumCoa = \App\Models\Coa::find($aset->accum_depr_coa_id);
    if ($accumCoa) {
        echo "Accum COA (Akumulasi Penyusutan):\n";
        echo "  ID: {$accumCoa->id}\n";
        echo "  Kode: {$accumCoa->kode_akun}\n";
        echo "  Nama: {$accumCoa->nama_akun}\n";
        echo "  Tipe: {$accumCoa->tipe_akun}\n\n";
    }
}

if ($aset->expense_coa_id === $aset->accum_depr_coa_id) {
    echo "❌ MASALAH: expense_coa_id dan accum_depr_coa_id SAMA!\n";
    echo "   Seharusnya berbeda:\n";
    echo "   - expense_coa_id = COA Beban Penyusutan (tipe: expense)\n";
    echo "   - accum_depr_coa_id = COA Akumulasi Penyusutan (tipe: asset/contra-asset)\n";
}

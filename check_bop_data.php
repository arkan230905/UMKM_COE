<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BOP DATA ===\n\n";

// Check Bops table
echo "1. BOP Table (Master BOP):\n";
$bops = \App\Models\Bop::select('id', 'kode_akun', 'nama_akun', 'budget')->take(5)->get();
if ($bops->count() > 0) {
    foreach ($bops as $bop) {
        echo "   ID: {$bop->id} | Kode: {$bop->kode_akun} | Nama: {$bop->nama_akun} | Budget: Rp " . number_format($bop->budget, 0, ',', '.') . "\n";
    }
} else {
    echo "   No data in bops table\n";
}

echo "\n2. BomProsesBop Table (BOP di BOM):\n";
$bomProsesBops = \App\Models\BomProsesBop::with(['komponenBop', 'bomProses.prosesProduksi'])->take(5)->get();
if ($bomProsesBops->count() > 0) {
    foreach ($bomProsesBops as $bpb) {
        $komponenName = $bpb->komponenBop->nama_komponen ?? 'N/A';
        $prosesName = $bpb->bomProses->prosesProduksi->nama_proses ?? 'N/A';
        echo "   ID: {$bpb->id} | Komponen: {$komponenName} | Proses: {$prosesName} | Qty: {$bpb->kuantitas} | Tarif: Rp " . number_format($bpb->tarif, 0, ',', '.') . " | Total: Rp " . number_format($bpb->total_biaya, 0, ',', '.') . "\n";
    }
} else {
    echo "   No data in bom_proses_bops table\n";
}

echo "\n3. KomponenBop Table (Komponen BOP):\n";
$komponenBops = \App\Models\KomponenBop::select('id', 'kode_komponen', 'nama_komponen')->take(5)->get();
if ($komponenBops->count() > 0) {
    foreach ($komponenBops as $kb) {
        echo "   ID: {$kb->id} | Kode: {$kb->kode_komponen} | Nama: {$kb->nama_komponen}\n";
    }
} else {
    echo "   No data in komponen_bops table\n";
}

echo "\n4. BomJobBOP Table (BOP di Job Costing):\n";
$bomJobBops = \App\Models\BomJobBOP::with('bop')->take(5)->get();
if ($bomJobBops->count() > 0) {
    foreach ($bomJobBops as $bjb) {
        $bopName = $bjb->nama_bop ?? ($bjb->bop->nama_bop ?? 'N/A');
        echo "   ID: {$bjb->id} | Nama: {$bopName} | Jumlah: {$bjb->jumlah} | Tarif: Rp " . number_format($bjb->tarif, 0, ',', '.') . " | Subtotal: Rp " . number_format($bjb->subtotal, 0, ',', '.') . "\n";
    }
} else {
    echo "   No data in bom_job_bop table\n";
}

echo "\n=== DONE ===\n";

<?php

echo "=== FIX HPP DETAIL VIEW COLUMN NAMES ===\n\n";

echo "Updating HPP detail view to use correct column names...\n";

$viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\show.blade.php';
$viewContent = file_get_contents($viewFile);

// Fix the column name from 'j.nama_jabatan' to 'j.nama'
$correctedDataLoading = '
@php
    // Load data based on selected component IDs from bom_job_costings
    $selectedBBBData = [];
    $selectedBTKLData = [];
    $selectedBOPData = [];
    
    if ($bomJobCosting) {
        // Load selected BBB data from bom_job_bbb table
        if ($bomJobCosting->selected_bbb_ids && is_array($bomJobCosting->selected_bbb_ids) && count($bomJobCosting->selected_bbb_ids) > 0) {
            $selectedBBBData = DB::table(\'bom_job_bbb as bbb\')
                ->leftJoin(\'bahan_bakus as bb\', \'bbb.bahan_baku_id\', \'=\', \'bb.id\')
                ->leftJoin(\'satuans as s\', \'bb.satuan_id\', \'=\', \'s.id\')
                ->whereIn(\'bbb.id\', $bomJobCosting->selected_bbb_ids)
                ->where(\'bbb.user_id\', auth()->id())
                ->select(
                    \'bbb.id\',
                    \'bb.nama_bahan\',
                    \'bbb.jumlah\',
                    \'bbb.satuan\',
                    \'bbb.harga_satuan\',
                    \'bbb.subtotal\',
                    \'s.nama as satuan_nama\'
                )
                ->orderBy(\'bbb.created_at\', \'desc\')
                ->get();
        }
        
        // Load selected BTKL data from proses_produksis table
        if ($bomJobCosting->selected_btkl_ids && is_array($bomJobCosting->selected_btkl_ids) && count($bomJobCosting->selected_btkl_ids) > 0) {
            $selectedBTKLData = DB::table(\'proses_produksis as pp\')
                ->leftJoin(\'jabatans as j\', \'pp.jabatan_id\', \'=\', \'j.id\')
                ->whereIn(\'pp.id\', $bomJobCosting->selected_btkl_ids)
                ->where(\'pp.user_id\', auth()->id())
                ->select(
                    \'pp.id\',
                    \'pp.kode_proses\',
                    \'pp.nama_proses\',
                    \'j.nama as nama_jabatan\',
                    \'pp.tarif_btkl\',
                    \'pp.kapasitas_per_jam\',
                    \'pp.btkl_per_produk\',
                    \'pp.bop_per_produk\'
                )
                ->orderBy(\'pp.nama_proses\')
                ->get();
        }
        
        // Load selected BOP data from bop_proses table
        if ($bomJobCosting->selected_btkl_ids && is_array($bomJobCosting->selected_btkl_ids) && count($bomJobCosting->selected_btkl_ids) > 0) {
            $selectedBOPData = DB::table(\'bop_proses as bp\')
                ->leftJoin(\'coas as c\', \'bp.coa_id\', \'=\', \'c.id\')
                ->whereIn(\'bp.proses_btkl_id\', $bomJobCosting->selected_btkl_ids)
                ->where(\'bp.user_id\', auth()->id())
                ->select(
                    \'bp.id\',
                    \'bp.nama_komponen\',
                    \'bp.jumlah\',
                    \'bp.tarif\',
                    \'bp.total\',
                    \'c.nama_coa\'
                )
                ->orderBy(\'bp.nama_komponen\')
                ->get();
        }
    }
    
    // Calculate totals for display
    $totalBBB = $selectedBBBData->sum(\'subtotal\');
    $totalBTKL = $selectedBTKLData->sum(\'btkl_per_produk\');
    $totalBOP = $selectedBOPData->sum(\'total\');
    $totalBiayaBahan = $totalBBB; // Only BBB for biaya bahan
@endphp
';

// Find and replace the existing @php block
$pattern = '/@php.*?@endphp/s';
if (preg_match($pattern, $viewContent)) {
    $newViewContent = preg_replace($pattern, $correctedDataLoading, $viewContent);
    file_put_contents($viewFile, $newViewContent);
    echo "✅ Updated HPP detail view with correct column names\n";
} else {
    echo "❌ Could not find @php block to replace\n";
}

echo "\n=== HPP DETAIL VIEW FIXED ===\n";

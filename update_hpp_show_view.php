<?php

echo "=== UPDATE HPP SHOW VIEW FOR DYNAMIC COMPONENTS ===\n\n";

echo "Updating HPP show view to display data based on selected component IDs...\n";

$viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\show.blade.php';
$viewContent = file_get_contents($viewFile);

// Add new section at the beginning of the content section to load dynamic data
$dynamicDataLoading = '
@php
    // Load dynamic component data based on selected IDs
    $selectedBBBData = [];
    $selectedBTKLData = [];
    $selectedBOPData = [];
    
    if ($bomJobCosting) {
        // Load selected BBB data
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
        
        // Load selected BTKL data
        if ($bomJobCosting->selected_btkl_ids && is_array($bomJobCosting->selected_btkl_ids) && count($bomJobCosting->selected_btkl_ids) > 0) {
            $selectedBTKLData = DB::table(\'proses_btkl as pb\')
                ->leftJoin(\'jabatans as j\', \'pb.jabatan_id\', \'=\', \'j.id\')
                ->whereIn(\'pb.id\', $bomJobCosting->selected_btkl_ids)
                ->where(\'pb.user_id\', auth()->id())
                ->select(
                    \'pb.id\',
                    \'pb.kode_proses\',
                    \'pb.nama_proses\',
                    \'j.nama_jabatan\',
                    \'pb.tarif_btkl\',
                    \'pb.kapasitas_per_jam\',
                    \'pb.btkl_per_produk\',
                    \'pb.bop_per_produk\'
                )
                ->orderBy(\'pb.nama_proses\')
                ->get();
        }
        
        // Load selected BOP data (from BTKL processes)
        if ($bomJobCosting->selected_btkl_ids && is_array($bomJobCosting->selected_btkl_ids) && count($bomJobCosting->selected_btkl_ids) > 0) {
            // Get BOP components from selected BTKL processes
            $selectedBOPData = DB::table(\'bop_komponens as bk\')
                ->leftJoin(\'coas as c\', \'bk.coa_id\', \'=\', \'c.id\')
                ->whereIn(\'bk.proses_btkl_id\', $bomJobCosting->selected_btkl_ids)
                ->where(\'bk.user_id\', auth()->id())
                ->select(
                    \'bk.id\',
                    \'bk.nama_komponen\',
                    \'bk.jumlah\',
                    \'bk.tarif\',
                    \'bk.total\',
                    \'c.nama_coa\'
                )
                ->orderBy(\'bk.nama_komponen\')
                ->get();
        }
    }
@endphp
';

// Find the position after @section(\'content\') and insert the dynamic data loading
$insertPosition = strpos($viewContent, '@section(\'content\')');
if ($insertPosition !== false) {
    // Find the next line after the section declaration
    $nextLinePosition = strpos($viewContent, "\n", $insertPosition) + 1;
    $newViewContent = substr_replace($viewContent, $dynamicDataLoading . "\n", $nextLinePosition, 0);
    file_put_contents($viewFile, $newViewContent);
    echo "✅ Added dynamic data loading to HPP show view\n";
} else {
    echo "❌ Could not find @section(\'content\') in view\n";
}

echo "\n=== HPP SHOW VIEW UPDATED ===\n";

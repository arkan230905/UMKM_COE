<?php

echo "=== CREATE FIXED HPP DETAIL VIEW ===\n\n";

echo "Creating new HPP detail view with corrected BTKL query...\n";

$viewContent = '@extends(\'layouts.app\')

@section(\'title\', \'Detail Harga Pokok Produksi\')

@section(\'content\')

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
        
        // Load selected BTKL data from proses_produksis table with corrected columns
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
                    \'pp.satuan_btkl\',
                    \'pp.kapasitas_per_jam\',
                    DB::raw(\'(pp.tarif_btkl / NULLIF(pp.kapasitas_per_jam, 0)) as btkl_per_produk\'),
                    DB::raw(\'0 as bop_per_produk\')
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

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-eye me-2"></i>Detail Harga Pokok Produksi
        </h2>
        <a href="{{ route(\'master-data.harga-pokok-produksi.index\') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Product Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Informasi Produk
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $produk->nama_produk }}</h5>
                    @if($produk->barcode)
                        <p class="text-muted mb-0">Kode: {{ $produk->barcode }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <small class="text-muted">Total HPP</small>
                        <div class="h4 text-primary fw-bold">
                            Rp {{ number_format($totalBBB + $totalBTKL + $totalBOP, 0, \',\', \'.\') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BBB Details -->
    @if($selectedBBBData->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Detail Biaya Bahan Baku
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center">No</th>
                            <th style="width: 25%;">Nama Bahan Baku</th>
                            <th style="width: 15%;" class="text-center">Jumlah</th>
                            <th style="width: 15%;" class="text-center">Harga Satuan</th>
                            <th style="width: 15%;" class="text-end">Subtotal</th>
                            <th style="width: 10%;" class="text-center">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedBBBData as $index => $bbb)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $bbb->nama_bahan }}</div>
                            </td>
                            <td class="text-center">
                                {{ number_format($bbb->jumlah, 2, \',\', \'.\') }} {{ $bbb->satuan_nama ?? $bbb->satuan }}
                            </td>
                            <td class="text-center">
                                Rp {{ number_format($bbb->harga_satuan, 0, \',\', \'.\') }}
                            </td>
                            <td class="text-end fw-bold text-primary">
                                Rp {{ number_format($bbb->subtotal, 0, \',\', \'.\') }}
                            </td>
                            <td class="text-center">
                                <small class="text-muted">{{ \Carbon\Carbon::parse($bbb->created_at)->format(\'d/m/Y\') }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end fw-bold">TOTAL:</th>
                            <th class="text-end fw-bold text-primary h5">
                                Rp {{ number_format($totalBBB, 0, \',\', \'.\') }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- BTKL Details -->
    @if($selectedBTKLData->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-users me-2"></i>Detail Biaya Tenaga Kerja Langsung
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center">No</th>
                            <th style="width: 15%;">Kode Proses</th>
                            <th style="width: 25%;">Nama Proses</th>
                            <th style="width: 15%;">Jabatan</th>
                            <th style="width: 15%;" class="text-center">Tarif/Jam</th>
                            <th style="width: 15%;" class="text-end">BTKL/pcs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedBTKLData as $index => $btkl)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $btkl->kode_proses }}</td>
                            <td><strong>{{ $btkl->nama_proses }}</strong></td>
                            <td>{{ $btkl->nama_jabatan }}</td>
                            <td class="text-center">
                                Rp {{ number_format($btkl->tarif_btkl, 0, \',\', \'.\') }}/{{ $btkl->satuan_btkl }}
                            </td>
                            <td class="text-end fw-bold text-info">
                                Rp {{ number_format($btkl->btkl_per_produk, 0, \',\', \'.\') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end fw-bold">TOTAL:</th>
                            <th class="text-end fw-bold text-info h5">
                                Rp {{ number_format($totalBTKL, 0, \',\', \'.\') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- BOP Details -->
    @if($selectedBOPData->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">
                <i class="fas fa-industry me-2"></i>Detail Biaya Overhead Pabrik
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center">No</th>
                            <th style="width: 30%;">Nama Komponen</th>
                            <th style="width: 15%;" class="text-center">Jumlah</th>
                            <th style="width: 15%;" class="text-center">Tarif</th>
                            <th style="width: 15%;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedBOPData as $index => $bop)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td><strong>{{ $bop->nama_komponen }}</strong></td>
                            <td class="text-center">{{ $bop->jumlah }}</td>
                            <td class="text-center">
                                Rp {{ number_format($bop->tarif, 0, \',\', \'.\') }}
                            </td>
                            <td class="text-end fw-bold text-warning">
                                Rp {{ number_format($bop->total, 0, \',\', \'.\') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end fw-bold">TOTAL:</th>
                            <th class="text-end fw-bold text-warning h5">
                                Rp {{ number_format($totalBOP, 0, \',\', \'.\') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">
                <i class="fas fa-calculator me-2"></i>Ringkasan Total HPP
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center p-3 bg-success text-white rounded">
                        <h6>Biaya Bahan</h6>
                        <h4>Rp {{ number_format($totalBBB, 0, \',\', \'.\') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-info text-white rounded">
                        <h6>Total BTKL</h6>
                        <h4>Rp {{ number_format($totalBTKL, 0, \',\', \'.\') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-warning text-dark rounded">
                        <h6>Total BOP</h6>
                        <h4>Rp {{ number_format($totalBOP, 0, \',\', \'.\') }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-dark text-white rounded">
                        <h6>Total HPP</h6>
                        <h4>Rp {{ number_format($totalBBB + $totalBTKL + $totalBOP, 0, \',\', \'.\') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection';

// Create the new view file
$viewFile = 'c:\UMKM_COE\resources\views\master-data\bom\show.blade.php';
file_put_contents($viewFile, $viewContent);

echo "✅ Created fixed HPP detail view at: $viewFile\n";
echo "✅ Fixed BTKL query with correct columns:\n";
echo "  - Added satuan_btkl column\n";
echo "  - Calculated btkl_per_produk dynamically\n";
echo "  - Set bop_per_produk to 0 (temporary)\n";
echo "  - Proper error handling with NULLIF\n\n";

echo "=== FIXED HPP DETAIL VIEW CREATED ===\n";

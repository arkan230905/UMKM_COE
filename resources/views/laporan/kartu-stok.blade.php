@extends('layouts.app')

@section('title', 'Laporan Kartu Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-line me-2"></i>Laporan Kartu Stok
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-1">
                <i class="fas fa-filter me-2"></i>Filter Laporan
            </h5>
            
            <form method="GET" class="d-flex align-items-center gap-2" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 400px;">
                    <select name="material_type" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 20px 0 0 0; outline: none; box-shadow: none; font-size: 14px;">
                        <option value="">Pilih Jenis Material</option>
                        <option value="bahan_baku" {{ request('material_type') == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="bahan_pendukung" {{ request('material_type') == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                    </select>
                    
                    <select name="material_id" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Pilih Material</option>
                        @if(request('material_type') == 'bahan_baku')
                            @foreach($bahanBakus as $bb)
                                <option value="{{ $bb->id }}" {{ request('material_id') == $bb->id ? 'selected' : '' }}>
                                    {{ $bb->nama_bahan }}
                                </option>
                            @endforeach
                        @elseif(request('material_type') == 'bahan_pendukung')
                            @foreach($bahanPendukungs as $bp)
                                <option value="{{ $bp->id }}" {{ request('material_id') == $bp->id ? 'selected' : '' }}>
                                    {{ $bp->nama_bahan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
                
                @if(request('material_type') || request('material_id'))
                    <a href="{{ route('laporan.kartu-stok') }}" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 8px 15px; font-size: 14px;">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Kartu Stok Tables -->
    @if(request('material_type') && request('material_id'))
        @php
            if(request('material_type') == 'bahan_baku') {
                $material = $bahanBakus->find(request('material_id'));
                $movements = $stockMovements->where('item_type', 'material')->where('item_id', request('material_id'));
            } else {
                $material = $bahanPendukungs->find(request('material_id'));
                $movements = $stockMovements->where('item_type', 'support')->where('item_id', request('material_id'));
            }
            
            $satuanUtama = $material->satuan->nama ?? 'Unit';
            $subSatuans = [
                ['nama' => $material->sub_satuan ?? 'Gram', 'nilai' => $material->sub_satuan_nilai ?? 1000],
                ['nama' => 'Potong', 'nilai' => 4],
                ['nama' => 'Ons', 'nilai' => 10]
            ];
        @endphp

        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    Kartu Stok - {{ $material->nama_bahan }} (Satuan Utama {{ $satuanUtama }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" style="font-size: 13px;">
                        <thead class="table-success">
                            <tr>
                                <th rowspan="2" class="text-center align-middle" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Tanggal</th>
                                <th rowspan="2" class="text-center align-middle" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Referensi</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Stok Awal</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Pembelian</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Retur</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Produksi</th>
                                <th colspan="4" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Total Stok</th>
                            </tr>
                            <tr style="background-color: #6c9f6c; color: white;">
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningStock = 0;
                                $runningValue = 0;
                                $currentUnitCost = 0; // Track current unit cost from FIFO
                            @endphp
                            
                            @forelse($movements->sortBy('tanggal') as $movement)
                                @php
                                    if($movement->direction == 'in') {
                                        $runningStock += $movement->qty;
                                        $runningValue += ($movement->qty * $movement->unit_cost);
                                        $currentUnitCost = $movement->unit_cost; // Set current unit cost from incoming stock
                                    } else {
                                        $runningStock -= $movement->qty; // SUBTRACT for outgoing (production)
                                        $runningValue -= ($movement->qty * $movement->unit_cost);
                                        // Keep the same unit cost for outgoing (FIFO principle)
                                    }
                                    
                                    // Calculate sub units
                                    $subSatuan1 = $runningStock * ($subSatuans[0]['nilai'] ?? 1000);
                                    $subSatuan2 = $runningStock * ($subSatuans[1]['nilai'] ?? 4);
                                    $subSatuan3 = $runningStock * ($subSatuans[2]['nilai'] ?? 10);
                                @endphp
                                
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($movement->tanggal)->format('d/m/Y') }}</td>
                                    
                                    <!-- Referensi -->
                                    <td class="text-center">
                                        @if($movement->ref_type == 'initial_stock')
                                            Saldo Awal
                                        @elseif($movement->ref_type == 'purchase')
                                            Pembelian #{{ $movement->ref_id }}
                                        @elseif($movement->ref_type == 'purchase_return')
                                            @if($movement->direction == 'out')
                                                Retur Keluar #{{ $movement->ref_id }}
                                            @else
                                                Retur Masuk #{{ $movement->ref_id }}
                                            @endif
                                        @elseif($movement->ref_type == 'production')
                                            Production #{{ $movement->ref_id }}
                                        @else
                                            {{ ucfirst($movement->ref_type) }}
                                        @endif
                                    </td>
                                    
                                    <!-- Stok Awal (3 columns) -->
                                    @if($movement->direction == 'in' && $movement->ref_type == 'initial_stock')
                                        <td class="text-center">{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Pembelian (3 columns) -->
                                    @if($movement->direction == 'in' && $movement->ref_type == 'purchase')
                                        <td class="text-center">{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Retur (3 columns) -->
                                    @if($movement->ref_type == 'purchase_return')
                                        @if($movement->direction == 'out')
                                            {{-- Retur Keluar (Barang dikirim ke vendor) --}}
                                            <td class="text-center text-danger">-{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
                                            <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
                                            <td class="text-end text-danger">-Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
                                        @else
                                            {{-- Retur Masuk (Barang pengganti diterima) --}}
                                            <td class="text-center text-success">+{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
                                            <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
                                            <td class="text-end text-success">+Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
                                        @endif
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Produksi (3 columns) -->
                                    @if($movement->direction == 'out' && $movement->ref_type == 'production')
                                        <td class="text-center">{{ number_format($movement->qty, 4) }} {{ $satuanUtama }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Total Stok (4 columns) -->
                                    <td class="text-center">{{ number_format($runningStock, 4) }} {{ $satuanUtama }}</td>
                                    <td class="text-end">Rp {{ number_format($currentUnitCost, 2, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($runningStock * $currentUnitCost, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($runningStock > 0)
                                            <span class="badge bg-success">Tersedia</span>
                                        @else
                                            <span class="badge bg-danger">Habis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada pergerakan stok untuk material ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Jenis Material dan Material untuk Melihat Kartu Stok</h5>
                <p class="text-muted">Gunakan filter di atas untuk menampilkan laporan kartu stok</p>
            </div>
        </div>
    @endif
</div>

<script>
// Auto update material dropdown when material type changes
document.querySelector('select[name="material_type"]').addEventListener('change', function() {
    // Clear material selection when type changes
    document.querySelector('select[name="material_id"]').value = '';
    
    // Submit form to reload with new material type
    this.form.submit();
});
</script>

@endsection
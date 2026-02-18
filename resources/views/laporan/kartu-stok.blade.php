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
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Pembelian</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Produksi</th>
                                <th colspan="2" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Total Jika Dalam Satuan Utama</th>
                                <th colspan="4" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">JUMLAH STOK</th>
                            </tr>
                            <tr style="background-color: #6c9f6c; color: white;">
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Harga</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Qty</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Total</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Stok Satuan Utama</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Sub Satuan 1</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Sub Satuan 2</th>
                                <th class="text-center" style="border: 1px solid #5a8a5a;">Sub Satuan 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningStock = 0;
                                $runningValue = 0;
                            @endphp
                            
                            @forelse($movements->sortBy('created_at') as $movement)
                                @php
                                    if($movement->movement_type == 'in') {
                                        $runningStock += $movement->quantity;
                                        $runningValue += ($movement->quantity * $movement->unit_price);
                                    } else {
                                        $runningStock -= $movement->quantity;
                                        $runningValue -= ($movement->quantity * $movement->unit_price);
                                    }
                                    
                                    // Calculate sub units
                                    $subSatuan1 = $runningStock * ($subSatuans[0]['nilai'] ?? 1000);
                                    $subSatuan2 = $runningStock * ($subSatuans[1]['nilai'] ?? 4);
                                    $subSatuan3 = $runningStock * ($subSatuans[2]['nilai'] ?? 10);
                                @endphp
                                
                                <tr>
                                    <td class="text-center">{{ $movement->created_at->format('d/m/Y') }}</td>
                                    
                                    <!-- Pembelian (3 columns) -->
                                    @if($movement->movement_type == 'in' && $movement->reference_type == 'purchase')
                                        <td class="text-center">{{ number_format($movement->quantity, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-end">RP{{ number_format($movement->unit_price, 2, '.', ' ') }}</td>
                                        <td class="text-end">RP{{ number_format($movement->quantity * $movement->unit_price, 2, '.', ' ') }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Produksi (3 columns) -->
                                    @if($movement->movement_type == 'out' && $movement->reference_type == 'production')
                                        <td class="text-center">{{ number_format($movement->quantity, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-end">RP{{ number_format($movement->unit_price, 2, '.', ' ') }}</td>
                                        <td class="text-end">RP{{ number_format($movement->quantity * $movement->unit_price, 2, '.', ' ') }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    
                                    <!-- Total Jika Dalam Satuan Utama (2 columns) -->
                                    <td class="text-center">{{ number_format($runningStock, 0) }} {{ $satuanUtama }}</td>
                                    <td class="text-end">RP{{ number_format($runningValue, 2, '.', ' ') }}</td>
                                    
                                    <!-- Jumlah Stok (4 columns) -->
                                    <td class="text-center">{{ number_format($runningStock, 0) }} {{ $satuanUtama }}</td>
                                    <td class="text-center">{{ number_format($subSatuan1, 0) }} {{ $subSatuans[0]['nama'] ?? 'Gram' }}</td>
                                    <td class="text-center">{{ number_format($subSatuan2, 0) }} {{ $subSatuans[1]['nama'] ?? 'Potong' }}</td>
                                    <td class="text-center">{{ number_format($subSatuan3, 0) }} {{ $subSatuans[2]['nama'] ?? 'Ons' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-4">
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
    this.form.submit();
});
</script>

@endsection
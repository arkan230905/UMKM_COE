@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Laporan Stok
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
            
            <form method="GET" action="{{ route('laporan.stok') }}" class="d-flex align-items-center gap-2" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 400px;">
                    <select name="tipe" class="form-select border-0" id="tipeSelect" style="padding: 8px 15px; background: white; border-radius: 20px 0 0 0; outline: none; box-shadow: none; font-size: 14px;">
                        <option value="material" {{ request('tipe', 'material') == 'material' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="product" {{ request('tipe') == 'product' ? 'selected' : '' }}>Produk</option>
                        <option value="bahan_pendukung" {{ request('tipe') == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                    </select>
                    
                    <select name="item_id" class="form-select border-0" id="itemSelect" style="padding: 8px 15px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Pilih Item</option>
                        @if(request('tipe', 'material') == 'material')
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" {{ request('item_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_bahan }}
                                </option>
                            @endforeach
                        @elseif(request('tipe') == 'product')
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('item_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_produk }}
                                </option>
                            @endforeach
                        @elseif(request('tipe') == 'bahan_pendukung')
                            @foreach($bahanPendukungs as $bp)
                                <option value="{{ $bp->id }}" {{ request('item_id') == $bp->id ? 'selected' : '' }}>
                                    {{ $bp->nama_bahan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
                
                @if(request('tipe') || request('item_id'))
                    <a href="{{ route('laporan.stok') }}" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 8px 15px; font-size: 14px;">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Kartu Stok Tables -->
    @if(request('item_id'))
        @php
            if($tipe == 'material') {
                $material = $materials->find(request('item_id'));
            } elseif($tipe == 'product') {
                $material = $products->find(request('item_id'));
            } else {
                $material = $bahanPendukungs->find(request('item_id'));
            }
            
            $namaItem = $material->nama_bahan ?? $material->nama_produk ?? 'Item';
            $satuanUtama = $material->satuan->nama ?? $material->satuanRelation->nama ?? 'Unit';
            
            // Sub satuan configuration
            $subSatuans = [
                ['nama' => $material->sub_satuan ?? 'Gram', 'nilai' => $material->sub_satuan_nilai ?? 1000],
                ['nama' => 'Potong', 'nilai' => 4],
                ['nama' => 'Ons', 'nilai' => 10]
            ];
        @endphp

        <div class="card">
            <div class="card-header" style="background-color: #6c9f6c; color: white;">
                <h5 class="mb-0">
                    Kartu Stok - {{ $namaItem }} (Satuan Utama {{ $satuanUtama }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table table-bordered mb-0" style="font-size: 13px; min-width: 1800px; white-space: nowrap;">
                        <thead class="table-success">
                            <tr>
                                <th rowspan="2" class="text-center align-middle" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Tanggal</th>
                                <th colspan="3" class="text-center" style="background-color: #6c9f6c; color: white; border: 1px solid #5a8a5a;">Stok Awal</th>
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
                                $stokAwalQty = $material->stok ?? 0; // Stok awal dari master data
                                $stokAwalValue = $stokAwalQty * ($material->harga_satuan ?? $material->harga_pokok ?? 0);
                                $runningStock = $stokAwalQty;
                                $runningValue = $stokAwalValue;
                                $isFirstRow = true;
                                
                                // Calculate sub units for initial stock
                                $subSatuan1Awal = $stokAwalQty * ($subSatuans[0]['nilai'] ?? 1000);
                                $subSatuan2Awal = $stokAwalQty * ($subSatuans[1]['nilai'] ?? 4);
                                $subSatuan3Awal = $stokAwalQty * ($subSatuans[2]['nilai'] ?? 10);
                            @endphp
                            
                            @if(count($running) > 0)
                                @foreach($running as $row)
                                    @php
                                        $runningStock = $row['saldo_qty'];
                                        $runningValue = $row['saldo_nilai'];
                                        
                                        // Calculate sub units
                                        $subSatuan1 = $runningStock * ($subSatuans[0]['nilai'] ?? 1000);
                                        $subSatuan2 = $runningStock * ($subSatuans[1]['nilai'] ?? 4);
                                        $subSatuan3 = $runningStock * ($subSatuans[2]['nilai'] ?? 10);
                                    @endphp
                                    
                                    <tr>
                                        <td class="text-center" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                                        
                                        <!-- Stok Awal (3 columns) - hanya tampil di baris pertama -->
                                        @if($isFirstRow)
                                            <td class="text-center" style="white-space: nowrap;">{{ number_format($stokAwalQty, 0) }} {{ $satuanUtama }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($stokAwalQty > 0 ? $stokAwalValue / $stokAwalQty : 0, 0, ',', '.') }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($stokAwalValue, 0, ',', '.') }}</td>
                                        @else
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        @endif
                                        
                                        <!-- Pembelian (3 columns) -->
                                        @if($row['in_qty'] > 0)
                                            <td class="text-center" style="white-space: nowrap;">{{ number_format($row['in_qty'], 0) }} {{ $satuanUtama }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($row['in_nilai'] / $row['in_qty'], 0, ',', '.') }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($row['in_nilai'], 0, ',', '.') }}</td>
                                        @else
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        @endif
                                        
                                        <!-- Produksi (3 columns) -->
                                        @if($row['out_qty'] > 0)
                                            <td class="text-center" style="white-space: nowrap;">{{ number_format($row['out_qty'], 0) }} {{ $satuanUtama }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($row['out_nilai'] / $row['out_qty'], 0, ',', '.') }}</td>
                                            <td class="text-end" style="white-space: nowrap;">RP{{ number_format($row['out_nilai'], 0, ',', '.') }}</td>
                                        @else
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        @endif
                                        
                                        <!-- Total Jika Dalam Satuan Utama (2 columns) -->
                                        <td class="text-center" style="white-space: nowrap;">{{ number_format($runningStock, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-end" style="white-space: nowrap;">RP{{ number_format($runningValue, 0, ',', '.') }}</td>
                                        
                                        <!-- Jumlah Stok (4 columns) -->
                                        <td class="text-center" style="white-space: nowrap;">{{ number_format($runningStock, 0) }} {{ $satuanUtama }}</td>
                                        <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan1, 0) }} {{ $subSatuans[0]['nama'] ?? 'Gram' }}</td>
                                        <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan2, 0) }} {{ $subSatuans[1]['nama'] ?? 'Potong' }}</td>
                                        <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan3, 0) }} {{ $subSatuans[2]['nama'] ?? 'Ons' }}</td>
                                    </tr>
                                    
                                    @php
                                        $isFirstRow = false;
                                    @endphp
                                @endforeach
                            @else
                                <!-- Tampilkan stok awal meskipun belum ada transaksi -->
                                <tr>
                                    <td class="text-center" style="white-space: nowrap;">{{ now()->format('d/m/Y') }}</td>
                                    
                                    <!-- Stok Awal (3 columns) -->
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($stokAwalQty, 0) }} {{ $satuanUtama }}</td>
                                    <td class="text-end" style="white-space: nowrap;">RP{{ number_format($stokAwalQty > 0 ? $stokAwalValue / $stokAwalQty : 0, 0, ',', '.') }}</td>
                                    <td class="text-end" style="white-space: nowrap;">RP{{ number_format($stokAwalValue, 0, ',', '.') }}</td>
                                    
                                    <!-- Pembelian (3 columns) - kosong -->
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    
                                    <!-- Produksi (3 columns) - kosong -->
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    
                                    <!-- Total Jika Dalam Satuan Utama (2 columns) -->
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($stokAwalQty, 0) }} {{ $satuanUtama }}</td>
                                    <td class="text-end" style="white-space: nowrap;">RP{{ number_format($stokAwalValue, 0, ',', '.') }}</td>
                                    
                                    <!-- Jumlah Stok (4 columns) -->
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($stokAwalQty, 0) }} {{ $satuanUtama }}</td>
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan1Awal, 0) }} {{ $subSatuans[0]['nama'] ?? 'Gram' }}</td>
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan2Awal, 0) }} {{ $subSatuans[1]['nama'] ?? 'Potong' }}</td>
                                    <td class="text-center" style="white-space: nowrap;">{{ number_format($subSatuan3Awal, 0) }} {{ $subSatuans[2]['nama'] ?? 'Ons' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Item untuk Melihat Kartu Stok</h5>
                <p class="text-muted">Gunakan filter di atas untuk menampilkan laporan kartu stok</p>
            </div>
        </div>
    @endif
</div>

<script>
// Auto update item dropdown when tipe changes
document.getElementById('tipeSelect').addEventListener('change', function() {
    this.form.submit();
});
</script>

@endsection

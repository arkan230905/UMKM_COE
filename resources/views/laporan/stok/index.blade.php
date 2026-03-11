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
            
            <form method="GET" action="{{ route('laporan.stok') }}" class="d-flex align-items-center gap-2 flex-wrap" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 400px;">
                    <select name="tipe" class="form-select border-0" id="tipeSelect" style="padding: 8px 15px; background: white; border-radius: 20px 0 0 0; outline: none; box-shadow: none; font-size: 14px;">
                        <option value="material" {{ request('tipe', 'material') == 'material' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="product" {{ request('tipe') == 'product' ? 'selected' : '' }}>Produk</option>
                        <option value="bahan_pendukung" {{ request('tipe') == 'bahan_pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                    </select>
                    
                    <select name="item_id" class="form-select border-0" id="itemSelect" style="padding: 8px 15px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Pilih Item</option>
                        @if($tipe == 'material')
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" {{ request('item_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama_bahan }}
                                </option>
                            @endforeach
                        @elseif($tipe == 'product')
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('item_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_produk }}
                                </option>
                            @endforeach
                        @elseif($tipe == 'bahan_pendukung')
                            @foreach($bahanPendukungs as $bp)
                                <option value="{{ $bp->id }}" {{ request('item_id') == $bp->id ? 'selected' : '' }}>
                                    {{ $bp->nama_bahan }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Satuan Filter -->
                @if(request('item_id') && isset($availableSatuans) && count($availableSatuans) >= 1)
                    <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white;">
                        <select name="satuan_id" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 20px; outline: none; box-shadow: none; font-size: 14px;">
                            <option value="">Satuan Utama ({{ $availableSatuans[0]['nama'] ?? 'Default' }})</option>
                            @foreach($availableSatuans as $satuan)
                                <option value="{{ $satuan['id'] }}" {{ request('satuan_id') == $satuan['id'] ? 'selected' : '' }}>
                                    {{ $satuan['nama'] }}{{ $satuan['is_primary'] ? ' (Utama)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
                
                @if(request('tipe') || request('item_id') || request('satuan_id'))
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
            $mainSatuan = $material->satuan;
            $mainSatuanNama = $mainSatuan->nama ?? 'Unit';
            
            // Get selected satuan for display
            $selectedSatuan = null;
            if(request('satuan_id')) {
                foreach($availableSatuans as $satuan) {
                    if($satuan['id'] == request('satuan_id')) {
                        $selectedSatuan = $satuan;
                        break;
                    }
                }
            }
            
            // If no specific satuan selected, use primary satuan
            if(!$selectedSatuan) {
                $selectedSatuan = [
                    'id' => $mainSatuan->id,
                    'nama' => $mainSatuan->nama,
                    'is_primary' => true,
                    'conversion_to_primary' => 1
                ];
            }
            
            $displaySatuanNama = $selectedSatuan['nama'];
            $conversionToPrimary = $selectedSatuan['conversion_to_primary'];
        @endphp

        <div class="card">
            <div class="card-header" style="background-color: #6c9f6c; color: white;">
                <h5 class="mb-0">
                    Kartu Stok - {{ $namaItem }} (Satuan: {{ $displaySatuanNama }})
                    @if(!$selectedSatuan['is_primary'])
                        <small class="ms-2">1 {{ $mainSatuanNama }} = {{ number_format(1 / $conversionToPrimary, 0) }} {{ $displaySatuanNama }}</small>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @if(!empty($dailyStock))
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" style="font-size: 12px;">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle" style="width: 80px;">Tanggal</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 100px;">Referensi</th>
                                    <th colspan="3" class="text-center">Stok Awal</th>
                                    <th colspan="3" class="text-center">Pembelian</th>
                                    <th colspan="3" class="text-center">Produksi</th>
                                    <th colspan="3" class="text-center">Total Stok</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-center" style="width: 80px;">Harga</th>
                                    <th class="text-center" style="width: 100px;">Total</th>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-center" style="width: 80px;">Harga</th>
                                    <th class="text-center" style="width: 100px;">Total</th>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-center" style="width: 80px;">Harga</th>
                                    <th class="text-center" style="width: 100px;">Total</th>
                                    <th class="text-center" style="width: 60px;">Qty</th>
                                    <th class="text-center" style="width: 80px;">Harga</th>
                                    <th class="text-center" style="width: 100px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyStock as $day)
                                    @php
                                        $conversionRatio = $selectedSatuan['conversion_to_primary'] ?? 1;
                                        $saldoAwalQtyDisplay = $day['saldo_awal_qty'] / $conversionRatio;
                                        $pembelianQtyDisplay = $day['pembelian_qty'] / $conversionRatio;
                                        $produksiQtyDisplay = $day['produksi_qty'] / $conversionRatio;
                                        $saldoAkhirQtyDisplay = $day['saldo_akhir_qty'] / $conversionRatio;

                                        $saldoAwalHarga = $saldoAwalQtyDisplay > 0 ? $day['saldo_awal_nilai'] / $saldoAwalQtyDisplay : 0;
                                        $pembelianHarga = $pembelianQtyDisplay > 0 ? $day['pembelian_nilai'] / $pembelianQtyDisplay : 0;
                                        $produksiHarga = $produksiQtyDisplay > 0 ? $day['produksi_nilai'] / $produksiQtyDisplay : 0;
                                        $saldoAkhirHarga = $saldoAkhirQtyDisplay > 0 ? $day['saldo_akhir_nilai'] / $saldoAkhirQtyDisplay : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($day['tanggal'])->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @if($day['is_opening_balance'] ?? false)
                                                <span class="badge bg-primary">Saldo Awal</span>
                                            @else
                                                {{ ucfirst($day['ref_type'] ?? '') }} #{{ $day['ref_id'] ?? '' }}
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($saldoAwalQtyDisplay, 0, ',', '.') }} {{ $displaySatuanNama }}</td>
                                        <td class="text-end">Rp {{ number_format($saldoAwalHarga, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($day['saldo_awal_nilai'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($pembelianQtyDisplay, 0, ',', '.') }} {{ $displaySatuanNama }}</td>
                                        <td class="text-end">Rp {{ number_format($pembelianHarga, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($day['pembelian_nilai'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($produksiQtyDisplay, 0, ',', '.') }} {{ $displaySatuanNama }}</td>
                                        <td class="text-end">Rp {{ number_format($produksiHarga, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($day['produksi_nilai'], 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($saldoAkhirQtyDisplay, 0, ',', '.') }} {{ $displaySatuanNama }}</td>
                                        <td class="text-end">Rp {{ number_format($saldoAkhirHarga, 2, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($day['saldo_akhir_nilai'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th class="text-center" colspan="10">TOTAL AKHIR</th>
                                    <th class="text-end">{{ number_format(end($dailyStock)['saldo_akhir_qty'] / ($selectedSatuan['conversion_to_primary'] ?? 1), 0, ',', '.') }} {{ $displaySatuanNama }}</th>
                                    <th class="text-end">Rp {{ number_format(end($dailyStock)['saldo_akhir_qty'] > 0 ? end($dailyStock)['saldo_akhir_nilai'] / (end($dailyStock)['saldo_akhir_qty'] / ($selectedSatuan['conversion_to_primary'] ?? 1)) : 0, 2, ',', '.') }}</th>
                                    <th class="text-end">Rp {{ number_format(end($dailyStock)['saldo_akhir_nilai'], 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak Ada Data Stok</h5>
                            <p class="text-muted">Tidak ada pergerakan stok untuk item yang dipilih pada periode ini.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak Ada Data Stok</h5>
                <p class="text-muted">Tidak ada pergerakan stok untuk item yang dipilih pada periode ini.</p>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeSelect = document.getElementById('tipeSelect');
    const itemSelect = document.getElementById('itemSelect');
    
    tipeSelect.addEventListener('change', function() {
        // Reset item selection when type changes
        itemSelect.value = '';
        // You can add AJAX here to load items dynamically
    });
});
</script>
@endsection

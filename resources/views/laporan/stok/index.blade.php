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
                @if(request('item_id'))
                    @php
                        if($tipe == 'material') {
                            $selectedItem = $materials->find(request('item_id'));
                            $namaItem = $selectedItem->nama_bahan;
                        } elseif($tipe == 'product') {
                            $selectedItem = $products->find(request('item_id'));
                            $namaItem = $selectedItem->nama_produk;
                        } elseif($tipe == 'bahan_pendukung') {
                            $selectedItem = $bahanPendukungs->find(request('item_id'));
                            $namaItem = $selectedItem->nama_bahan;
                        }
                        
                        // Get all available satuan conversions for this item
                        $availableSatuans = [];
                        if($selectedItem) {
                            $mainSatuan = $tipe == 'bahan_pendukung' ? $selectedItem->satuanRelation : $selectedItem->satuan;
                            if($mainSatuan) {
                                $availableSatuans[] = [
                                    'id' => $mainSatuan->id,
                                    'nama' => $mainSatuan->nama,
                                    'is_primary' => true,
                                    'conversion_to_primary' => 1
                                ];
                            }
                            
                            // Debug: Log item data
                            \Log::info('Item Data - ID: ' . $selectedItem->id . ', Nama: ' . $selectedItem->nama_bahan);
                            \Log::info('Main Satuan: ' . $mainSatuan->nama . ' (ID: ' . $mainSatuan->id . ')');
                            \Log::info('Sub Satuan 1 ID: ' . ($selectedItem->sub_satuan_1_id ?? 'null'));
                            \Log::info('Sub Satuan 1 Konversi: ' . ($selectedItem->sub_satuan_1_konversi ?? 'null'));
                            \Log::info('Sub Satuan 1 Nilai: ' . ($selectedItem->sub_satuan_1_nilai ?? 'null'));
                            \Log::info('Sub Satuan 2 ID: ' . ($selectedItem->sub_satuan_2_id ?? 'null'));
                            \Log::info('Sub Satuan 2 Konversi: ' . ($selectedItem->sub_satuan_2_konversi ?? 'null'));
                            \Log::info('Sub Satuan 2 Nilai: ' . ($selectedItem->sub_satuan_2_nilai ?? 'null'));
                            \Log::info('Sub Satuan 3 ID: ' . ($selectedItem->sub_satuan_3_id ?? 'null'));
                            \Log::info('Sub Satuan 3 Konversi: ' . ($selectedItem->sub_satuan_3_konversi ?? 'null'));
                            \Log::info('Sub Satuan 3 Nilai: ' . ($selectedItem->sub_satuan_3_nilai ?? 'null'));
                            
                            // Try to get conversions from database table if exists
                            try {
                                $conversions = \App\Models\SatuanConversion::where('source_satuan_id', $mainSatuan->id)
                                    ->orWhere('target_satuan_id', $mainSatuan->id)
                                    ->with(['source', 'target'])
                                    ->get();
                                
                                foreach($conversions as $conv) {
                                    if($conv->source_satuan_id == $mainSatuan->id) {
                                        $availableSatuans[] = [
                                            'id' => $conv->target_satuan_id,
                                            'nama' => $conv->target->nama,
                                            'is_primary' => false,
                                            'conversion_to_primary' => $conv->amount_target / $conv->amount_source
                                        ];
                                    } else {
                                        $availableSatuans[] = [
                                            'id' => $conv->source_satuan_id,
                                            'nama' => $conv->source->nama,
                                            'is_primary' => false,
                                            'conversion_to_primary' => $conv->amount_source / $conv->amount_target
                                            ];
                                        }
                                }
                            } catch (\Exception $e) {
                                    \Log::info('SatuanConversion table not found, using item-specific data');
                                    // Use item-specific conversion data from bahan baku/bahan pendukung
                                    // Add sub satuan 1
                                    if($selectedItem->sub_satuan_1_id) {
                                        $subSatuan1 = \App\Models\Satuan::find($selectedItem->sub_satuan_1_id);
                                        if($subSatuan1) {
                                            $conversionRatio = 1;
                                            // Prioritize sub_satuan_1_nilai (sub units per primary unit)
                                            if($selectedItem->sub_satuan_1_nilai > 0) {
                                                // sub_satuan_1_nilai: berapa sub unit = 1 primary unit
                                                // So 1 primary unit = X sub unit
                                                // We want conversion_to_primary: how many primary units = 1 sub unit
                                                $conversionRatio = 1 / $selectedItem->sub_satuan_1_nilai;
                                                \Log::info('Sub Satuan 1 Nilai: ' . $selectedItem->sub_satuan_1_nilai . ' (sub per primary)');
                                            } elseif($selectedItem->sub_satuan_1_konversi > 0) {
                                                // sub_satuan_1_konversi: berapa primary unit = 1 sub unit
                                                // So 1 sub unit = X primary unit
                                                $conversionRatio = $selectedItem->sub_satuan_1_konversi;
                                                \Log::info('Sub Satuan 1 Konversi: ' . $selectedItem->sub_satuan_1_konversi . ' (primary per sub)');
                                            }
                                            
                                            $availableSatuans[] = [
                                                'id' => $subSatuan1->id,
                                                'nama' => $subSatuan1->nama,
                                                'is_primary' => false,
                                                'conversion_to_primary' => $conversionRatio
                                            ];
                                            \Log::info('Added Sub Satuan 1: ' . $subSatuan1->nama . ' with ratio: ' . $conversionRatio);
                                        }
                                    }
                                    
                                    // Add sub satuan 2
                                    if($selectedItem->sub_satuan_2_id) {
                                        $subSatuan2 = \App\Models\Satuan::find($selectedItem->sub_satuan_2_id);
                                        if($subSatuan2) {
                                            $conversionRatio = 1;
                                            // Prioritize sub_satuan_2_nilai (sub units per primary unit)
                                            if($selectedItem->sub_satuan_2_nilai > 0) {
                                                $conversionRatio = 1 / $selectedItem->sub_satuan_2_nilai;
                                                \Log::info('Sub Satuan 2 Nilai: ' . $selectedItem->sub_satuan_2_nilai . ' (sub per primary)');
                                            } elseif($selectedItem->sub_satuan_2_konversi > 0) {
                                                $conversionRatio = $selectedItem->sub_satuan_2_konversi;
                                                \Log::info('Sub Satuan 2 Konversi: ' . $selectedItem->sub_satuan_2_konversi . ' (primary per sub)');
                                            }
                                            
                                            $availableSatuans[] = [
                                                'id' => $subSatuan2->id,
                                                'nama' => $subSatuan2->nama,
                                                'is_primary' => false,
                                                'conversion_to_primary' => $conversionRatio
                                            ];
                                            \Log::info('Added Sub Satuan 2: ' . $subSatuan2->nama . ' with ratio: ' . $conversionRatio);
                                        }
                                    }
                                    
                                    // Add sub satuan 3
                                    if($selectedItem->sub_satuan_3_id) {
                                        $subSatuan3 = \App\Models\Satuan::find($selectedItem->sub_satuan_3_id);
                                        if($subSatuan3) {
                                            $conversionRatio = 1;
                                            // Prioritize sub_satuan_3_nilai (sub units per primary unit)
                                            if($selectedItem->sub_satuan_3_nilai > 0) {
                                                $conversionRatio = 1 / $selectedItem->sub_satuan_3_nilai;
                                                \Log::info('Sub Satuan 3 Nilai: ' . $selectedItem->sub_satuan_3_nilai . ' (sub per primary)');
                                            } elseif($selectedItem->sub_satuan_3_konversi > 0) {
                                                $conversionRatio = $selectedItem->sub_satuan_3_konversi;
                                                \Log::info('Sub Satuan 3 Konversi: ' . $selectedItem->sub_satuan_3_konversi . ' (primary per sub)');
                                            }
                                            
                                            $availableSatuans[] = [
                                                'id' => $subSatuan3->id,
                                                'nama' => $subSatuan3->nama,
                                                'is_primary' => false,
                                                'conversion_to_primary' => $conversionRatio
                                            ];
                                            \Log::info('Added Sub Satuan 3: ' . $subSatuan3->nama . ' with ratio: ' . $conversionRatio);
                                        }
                                    }
                                }
                            }
                    @endphp
                    
                    @if(count($availableSatuans) > 1)
                        <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white;">
                            <select name="satuan_id" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 20px; outline: none; box-shadow: none; font-size: 14px;">
                                <option value="">Semua Satuan</option>
                                @foreach($availableSatuans as $satuan)
                                    <option value="{{ $satuan['id'] }}" {{ request('satuan_id') == $satuan['id'] ? 'selected' : '' }}>
                                        {{ $satuan['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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

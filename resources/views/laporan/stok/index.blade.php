@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Laporan Stok
        </h2>
    </div>

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
                    <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white;">
                        <select name="satuan_id" class="form-select border-0" style="padding: 8px 15px; background: white; border-radius: 20px; outline: none; box-shadow: none; font-size: 14px;">
                            <option value="">Semua Satuan</option>
                            @if(isset($availableSatuans))
                                @foreach($availableSatuans as $satuan)
                                    <option value="{{ $satuan['id'] }}" {{ request('satuan_id') == $satuan['id'] ? 'selected' : '' }}>
                                        {{ $satuan['nama'] }}{{ $satuan['is_primary'] ? ' (Utama)' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @endif
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
            </form>
        </div>
    </div>

    <!-- Debug Information (temporary) -->
    @if(isset($debug_info) && request('item_id') == 5)
    <div class="alert alert-info">
        <strong>Debug Info:</strong><br>
        Total Movements: {{ $debug_info['total_movements'] }}<br>
        Daily Stock Count: {{ $debug_info['daily_stock_count'] }}<br>
        Has Manual Conversion: {{ $debug_info['has_manual_conversion'] }}<br>
        @if(isset($debug_info['sample_movement']))
        Sample Movement: Qty={{ $debug_info['sample_movement']['qty'] }}, Cost={{ $debug_info['sample_movement']['total_cost'] }}, Type={{ $debug_info['sample_movement']['ref_type'] }}<br>
        @endif
        Potong Conversion Factor: {{ $debug_info['potong_conversion_factor'] }}
    </div>
    @endif

    <!-- Stock Cards for All Items -->
    @if(request('item_id'))
        @php
            // Get selected item data
            $selectedItem = null;
            $itemName = '';
            
            if($tipe == 'material') {
                $selectedItem = $materials->find(request('item_id'));
                $itemName = $selectedItem->nama_bahan ?? 'Item';
            } elseif($tipe == 'product') {
                $selectedItem = $products->find(request('item_id'));
                $itemName = $selectedItem->nama_produk ?? 'Item';
            } elseif($tipe == 'bahan_pendukung') {
                $selectedItem = $bahanPendukungs->find(request('item_id'));
                $itemName = $selectedItem->nama_bahan ?? 'Item';
            }
            
            // Get available units for this item from database
            $units = [];
            if($selectedItem && isset($availableSatuans)) {
                foreach($availableSatuans as $satuan) {
                    $units[] = [
                        'id' => $satuan['id'],
                        'name' => $satuan['nama'],
                        'is_primary' => $satuan['is_primary'],
                        'conversion' => $satuan['conversion_to_primary'],
                        'price_conversion' => $satuan['price_conversion'] ?? $satuan['conversion_to_primary'], // Use price_conversion if available
                        'price' => $selectedItem->harga_satuan ?? 0 // Use actual price from database, no fallback
                    ];
                }
            }
            
            // Fallback untuk item tanpa satuan
            if(empty($units)) {
                $units = [
                    ['id' => '1', 'name' => 'Unit Utama', 'conversion' => 1, 'price' => $selectedItem->harga_satuan ?? 0],
                ];
            }
            
            $selectedUnit = request('satuan_id', '');
            $showAllUnits = empty($selectedUnit);
        @endphp
        
        @foreach($units as $unit)
            @if($showAllUnits || $selectedUnit == $unit['id'])
                @php
                    // Use EXACT EXCEL DATA - matching your spreadsheet exactly
                    $baseQty = (float)($selectedItem->stok ?? 0); // Actual stock from database
                    $basePrice = (float)($selectedItem->harga_satuan ?? 0); // Actual price from database
                    
                    // Special handling for bahan pendukung - always 200 units starting stock
                    if($tipe == 'bahan_pendukung') {
                        $baseQty = 200; // Fixed for bahan pendukung as per user requirement
                    }
                    
                    $baseTotal = $baseQty * $basePrice;
                    
                    // Calculate converted quantities using ACTUAL conversion ratios from database
                    $convertedQty = $baseQty * $unit['conversion'];
                    $convertedPrice = isset($unit['price_conversion']) && $unit['price_conversion'] > 0 ? 
                        $basePrice / $unit['price_conversion'] : 
                        ($unit['conversion'] > 0 ? $basePrice / $unit['conversion'] : $basePrice);
                    
                    // Calculate biaya bahan per unit using BomJobCosting (same as produksi)
                    $biayaBahanPerUnit = 0;
                @endphp
                
                @php
                    // Use actual transaction data from controller instead of static data
                    $stockData = [];
                    
                    if (isset($dailyStock) && count($dailyStock) > 0) {
                        // Convert controller data to view format with unit conversions
                        foreach ($dailyStock as $transaction) {
                            // Convert quantities to selected unit
                            $convertedSaldoAwalQty = $transaction['saldo_awal_qty'] * $unit['conversion'];
                            $convertedProduksiQty = $transaction['produksi_qty'] * $unit['conversion'];
                            $convertedSaldoAkhirQty = $transaction['saldo_akhir_qty'] * $unit['conversion'];
                            
                            // For products, use sales data instead of purchase data
                            if($tipe == 'product') {
                                $convertedPembelianQty = isset($transaction['penjualan_qty']) ? $transaction['penjualan_qty'] * $unit['conversion'] : 0;
                                $priceConversion = isset($unit['price_conversion']) ? $unit['price_conversion'] : $unit['conversion'];
                                $convertedPembelianHarga = isset($transaction['penjualan_nilai']) && $transaction['penjualan_qty'] > 0 ? 
                                    ($priceConversion > 0 ? $transaction['penjualan_nilai'] / $transaction['penjualan_qty'] / $priceConversion : 0) : 0;
                                $convertedPembelianTotal = isset($transaction['penjualan_nilai']) ? $transaction['penjualan_nilai'] : 0;
                                
                                // Add separate penjualan data for products
                                $convertedPenjualanQty = $convertedPembelianQty;
                                $convertedPenjualanHarga = $convertedPembelianHarga;
                                $convertedPenjualanTotal = $convertedPembelianTotal;
                            } else {
                                $convertedPembelianQty = $transaction['pembelian_qty'] * $unit['conversion'];
                                $priceConversion = isset($unit['price_conversion']) ? $unit['price_conversion'] : $unit['conversion'];
                                $convertedPembelianHarga = $priceConversion > 0 ? $transaction['pembelian_nilai'] / max($transaction['pembelian_qty'], 1) / $priceConversion : 0;
                                $convertedPembelianTotal = $transaction['pembelian_nilai'];
                                
                                // No sales data for non-products
                                $convertedPenjualanQty = 0;
                                $convertedPenjualanHarga = 0;
                                $convertedPenjualanTotal = 0;
                            }
                            
                            // Convert prices using price_conversion if available, otherwise use quantity conversion
                            $priceConversion = isset($unit['price_conversion']) ? $unit['price_conversion'] : $unit['conversion'];
                            $convertedSaldoAwalHarga = $priceConversion > 0 ? $transaction['saldo_awal_nilai'] / max($transaction['saldo_awal_qty'], 1) / $priceConversion : 0;
                            $convertedProduksiHarga = $priceConversion > 0 ? $transaction['produksi_nilai'] / max($transaction['produksi_qty'], 1) / $priceConversion : 0;
                            $convertedSaldoAkhirHarga = $priceConversion > 0 ? $transaction['saldo_akhir_nilai'] / max($transaction['saldo_akhir_qty'], 1) / $priceConversion : 0;
                            
                            $stockData[] = [
                                'tanggal' => \Carbon\Carbon::parse($transaction['tanggal'])->format('d/m/Y'),
                                'saldo_awal_qty' => $convertedSaldoAwalQty,
                                'saldo_awal_harga' => $convertedSaldoAwalHarga,
                                'saldo_awal_total' => $transaction['saldo_awal_nilai'],
                                'pembelian_qty' => $convertedPembelianQty,
                                'pembelian_harga' => $convertedPembelianHarga,
                                'pembelian_total' => $convertedPembelianTotal,
                                'penjualan_qty' => $convertedPenjualanQty ?? 0,
                                'penjualan_harga' => $convertedPenjualanHarga ?? 0,
                                'penjualan_total' => $convertedPenjualanTotal ?? 0,
                                'produksi_qty' => $convertedProduksiQty,
                                'produksi_harga' => $convertedProduksiHarga,
                                'produksi_total' => $transaction['produksi_nilai'],
                                'saldo_akhir_qty' => $convertedSaldoAkhirQty,
                                'saldo_akhir_harga' => $convertedSaldoAkhirHarga,
                                'saldo_akhir_total' => $transaction['saldo_akhir_nilai'],
                                'ref_type' => $transaction['ref_type'] ?? '',
                                'is_opening_balance' => $transaction['is_opening_balance'] ?? false
                            ];
                        }
                    } else {
                        // Fallback: show initial stock if no transactions
                        $stockData = [
                            [
                                'tanggal' => '01/03/2026',
                                'saldo_awal_qty' => $convertedQty,
                                'saldo_awal_harga' => $convertedPrice,
                                'saldo_awal_total' => $baseTotal,
                                'pembelian_qty' => 0,
                                'pembelian_harga' => 0,
                                'pembelian_total' => 0,
                                'penjualan_qty' => 0,
                                'penjualan_harga' => 0,
                                'penjualan_total' => 0,
                                'produksi_qty' => 0,
                                'produksi_harga' => 0,
                                'produksi_total' => 0,
                                'saldo_akhir_qty' => $convertedQty,
                                'saldo_akhir_harga' => $convertedPrice,
                                'saldo_akhir_total' => $baseTotal,
                                'ref_type' => 'initial_stock',
                                'is_opening_balance' => false
                            ]
                        ];
                    }
                @endphp
                
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #6c9f6c; color: white;">
                        <h5 class="mb-0">
                            Kartu Stok - {{ $itemName }} (Satuan {{ $unit['name'] }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" style="font-size: 12px;">
                                <thead style="background-color: #6c9f6c; color: white;">
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle" style="width: 80px;">Tanggal</th>
                                        <th rowspan="2" class="text-center align-middle" style="width: 120px;">Keterangan</th>
                                        <th colspan="3" class="text-center">Stok Awal</th>
                                        @if($tipe == 'product')
                                            <th colspan="3" class="text-center">Penjualan</th>
                                        @else
                                            <th colspan="3" class="text-center">Pembelian</th>
                                        @endif
                                        <th colspan="3" class="text-center">Produksi</th>
                                        <th colspan="3" class="text-center">Total Stok Dalam Satuan {{ $unit['name'] }}</th>
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
                                    @foreach($stockData as $row)
                                        @php
                                            // Generate transaction description based on ref_type
                                            $keterangan = '';
                                            if (isset($row['is_opening_balance']) && $row['is_opening_balance']) {
                                                $keterangan = 'Saldo Awal Bulan';
                                            } elseif (isset($row['ref_type'])) {
                                                switch($row['ref_type']) {
                                                    case 'initial_stock':
                                                        $keterangan = 'Stok Awal';
                                                        break;
                                                    case 'purchase':
                                                        $keterangan = 'Pembelian';
                                                        break;
                                                    case 'sale':
                                                        $keterangan = 'Sale';
                                                        break;
                                                    case 'production':
                                                        if ($tipe === 'product') {
                                                            $keterangan = 'Hasil Produksi';
                                                        } else {
                                                            $keterangan = 'Pemakaian Produksi';
                                                        }
                                                        break;
                                                    case 'adjustment':
                                                        $keterangan = 'Penyesuaian Stok';
                                                        break;
                                                    case 'opening_balance':
                                                        $keterangan = 'Saldo Awal Bulan';
                                                        break;
                                                    default:
                                                        $keterangan = ucfirst(str_replace('_', ' ', $row['ref_type']));
                                                }
                                            } else {
                                                $keterangan = 'Transaksi';
                                            }
                                        @endphp
                                        <tr class="{{ (isset($row['is_opening_balance']) && $row['is_opening_balance']) ? 'table-info' : '' }}">
                                            <td class="text-center">{{ $row['tanggal'] }}</td>
                                            <td class="text-center">{{ $keterangan }}</td>
                                            <td class="text-end">{{ $row['saldo_awal_qty'] > 0 ? number_format($row['saldo_awal_qty'], (in_array($unit['name'], ['Potong', 'Ekor', 'Buah', 'Pcs']) ? 0 : ($unit['name'] == 'Gram' ? 0 : 2)), ',', '.') . ' ' . $unit['name'] : '' }}</td>
                                            <td class="text-end">{{ $row['saldo_awal_harga'] > 0 ? 'RP' . rtrim(rtrim(number_format($row['saldo_awal_harga'], 2, ',', '.'), '0'), ',') : '' }}</td>
                                            <td class="text-end">{{ $row['saldo_awal_total'] > 0 ? 'RP' . number_format($row['saldo_awal_total'], 0, ',', '.') : '' }}</td>
                                            @if($tipe == 'product')
                                                <!-- For products, show sales data instead of purchase data -->
                                            <td class="text-end">{{ isset($row['penjualan_qty']) && $row['penjualan_qty'] > 0 ? number_format($row['penjualan_qty'], (in_array($unit['name'], ['Potong', 'Ekor', 'Buah', 'Pcs']) ? 0 : ($unit['name'] == 'Gram' ? 0 : 2)), ',', '.') . ' ' . $unit['name'] : '' }}</td>
                                                <td class="text-end">{{ isset($row['penjualan_harga']) && $row['penjualan_harga'] > 0 ? 'RP' . rtrim(rtrim(number_format($row['penjualan_harga'], 2, ',', '.'), '0'), ',') : '' }}</td>
                                                <td class="text-end">{{ isset($row['penjualan_total']) && $row['penjualan_total'] > 0 ? 'RP' . number_format($row['penjualan_total'], 0, ',', '.') : '' }}</td>
                                            @else
                                                <!-- For materials and bahan pendukung, show purchase data -->
                                                <td class="text-end">{{ $row['pembelian_qty'] > 0 ? number_format($row['pembelian_qty'], (in_array($unit['name'], ['Potong', 'Ekor', 'Buah', 'Pcs']) ? 0 : ($unit['name'] == 'Gram' ? 0 : 2)), ',', '.') . ' ' . $unit['name'] : '' }}</td>
                                                <td class="text-end">{{ $row['pembelian_harga'] > 0 ? 'RP' . rtrim(rtrim(number_format($row['pembelian_harga'], 2, ',', '.'), '0'), ',') : '' }}</td>
                                                <td class="text-end">{{ $row['pembelian_total'] > 0 ? 'RP' . number_format($row['pembelian_total'], 0, ',', '.') : '' }}</td>
                                            @endif
                                            <td class="text-end">{{ $row['produksi_qty'] > 0 ? number_format($row['produksi_qty'], (in_array($unit['name'], ['Potong', 'Ekor', 'Buah', 'Pcs']) ? 0 : ($unit['name'] == 'Gram' ? 0 : 2)), ',', '.') . ' ' . $unit['name'] : '' }}</td>
                                            <td class="text-end">{{ $row['produksi_harga'] > 0 ? 'RP' . rtrim(rtrim(number_format($row['produksi_harga'], 2, ',', '.'), '0'), ',') : '' }}</td>
                                            <td class="text-end">{{ $row['produksi_total'] > 0 ? 'RP' . number_format($row['produksi_total'], 0, ',', '.') : '' }}</td>
                                            <td class="text-end fw-bold">{{ number_format($row['saldo_akhir_qty'], (in_array($unit['name'], ['Potong', 'Ekor', 'Buah', 'Pcs']) ? 0 : ($unit['name'] == 'Gram' ? 0 : 2)), ',', '.') }} {{ $unit['name'] }}</td>
                                            <td class="text-end">RP{{ rtrim(rtrim(number_format($row['saldo_akhir_harga'], 2, ',', '.'), '0'), ',') }}</td>
                                            <td class="text-end">RP{{ number_format($row['saldo_akhir_total'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Item untuk Melihat Laporan Stok</h5>
                <p class="text-muted">Silakan pilih "Ayam Kampung" dari dropdown di atas untuk melihat kartu stok.</p>
            </div>
        </div>
    @endif
</div>
@endsection
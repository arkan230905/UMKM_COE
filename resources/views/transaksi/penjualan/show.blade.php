@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-eye me-2"></i>Detail Transaksi Penjualan
        </h3>
        <div>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @php
        $detailCount = $penjualan->details->count();
        $totalSubtotal = 0; $totalHPP = 0; $totalProfit = 0; $totalDiskon = 0;
        if ($detailCount > 0) {
            foreach ($penjualan->details as $d) {
                $hpp = $d->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                $sub = $d->subtotal ?? ($d->jumlah * $d->harga_satuan - ($d->diskon_nominal ?? 0));
                $totalSubtotal += $sub;
                $totalHPP += $hpp * $d->jumlah;
                $totalProfit += ($d->harga_satuan - $hpp) * $d->jumlah;
                $totalDiskon += $d->diskon_nominal ?? 0;
            }
        } else {
            $hpp = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
            $hdrHarga = $penjualan->harga_satuan;
            if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
            }
            $totalSubtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
            $totalHPP = $hpp * ($penjualan->jumlah ?? 0);
            $totalProfit = ($hdrHarga - $hpp) * ($penjualan->jumlah ?? 0);
            $totalDiskon = $penjualan->diskon_nominal ?? 0;
        }
        
        // Additional costs
        $biayaOngkir = $penjualan->biaya_ongkir ?? 0;
        $biayaPPN = $totalSubtotal * 0.11; // 11% PPN
        
        // Calculate grand total
        $grandTotal = $totalSubtotal + $biayaPPN + $biayaOngkir - $totalDiskon;
    @endphp

    {{-- Row 1: Informasi Transaksi + Ringkasan --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nomor Transaksi:</strong><br>
                            <span class="text-primary">{{ $penjualan->nomor_penjualan ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Tanggal:</strong><br>
                            {{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Metode Pembayaran:</strong><br>
                            <span class="badge {{ ($penjualan->payment_method ?? '') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                @switch($penjualan->payment_method ?? '')
                                    @case('cash') Tunai @break
                                    @case('transfer') Transfer Bank @break
                                    @case('credit') Kredit @break
                                    @default Tidak Diketahui
                                @endswitch
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status Transaksi:</strong><br>
                            <span class="badge {{ ($penjualan->status ?? 'lunas') === 'lunas' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($penjualan->status ?? 'lunas') }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Qty Retur:</strong><br>
                            @php $totalQtyRetur = $penjualan->total_qty_retur ?? 0; @endphp
                            @if($totalQtyRetur > 0)
                                <span class="badge bg-danger">{{ (int)$totalQtyRetur }}</span>
                            @else
                                <span class="badge bg-success">0</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Catatan:</strong><br>
                            {{ $penjualan->catatan ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal Produk:</span>
                            <strong class="text-primary">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total HPP:</span>
                            <strong class="text-info">Rp {{ number_format($totalHPP, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Profit:</span>
                            <strong class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    
                    {{-- Additional Costs --}}
                    @if($biayaOngkir > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Biaya Ongkir:</span>
                            <strong class="text-secondary">Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Biaya PPN (11%):</span>
                            <strong class="text-warning">Rp {{ number_format($biayaPPN, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Diskon:</span>
                            <strong class="text-danger">-Rp {{ number_format($totalDiskon, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Penjualan:</strong></span>
                            <strong class="text-dark fs-5">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                        </div>
                        <small class="text-muted d-block mt-1">
                            *Termasuk PPN, Ongkir & Servis
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Detail Produk + Aksi --}}
    <div class="row mt-4">
        <div class="col-12">
            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs" id="penjualanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-pane" type="button" role="tab" aria-controls="detail-pane" aria-selected="true">
                        <i class="fas fa-list me-2"></i>Detail Transaksi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="struk-tab" data-bs-toggle="tab" data-bs-target="#struk-pane" type="button" role="tab" aria-controls="struk-pane" aria-selected="false">
                        <i class="fas fa-receipt me-2"></i>Struk Penjualan
                    </button>
                </li>
                <li class="nav-item" role="presentation" style="display: block !important;">
                    <button class="nav-link" id="bukti-pembayaran-tab" data-bs-toggle="tab" data-bs-target="#bukti-pembayaran-pane" type="button" role="tab" aria-controls="bukti-pembayaran-pane" aria-selected="false" style="display: block !important; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                        <i class="fas fa-file-image me-2"></i>Bukti Pembayaran
                    </button>
                </li>
            </ul>
            
            <!-- DEBUG: Tab count = 3 tabs should be visible - Updated {{ date('Y-m-d H:i:s') }} -->

            {{-- Tab Content --}}
            <div class="tab-content" id="penjualanTabsContent">
                {{-- Detail Tab --}}
                <div class="tab-pane fade show active" id="detail-pane" role="tabpanel" aria-labelledby="detail-tab">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Detail Produk</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th class="text-end">Qty</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-end">HPP</th>
                                                    <th class="text-end">Profit</th>
                                                    <th class="text-end">Diskon</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($detailCount > 0)
                                                    @foreach($penjualan->details as $detail)
                                                        @php
                                                            $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                            $margin = ($detail->harga_satuan - $actualHPP) * $detail->jumlah;
                                                            $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                                            <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah,2,',','.'),'0'),',') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                            <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                            <td class="text-end">
                                                                @if($detail->diskon_persen > 0) {{ number_format($detail->diskon_persen, 2, ',', '.') }}% @endif
                                                                @if($detail->diskon_nominal > 0) (Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}) @endif
                                                                @if($detail->diskon_persen == 0 && $detail->diskon_nominal == 0) - @endif
                                                            </td>
                                                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @php
                                                        $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                        $hdrHarga = $penjualan->harga_satuan;
                                                        if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                        }
                                                        $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                                                        <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah,2,',','.'),'0'),',') }}</td>
                                                        <td class="text-end">Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                        <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                        <td class="text-end">
                                                            @if($penjualan->diskon_nominal > 0) Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }} @else - @endif
                                                        </td>
                                                        <td class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
                                </div>
                                <div class="card-body">
                                    {{-- Baris pertama: Detail, Edit, Jurnal --}}
                                    <div class="mb-4">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}" class="btn btn-outline-success btn-sm flex-fill text-center">
                                                <i class="fas fa-eye d-block mb-1"></i><small>Detail</small>
                                            </a>
                                            <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-outline-warning btn-sm flex-fill text-center">
                                                <i class="fas fa-edit d-block mb-1"></i><small>Edit</small>
                                            </a>
                                            <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn btn-outline-primary btn-sm flex-fill text-center">
                                                <i class="fas fa-book d-block mb-1"></i><small>Jurnal</small>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    {{-- Baris kedua: Cetak, Retur, Hapus --}}
                                    <div>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="#" onclick="showStrukTab()" class="btn btn-outline-secondary btn-sm flex-fill text-center">
                                                <i class="fas fa-print d-block mb-1"></i><small>Cetak</small>
                                            </a>
                                            <a href="{{ route('transaksi.retur-penjualan.detail-retur', $penjualan->id) }}" class="btn btn-outline-info btn-sm flex-fill text-center">
                                                <i class="fas fa-undo d-block mb-1"></i><small>Retur</small>
                                            </a>
                                            <button type="button" onclick="confirmDeletePenjualan({{ $penjualan->id }})" class="btn btn-outline-danger btn-sm flex-fill text-center">
                                                <i class="fas fa-trash d-block mb-1"></i><small>Hapus</small>
                                            </button>
                                        </div>
                                        <!-- Hidden form for delete -->
                                        <form id="deletePenjualanForm{{ $penjualan->id }}" action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Bukti Pembayaran Section --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Ambil bukti pembayaran dari database
                                        $buktiPembayaranInline = $penjualan->buktiPembayaran ?? collect();
                                    @endphp
                                    
                                    {{-- Upload Form --}}
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Upload Bukti Transfer</h6>
                                        <form id="uploadBuktiFormInline" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <input type="file" class="form-control" id="bukti_file_inline" name="bukti_file" 
                                                               accept="image/*,.pdf,.doc,.docx" required>
                                                        <div class="form-text">Format: JPG, PNG, PDF (Max 5MB)</div>
                                                        <div class="invalid-feedback" id="file-error-inline"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <input type="text" class="form-control" id="keterangan_inline" name="keterangan" 
                                                               placeholder="Contoh: Transfer dari rekening pribadi, referensi: ...">
                                                        <div class="form-text">Catatan (Opsional)</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Upload Bukti
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <hr>
                                    
                                    {{-- Daftar Bukti Pembayaran --}}
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-3">Daftar Bukti Pembayaran</h6>
                                        @if($buktiPembayaranInline->count() > 0)
                                            <div class="row">
                                                @foreach($buktiPembayaranInline as $bukti)
                                                    <div class="col-md-4 mb-3">
                                                        <div class="card bukti-card">
                                                            <div class="card-body text-center p-3">
                                                                @if(in_array(strtolower(pathinfo($bukti->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                                    <img src="{{ asset('storage/' . $bukti->file_path) }}" 
                                                                         class="img-fluid rounded mb-2 bukti-image" 
                                                                         onclick="showImageModal('{{ asset('storage/' . $bukti->file_path) }}', '{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}')">
                                                                @else
                                                                    <div class="text-center py-4">
                                                                        <i class="fas fa-file-alt fa-3x text-muted mb-2"></i>
                                                                        <p class="mb-0 small">{{ basename($bukti->file_path) }}</p>
                                                                    </div>
                                                                @endif
                                                                
                                                                <small class="text-muted d-block mb-1">{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}</small>
                                                                <small class="text-muted d-block mb-2">{{ $bukti->created_at->format('d/m/Y H:i') }}</small>
                                                                
                                                                <div class="bukti-actions">
                                                                    <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-outline-primary me-1"
                                                                       title="Lihat">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                                       download 
                                                                       class="btn btn-sm btn-outline-success me-1"
                                                                       title="Download">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-outline-danger"
                                                                            onclick="deleteBukti({{ $bukti->id }})"
                                                                            title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="empty-bukti">
                                                <i class="fas fa-file-image fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">Belum ada bukti pembayaran</h6>
                                                <p class="text-muted">Upload bukti pembayaran untuk melengkapi transaksi</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Struk Tab --}}
                <div class="tab-pane fade" id="struk-pane" role="tabpanel" aria-labelledby="struk-tab">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Struk Penjualan</h5>
                                    <button type="button" class="btn btn-primary" onclick="printStruk()">
                                        <i class="fas fa-print me-2"></i>Cetak Struk
                                    </button>
                                </div>
                                <div class="card-body d-flex justify-content-center p-2">
                                    <div id="strukContent" class="struk-container">
                                        {{-- Header Perusahaan --}}
                                        <div class="struk-header">
                                            @php
                                                // Get company data with fallback
                                                $dataPerusahaan = (object)[
                                                    'nama' => 'TOKO ANDA',
                                                    'alamat' => 'Alamat Toko',
                                                    'telepon' => '021-12345678'
                                                ];
                                                
                                                try {
                                                    $company = \App\Models\Perusahaan::select('nama', 'alamat', 'telepon')->first();
                                                    if ($company) {
                                                        $dataPerusahaan = $company;
                                                    }
                                                } catch (Exception $e) {
                                                    // Use fallback data
                                                }
                                            @endphp
                                            <div class="company-name">{{ strtoupper($dataPerusahaan->nama ?? 'MANUFAKTUR COE') }}</div>
                                            <div class="company-info">
                                                {{ $dataPerusahaan->alamat ?? 'Jl. Kebon No. 123' }}<br>
                                                Telp: {{ $dataPerusahaan->telepon ?? '0812-3456-7890' }}
                                            </div>
                                        </div>
                                        
                                        {{-- Info Transaksi --}}
                                        <div class="transaction-info">
                                            <div class="info-row">
                                                <span>No. Transaksi</span>
                                                <span>: {{ $penjualan->nomor_penjualan ?? 'SJ-' . date('Ymd') . '-' . str_pad($penjualan->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Tanggal</span>
                                                <span>: {{ optional($penjualan->tanggal_transaksi)->format('d/m/Y H:i') ?? date('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Kasir</span>
                                                <span>: {{ strtoupper(auth()->user()->name ?? 'TIM COE PROCESS COSTING') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Items --}}
                                        <div class="items-section">
                                            @if($detailCount > 0)
                                                @foreach($penjualan->details as $detail)
                                                    @php
                                                        $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                                    @endphp
                                                    <div class="item">
                                                        <div class="item-name">{{ $detail->produk->nama_produk ?? '-' }}</div>
                                                        <div class="item-detail">
                                                            <span>{{ number_format($detail->jumlah, 0) }} x {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</span>
                                                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if(($detail->diskon_nominal ?? 0) > 0)
                                                            <div class="item-discount">
                                                                <span>Diskon:</span>
                                                                <span>-Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                @php
                                                    $hdrHarga = $penjualan->harga_satuan;
                                                    if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                    }
                                                @endphp
                                                <div class="item">
                                                    <div class="item-name">{{ $penjualan->produk?->nama_produk ?? '-' }}</div>
                                                    <div class="item-detail">
                                                        <span>{{ number_format($penjualan->jumlah, 0) }} x Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</span>
                                                        <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                                                    </div>
                                                    @if(($penjualan->diskon_nominal ?? 0) > 0)
                                                        <div class="item-discount">
                                                            <span>Diskon:</span>
                                                            <span>-Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Summary --}}
                                        <div class="summary-section">
                                            <div class="summary-row">
                                                <span>Subtotal</span>
                                                <span>Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</span>
                                            </div>
                                            @if($totalDiskon > 0)
                                                <div class="summary-row">
                                                    <span>Total Diskon</span>
                                                    <span>-Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="total-section">
                                            <div class="total-row">
                                                <span>TOTAL:</span>
                                                <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Payment Info --}}
                                        <div class="payment-info">
                                            <div class="info-row">
                                                <span>Pembayaran</span>
                                                <span>: 
                                                    @switch($penjualan->payment_method ?? 'cash')
                                                        @case('cash') Tunai @break
                                                        @case('transfer') Transfer Bank @break
                                                        @case('credit') Kredit @break
                                                        @default Tunai
                                                    @endswitch
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">--------------------------------</div>
                                        
                                        {{-- Footer --}}
                                        <div class="footer">
                                            Terima kasih atas kunjungan Anda!<br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bukti Pembayaran Tab --}}
                <div class="tab-pane fade" id="bukti-pembayaran-pane" role="tabpanel" aria-labelledby="bukti-pembayaran-tab">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadBuktiModal">
                                    <i class="fas fa-plus me-2"></i>Tambah Bukti
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                // Ambil bukti pembayaran dari database (asumsi ada relasi)
                                $buktiPembayaran = $penjualan->buktiPembayaran ?? collect();
                            @endphp
                            
                            @if($buktiPembayaran->count() > 0)
                                <div class="row">
                                    @foreach($buktiPembayaran as $bukti)
                                        <div class="col-md-4 mb-3">
                                            <div class="card bukti-card">
                                                <div class="card-body text-center p-3">
                                                    @if(in_array(strtolower(pathinfo($bukti->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                        <img src="{{ asset('storage/' . $bukti->file_path) }}" 
                                                             class="img-fluid rounded mb-2 bukti-image" 
                                                             onclick="showImageModal('{{ asset('storage/' . $bukti->file_path) }}', '{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}')">
                                                    @else
                                                        <div class="text-center py-4">
                                                            <i class="fas fa-file-alt fa-3x text-muted mb-2"></i>
                                                            <p class="mb-0 small">{{ basename($bukti->file_path) }}</p>
                                                        </div>
                                                    @endif
                                                    
                                                    <small class="text-muted d-block mb-1">{{ $bukti->keterangan ?? 'Bukti Pembayaran' }}</small>
                                                    <small class="text-muted d-block mb-2">{{ $bukti->created_at->format('d/m/Y H:i') }}</small>
                                                    
                                                    <div class="bukti-actions">
                                                        <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary me-1"
                                                           title="Lihat">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ asset('storage/' . $bukti->file_path) }}" 
                                                           download 
                                                           class="btn btn-sm btn-outline-success me-1"
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteBukti({{ $bukti->id }})"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-bukti">
                                    <i class="fas fa-file-image fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada bukti pembayaran</h5>
                                    <p class="text-muted">Klik tombol "Tambah Bukti" untuk mengunggah bukti pembayaran</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Riwayat Retur --}}
    @if($penjualan->returPenjualans->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Riwayat Retur</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Retur</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Produk</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->returPenjualans as $retur)
                                <tr>
                                    <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                    <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $retur->jenis_retur === 'tukar_barang' ? 'Tukar Barang' : 'Refund' }}</td>
                                    <td>
                                        @foreach($retur->detailReturPenjualans as $d)
                                            <div>{{ $d->produk?->nama_produk }} ({{ (int)$d->qty_retur }} pcs)</div>
                                        @endforeach
                                    </td>
                                    <td class="text-end">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
                                    <td>
                                    span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                                            {{ ucfirst($retur->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

        </div>
    </div>
    @endif
</div>

{{-- Modal Upload Bukti Pembayaran --}}
<div class="modal fade" id="uploadBuktiModal" tabindex="-1" aria-labelledby="uploadBuktiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadBuktiModalLabel">
                    <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadBuktiForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bukti_file" class="form-label">File Bukti Pembayaran</label>
                        <input type="file" class="form-control" id="bukti_file" name="bukti_file" 
                               accept="image/*,.pdf,.doc,.docx" required>
                        <div class="form-text">Format yang didukung: JPG, PNG, PDF, DOC, DOCX (Max: 5MB)</div>
                        <div class="invalid-feedback" id="file-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" 
                                  placeholder="Masukkan keterangan bukti pembayaran..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Preview Image --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Preview Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<style>
.struk-container {
    width: 280px;
    background: white;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.4;
    border: 1px solid #ddd;
    margin: 0 auto;
}

.struk-header {
    text-align: center;
    margin-bottom: 10px;
    border-bottom: 1px dashed #333;
    padding-bottom: 8px;
}

.company-name {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 3px;
    text-transform: uppercase;
}

.company-info {
    font-size: 9px;
    line-height: 1.2;
    color: #555;
}

.divider {
    text-align: center;
    margin: 8px 0;
    font-size: 10px;
    color: #666;
}

.transaction-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
}

.items-section {
    margin-bottom: 8px;
}

.item {
    margin-bottom: 6px;
}

.item-name {
    font-weight: bold;
    font-size: 10px;
    margin-bottom: 1px;
}

.item-detail {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
}

.item-discount {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    color: #666;
    font-style: italic;
}

.summary-section {
    padding-top: 6px;
    margin-bottom: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
    font-size: 10px;
}

.total-section {
    margin-bottom: 8px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 12px;
    border-top: 1px solid #333;
    padding-top: 4px;
    margin-top: 4px;
}

.payment-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.footer {
    text-align: center;
    border-top: 1px dashed #333;
    padding-top: 8px;
    font-size: 8px;
    color: #666;
    line-height: 1.3;
}

/* Print preparation styles */
body.printing {
    overflow: hidden;
}

body.printing * {
    -webkit-print-color-adjust: exact !important;
    color-adjust: exact !important;
}

/* Ensure struk is ready for print */
.tab-pane#struk-pane.active .struk-container {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

/* Bukti Pembayaran Styles */
.bukti-card {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #dee2e6;
}

.bukti-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.bukti-image {
    max-height: 150px;
    object-fit: cover;
    cursor: pointer;
    transition: opacity 0.2s ease-in-out;
}

.bukti-image:hover {
    opacity: 0.8;
}

.bukti-actions .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.empty-bukti {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Force tab visibility */
#bukti-pembayaran-tab {
    display: block !important;
    visibility: visible !important;
}

.nav-tabs .nav-item {
    display: block !important;
}
</style>

<script>
function showStrukTab() {
    try {
        // Activate struk tab
        const strukTab = new bootstrap.Tab(document.getElementById('struk-tab'));
        strukTab.show();
    } catch (error) {
        console.error('Error showing struk tab:', error);
        alert('Terjadi kesalahan saat membuka tab struk. Silakan refresh halaman.');
    }
}

function printStruk() {
    try {
        // Get the struk content
        const strukContent = document.getElementById('strukContent');
        if (!strukContent) {
            alert('Konten struk tidak ditemukan');
            return;
        }

        // Create a new window for printing
        const printWindow = window.open('', '_blank', 'width=400,height=600,scrollbars=yes');
        
        // Write the print content
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Struk Penjualan</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: 'Courier New', monospace;
                        padding: 10px;
                        background: white;
                    }
                    
                    .struk-container {
                        width: 280px;
                        margin: 0 auto;
                        font-size: 11px;
                        line-height: 1.4;
                    }
                    
                    .struk-header {
                        text-align: center;
                        margin-bottom: 10px;
                        padding-bottom: 8px;
                    }
                    
                    .company-name {
                        font-size: 14px;
                        font-weight: bold;
                        margin-bottom: 3px;
                        text-transform: uppercase;
                    }
                    
                    .company-info {
                        font-size: 9px;
                        line-height: 1.2;
                        color: #555;
                    }
                    
                    .divider {
                        text-align: center;
                        margin: 8px 0;
                        font-size: 10px;
                        color: #666;
                    }
                    
                    .transaction-info {
                        margin-bottom: 8px;
                        font-size: 10px;
                    }
                    
                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 2px;
                    }
                    
                    .items-section {
                        margin-bottom: 8px;
                    }
                    
                    .item {
                        margin-bottom: 6px;
                    }
                    
                    .item-name {
                        font-weight: bold;
                        font-size: 10px;
                        margin-bottom: 1px;
                    }
                    
                    .item-detail {
                        display: flex;
                        justify-content: space-between;
                        font-size: 9px;
                    }
                    
                    .item-discount {
                        display: flex;
                        justify-content: space-between;
                        font-size: 9px;
                        color: #666;
                        font-style: italic;
                    }
                    
                    .summary-section {
                        padding-top: 6px;
                        margin-bottom: 8px;
                    }
                    
                    .summary-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 2px;
                        font-size: 10px;
                    }
                    
                    .total-section {
                        margin-bottom: 8px;
                    }
                    
                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        font-weight: bold;
                        font-size: 12px;
                        border-top: 1px solid #333;
                        padding-top: 4px;
                        margin-top: 4px;
                    }
                    
                    .payment-info {
                        margin-bottom: 8px;
                        font-size: 10px;
                    }
                    
                    .footer {
                        text-align: center;
                        padding-top: 8px;
                        font-size: 8px;
                        color: #666;
                        line-height: 1.3;
                    }
                    
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 5mm;
                        }
                        
                        body {
                            margin: 0;
                            padding: 0;
                        }
                        
                        .struk-container {
                            width: 100%;
                            margin: 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="struk-container">
                    ${strukContent.innerHTML}
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        
        // Wait for content to load, then print and close
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        };
        
    } catch (error) {
        console.error('Error printing struk:', error);
        alert('Terjadi kesalahan saat mencetak. Silakan coba lagi.');
    }
}

// Add keyboard shortcut for print when on struk tab
document.addEventListener('keydown', function(e) {
    try {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            const strukTab = document.getElementById('struk-pane');
            if (strukTab && strukTab.classList.contains('active')) {
                e.preventDefault();
                printStruk();
            }
        }
    } catch (error) {
        console.error('Error handling keyboard shortcut:', error);
    }
});

// Function to show image modal
function showImageModal(imageSrc, title) {
    document.getElementById('previewImage').src = imageSrc;
    document.getElementById('imagePreviewModalLabel').textContent = title;
    const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    imageModal.show();
}

// Function to delete bukti pembayaran
function deleteBukti(buktiId) {
    if (confirm('Yakin ingin menghapus bukti pembayaran ini?')) {
        fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran/${buktiId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus bukti pembayaran: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus bukti pembayaran: ' + error.message);
        });
    }
}

// Handle upload bukti pembayaran form
document.getElementById('uploadBuktiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('bukti_file');
    const file = fileInput.files[0];
    const fileError = document.getElementById('file-error');
    
    // Reset error state
    fileInput.classList.remove('is-invalid');
    fileError.textContent = '';
    
    // Validate file size (5MB = 5 * 1024 * 1024 bytes)
    if (file && file.size > 5 * 1024 * 1024) {
        fileInput.classList.add('is-invalid');
        fileError.textContent = 'Ukuran file tidak boleh lebih dari 5MB';
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    submitBtn.disabled = true;
    
    fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload page to show new bukti
            location.reload();
        } else {
            alert('Gagal upload bukti pembayaran: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat upload bukti pembayaran: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle upload bukti pembayaran form (inline in detail tab)
document.getElementById('uploadBuktiFormInline')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('bukti_file_inline');
    const file = fileInput.files[0];
    const fileError = document.getElementById('file-error-inline');
    
    // Reset error state
    fileInput.classList.remove('is-invalid');
    fileError.textContent = '';
    
    // Validate file size (5MB = 5 * 1024 * 1024 bytes)
    if (file && file.size > 5 * 1024 * 1024) {
        fileInput.classList.add('is-invalid');
        fileError.textContent = 'Ukuran file tidak boleh lebih dari 5MB';
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    submitBtn.disabled = true;
    
    fetch(`/transaksi/penjualan/{{ $penjualan->id }}/bukti-pembayaran`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload page to show new bukti
            location.reload();
        } else {
            alert('Gagal upload bukti pembayaran: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat upload bukti pembayaran: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Prevent any potential 404 requests
document.addEventListener('DOMContentLoaded', function() {
    // Force initialize Bootstrap tabs if needed
    try {
        const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabElements.forEach(function(tabElement) {
            new bootstrap.Tab(tabElement);
        });
    } catch (error) {
        console.error('Error initializing Bootstrap tabs:', error);
    }
});
window.addEventListener('error', function(e) {
    if (e.target && e.target.src && e.target.src.includes('404')) {
        console.warn('Blocked 404 request:', e.target.src);
        e.preventDefault();
    }
});

// Function to confirm delete penjualan
function confirmDeletePenjualan(penjualanId) {
    if (confirm('Yakin ingin hapus transaksi ini?')) {
        document.getElementById('deletePenjualanForm' + penjualanId).submit();
    }
}
</script>

@endsection
            </div>
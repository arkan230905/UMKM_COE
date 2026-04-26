@extends('layouts.app')

@section('title', 'Detail Pembelian')

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 20px;
}

.section-header {
    background: #f8f9fa;
    margin: -20px -20px 20px -20px;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #dee2e6;
}

.conversion-examples {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.info-display {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
}

.calculation-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 15px;
}

.total-section {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Detail Pembelian #{{ $pembelian->nomor_pembelian ?? $pembelian->id }}
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Header Information -->
    <div class="form-section">
        <div class="section-header">
            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pembelian</h6>
        </div>
        
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Vendor</label>
                <div class="info-display">
                    {{ $pembelian->vendor->nama_vendor ?? '-' }} 
                    @if($pembelian->vendor)
                        ({{ $pembelian->vendor->kategori }})
                    @endif
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Nomor Faktur Pembelian</label>
                <div class="info-display">
                    {{ $pembelian->nomor_faktur ?? '-' }}
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Tanggal</label>
                <div class="info-display">
                    {{ $pembelian->tanggal?->format('d-m-Y') ?? '-' }}
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Metode Pembayaran</label>
                <div class="info-display">
                    @if($pembelian->payment_method === 'credit')
                        💳 Kredit (Hutang)
                    @elseif($pembelian->kasBank)
                        @php
                            $accountName = strtolower($pembelian->kasBank->nama_akun);
                            $isKasAccount = str_contains($accountName, 'kas') && !str_contains($accountName, 'bank');
                            $isBankAccount = str_contains($accountName, 'bank') || str_contains($accountName, 'bca') || str_contains($accountName, 'mandiri') || str_contains($accountName, 'bri') || str_contains($accountName, 'bni');
                        @endphp
                        @if($isKasAccount)
                            💵 {{ $pembelian->kasBank->nama_akun }}
                        @elseif($isBankAccount || $pembelian->payment_method === 'transfer')
                            🏦 Transfer - {{ $pembelian->kasBank->nama_akun }}
                        @else
                            💰 {{ $pembelian->kasBank->nama_akun }}
                        @endif
                    @else
                        💵 Tunai
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Conversion Examples -->
    <div class="form-section">
        <div class="section-header">
            <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Contoh Konversi Satuan Pembelian</h6>
        </div>
        
        <div class="conversion-examples">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary mb-2">Satuan Bahan & Konversi</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>• 1 Liter = 1 kg (cairan utama)</li>
                        <li>• 1 Ton = 1000 kg (bahan utama)</li>
                        <li>• 1 Kg = 2 Kg (bahan khusus)</li>
                        <li>• 1 Kg = 1 Kg (bahan normal)</li>
                        <li>• 500 Gram = 0.5 Kg</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-success mb-2">Satuan Konversi</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>• 1 Tabung = 12 kg (tabung 12 kg)</li>
                        <li>• 1 Karung = 25 kg (karung 25 kg)</li>
                        <li>• 1 Kaleng = 5.5 kg (kaleng 5.5 kg)</li>
                        <li>• 1 Jerigen = 5 kg (jerigen 5 kg)</li>
                        <li>• 1 Karton = 0.5 kg (karton 0.5 kg)</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-info mb-2">Estimasi Harga Satuan</h6>
                    <ul class="list-unstyled small mb-0">
                        <li>• 1 kg = Rp 5000 = Rp 5000 Gram</li>
                        <li>• 1 Liter = Rp 6000 = Rp 6000 Liter</li>
                        <li>• 1 Kaleng = Rp 27500 = Rp 5000 Kg</li>
                        <li>• 1 Tabung = Rp 60000 = Rp 5000 Kg</li>
                        <li>• 1 Ton = Rp 5000000 = Rp 5000 Kg</li>
                    </ul>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <small><i class="fas fa-lightbulb me-1"></i> 
                <strong>Tips:</strong> Sistem telah mengkonversi satuan pembelian ke satuan utama untuk perhitungan stok sesuai faktor konversi yang digunakan.
                </small>
            </div>
        </div>
    </div>

    <!-- Purchase Details -->
    <div class="form-section">
        <div class="section-header">
            <h6 class="mb-0"><i class="fas fa-box me-2"></i>Detail Barang yang Dibeli</h6>
        </div>
        
        @foreach(($pembelian->details ?? []) as $index => $detail)
        <div class="card border-info mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-cube me-2"></i>Item #{{ $index + 1 }}: 
                    {{ $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : ($detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown') }}
                </h6>
            </div>
            <div class="card-body">
                <!-- Basic Item Information -->
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Nama Item</label>
                        <div class="info-display">
                            {{ $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : ($detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown') }}
                        </div>
                        <small class="text-muted">
                            {{ $detail->bahanBaku ? 'Bahan Baku' : 'Bahan Pendukung' }}
                        </small>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Jumlah</label>
                        <div class="info-display">
                            @php
                                $qty = $detail->jumlah;
                                $qtyFormatted = ($qty == floor($qty)) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
                            @endphp
                            {{ $qtyFormatted }}
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Satuan Pembelian</label>
                        <div class="info-display">
                            {{ $detail->satuan_nama ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Harga per Satuan</label>
                        <div class="info-display">
                            Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Harga Total</label>
                        <div class="info-display bg-success text-white">
                            Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Status</label>
                        <div class="info-display">
                            <span class="badge bg-success">Tersimpan</span>
                        </div>
                    </div>
                </div>
                
                <!-- Satuan Utama Section -->
                <div class="card border-info mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Satuan Utama Item</h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Satuan Utama Item</label>
                                <div class="info-display">
                                    {{ $detail->satuan_utama ?? '-' }}
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Jumlah dalam Satuan Utama</label>
                                <div class="info-display bg-light">
                                    <small class="text-muted">Input manual jumlah dalam satuan utama</small><br>
                                    @php
                                        $qtyUtama = $detail->jumlah_satuan_utama ?? 0;
                                        $qtyUtamaFormatted = ($qtyUtama == floor($qtyUtama)) ? number_format($qtyUtama, 0, ',', '.') : number_format($qtyUtama, 2, ',', '.');
                                    @endphp
                                    <strong>{{ $qtyUtamaFormatted }} {{ $detail->satuan_utama ?? '' }}</strong>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Konversi yang Digunakan</label>
                                <div class="info-display bg-light">
                                    @php
                                        // Get raw database values
                                        $rawJumlahSatuanUtama = null;
                                        try {
                                            $rawJumlahSatuanUtama = $detail->getAttributes()['jumlah_satuan_utama'] ?? null;
                                        } catch (\Exception $e) {
                                            // Column might not exist yet
                                        }
                                        
                                        $manualInput = $detail->jumlah_satuan_utama ?? 0;
                                        $calculatedValue = $detail->jumlah * ($detail->faktor_konversi ?? 1);
                                        $isManualInput = $rawJumlahSatuanUtama !== null && abs($manualInput - $calculatedValue) > 0.01;
                                        
                                        // Format numbers
                                        $qtyJumlah = $detail->jumlah;
                                        $qtyJumlahFmt = ($qtyJumlah == floor($qtyJumlah)) ? number_format($qtyJumlah, 0, ',', '.') : number_format($qtyJumlah, 2, ',', '.');
                                        $manualInputFmt = ($manualInput == floor($manualInput)) ? number_format($manualInput, 0, ',', '.') : number_format($manualInput, 2, ',', '.');
                                        $calculatedValueFmt = ($calculatedValue == floor($calculatedValue)) ? number_format($calculatedValue, 0, ',', '.') : number_format($calculatedValue, 2, ',', '.');
                                    @endphp
                                    @if($isManualInput)
                                        <span class="badge bg-warning text-dark">Manual Input</span><br>
                                        {{ $qtyJumlahFmt }} {{ $detail->satuan_nama }} = {{ $manualInputFmt }} {{ $detail->satuan_utama }}
                                    @else
                                        <span class="badge bg-info">Otomatis</span><br>
                                        {{ $qtyJumlahFmt }} {{ $detail->satuan_nama }} = {{ $calculatedValueFmt }} {{ $detail->satuan_utama }}
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Harga per Satuan Utama</label>
                                <div class="info-display bg-warning">
                                    @php
                                        $hargaPerSatuanUtama = ($detail->jumlah_satuan_utama ?? 0) > 0 ? ($detail->subtotal ?? 0) / ($detail->jumlah_satuan_utama ?? 1) : 0;
                                    @endphp
                                    Rp {{ number_format($hargaPerSatuanUtama, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sub Satuan Conversion Section -->
                @php
                    $item = $detail->bahanBaku ?? $detail->bahanPendukung;
                @endphp
                @if($item)
                <div class="card border-info">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Konversi Sub Satuan</h6>
                    </div>
                    <div class="card-body py-3">
                        <!-- Data Konversi Sub Satuan yang Tersedia (Info Only) -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i>Data Konversi Sub Satuan yang Tersedia</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <strong>Sub Satuan 1:</strong><br>
                                    @if($item->subSatuan1 && $item->sub_satuan_1_nilai)
                                        {{ $item->subSatuan1->nama }} (1 {{ $item->satuan->nama ?? $item->satuanRelation->nama ?? 'Unit' }} = {{ number_format($item->sub_satuan_1_nilai, 4) }} {{ $item->subSatuan1->nama }})
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <strong>Sub Satuan 2:</strong><br>
                                    @if($item->subSatuan2 && $item->sub_satuan_2_nilai)
                                        {{ $item->subSatuan2->nama }} (1 {{ $item->satuan->nama ?? $item->satuanRelation->nama ?? 'Unit' }} = {{ number_format($item->sub_satuan_2_nilai, 4) }} {{ $item->subSatuan2->nama }})
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <strong>Sub Satuan 3:</strong><br>
                                    @if($item->subSatuan3 && $item->sub_satuan_3_nilai)
                                        {{ $item->subSatuan3->nama }} (1 {{ $item->satuan->nama ?? $item->satuanRelation->nama ?? 'Unit' }} = {{ number_format($item->sub_satuan_3_nilai, 4) }} {{ $item->subSatuan3->nama }})
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Konversi yang Digunakan dalam Pembelian -->
                        @if($detail->konversiManual && $detail->konversiManual->count() > 0)
                        <div class="alert alert-success">
                            <h6 class="mb-2"><i class="fas fa-cogs me-1"></i>Konversi Manual yang Digunakan</h6>
                            @foreach($detail->konversiManual as $konversi)
                            
                            <!-- Pilihan Sub Satuan untuk Pembelian -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Sub Satuan yang Dipilih untuk Pembelian</label>
                                    <div class="info-display">
                                        <span class="badge bg-primary">{{ $konversi->satuan_nama }}</span>
                                        <small class="text-muted ms-2">Sub satuan yang digunakan untuk pembelian ini</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Konversi untuk Pembelian Ini -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Konversi untuk Pembelian Ini</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white fw-bold">1 {{ $detail->satuan_utama ?? 'Kilogram' }} =</span>
                                        <div class="form-control fw-bold text-center bg-light">{{ number_format($konversi->faktor_konversi_manual, 2) }}</div>
                                        <span class="input-group-text bg-success text-white fw-bold">{{ $konversi->satuan_nama }}</span>
                                    </div>
                                    <small class="text-muted">Konversi yang digunakan sesuai kebutuhan pembelian</small>
                                </div>
                            </div>
                            
                            <!-- Input Jumlah dan Harga -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Jumlah dalam Sub Satuan</label>
                                    <div class="form-control fw-bold bg-light">
                                        @php
                                            $qtyKonversi = $konversi->jumlah_konversi;
                                            $qtyKonversiFmt = ($qtyKonversi == floor($qtyKonversi)) ? number_format($qtyKonversi, 0, ',', '.') : number_format($qtyKonversi, 2, ',', '.');
                                        @endphp
                                        {{ $qtyKonversiFmt }}
                                    </div>
                                    <small class="text-muted">Jumlah dalam sub satuan yang dipilih</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Harga per Sub Satuan</label>
                                    <div class="form-control bg-warning fw-bold">
                                        @php
                                            $hargaPerSubSatuan = $konversi->jumlah_konversi > 0 ? ($detail->subtotal ?? 0) / $konversi->jumlah_konversi : 0;
                                        @endphp
                                        Rp {{ number_format($hargaPerSubSatuan, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">Harga per unit sub satuan yang dipilih</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Total Harga Sub Satuan</label>
                                    <div class="form-control bg-info text-dark fw-bold">
                                        Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted">Total harga untuk jumlah sub satuan</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-secondary">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i>Konversi Sub Satuan</h6>
                            <p class="mb-2 text-muted">Tidak ada konversi manual yang digunakan untuk pembelian ini.</p>
                            
                            @if($item->subSatuan1 || $item->subSatuan2 || $item->subSatuan3)
                            <div class="mt-2">
                                <small class="text-muted">Konversi otomatis berdasarkan data master (tidak digunakan):</small>
                                @php
                                    $jumlahSatuanUtama = $detail->jumlah_satuan_utama ?? ($detail->jumlah * ($detail->faktor_konversi ?? 1));
                                @endphp
                                
                                @if($item->subSatuan1 && $item->sub_satuan_1_nilai)
                                <br><small class="text-muted">
                                    = {{ number_format($jumlahSatuanUtama * $item->sub_satuan_1_nilai, 2) }} {{ $item->subSatuan1->nama }} (master: 1:{{ number_format($item->sub_satuan_1_nilai, 4) }})
                                </small>
                                @endif
                                
                                @if($item->subSatuan2 && $item->sub_satuan_2_nilai)
                                <br><small class="text-muted">
                                    = {{ number_format($jumlahSatuanUtama * $item->sub_satuan_2_nilai, 2) }} {{ $item->subSatuan2->nama }} (master: 1:{{ number_format($item->sub_satuan_2_nilai, 4) }})
                                </small>
                                @endif
                                
                                @if($item->subSatuan3 && $item->sub_satuan_3_nilai)
                                <br><small class="text-muted">
                                    = {{ number_format($jumlahSatuanUtama * $item->sub_satuan_3_nilai, 2) }} {{ $item->subSatuan3->nama }} (master: 1:{{ number_format($item->sub_satuan_3_nilai, 4) }})
                                </small>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Calculation Section -->
    <div class="form-section">
        <div class="section-header">
            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Perhitungan Biaya</h6>
        </div>
        
        <div class="calculation-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Subtotal</label>
                    <div class="info-display">
                        @php
                            $subtotalItems = ($pembelian->details ?? [])->sum('subtotal');
                        @endphp
                        Rp {{ number_format($subtotalItems, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Biaya Kirim</label>
                    <div class="info-display">
                        Rp {{ number_format($pembelian->biaya_kirim ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">PPN (%)</label>
                    <div class="info-display">
                        {{ $pembelian->ppn_persen ?? 0 }}%
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">PPN Nominal</label>
                    <div class="info-display">
                        Rp {{ number_format($pembelian->ppn_nominal ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Section -->
    <div class="form-section">
        <div class="total-section">
            <h4 class="mb-3">Total Harga Pembelian</h4>
            <h2 class="text-primary mb-0">Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}</h2>
        </div>
    </div>

    <!-- Keterangan -->
    @if($pembelian->keterangan)
    <div class="form-section">
        <div class="section-header">
            <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Keterangan</h6>
        </div>
        <div class="info-display">
            {{ $pembelian->keterangan }}
        </div>
    </div>
    @endif
    
    <!-- Action Buttons -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" 
                   class="btn btn-primary" 
                   title="Lihat Detail Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-eye me-2"></i>Detail
                </a>
                <button type="button" 
                        class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#journalModal"
                        title="Lihat Jurnal Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-book me-2"></i>Lihat Jurnal
                </button>
                <a href="{{ route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id]) }}" 
                   class="btn btn-secondary" 
                   title="Retur Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-undo me-2"></i>Retur
                </a>
                <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" 
                      method="POST" 
                      class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" 
                            title="Hapus Pembelian {{ $pembelian->nomor_pembelian }}">
                        <i class="fas fa-trash me-2"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="journalModalLabel">
                    <i class="fas fa-book me-2"></i>Jurnal Pembelian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Get actual journal entries for this purchase from jurnal_umum table
                                $journalEntries = \App\Models\JurnalUmum::where('tipe_referensi', 'pembelian')
                                    ->where('referensi', $pembelian->nomor_pembelian)
                                    ->with('coa')
                                    ->orderBy('id', 'asc')
                                    ->get();
                                
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            
                            @if($journalEntries && $journalEntries->count() > 0)
                                @foreach($journalEntries as $entry)
                                    @php
                                        $totalDebit += $entry->debit;
                                        $totalCredit += $entry->kredit;
                                    @endphp
                                    <tr>
                                        <td>{{ $entry->tanggal ? \Carbon\Carbon::parse($entry->tanggal)->format('d-m-Y') : '-' }}</td>
                                        <td>
                                            @if($entry->coa)
                                                <span class="badge bg-primary">{{ $entry->coa->nama_akun }}</span><br>
                                                <small class="text-muted">{{ $entry->coa->kode_akun }}</small>
                                            @else
                                                <span class="badge bg-secondary">COA tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td>{{ $entry->keterangan }}</td>
                                        <td class="text-end">
                                            @if($entry->debit > 0)
                                                Rp {{ number_format($entry->debit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->kredit > 0)
                                                Rp {{ number_format($entry->kredit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <!-- Total Row -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Jurnal belum dibuat untuk pembelian ini
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading indicator for delete form
    const deleteForm = document.querySelector('form[action*="destroy"]');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const confirmed = confirm('Apakah Anda yakin ingin menghapus pembelian {{ $pembelian->nomor_pembelian }}?\n\nPerhatian: Data yang dihapus tidak dapat dikembalikan!');
            
            if (confirmed) {
                // Show loading indicator
                const submitButton = this.querySelector('button[type="submit"], button:not([type])');
                if (submitButton) {
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
                    submitButton.disabled = true;
                    
                    // Add overlay to prevent multiple clicks
                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 18px;
                    `;
                    overlay.innerHTML = '<div><i class="fas fa-spinner fa-spin me-2"></i>Menghapus data pembelian...</div>';
                    document.body.appendChild(overlay);
                }
                
                return true; // Allow form submission
            } else {
                e.preventDefault(); // Cancel form submission
                return false;
            }
        });
    }
});
</script>
@endpush

@endsection

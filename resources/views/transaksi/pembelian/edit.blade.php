@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-edit me-2"></i>Edit Pembelian #{{ $pembelian->nomor_pembelian ?? $pembelian->id }}
        </h2>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaksi.pembelian.update', $pembelian->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Header Information -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Vendor</label>
                        <div class="form-control-plaintext">
                            {{ $pembelian->vendor->nama_vendor ?? '-' }} 
                            @if($pembelian->vendor)
                                ({{ $pembelian->vendor->kategori }})
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Nomor Faktur Pembelian</label>
                        <div class="form-control-plaintext">
                            {{ $pembelian->nomor_faktur ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <div class="form-control-plaintext">
                            {{ $pembelian->tanggal?->format('d-m-Y') ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Metode Pembayaran</label>
                        <div class="form-control-plaintext">
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
        </div>

        <!-- Bukti Faktur Section - EDITABLE -->
        <div class="card mb-4" style="border: 1px solid #D4C4B0;">
            <div class="card-header" style="background: #F8F6F3; border-bottom: 2px solid #D4C4B0;">
                <h6 class="mb-0" style="color: #6B4F3A;"><i class="fas fa-file-image me-2"></i>Upload / Update Bukti Faktur</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bukti Faktur <span class="text-muted">(Opsional)</span></label>
                        <input type="file" name="bukti_faktur" class="form-control" accept="image/*,.pdf" onchange="previewBuktiFaktur(this)">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Format: JPG, PNG, PDF | Maksimal: 2MB
                        </small>
                        
                        @if($pembelian->bukti_faktur)
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Bukti faktur saat ini: 
                                    <a href="{{ url('/storage/' . $pembelian->bukti_faktur) }}" target="_blank" class="text-decoration-none fw-bold">
                                        <i class="fas fa-eye me-1"></i>Lihat Bukti
                                    </a>
                                </small>
                            </div>
                        @else
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    Belum ada bukti faktur
                                </small>
                            </div>
                        @endif
                        
                        <div id="bukti_faktur_preview" class="mt-3" style="display: none;">
                            <p class="small text-muted mb-2">Preview:</p>
                            <img id="bukti_faktur_img" src="#" alt="Preview" style="max-width: 300px; max-height: 200px; border: 2px solid #dee2e6; border-radius: 8px; padding: 5px;">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card" style="border: 2px solid #D4C4B0; background: #FFFBF5;">
                            <div class="card-header" style="background: #F8F6F3; border-bottom: 1px solid #D4C4B0;">
                                <h6 class="mb-0" style="color: #6B4F3A;"><i class="fas fa-lightbulb me-2"></i>Informasi</h6>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Hanya bukti faktur yang bisa diupdate pada halaman edit</li>
                                    <li>Data pembelian lainnya tidak dapat diubah untuk menjaga konsistensi transaksi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Details - READ ONLY -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Detail Barang yang Dibeli</h6>
            </div>
            <div class="card-body">
                @foreach(($pembelian->details ?? []) as $index => $detail)
                <div class="card border-info mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-cube me-2"></i>Item #{{ $index + 1 }}: 
                            {{ $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : ($detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Nama Item</label>
                                <div class="form-control-plaintext">
                                    {{ $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : ($detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown') }}
                                </div>
                                <small class="text-muted">
                                    {{ $detail->bahanBaku ? 'Bahan Baku' : 'Bahan Pendukung' }}
                                </small>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Jumlah</label>
                                <div class="form-control-plaintext">
                                    {{ format_number_smart($detail->jumlah) }}
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Satuan Pembelian</label>
                                <div class="form-control-plaintext">
                                    {{ $detail->satuan_nama ?? '-' }}
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Harga per Satuan</label>
                                <div class="form-control-plaintext">
                                    Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Harga Total</label>
                                <div class="form-control-plaintext bg-success text-white rounded px-2">
                                    Rp {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Status</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-success">Tersimpan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Calculation Section - READ ONLY -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Perhitungan Biaya</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Subtotal</label>
                        <div class="form-control-plaintext">
                            @php
                                $subtotalItems = ($pembelian->details ?? [])->sum('subtotal');
                            @endphp
                            Rp {{ number_format($subtotalItems, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Biaya Kirim</label>
                        <div class="form-control-plaintext">
                            Rp {{ number_format($pembelian->biaya_kirim ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">PPN (%)</label>
                        <div class="form-control-plaintext">
                            {{ $pembelian->ppn_persen ?? 0 }}%
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">PPN Nominal</label>
                        <div class="form-control-plaintext">
                            Rp {{ number_format($pembelian->ppn_nominal ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Section - READ ONLY -->
        <div class="card mb-4">
            <div class="card-body text-center py-4">
                <h4 class="mb-3">Total Harga Pembelian</h4>
                <h2 class="text-primary mb-0">Rp {{ number_format($pembelian->total_harga ?? 0, 0, ',', '.') }}</h2>
            </div>
        </div>

        <!-- Keterangan - READ ONLY -->
        @if($pembelian->keterangan)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Keterangan</h6>
            </div>
            <div class="card-body">
                <div class="form-control-plaintext">
                    {{ $pembelian->keterangan }}
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update Bukti Faktur
            </button>
        </div>
    </form>
</div>

<script>
// Preview bukti faktur function
function previewBuktiFaktur(input) {
    const preview = document.getElementById('bukti_faktur_preview');
    const img = document.getElementById('bukti_faktur_img');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check if file is image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            // For PDF files, show a placeholder or icon
            img.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik0xNCAySDZhMiAyIDAgMCAwLTIgMnYxNmEyIDIgMCAwIDAgMiAyaDhhMiAyIDAgMCAwIDItMnY4TTggMTguSDZ2LTZoMk0xNiAySDZ2LTJoMTB2MTBIMTZ2LTJ6Ii8+PC9zdmc+';
            preview.style.display = 'block';
        }
    } else {
        preview.style.display = 'none';
    }
}
</script>

@endsection

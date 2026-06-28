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
                <label class="form-label fw-bold">Bukti Faktur</label>
                <div class="info-display">
                    @if($pembelian->bukti_faktur)
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ url('/' . $pembelian->bukti_faktur) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-image me-1"></i>Lihat Bukti
                            </a>
                            @if(str_contains($pembelian->bukti_faktur, '.pdf'))
                                <span class="badge bg-danger">PDF</span>
                            @else
                                <span class="badge bg-success">Gambar</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">Tidak ada bukti faktur</span>
                    @endif
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
            
            @if($pembelian->payment_method === 'credit')
            <!-- DP Information (Only for Credit) -->
            <div class="col-md-3">
                <label class="form-label fw-bold">Down Payment (DP)</label>
                <div class="info-display">
                    <span class="text-primary fw-bold">Rp {{ number_format($pembelian->dp ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Tanggal Jatuh Tempo</label>
                <div class="info-display">
                    @if($pembelian->tanggal_jatuh_tempo)
                        <span class="fw-bold">{{ \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo)->format('d-m-Y') }}</span>
                        @php
                            $now = \Carbon\Carbon::now();
                            $dueDate = \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo);
                            $isOverdue = $now->gt($dueDate) && $pembelian->status_pembelian !== 'lunas';
                        @endphp
                        @if($isOverdue)
                            <span class="badge bg-danger ms-2">Jatuh Tempo!</span>
                        @elseif($pembelian->status_pembelian === 'lunas')
                            <span class="badge bg-success ms-2">Lunas</span>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">Sisa Utang</label>
                <div class="info-display">
                    @php
                        // Gunakan accessor sisa_utang yang sudah ada di model
                        // sisa_utang = total_harga - total_dibayar - total_refund
                        $sisaUtangAktual = $pembelian->sisa_utang;
                    @endphp
                    <span class="text-danger fw-bold">Rp {{ number_format(max(0, $sisaUtangAktual), 0, ',', '.') }}</span>
                    @if($sisaUtangAktual <= 0)
                        <span class="badge bg-success ms-2">Lunas</span>
                    @endif
                </div>
            </div>
            @endif
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
                            {{ format_number_smart($detail->jumlah) }}
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

                
                <!-- SATUAN UTAMA ITEM SECTION - REMOVED -->
                
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
            
            @if($pembelian->payment_method === 'credit')
                <div class="mt-3 pt-3 border-top">
                    <div class="row">
                        @if(($pembelian->dp ?? 0) > 0)
                        <div class="col-md-4">
                            <small class="text-muted">DP (Down Payment)</small>
                            <h5 class="text-info mb-0">Rp {{ number_format($pembelian->dp ?? 0, 0, ',', '.') }}</h5>
                        </div>
                        @endif
                        @if($pembelian->tanggal_jatuh_tempo)
                        <div class="col-md-4">
                            <small class="text-muted">Tanggal Jatuh Tempo</small>
                            @php
                                $dueDate = \Carbon\Carbon::parse($pembelian->tanggal_jatuh_tempo);
                                $today = \Carbon\Carbon::today();
                                $isOverdue = $dueDate->lt($today);
                            @endphp
                            <h5 class="{{ $isOverdue ? 'text-danger' : 'text-warning' }} mb-0">
                                {{ $dueDate->format('d F Y') }}
                                @if($isOverdue)
                                    <span class="badge bg-danger ms-2">Jatuh Tempo!</span>
                                @endif
                            </h5>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <small class="text-muted">Sisa Utang</small>
                            <h5 class="text-danger mb-0">Rp {{ number_format($pembelian->sisa_utang ?? 0, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            @endif
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
    
<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4 shadow-lg border-0">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="journalModalLabel" style="color: #1F2937;">
                    Jurnal Pembelian
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-2">
                <!-- Transaction Info Card -->
                <div class="card border rounded-3 mb-4" style="background-color: #ffffff; border-color: #E5E7EB !important;">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Nomor Pembelian</small>
                                    <span class="fw-bold fs-5" style="color: #1F2937;">{{ $pembelian->nomor_pembelian }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Vendor</small>
                                    <span class="fw-semibold" style="color: #6B4F3A; font-size: 0.95rem;">{{ $pembelian->vendor ? $pembelian->vendor->nama : '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <small class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 500;">Tanggal</small>
                                    <span class="fw-semibold" style="color: #1F2937; font-size: 0.95rem;">{{ $pembelian->tanggal ? \Carbon\Carbon::parse($pembelian->tanggal)->locale('id')->isoFormat('D MMMM YYYY') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Table -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead style="background-color: #F8F6F3;">
                            <tr>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Tanggal</th>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Akun</th>
                                <th class="border-0 py-3 px-3" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Keterangan</th>
                                <th class="border-0 py-3 px-3 text-end" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Debit</th>
                                <th class="border-0 py-3 px-3 text-end" style="color: #6B4F3A; font-weight: 600; font-size: 0.875rem;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #ffffff;">
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
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td class="px-3 py-3" style="color: #6B7280; font-size: 0.875rem;">
                                            {{ $entry->tanggal ? \Carbon\Carbon::parse($entry->tanggal)->locale('id')->isoFormat('D MMMM YYYY') : '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            @if($entry->coa)
                                                <span class="d-block fw-semibold" style="color: #1F2937; font-size: 0.9rem; margin-bottom: 0.25rem;">
                                                    {{ $entry->coa->nama_akun }}
                                                </span>
                                                <span class="d-block" style="color: #6B7280; font-size: 0.8rem;">
                                                    {{ $entry->coa->kode_akun }}
                                                </span>
                                            @else
                                                <span class="d-block fw-semibold text-muted" style="font-size: 0.9rem;">COA tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3" style="color: #6B7280; font-size: 0.875rem;">{{ $entry->keterangan }}</td>
                                        <td class="text-end px-3 py-3" style="color: #1F2937; font-weight: 500; font-size: 0.875rem;">
                                            @if($entry->debit > 0)
                                                Rp {{ number_format($entry->debit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end px-3 py-3" style="color: #1F2937; font-weight: 500; font-size: 0.875rem;">
                                            @if($entry->kredit > 0)
                                                Rp {{ number_format($entry->kredit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5 border-bottom" style="border-color: #E5E7EB !important;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Jurnal belum dibuat untuk pembelian ini
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot style="background-color: #F8F6F3;">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end py-3 px-3 border-0" style="color: #1F2937; font-size: 0.95rem;">Total:</td>
                                <td class="text-end py-3 px-3 border-0" style="color: #1F2937; font-size: 0.95rem;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                                <td class="text-end py-3 px-3 border-0" style="color: #1F2937; font-size: 0.95rem;">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-3">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Modern Journal Modal Styles */
    #journalModal .modal-content {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    #journalModal tbody tr:hover {
        background-color: #F9FAFB !important;
    }
</style>
@endpush

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


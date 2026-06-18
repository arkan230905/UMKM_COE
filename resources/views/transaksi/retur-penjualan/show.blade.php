@extends('layouts.app')

@section('title', 'Detail Retur Penjualan - ' . ($returPenjualan->nomor_retur ?? $returPenjualan->id))

@push('styles')
<style>
    .text-theme { color: #5c3d2e !important; }
    
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
        overflow: hidden;
        background-color: #fff;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .card-modern .card-header {
        border-bottom: 1px solid #f3efea;
        background-color: #fff;
        padding: 1.25rem 1.5rem;
    }

    .card-modern .card-body {
        padding: 1.5rem;
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background-color: #faf7f2;
        border: 1px solid #f1eae1;
        height: 100%;
    }
    
    .info-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(92, 61, 46, 0.08);
        color: #5c3d2e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    .info-item-label {
        font-size: 0.8rem;
        color: #7c7267;
        margin-bottom: 2px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-item-value {
        font-size: 0.95rem;
        color: #3e3327;
        font-weight: 600;
    }

    .table-modern th {
        background-color: #faf7f2;
        border-bottom: 2px solid #eeddcc !important;
        color: #5c3d2e;
        font-weight: 600;
        padding: 12px 16px;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .table-modern td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1eae1;
    }

    .jurnal-table th { 
        background: #f8f9fa; 
        font-size: 0.85rem;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .jurnal-table td { 
        font-size: 0.875rem; 
        vertical-align: middle;
    }

    .debit-col  { 
        color: #0d6efd; 
        font-weight: 600;
        text-align: right;
    }
    .kredit-col { 
        color: #198754; 
        font-weight: 600;
        text-align: right;
    }

    .balance-row td { 
        background: #f0fff4; 
        font-weight: 700;
    }
    .balance-row.unbalanced td { 
        background: #fff5f5; 
    }

    .nav-tabs-modern {
        border-bottom: 2px solid #f1eae1;
        gap: 10px;
    }
    
    .nav-tabs-modern .nav-link {
        border: none !important;
        border-bottom: 3px solid transparent !important;
        color: #8c8276 !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 0 !important;
        transition: all 0.3s ease;
        background: transparent !important;
    }
    
    .nav-tabs-modern .nav-link:hover {
        color: #5c3d2e !important;
        border-bottom-color: rgba(92, 61, 46, 0.3) !important;
    }
    
    .nav-tabs-modern .nav-link.active {
        color: #5c3d2e !important;
        border-bottom-color: #5c3d2e !important;
        font-weight: 700;
    }

    .btn-back-theme {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border: 1px solid #d4a574;
        border-radius: 12px;
        color: #5c3d2e;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .btn-back-theme:hover {
        background-color: #f5efe6;
        border-color: #a0825d;
    }

    .btn-action-modern {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 10px;
        border-radius: 12px;
        border: 1px solid #f1eae1;
        background-color: #fff;
        color: #7c7267;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        text-decoration: none !important;
        font-weight: 600;
        text-align: center;
    }
    
    .btn-action-modern i {
        font-size: 1.4rem;
        margin-bottom: 8px;
        transition: transform 0.3s ease;
    }
    
    .btn-action-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(92, 61, 46, 0.1);
        border-color: #d4a574;
    }

    .btn-action-detail:hover { color: #2e7d32; border-color: #81c784; background-color: #f1f8e9; }
    .btn-action-jurnal:hover { color: #1565c0; border-color: #90caf9; background-color: #e3f2fd; }
</style>
@endpush

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h3 class="mb-1 text-theme fw-bold">
                <i class="fas fa-undo me-2"></i>Detail Retur Penjualan
            </h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                {{ $returPenjualan->nomor_retur }}
            </p>
        </div>
        <div>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn-back-theme">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="card-modern mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs-modern" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-pane" type="button" role="tab" aria-controls="detail-pane" aria-selected="true">
                        <i class="fas fa-info-circle me-2"></i>Detail
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="jurnal-tab" data-bs-toggle="tab" data-bs-target="#jurnal-pane" type="button" role="tab" aria-controls="jurnal-pane" aria-selected="false">
                        <i class="fas fa-book me-2"></i>Jurnal
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            {{-- Detail Tab --}}
            <div class="tab-pane fade show active" id="detail-pane" role="tabpanel" aria-labelledby="detail-tab">
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Left: Informasi Retur --}}
                        <div class="col-md-6">
                            <h6 class="mb-3 text-theme fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Retur</h6>
                            
                            <div class="row g-2 mb-4">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-hashtag"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">No. Retur</div>
                                            <div class="info-item-value">{{ $returPenjualan->nomor_retur }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="far fa-calendar"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Tanggal Retur</div>
                                            <div class="info-item-value">{{ $returPenjualan->tanggal->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Jenis Retur</div>
                                            <div class="info-item-value">
                                                @switch($returPenjualan->jenis_retur)
                                                    @case('refund')<span class="badge bg-danger">Refund</span>@break
                                                    @case('kredit')<span class="badge bg-info">Kredit</span>@break
                                                    @case('tukar_barang')<span class="badge bg-warning">Tukar Barang</span>@break
                                                    @default<span class="badge bg-secondary">-</span>
                                                @endswitch
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Status</div>
                                            <div class="info-item-value">
                                                @switch($returPenjualan->status)
                                                    @case('belum_dibayar')<span class="badge bg-warning">Belum Dibayar</span>@break
                                                    @case('lunas')<span class="badge bg-success">Lunas</span>@break
                                                    @case('selesai')<span class="badge bg-success">Selesai</span>@break
                                                    @default<span class="badge bg-secondary">-</span>
                                                @endswitch
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3 text-theme fw-bold"><i class="fas fa-money-bill me-2"></i>Ringkasan Nilai</h6>
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-calculator"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Nilai Retur</div>
                                            <div class="info-item-value">Rp {{ number_format((float)($returPenjualan->total_retur - $returPenjualan->ppn), 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                                @if($returPenjualan->ppn > 0)
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="fas fa-percent"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">PPN (11%)</div>
                                            <div class="info-item-value">Rp {{ number_format((float)$returPenjualan->ppn, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-12">
                                    <div class="info-item" style="background: linear-gradient(135deg, #fdfaf6, #f5efe6); border: 1px dashed #d4a574;">
                                        <div class="info-item-icon" style="background: rgba(212, 165, 116, 0.15);">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="info-item-content">
                                            <div class="info-item-label">Total Retur</div>
                                            <div class="info-item-value" style="color: #5c3d2e;">Rp {{ number_format((float)$returPenjualan->total_retur, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Detail Barang Diretur --}}
                        <div class="col-md-6">
                            <h6 class="mb-3 text-theme fw-bold"><i class="fas fa-box me-2"></i>Detail Barang Diretur</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-modern">
                                    <thead>
                                        <tr>
                                            <th style="width:5%">#</th>
                                            <th>Produk</th>
                                            <th class="text-end" style="width:10%">Qty</th>
                                            <th class="text-end" style="width:20%">Harga/Unit</th>
                                            <th class="text-end" style="width:20%">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($returPenjualan->detailReturPenjualans as $no => $detail)
                                            <tr>
                                                <td>{{ $no + 1 }}</td>
                                                <td>
                                                    <strong>{{ $detail->produk->nama_produk ?? '-' }}</strong>
                                                    <br><small class="text-muted">SKU: {{ $detail->produk->kode_produk ?? '-' }}</small>
                                                </td>
                                                <td class="text-end">{{ $detail->qty_retur }}</td>
                                                <td class="text-end">Rp {{ number_format($detail->harga_barang, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold">Rp {{ number_format($detail->qty_retur * $detail->harga_barang, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jurnal Tab --}}
            <div class="tab-pane fade" id="jurnal-pane" role="tabpanel" aria-labelledby="jurnal-tab">
                <div class="card-body">
                    @if($journalEntry)
                        {{-- Jurnal Tersedia --}}
                        @php
                            $lines = $journalEntry->linesWithAccount;
                            $totalDebit = $lines->sum('debit');
                            $totalKredit = $lines->sum('credit');
                            $isBalanced = round($totalDebit - $totalKredit, 2) === 0.0;
                        @endphp

                        <div class="alert alert-success mb-3 py-2">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jurnal telah dibuat otomatis</strong> 
                            <span class="badge bg-success ms-2">{{ $isBalanced ? 'Balance ✓' : 'Tidak Balance' }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover jurnal-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Jurnal</th>
                                        <th>No. Referensi</th>
                                        <th>Nama Akun</th>
                                        <th class="text-end" style="width:15%">Debit</th>
                                        <th class="text-end" style="width:15%">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lines as $line)
                                        <tr>
                                            <td class="text-nowrap">{{ $journalEntry->created_at->format('d/m/Y') }}</td>
                                            <td class="text-nowrap">{{ $returPenjualan->nomor_retur ?? '-' }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $line->coa->nama_akun ?? 'Akun tidak ditemukan' }}</div>
                                                <small class="text-muted">{{ $line->coa->kode_akun ?? '-' }}</small>
                                            </td>
                                            <td class="debit-col">
                                                {{ $line->debit > 0 ? 'Rp ' . number_format((float)$line->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="kredit-col">
                                                {{ $line->credit > 0 ? 'Rp ' . number_format((float)$line->credit, 0, ',', '.') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Tidak ada data jurnal</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="balance-row {{ !$isBalanced ? 'unbalanced' : '' }}">
                                        <td colspan="3" class="text-end"><strong>TOTAL DEBIT & KREDIT</strong></td>
                                        <td class="debit-col"><strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></td>
                                        <td class="kredit-col"><strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    @if(!$isBalanced)
                                        <tr>
                                            <td colspan="5" class="text-center text-danger small py-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Jurnal tidak seimbang! Selisih: Rp {{ number_format(abs($totalDebit - $totalKredit), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Jurnal belum tersedia.</strong> Jurnal akan dibuat otomatis ketika halaman dimuat.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle fragment/anchor navigation to tab
        const urlHash = window.location.hash;
        if (urlHash === '#jurnal-tab') {
            const jurnalTab = document.getElementById('jurnal-tab');
            if (jurnalTab) {
                const tab = new bootstrap.Tab(jurnalTab);
                tab.show();
                // Scroll to the tab
                jurnalTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
</script>
@endpush

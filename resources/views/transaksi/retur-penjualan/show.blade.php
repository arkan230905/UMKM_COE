@extends('layouts.app')

@section('title', 'Detail Retur Penjualan - ' . ($returPenjualan->nomor_retur ?? $returPenjualan->id))

@push('styles')
<style>
.jurnal-section {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.jurnal-table th { 
    background: #f8f9fa; 
    font-size: 0.85rem;
    font-weight: 600;
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

.total-row {
    background: #f8f9fa;
    font-weight: 600;
    text-align: right;
}

.alert-missing {
    border-left: 4px solid #dc3545;
    background: #fff5f5;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-2">
                <i class="fas fa-eye me-2 text-primary"></i>Detail Retur Penjualan
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item">Retur Penjualan</li>
                    <li class="breadcrumb-item active">{{ $returPenjualan->nomor_retur }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        {{-- Info Retur --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Retur</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" style="width:50%">No. Retur</td>
                            <td><strong>{{ $returPenjualan->nomor_retur }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $returPenjualan->tanggal->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Retur</td>
                            <td>
                                @switch($returPenjualan->jenis_retur)
                                    @case('refund')<span class="badge bg-danger">Refund</span>@break
                                    @case('kredit')<span class="badge bg-info">Kredit</span>@break
                                    @case('tukar_barang')<span class="badge bg-warning">Tukar Barang</span>@break
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @switch($returPenjualan->status)
                                    @case('belum_dibayar')<span class="badge bg-warning">Belum Dibayar</span>@break
                                    @case('lunas')<span class="badge bg-success">Lunas</span>@break
                                    @case('selesai')<span class="badge bg-success">Selesai</span>@break
                                @endswitch
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-2"></td></tr>
                        <tr>
                            <td class="text-muted">Nilai Retur</td>
                            <td>Rp {{ number_format((float)($returPenjualan->total_retur - $returPenjualan->ppn), 0, ',', '.') }}</td>
                        </tr>
                        @if($returPenjualan->ppn > 0)
                        <tr>
                            <td class="text-muted">PPN</td>
                            <td>Rp {{ number_format((float)$returPenjualan->ppn, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted fw-bold">Total</td>
                            <td><strong>Rp {{ number_format((float)$returPenjualan->total_retur, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Jurnal Section --}}
        <div class="col-md-8">
            <div class="jurnal-section">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open me-2 text-primary"></i>Jurnal Retur Penjualan
                    </h5>
                </div>

                {{-- Status Validasi --}}
                @if(!$validation['valid'])
                    <div class="alert alert-missing mb-3">
                        <h6 class="text-danger mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>Akun Tidak Lengkap
                        </h6>
                        <p class="mb-2 text-muted small">Akun berikut belum dibuat dan diperlukan untuk membuat jurnal:</p>
                        @foreach($validation['missing'] as $item)
                            <div class="mb-2">
                                <strong>{{ $item['nama'] }}</strong>
                                <span class="badge bg-secondary ms-1">{{ $item['tipe'] }}</span>
                                <div class="small text-muted">{{ $item['pesan'] }}</div>
                            </div>
                        @endforeach
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('master-data.coa.create') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah Akun COA
                            </a>
                            <a href="{{ route('master-data.coa.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-1"></i>Lihat Semua Akun
                            </a>
                        </div>
                    </div>
                @else
                    @if($journalEntry)
                        {{-- Jurnal Tersedia --}}
                        @php
                            $lines = $journalEntry->linesWithAccount;
                            $totalDebit = $lines->sum('debit');
                            $totalKredit = $lines->sum('credit');
                            $isBalanced = round($totalDebit - $totalKredit, 2) === 0.0;
                        @endphp

                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Jurnal telah dibuat</strong> 
                            <span class="badge bg-success ms-2">{{ $isBalanced ? 'Balance ✓' : 'Tidak Balance' }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover jurnal-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:12%">Kode Akun</th>
                                        <th>Nama Akun</th>
                                        <th>Keterangan</th>
                                        <th class="text-end" style="width:15%">Debit</th>
                                        <th class="text-end" style="width:15%">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lines as $line)
                                        <tr>
                                            <td><code>{{ $line->coa->kode_akun ?? '-' }}</code></td>
                                            <td>{{ $line->coa->nama_akun ?? 'Akun tidak ditemukan' }}</td>
                                            <td class="text-muted small">{{ $line->memo ?? '-' }}</td>
                                            <td class="debit-col">
                                                {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="kredit-col">
                                                {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
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
                                        <td colspan="3" class="text-end"><strong>TOTAL</strong></td>
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

                        <div class="mt-2 small text-muted">
                            Dibuat: {{ $journalEntry->created_at->format('d/m/Y H:i') }}
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Jurnal sedang disiapkan...</strong> Sistem sedang membuat jurnal untuk transaksi ini.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Items --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Detail Barang Diretur</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th>Produk</th>
                                    <th class="text-end" style="width:10%">Qty</th>
                                    <th class="text-end" style="width:15%">Harga/Unit</th>
                                    <th class="text-end" style="width:15%">Subtotal</th>
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
                                        <td class="text-end">Rp {{ number_format($detail->qty_retur * $detail->harga_barang, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

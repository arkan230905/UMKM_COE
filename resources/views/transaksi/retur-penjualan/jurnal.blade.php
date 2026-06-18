@extends('layouts.app')

@section('title', 'Jurnal Retur Penjualan - ' . ($returPenjualan->nomor_retur ?? $returPenjualan->id))

@push('styles')
<style>
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

.alert-missing {
    border-left: 4px solid #dc3545;
    background: #fff5f5;
}

.akun-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f5c6cb;
}
.akun-item:last-child { border-bottom: none; }

.akun-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 20px;
    white-space: nowrap;
}

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
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="color: #5c3d2e; font-weight: bold;">
                <i class="fas fa-book me-2"></i>Jurnal Retur Penjualan
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.retur-penjualan.show', $returPenjualan->id) }}">{{ $returPenjualan->nomor_retur ?? '#'.$returPenjualan->id }}</a></li>
                    <li class="breadcrumb-item active">Jurnal</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.retur-penjualan.show', $returPenjualan->id) }}" class="btn-back-theme">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-header">
            <h6 class="mb-0" style="color: #5c3d2e; font-weight: bold;">
                <i class="fas fa-book-open me-2"></i>Jurnal Transaksi Retur - {{ $returPenjualan->nomor_retur }}
            </h6>
        </div>
        <div class="card-body">
        {{-- Status Validasi --}}
        @if(!$validation['valid'])
            <div class="alert alert-missing mb-3">
                <h6 class="text-danger mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>Akun Tidak Lengkap
                </h6>
                <p class="mb-2 text-muted small">Akun berikut belum dibuat dan diperlukan untuk membuat jurnal:</p>
                <div>
                    @foreach($validation['missing'] as $item)
                        <div class="akun-item">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-times-circle text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <strong>{{ $item['nama'] }}</strong>
                                    <span class="akun-badge
                                        @if(in_array($item['tipe'], ['Asset','Aset'])) bg-primary text-white
                                        @elseif($item['tipe'] === 'Revenue') bg-success text-white
                                        @elseif($item['tipe'] === 'Liability') bg-warning text-dark
                                        @elseif(in_array($item['tipe'], ['Expense','Beban'])) bg-danger text-white
                                        @else bg-secondary text-white @endif">
                                        {{ $item['tipe'] }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ $item['pesan'] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <a href="{{ route('master-data.coa.create') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-plus me-1"></i>Tambah Akun (COA)
                    </a>
                    <a href="{{ route('master-data.coa.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>Lihat Semua Akun
                    </a>
                </div>
            </div>
        @else
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Semua akun tersedia.</strong>
                {{ $journalEntry ? 'Jurnal sudah dibuat.' : 'Jurnal akan dibuat secara otomatis saat retur disimpan.' }}
            </div>
        @endif

        {{-- Jurnal Yang Sudah Ada --}}
        @if($journalEntry)
            @php
                $lines = $journalEntry->linesWithAccount;
                $totalDebit = $lines->sum('debit');
                $totalKredit = $lines->sum('credit');
                $isBalanced = round($totalDebit - $totalKredit, 2) === 0.0;
            @endphp

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0" style="color: #5c3d2e;">
                        <i class="fas fa-book-open me-2"></i>
                        Jurnal Retur
                        <span class="badge {{ $isBalanced ? 'bg-success' : 'bg-danger' }} ms-2">
                            {{ $isBalanced ? 'Balance ✓' : 'Tidak Balance !' }}
                        </span>
                    </h6>
                    <small class="text-muted">
                        Dibuat: {{ $journalEntry->created_at->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered jurnal-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:12%">Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Keterangan</th>
                                    <th class="text-end" style="width:18%">Debit</th>
                                    <th class="text-end" style="width:18%">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $line)
                                    <tr>
                                        <td><code>{{ $line->coa->kode_akun ?? '-' }}</code></td>
                                        <td>{{ $line->coa->nama_akun ?? 'Akun tidak ditemukan' }}</td>
                                        <td class="text-muted small">{{ $line->memo ?? '-' }}</td>
                                        <td class="text-end debit-col">
                                            {{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end kredit-col">
                                            {{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="balance-row {{ !$isBalanced ? 'unbalanced' : '' }}">
                                    <td colspan="3" class="text-end fw-bold">TOTAL</td>
                                    <td class="text-end debit-col">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                                    <td class="text-end kredit-col">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                                </tr>
                                @if(!$isBalanced)
                                    <tr>
                                        <td colspan="5" class="text-center text-danger small">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Jurnal tidak balance! Selisih: Rp {{ number_format(abs($totalDebit - $totalKredit), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        @elseif($validation['valid'])
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3 d-block"></i>
                    <h6 class="text-muted">Jurnal akan dibuat otomatis</h6>
                    <p class="text-muted small mb-0">Jurnal akan dibuat secara otomatis ketika retur disimpan.</p>
                </div>
            </div>
        @endif

        </div>
    </div>
</div>
@endsection

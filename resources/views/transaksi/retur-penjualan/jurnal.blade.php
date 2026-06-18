@extends('layouts.app')

@section('title', 'Jurnal Retur Penjualan - ' . ($returPenjualan->nomor_retur ?? $returPenjualan->id))

@push('styles')
<style>
.jurnal-table th { background: #f8f9fa; font-size: 0.85rem; }
.jurnal-table td { font-size: 0.875rem; vertical-align: middle; }
.debit-col  { color: #0d6efd; font-weight: 600; }
.kredit-col { color: #198754; font-weight: 600; }
.missing-card {
    border-left: 4px solid #dc3545;
    background: #fff5f5;
}
.missing-card .akun-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f5c6cb;
}
.missing-card .akun-item:last-child { border-bottom: none; }
.akun-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 20px;
    white-space: nowrap;
}
.balance-row td { background: #f0fff4; font-weight: 700; }
.balance-row.unbalanced td { background: #fff5f5; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-book me-2 text-primary"></i>Jurnal Retur Penjualan
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item">Retur Penjualan</li>
                    <li class="breadcrumb-item active">{{ $returPenjualan->nomor_retur ?? '#'.$returPenjualan->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            @if($validation['valid'] && !$journalEntry)
                <form action="{{ route('transaksi.retur-penjualan.jurnal.rebuild', $returPenjualan->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm"
                            onclick="return confirm('Buat ulang jurnal untuk retur ini?')">
                        <i class="fas fa-sync me-1"></i>Buat Jurnal
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {!! nl2br(e(session('error'))) !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Kolom Kiri: Info Retur --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Retur</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">No. Retur</td>
                            <td><strong class="text-primary">{{ $returPenjualan->nomor_retur ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ optional($returPenjualan->tanggal)->format('d/m/Y') ?? $returPenjualan->tanggal }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Retur</td>
                            <td>
                                @switch($returPenjualan->jenis_retur ?? '')
                                    @case('refund') <span class="badge bg-danger">Refund</span> @break
                                    @case('kredit') <span class="badge bg-info">Kredit</span> @break
                                    @case('tukar_barang') <span class="badge bg-warning text-dark">Tukar Barang</span> @break
                                    @default <span class="badge bg-secondary">{{ ucfirst($returPenjualan->jenis_retur ?? '-') }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @switch($returPenjualan->status ?? '')
                                    @case('belum_dibayar') <span class="badge bg-warning">Belum Dibayar</span> @break
                                    @case('lunas') <span class="badge bg-success">Lunas</span> @break
                                    @case('selesai') <span class="badge bg-success">Selesai</span> @break
                                    @default <span class="badge bg-secondary">{{ ucfirst($returPenjualan->status ?? '-') }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-2"></td></tr>
                        @php
                            $totalRetur = (float)($returPenjualan->total_retur ?? 0);
                            $ppnAmount = (float)($returPenjualan->ppn ?? 0);
                            $nilaiRetur = $totalRetur - $ppnAmount;
                        @endphp
                        <tr>
                            <td class="text-muted">Nilai Retur</td>
                            <td>Rp {{ number_format($nilaiRetur, 0, ',', '.') }}</td>
                        </tr>
                        @if($ppnAmount > 0)
                        <tr>
                            <td class="text-muted">PPN (11%)</td>
                            <td>Rp {{ number_format($ppnAmount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted fw-bold">Total</td>
                            <td><strong class="text-dark">Rp {{ number_format($totalRetur, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Status Validasi + Jurnal --}}
        <div class="col-md-8">

            {{-- ── STATUS VALIDASI ─────────────────────────────────────────── --}}
            @if(!$validation['valid'])
                <div class="card missing-card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Jurnal Belum Dapat Dibuat – Akun Belum Tersedia
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Sistem menemukan <strong>{{ count($validation['missing']) }} akun</strong> yang belum dibuat.
                            Lengkapi akun-akun berikut agar jurnal dapat dibuat dengan benar:
                        </p>

                        @php
                            $missingNames = array_map(fn($m) => $m['nama'], $validation['missing']);
                            if (count($missingNames) > 1) {
                                $listStr = implode(', ', array_slice($missingNames, 0, -1)) . ' dan ' . end($missingNames);
                            } else {
                                $listStr = $missingNames[0] ?? '';
                            }
                        @endphp

                        <div class="alert alert-danger py-2 mb-3">
                            <i class="fas fa-times-circle me-1"></i>
                            <strong>Akun berikut belum dibuat:</strong> {{ $listStr }}
                        </div>

                        <div class="missing-items">
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
                </div>
            @else
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Semua akun tersedia.</strong>
                    {{ $journalEntry ? 'Jurnal telah dibuat secara otomatis.' : 'Jurnal akan dibuat otomatis.' }}
                </div>
            @endif

            {{-- ── JURNAL YANG SUDAH ADA ───────────────────────────────────── --}}
            @if($journalEntry)
                @php
                    $lines      = $journalEntry->linesWithAccount;
                    $totalDebit = $lines->sum('debit');
                    $totalKredit = $lines->sum('credit');
                    $isBalanced = round($totalDebit - $totalKredit, 2) === 0.0;
                @endphp

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
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
                        <i class="fas fa-hourglass-half fa-3x text-muted mb-3 d-block"></i>
                        <h6 class="text-muted">Membuat jurnal...</h6>
                        <p class="text-muted small">Jurnal sedang dibuat secara otomatis. Mohon tunggu sebentar.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

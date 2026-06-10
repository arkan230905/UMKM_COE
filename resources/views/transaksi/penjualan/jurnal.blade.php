@extends('layouts.app')

@section('title', 'Jurnal Penjualan - ' . ($penjualan->nomor_penjualan ?? $penjualan->id))

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
                <i class="fas fa-book me-2 text-primary"></i>Jurnal Penjualan
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}">{{ $penjualan->nomor_penjualan ?? '#'.$penjualan->id }}</a></li>
                    <li class="breadcrumb-item active">Jurnal</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
            @if($validation['valid'])
                <form action="{{ route('transaksi.penjualan.jurnal.rebuild', $penjualan->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm"
                            onclick="return confirm('Buat ulang jurnal untuk transaksi ini?')">
                        <i class="fas fa-sync me-1"></i>{{ $journalEntry ? 'Buat Ulang Jurnal' : 'Buat Jurnal' }}
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

        {{-- Kolom Kiri: Info Transaksi --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">No. Transaksi</td>
                            <td><strong class="text-primary">{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ optional($penjualan->tanggal)->format('d/m/Y') ?? $penjualan->tanggal }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pembayaran</td>
                            <td>
                                @switch($penjualan->payment_method ?? '')
                                    @case('cash') <span class="badge bg-success">Tunai</span> @break
                                    @case('transfer') <span class="badge bg-info">Transfer Bank</span> @break
                                    @default <span class="badge bg-secondary">{{ ucfirst($penjualan->payment_method ?? '-') }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-2"></td></tr>
                        @php
                            $subtotalProduk = 0;
                            $totalDiskon = (float)($penjualan->diskon_nominal ?? 0);
                            if ($penjualan->details->count() > 0) {
                                foreach ($penjualan->details as $d) {
                                    $subtotalProduk += (float)($d->subtotal ?? ($d->jumlah * $d->harga_satuan));
                                    $totalDiskon += (float)($d->diskon_nominal ?? 0);
                                }
                            } else {
                                $subtotalProduk = (float)($penjualan->total ?? 0);
                            }
                            $biayaPPN    = (float)($penjualan->biaya_ppn ?? 0);
                            $biayaOngkir = (float)($penjualan->biaya_ongkir ?? 0);
                            $grandTotal  = (float)($penjualan->grand_total ?: ($penjualan->total + $biayaPPN + $biayaOngkir));
                        @endphp
                        <tr>
                            <td class="text-muted">Subtotal Produk</td>
                            <td>Rp {{ number_format($subtotalProduk, 0, ',', '.') }}</td>
                        </tr>
                        @if($totalDiskon > 0)
                        <tr>
                            <td class="text-muted">Diskon</td>
                            <td class="text-danger">- Rp {{ number_format($totalDiskon, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($biayaPPN > 0)
                        <tr>
                            <td class="text-muted">PPN (11%)</td>
                            <td>Rp {{ number_format($biayaPPN, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($biayaOngkir > 0)
                        <tr>
                            <td class="text-muted">Ongkir</td>
                            <td>Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted fw-bold">Grand Total</td>
                            <td><strong class="text-dark">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
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
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="showPanduanAkun()">
                                <i class="fas fa-question-circle me-1"></i>Panduan Akun
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Semua akun tersedia.</strong>
                    {{ $journalEntry ? 'Jurnal sudah dibuat.' : 'Klik "Buat Jurnal" untuk membuat jurnal transaksi ini.' }}
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
                            Jurnal Transaksi
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
                        <h6 class="text-muted">Jurnal belum dibuat</h6>
                        <p class="text-muted small mb-3">Semua akun sudah tersedia. Klik tombol di bawah untuk membuat jurnal.</p>
                        <form action="{{ route('transaksi.penjualan.jurnal.rebuild', $penjualan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Buat Jurnal Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal Panduan Akun --}}
<div class="modal fade" id="panduanAkunModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i>Panduan Akun Jurnal Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Berikut adalah akun-akun yang dibutuhkan untuk jurnal penjualan beserta tipe dan contoh kode akunnya:</p>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Akun</th>
                            <th>Tipe</th>
                            <th>Posisi</th>
                            <th>Contoh Kode</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Kas / Bank / Piutang</strong></td>
                            <td><span class="badge bg-primary">Asset</span></td>
                            <td>Debit</td>
                            <td>111, 112, 118</td>
                            <td>Akun penerimaan sesuai metode bayar</td>
                        </tr>
                        <tr>
                            <td><strong>Penjualan</strong></td>
                            <td><span class="badge bg-success">Revenue</span></td>
                            <td>Kredit</td>
                            <td>41</td>
                            <td>Nama akun harus persis "Penjualan"</td>
                        </tr>
                        <tr>
                            <td><strong>PPN Keluaran</strong></td>
                            <td><span class="badge bg-warning text-dark">Liability</span></td>
                            <td>Kredit</td>
                            <td>212</td>
                            <td>Wajib jika transaksi ada PPN. Nama: "PPN Keluaran"</td>
                        </tr>
                        <tr>
                            <td><strong>Pendapatan Lain-lain</strong></td>
                            <td><span class="badge bg-success">Revenue</span></td>
                            <td>Kredit</td>
                            <td>42</td>
                            <td>Wajib jika ada ongkir. Nama diawali "Pendapatan Lain"</td>
                        </tr>
                        <tr>
                            <td><strong>Diskon Penjualan</strong></td>
                            <td><span class="badge bg-danger">Expense</span></td>
                            <td>Debit</td>
                            <td>43</td>
                            <td>Wajib jika ada diskon. Nama mengandung "Diskon Penjualan"</td>
                        </tr>
                        <tr>
                            <td><strong>HPP {nama produk}</strong></td>
                            <td><span class="badge bg-danger">Expense</span></td>
                            <td>Debit</td>
                            <td>51, 56</td>
                            <td>Bisa spesifik per produk atau umum "Harga Pokok Penjualan"</td>
                        </tr>
                        <tr>
                            <td><strong>Persediaan Barang Jadi {nama produk}</strong></td>
                            <td><span class="badge bg-primary">Asset</span></td>
                            <td>Kredit</td>
                            <td>116</td>
                            <td>Bisa spesifik per produk atau mapping di master produk</td>
                        </tr>
                    </tbody>
                </table>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Tips:</strong> Untuk HPP dan Persediaan, Anda bisa membuat satu akun umum
                    ("Harga Pokok Penjualan" dan "Persediaan Barang Jadi") yang akan digunakan untuk semua produk,
                    atau akun spesifik per produk untuk laporan yang lebih detail.
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Tambah Akun Baru
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showPanduanAkun() {
    const modal = new bootstrap.Modal(document.getElementById('panduanAkunModal'));
    modal.show();
}
</script>
@endpush

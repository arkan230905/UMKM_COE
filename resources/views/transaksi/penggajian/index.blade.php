@extends('layouts.app')

@section('title', 'Data Penggajian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-money-check-alt me-2"></i>Data Penggajian
        </h2>
        <a href="{{ route('transaksi.penggajian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Penggajian
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.penggajian.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" name="nama_pegawai" class="form-control" 
                               value="{{ request('nama_pegawai') }}" placeholder="Cari nama pegawai...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="btkl" {{ request('jenis_pegawai') == 'btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ request('jenis_pegawai') == 'btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Sudah Dibayar</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Penggajian
                @if(request()->hasAny(['nama_pegawai', 'tanggal_mulai', 'tanggal_selesai', 'jenis_pegawai', 'status_pembayaran']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 120px">Nomor Penggajian</th>
                            <th>Tanggal Penggajian</th>
                            <th>Bulan Penggajian</th>
                            <th>Karyawan</th>
                            <th>Metode Pembayaran</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Tunjangan</th>
                            <th class="text-end">Asuransi</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Potongan</th>
                            <th class="text-end fw-bold">Total Gaji</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penggajians as $index => $gaji)
                            @php
                                $jenis = strtoupper($gaji->pegawai->jenis_pegawai ?? 'BTKTL');
                                $tanggal = \Carbon\Carbon::parse($gaji->tanggal_penggajian);
                                $bulanPenggajian = $tanggal->locale('id')->translatedFormat('F Y');
                                $coa = \App\Models\Coa::where('kode_akun', $gaji->coa_kasbank)->first();
                                
                                $gajiPokok = (float) ($gaji->gaji_pokok ?? 0);
                                $totalProduk = (float) ($gaji->total_produk_bulan ?? $gaji->total_produk_bulanan ?? 0);
                                $tarifProduk = (float) ($gaji->tarif_produk ?? 0);

                                if ($gajiPokok <= 0 && $totalProduk > 0 && $tarifProduk > 0) {
                                    $gajiPokok = $totalProduk * $tarifProduk;
                                }

                                $tunjangan = (float) ($gaji->total_tunjangan ?? $gaji->tunjangan ?? 0);
                                $asuransi = (float) ($gaji->asuransi ?? 0);
                                $bonus = (float) ($gaji->bonus ?? 0);
                                $potongan = (float) ($gaji->potongan ?? 0);
                                $totalGaji = $gajiPokok + $tunjangan + $bonus - $asuransi - $potongan;
                            @endphp
                            <tr>
                                <td class="text-center">PGJ{{ str_pad($gaji->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="text-center">{{ $tanggal->format('d/m/Y') }}</td>
                                <td>{{ $bulanPenggajian }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $gaji->pegawai->nama ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $jenis }}</small>
                                    </div>
                                </td>
                                <td>{{ $coa->nama_akun ?? $gaji->coa_kasbank }}</td>
                                <td class="text-center">
                                    @if($gaji->status_pembayaran === 'lunas')
                                        <span class="badge bg-success">Sudah Dibayar</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Dibayar</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($tunjangan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($asuransi, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($bonus, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($potongan, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format($totalGaji, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                        <!-- Row 1: Detail | Jurnal -->
                                        <a href="{{ route('transaksi.penggajian.show', $gaji->id) }}" class="btn btn-sm btn-success w-100" title="Detail Penggajian">
                                            Detail
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary w-100" 
                                                title="Lihat Jurnal"
                                                onclick="loadJournal({{ $gaji->id }}, 'PGJ{{ str_pad($gaji->id, 6, '0', STR_PAD_LEFT) }}')"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#journalModal">
                                            Jurnal
                                        </button>
                                        
                                        <!-- Row 2: Bayar | Hapus -->
                                        @if($gaji->status_pembayaran !== 'lunas')
                                            <form action="{{ route('transaksi.penggajian.markAsPaid', $gaji->id) }}" method="POST" class="m-0 d-inline w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success w-100" title="Tandai Sudah Dibayar">
                                                    Bayar
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary w-100" disabled title="Sudah Dibayar">
                                                Lunas
                                            </button>
                                        @endif

                                        @if($gaji->status_pembayaran !== 'lunas')
                                            <form action="{{ route('transaksi.penggajian.destroy', $gaji->id) }}" method="POST" class="m-0 d-inline w-100" onsubmit="return confirm('Yakin ingin hapus?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Hapus Penggajian">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary w-100" disabled title="Tidak bisa dihapus karena sudah dibayar">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data penggajian</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4 shadow-lg border-0">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="journalModalLabel" style="color: #1F2937;">
                    Jurnal Penggajian
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-2">


                <!-- Journal Content -->
                <div id="journalContentContainer" class="p-2">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                        <p class="text-muted">Memuat data jurnal...</p>
                    </div>
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

    #journalModal tbody tr {
        border-bottom: 1px solid #E5E7EB;
        transition: background-color 0.15s ease;
    }

    #journalModal tbody tr:hover {
        background-color: #F9FAFB !important;
    }

    #journalModal tbody tr:last-child {
        border-bottom: none;
    }

    #journalModal tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    #journalModal .account-name {
        color: #1F2937;
        font-weight: 600;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    #journalModal .account-code {
        color: #6B7280;
        font-size: 0.8rem;
        font-weight: 400;
    }
</style>
@endpush

@push('scripts')
<script>
    // Function to load journal data for a specific penggajian
    function loadJournal(penggajianId, nomorPenggajian) {
        // Show loading state
        const journalContentContainer = document.getElementById('journalContentContainer');
        const transactionInfoCard = document.getElementById('transactionInfoCard');
        
        if (!journalContentContainer) return;
        
        // Reset loading state
        journalContentContainer.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i><p class="text-muted">Memuat data jurnal...</p></div>';
        
        // Hide transaction info during loading
        if (transactionInfoCard) {
            transactionInfoCard.style.display = 'none';
        }
        
        // Fetch journal data
        fetch(`/transaksi/api/penggajian/${penggajianId}/journal?html=true`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                // Update transaction info card
                if (transactionInfoCard && data.penggajian) {
                    document.getElementById('nomorPenggajian').textContent = data.penggajian.nomor_penggajian || nomorPenggajian || '-';
                    document.getElementById('karyawanName').textContent = data.penggajian.pegawai_name || '-';
                    document.getElementById('tanggalPenggajian').textContent = data.penggajian.tanggal || '-';
                    document.getElementById('totalGaji').textContent = 'Rp ' + (data.penggajian.total_gaji || 0).toLocaleString('id-ID');
                    transactionInfoCard.style.display = 'block';
                }
                
                document.getElementById('journalContentContainer').innerHTML = data.html;
            } else {
                if (transactionInfoCard) transactionInfoCard.style.display = 'none';
                document.getElementById('journalContentContainer').innerHTML = `
                    <div class="text-center text-muted py-5 border-bottom" style="border-color: #E5E7EB !important;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Jurnal belum dibuat untuk penggajian ini
                    </div>
                `;
            }
        })
        .catch(error => {
            if (transactionInfoCard) transactionInfoCard.style.display = 'none';
            document.getElementById('journalContentContainer').innerHTML = `
                <div class="text-center text-danger py-5 border-bottom" style="border-color: #E5E7EB !important;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memuat data jurnal: ${error.message}
                </div>
            `;
        });
    }
</script>
@endpush

@endsection

@extends('layouts.app')

@section('title', 'Detail Penggajian')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-file-text"></i> Detail Penggajian</h3>

    @php
        $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
        $coa = \App\Models\Coa::where('kode_akun', $penggajian->coa_kasbank)->first();

        if ($jenis === 'btkl') {
            $gajiDasar = (float)($penggajian->tarif_per_jam ?? 0) * (float)($penggajian->total_jam_kerja ?? 0);
        } else {
            $gajiDasar = (float)($penggajian->gaji_pokok ?? 0);
        }

        $totalGajiHitung = $gajiDasar
            + (float)($penggajian->total_tunjangan ?? 0)
            + (float)($penggajian->asuransi ?? 0)
            + (float)($penggajian->bonus ?? 0)
            - (float)($penggajian->potongan ?? 0);
    @endphp

    <div class="row">
        <!-- Informasi Pegawai -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pegawai</h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="45%">Nama Pegawai</td>
                            <td>: <strong>{{ $penggajian->pegawai->nama ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>: {{ $penggajian->pegawai->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Jenis Pegawai</td>
                            <td>: 
                                <span class="badge {{ strtoupper($jenis) === 'btkl' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ strtoupper($jenis) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Penggajian</td>
                            <td>: {{ \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td>Metode Pembayaran</td>
                            <td>: <strong>{{ $coa->nama_akun ?? $penggajian->coa_kasbank }}</strong></td>
                        </tr>
                        <tr>
                            <td>Status Pembayaran</td>
                            <td>:
                                @if($penggajian->status_pembayaran === 'lunas')
                                    <span class="badge bg-success">Sudah Dibayar</span>
                                    @if($penggajian->tanggal_dibayar)
                                        <small class="text-muted">({{ \Carbon\Carbon::parse($penggajian->tanggal_dibayar)->format('d F Y') }})</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Belum Dibayar</span>
                                    <form action="{{ route('transaksi.penggajian.markAsPaid', $penggajian->id) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Tandai penggajian ini sebagai sudah dibayar?')">
                                            <i class="fas fa-check-circle me-1"></i>Tandai Sudah Dibayar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rincian Gaji -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Rincian Gaji</h5>
                    <table class="table table-borderless mb-0">
                        @if($jenis === 'btkl')
                            <tr>
                                <td width="45%">Tarif per Jam</td>
                                <td>: Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Jam Kerja</td>
                                <td>: {{ number_format($penggajian->total_jam_kerja ?? 0, 0) }} Jam</td>
                            </tr>
                            <tr>
                                <td>Gaji Dasar</td>
                                <td>: Rp {{ number_format(($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td width="45%">Gaji Pokok</td>
                                <td>: Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        
                        <!-- Tunjangan Detail -->
                        <tr class="table-light">
                            <td colspan="2"><strong>Tunjangan:</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Jabatan</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Transport</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Konsumsi</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td class="ps-4">&nbsp;&nbsp;Total Tunjangan</td>
                            <td>: Rp {{ number_format($penggajian->total_tunjangan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        
                        <tr>
                            <td>Asuransi / BPJS</td>
                            <td>: Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Bonus</td>
                            <td>: Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Potongan</td>
                            <td>: Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Gaji -->
    <div class="card border-0 mb-4" style="background-color: #f8f9fa;">
        <div class="card-body text-center py-4">
            <h5 class="mb-2 text-dark fw-bold">Total Gaji</h5>
            <h2 class="mb-0 fw-bold" style="color: #333; font-size: 2.5rem;">
                Rp {{ number_format($totalGajiHitung, 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <!-- Posting Status & Buttons -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Posting Status Badge -->
                <div>
                    <span class="me-2 text-muted">Status Posting:</span>
                    @if($penggajian->status_posting === 'posted')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Sudah Diposting ke Jurnal
                        </span>
                        <small class="text-muted ms-2">
                            ({{ \Carbon\Carbon::parse($penggajian->tanggal_posting)->format('d F Y H:i') }})
                        </small>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-clock"></i> Belum Diposting ke Jurnal
                        </span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    @if(in_array(auth()->user()->role, ['owner', 'admin']) && $penggajian->status_posting !== 'posted')
                        <form action="{{ route('transaksi.penggajian.post-journal', $penggajian->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin memposting penggajian ini ke jurnal umum?')">
                                <i class="bi bi-journal-check"></i> Posting ke Jurnal
                            </button>
                        </form>
                        
                        <form action="{{ route('transaksi.penggajian.recalculate', $penggajian->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin menghitung ulang penggajian ini berdasarkan master data terbaru?')">
                                <i class="bi bi-calculator"></i> Hitung Ulang
                            </button>
                        </form>
                        
                        <a href="{{ route('transaksi.penggajian.edit', $penggajian->id) }}" class="btn btn-info">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @endif

                    <a href="{{ route('transaksi.penggajian.slip', $penggajian->id) }}" class="btn btn-success" target="_blank">
                        <i class="bi bi-file-earmark-text"></i> Lihat Slip Gaji
                    </a>

                    <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
}
</style>
@endsection

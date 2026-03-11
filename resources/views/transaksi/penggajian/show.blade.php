@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-file-text"></i> Detail Penggajian</h3>

    @php
        $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
        $coa = \App\Models\Coa::where('kode_akun', $penggajian->coa_kasbank)->first();
    @endphp

    <div class="row">
        <!-- Informasi Pegawai -->
        <div class="col-md-6 mb-4">
            <div class="card bg-dark text-white border-0 h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pegawai</h5>
                    <table class="table table-dark table-borderless mb-0">
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
                    </table>
                </div>
            </div>
        </div>

        <!-- Rincian Gaji -->
        <div class="col-md-6 mb-4">
            <div class="card bg-dark text-white border-0 h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Rincian Gaji</h5>
                    <table class="table table-dark table-borderless mb-0">
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
                        
                        <tr>
                            <td>Tunjangan</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
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
                Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <!-- Buttons -->
    <div class="text-start">
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
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

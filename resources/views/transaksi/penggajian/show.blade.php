@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-file-text"></i> Detail Penggajian</h3>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <!-- Informasi Pegawai -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pegawai</h5>
                    <table class="table table-dark table-borderless">
                        <tr>
                            <td width="40%">Nama Pegawai</td>
                            <td>: <strong>{{ $penggajian->pegawai->nama ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>: {{ $penggajian->pegawai->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Jenis Pegawai</td>
                            <td>: 
                                <span class="badge {{ strtoupper($penggajian->pegawai->jenis_pegawai ?? 'btktl') === 'BTKL' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Penggajian</td>
                            <td>: {{ \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d F Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2 mb-3">Rincian Gaji</h5>
                    <table class="table table-dark table-borderless">
                        @php
                            $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
                        @endphp
                        
                        @if($jenis === 'btkl')
                            <tr>
                                <td width="40%">Tarif per Jam</td>
                                <td>: Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Jam Kerja</td>
                                <td>: {{ number_format($penggajian->total_jam_kerja ?? 0, 2) }} jam</td>
                            </tr>
                            <tr>
                                <td>Gaji Dasar</td>
                                <td>: Rp {{ number_format(($penggajian->tarif_per_jam ?? 0) * ($penggajian->total_jam_kerja ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td width="40%">Gaji Pokok</td>
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

            <!-- Perhitungan -->
            <div class="card bg-secondary mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Perhitungan</h5>
                </div>
                <div class="card-body">
                    @if($jenis === 'btkl')
                        <p class="mb-2">
                            <strong>BTKL:</strong> (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
                        </p>
                        <p class="mb-0">
                            = (Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }} × {{ number_format($penggajian->total_jam_kerja ?? 0, 2) }}) 
                            + Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}
                            + Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}
                            + Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}
                            - Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}
                        </p>
                    @else
                        <p class="mb-2">
                            <strong>BTKTL:</strong> Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
                        </p>
                        <p class="mb-0">
                            = Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}
                            + Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}
                            + Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}
                            + Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}
                            - Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Total Gaji -->
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0"><i class="bi bi-wallet2"></i> Total Gaji Bersih</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h3 class="mb-0">Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <div>
                    <button onclick="window.print()" class="btn btn-info btn-lg me-2">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <form action="{{ route('transaksi.penggajian.destroy', $penggajian->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
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

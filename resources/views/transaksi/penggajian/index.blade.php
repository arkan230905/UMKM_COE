@extends('layouts.app')

@section('content')
<style>
.currency {
    padding-right: 8px !important;
    min-width: 120px;
}
</style>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-cash-coin"></i> Data Penggajian</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('transaksi.penggajian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Penggajian
        </a>
    </div>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis</th>
                            <th class="text-center">Tanggal</th>
                            <th>Gaji Pokok / Tarif</th>
                            <th>Jam Kerja</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Bonus</th>
                            <th>Potongan</th>
                            <th>Total Gaji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penggajians as $index => $gaji)
                            @php
                                $jenis = strtoupper($gaji->pegawai->jenis_pegawai ?? 'BTKTL');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $gaji->pegawai->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $jenis === 'BTKL' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $jenis }}
                                    </span>
                                </td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($gaji->tanggal_penggajian)->format('d-m-Y') }}</td>
                                <td class="text-end currency">
                                    @if($jenis === 'BTKL')
                                        {{ number_format($gaji->tarif_per_jam ?? 0, 0, ',', '.') }}
                                    @else
                                        {{ number_format($gaji->gaji_pokok ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($jenis === 'BTKL')
                                        {{ number_format($gaji->total_jam_kerja ?? 0, 0, ',', '.') }} jam
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end currency">Rp {{ number_format($gaji->tunjangan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end currency">Rp {{ number_format($gaji->asuransi ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end currency">Rp {{ number_format($gaji->bonus ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end currency">Rp {{ number_format($gaji->potongan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end currency"><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ url('transaksi/penggajian/' . $gaji->id . '/slip') }}" class="btn btn-sm btn-success" title="Cetak Slip Gaji" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        <form action="{{ route('transaksi.penggajian.destroy', $gaji->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">Belum ada data penggajian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($penggajians->count() > 0)
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="10" class="text-end">Total Keseluruhan:</th>
                                <th class="text-end currency">Rp {{ number_format($penggajians->sum('total_gaji'), 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

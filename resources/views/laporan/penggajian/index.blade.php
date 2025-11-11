@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Penggajian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('laporan.penggajian') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Pilih Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" 
                                           value="{{ request('bulan', now()->format('Y-m')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('laporan.penggajian') }}" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Nama Pegawai</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Gaji Pokok / Tarif</th>
                                    <th>Jam Kerja</th>
                                    <th>Tunjangan</th>
                                    <th>Asuransi</th>
                                    <th>Bonus</th>
                                    <th>Potongan</th>
                                    <th>Total Gaji</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penggajians as $penggajian)
                                @php
                                    $jenis = strtoupper($penggajian->pegawai->jenis_pegawai ?? 'BTKTL');
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('F Y') : '-' }}</td>
                                    <td>{{ $penggajian->pegawai->nama ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $jenis === 'BTKL' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ $jenis }}
                                        </span>
                                    </td>
                                    <td>{{ $penggajian->tanggal_penggajian ? \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d-m-Y') : '-' }}</td>
                                    <td class="text-right">
                                        @if($jenis === 'BTKL')
                                            Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}/jam
                                        @else
                                            Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($jenis === 'BTKL')
                                            {{ number_format($penggajian->total_jam_kerja ?? 0, 2) }} jam
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">Rp {{ number_format($penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right"><strong>Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data penggajian</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="11" class="text-right">Total Keseluruhan</th>
                                    <th class="text-right"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('laporan.penggajian', ['bulan' => request('bulan'), 'export' => 'pdf']) }}" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

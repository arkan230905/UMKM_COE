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
                                    <th>Gaji Pokok</th>
                                    <th>Tunjangan</th>
                                    <th>Bonus</th>
                                    <th>Potongan</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penggajians as $penggajian)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $penggajian->periode->format('F Y') }}</td>
                                    <td>{{ $penggajian->pegawai->nama_pegawai }}</td>
                                    <td class="text-right">{{ format_rupiah($penggajian->gaji_pokok) }}</td>
                                    <td class="text-right">{{ format_rupiah($penggajian->tunjangan) }}</td>
                                    <td class="text-right">{{ format_rupiah($penggajian->bonus) }}</td>
                                    <td class="text-right">{{ format_rupiah($penggajian->potongan) }}</td>
                                    <td class="text-right">{{ format_rupiah($penggajian->total_gaji) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data penggajian</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-right">Total</th>
                                    <th class="text-right">{{ format_rupiah($total) }}</th>
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

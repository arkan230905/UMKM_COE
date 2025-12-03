@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Pembayaran Beban</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('transaksi.pembayaran-beban.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pembayaran
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pembayaran Beban</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Akun Beban</th>
                            <th>Akun Kas</th>
                            <th>Jumlah</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaranBeban as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->keterangan }}</td>
                            <td>{{ $item->coaBeban->kode }} - {{ $item->coaBeban->nama }}</td>
                            <td>{{ $item->coaKas->kode }} - {{ $item->coaKas->nama }}</td>
                            <td class="text-right">{{ format_rupiah($item->jumlah) }}</td>
                            <td>
                                <a href="{{ route('transaksi.pembayaran-beban.show', $item->id) }}" 
                                   class="btn btn-sm btn-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('transaksi.pembayaran-beban.print', $item->id) }}" 
                                   class="btn btn-sm btn-secondary" title="Cetak" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $pembayaranBeban->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            responsive: true,
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 6] },
                { className: 'text-right', targets: [5] },
                { width: '100px', targets: [6] }
            ]
        });
    });
</script>
@endpush

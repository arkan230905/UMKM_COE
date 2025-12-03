@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Retur Pembelian</h1>
        <a href="{{ route('transaksi.retur-pembelian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Retur Pembelian
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Tanggal</th>
                            <th>Ref Pembelian</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Total Item</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returs as $retur)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $retur->tanggal }}</td>
                                <td>
                                    @if($retur->ref_id)
                                        <a href="{{ route('transaksi.pembelian.show', $retur->ref_id) }}">
                                            Pembelian #{{ $retur->ref_id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($retur->alasan, 50) }}</td>
                                <td>
                                    @if($retur->status === 'posted')
                                        <span class="badge bg-success">Posted</span>
                                    @elseif($retur->status === 'approved')
                                        <span class="badge bg-info">Approved</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>{{ $retur->details->count() }} item</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('transaksi.retur-pembelian.show', $retur->id) }}" 
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($retur->status !== 'posted')
                                            <form action="{{ route('transaksi.retur-pembelian.destroy', $retur->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus retur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data retur pembelian</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

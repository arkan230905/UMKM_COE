@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Daftar Aset</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('master-data.aset.create') }}" class="btn btn-primary">Tambah Aset</a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Aset</th>
                    <th>Kategori</th>
                    <th>Jenis Aset</th>
                    <th>Harga</th>
                    <th>Tanggal Beli</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($asets as $aset)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $aset->nama }}</td>
                        <td>{{ $aset->kategori }}</td>
                        <td>
                            @if($aset->jenis_aset == 'Aset Tetap')
                                <span class="badge bg-primary">{{ $aset->jenis_aset }}</span>
                            @else
                                <span class="badge bg-warning">{{ $aset->jenis_aset }}</span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($aset->harga, 0, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('master-data.aset.edit', $aset->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('master-data.aset.destroy', $aset->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data aset</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

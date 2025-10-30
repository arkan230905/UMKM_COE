@extends('layouts.app')

@section('title', 'Daftar Aset')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Daftar Aset</h5>
            <div class="d-flex justify-content-end">
                <a href="{{ route('aset.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Aset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('aset.index') }}" method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jenis_aset">Jenis Aset</label>
                            <select name="jenis_aset" id="jenis_aset" class="form-control">
                                <option value="">-- Semua Jenis --</option>
                                <option value="Aset Tetap" {{ request('jenis_aset') == 'Aset Tetap' ? 'selected' : '' }}>Aset Tetap</option>
                                <option value="Aset Tidak Tetap" {{ request('jenis_aset') == 'Aset Tidak Tetap' ? 'selected' : '' }}>Aset Tidak Tetap</option>
                                <option value="Aset Tak Berwujud" {{ request('jenis_aset') == 'Aset Tak Berwujud' ? 'selected' : '' }}>Aset Tak Berwujud</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <select name="kategori" id="kategori" class="form-control">
                                <option value="">-- Semua Kategori --</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="dijual" {{ request('status') == 'dijual' ? 'selected' : '' }}>Dijual</option>
                                <option value="hilang" {{ request('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                                <option value="rusak" {{ request('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('aset.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Aset</th>
                            <th>Jenis Aset</th>
                            <th>Kategori</th>
                            <th>Harga (Rp)</th>
                            <th>Tanggal Beli</th>
                            <th>Nilai Buku (Rp)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asets as $aset)
                        <tr>
                            <td>{{ $loop->iteration + (($asets->currentPage() - 1) * $asets->perPage()) }}</td>
                            <td>{{ $aset->nama }}</td>
                            <td>{{ $aset->jenis_aset }}</td>
                            <td>{{ $aset->kategori }}</td>
                            <td class="text-right">@money($aset->harga, 'IDR', true)</td>
                            <td>{{ $aset->tanggal_beli->format('d/m/Y') }}</td>
                            <td class="text-right">@money($aset->nilai_buku ?? $aset->harga, 'IDR', true)</td>
                            <td>
                                @php
                                    $statusClass = [
                                        'aktif' => 'success',
                                        'nonaktif' => 'secondary',
                                        'dijual' => 'info',
                                        'hilang' => 'warning',
                                        'rusak' => 'danger'
                                    ][$aset->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ ucfirst($aset->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('aset.show', $aset->id) }}" class="btn btn-sm btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('aset.edit', $aset->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('aset.destroy', $aset->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data aset</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $asets->firstItem() ?? 0 }} - {{ $asets->lastItem() ?? 0 }} dari {{ $asets->total() }} data
                </div>
                <div>
                    {{ $asets->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi select2 jika diperlukan
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-people-fill me-2"></i>Daftar Pegawai
        </h2>
        <div>
            <a href="{{ route('master-data.pegawai.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Tambah Pegawai
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('master-data.pegawai.index') }}" class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari pegawai..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="{{ route('master-data.pegawai.index') }}">
                        <select name="jenis" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <option value="btkl" {{ request('jenis') == 'btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ request('jenis') == 'btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">#</th>
                            <th>Nama Pegawai</th>
                            <th>Kontak</th>
                            <th>Jabatan</th>
                            <th class="text-center">Kategori</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Tunjangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawais as $index => $pegawai)
                        <tr>
                            <td class="text-center text-muted">{{ ($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                        <i class="bi bi-person-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $pegawai->nama }}</div>
                                        <small class="text-muted">{{ $pegawai->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $pegawai->no_telp }}</div>
                                <small class="text-muted">{{ Str::limit($pegawai->alamat, 30) }}</small>
                            </td>
                            <td>{{ $pegawai->jabatan }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $pegawai->kategori_tenaga_kerja == 'btkl' ? 'primary' : 'success' }}">
                                    {{ strtoupper($pegawai->kategori_tenaga_kerja) }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">Rp {{ number_format($pegawai->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($pegawai->tunjangan, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('master-data.pegawai.edit', $pegawai->nomor_induk_pegawai) }}" 
                                       class="btn btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal{{ $pegawai->id }}"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $pegawai->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus data pegawai <strong>{{ $pegawai->nama }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('master-data.pegawai.destroy', $pegawai->nomor_induk_pegawai) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2"></i>
                                    Tidak ada data pegawai yang ditemukan.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        @if($pegawais->total() > 0)
                            Menampilkan {{ ($pegawais->currentPage() - 1) * $pegawais->perPage() + 1 }} - 
                            {{ min($pegawais->currentPage() * $pegawais->perPage(), $pegawais->total()) }} 
                            dari {{ $pegawais->total() }} data
                        @else
                            Tidak ada data yang ditemukan
                        @endif
                    </div>
                    @if($pegawais->hasPages())
                    <div>
                        {{ $pegawais->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Inisialisasi tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto close alert setelah 5 detik
    setTimeout(function() {
        var alert = document.querySelector('.alert');
        if (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
</script>
@endpush

<style>
    .avatar {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-top: none;
    }
    .table > :not(:first-child) {
        border-top: 1px solid #e9ecef;
    }
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .form-control, .form-select {
        border-radius: 0.375rem;
    }
    .btn {
        border-radius: 0.375rem;
    }
</style>
@endsection

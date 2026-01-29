@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-box-seam"></i> Stok {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}
        </h2>
        <p class="text-muted">Lihat stok {{ $tipe == 'material' ? 'bahan baku' : 'bahan pendukung' }} yang tersedia</p>
    </div>
    <div class="col-md-6 text-end">
        <!-- Filter Tipe -->
        <div class="btn-group me-2" role="group">
            <a href="{{ route('pegawai-pembelian.bahan-baku.index', ['tipe' => 'material']) }}" 
               class="btn {{ $tipe == 'material' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="bi bi-box"></i> Bahan Baku
            </a>
            <a href="{{ route('pegawai-pembelian.bahan-baku.index', ['tipe' => 'bahan_pendukung']) }}" 
               class="btn {{ $tipe == 'bahan_pendukung' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="bi bi-flask"></i> Bahan Pendukung
            </a>
        </div>
        <!-- Tombol Tambah dihapus untuk menjaga integritas data stok -->
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-body">
        @if($items->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px">#</th>
                        <th>Nama {{ $tipe == 'material' ? 'Bahan' : 'Bahan Pendukung' }}</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Satuan</th>
                        <th>Stok</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                    @if($tipe == 'material')
                                        <i class="fas fa-box text-primary"></i>
                                    @else
                                        <i class="fas fa-flask text-warning"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $item->nama_bahan }}</div>
                                    <small class="text-muted">ID: {{ $item->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($tipe == 'material')
                                {{ $item->satuan->nama ?? '-' }}
                            @else
                                {{ $item->satuanRelation->nama ?? '-' }}
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($item->harga_satuan_display, 0, ',', '.') }}
                            @if(isset($item->harga_satuan_display) && $item->harga_satuan_display != $item->harga_satuan)
                                <small class="text-muted d-block">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Rata-rata dari pembelian
                                </small>
                            @endif
                        </td>
                        <td>
                            @php
                                $stok = $item->current_stok ?? 0;
                            @endphp
                            @if($stok < 5)
                            <span class="badge bg-danger">{{ number_format($stok, 2, ',', '.') }}</span>
                            @elseif($stok < 10)
                            <span class="badge bg-warning">{{ number_format($stok, 2, ',', '.') }}</span>
                            @else
                            <span class="badge bg-success">{{ number_format($stok, 2, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai-pembelian.bahan-baku.show', $item->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!-- Edit dan Hapus dihapus untuk menjaga integritas data stok -->
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $items->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada data {{ $tipe == 'material' ? 'bahan baku' : 'bahan pendukung' }}</p>
            <!-- Tombol Tambah dihapus untuk menjaga integritas data stok -->
        </div>
        @endif
    </div>
</div>
@endsection

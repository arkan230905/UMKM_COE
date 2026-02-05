@extends('layouts.app')

@push('styles')
<style>
/* Bahan Pendukung page specific - BLACK text for kode and nama bahan */
.table tbody td:nth-child(1) {
    color: #333 !important; /* Kolom Kode */
    text-align: center !important;
    padding: 8px !important;
}
.table tbody td:nth-child(2) {
    color: #333 !important; /* Kolom Nama Bahan */
}
.table tbody td:nth-child(2) strong {
    color: #333 !important; /* Nama bahan yang bold */
}

/* Special styling for code column (1st column) - Purple rounded pill like bahan baku */
.table tbody td:nth-child(1) code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 20px !important;
    border-radius: 25px !important;
    display: inline-block !important;
    min-width: 120px !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    border: none !important;
}

.table tbody tr:hover td:nth-child(1) code {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    transform: scale(1.05) !important;
    transition: all 0.3s ease !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-flask me-2"></i>Bahan Pendukung
        </h2>
        <a href="{{ route('master-data.bahan-pendukung.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Bahan Pendukung
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Bahan Pendukung
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Kode</th>
                            <th>Nama Bahan</th>
                            <th>Satuan Utama</th>
                            <th class="text-end">Stok</th>
                            <th class="text-end">Stok Min</th>
                            <th class="text-end">Harga Satuan Utama</th>
                            <th class="text-end">Sub Satuan 1</th>
                            <th class="text-end">Sub Satuan 2</th>
                            <th class="text-end">Sub Satuan 3</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanPendukungs as $key => $bahan)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $bahan->kode_bahan ?? 'BP' . str_pad($bahan->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-flask text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                                            <small class="text-muted">ID: {{ $bahan->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($bahan->satuan)
                                        <span class="badge bg-info">{{ $bahan->satuan->nama }} ({{ $bahan->satuan->kode }})</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold">{{ $bahan->stok ? rtrim(rtrim(number_format($bahan->stok, 5, ',', '.'), '0'), ',') : '0' }}</span>
                                    @if(($bahan->stok ?? 0) <= ($bahan->stok_minimum ?? 0) && ($bahan->stok ?? 0) > 0)
                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok hampir habis"></i>
                                    @elseif(($bahan->stok ?? 0) <= 0)
                                        <i class="fas fa-times-circle text-danger ms-1" title="Stok habis"></i>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="text-muted">{{ $bahan->stok_minimum ? rtrim(rtrim(number_format($bahan->stok_minimum, 5, ',', '.'), '0'), ',') : '0' }}</span>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0, 0, ',', '.') }}
                                    @if(isset($bahan->harga_satuan_display) && $bahan->harga_satuan_display != $bahan->harga_satuan)
                                        <small class="text-muted d-block">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Rata-rata
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($bahan->subSatuan1 && ($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0) > 0)
                                        @php
                                            $hargaUtama = $bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0;
                                            $konversi1 = $bahan->sub_satuan_1_konversi ?? 1;
                                            $nilai1 = $bahan->sub_satuan_1_nilai ?? 1;
                                            $hargaSubSatuan1 = ($konversi1 * $hargaUtama) / $nilai1;
                                        @endphp
                                        <div class="fw-semibold text-primary">
                                            Rp {{ number_format($hargaSubSatuan1, 0, ',', '.') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $bahan->subSatuan1->nama ?? '' }}<br>
                                            {{ rtrim(rtrim(number_format($konversi1, 5, ',', '.'), '0'), ',') }} {{ $bahan->satuan ? $bahan->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($nilai1, 5, ',', '.'), '0'), ',') }} {{ $bahan->subSatuan1->nama ?? '' }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($bahan->subSatuan2 && ($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0) > 0)
                                        @php
                                            $hargaUtama = $bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0;
                                            $konversi2 = $bahan->sub_satuan_2_konversi ?? 1;
                                            $nilai2 = $bahan->sub_satuan_2_nilai ?? 1;
                                            $hargaSubSatuan2 = ($konversi2 * $hargaUtama) / $nilai2;
                                        @endphp
                                        <div class="fw-semibold text-success">
                                            Rp {{ number_format($hargaSubSatuan2, 0, ',', '.') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $bahan->subSatuan2->nama ?? '' }}<br>
                                            {{ rtrim(rtrim(number_format($konversi2, 5, ',', '.'), '0'), ',') }} {{ $bahan->satuan ? $bahan->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($nilai2, 5, ',', '.'), '0'), ',') }} {{ $bahan->subSatuan2->nama ?? '' }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($bahan->subSatuan3 && ($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0) > 0)
                                        @php
                                            $hargaUtama = $bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0;
                                            $konversi3 = $bahan->sub_satuan_3_konversi ?? 1;
                                            $nilai3 = $bahan->sub_satuan_3_nilai ?? 1;
                                            $hargaSubSatuan3 = ($konversi3 * $hargaUtama) / $nilai3;
                                        @endphp
                                        <div class="fw-semibold text-warning">
                                            Rp {{ number_format($hargaSubSatuan3, 0, ',', '.') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $bahan->subSatuan3->nama ?? '' }}<br>
                                            {{ rtrim(rtrim(number_format($konversi3, 5, ',', '.'), '0'), ',') }} {{ $bahan->satuan ? $bahan->satuan->nama : '' }} = {{ rtrim(rtrim(number_format($nilai3, 5, ',', '.'), '0'), ',') }} {{ $bahan->subSatuan3->nama ?? '' }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bahan->deskripsi)
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $bahan->deskripsi }}">
                                            {{ $bahan->deskripsi }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.bahan-pendukung.edit', $bahan->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.bahan-pendukung.destroy', $bahan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus bahan pendukung \'{{ $bahan->nama_bahan }}\'?')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bahan pendukung</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

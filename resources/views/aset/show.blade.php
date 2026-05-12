@extends('layouts.app')

@section('title', 'Detail Aset')

@section('content')
<div class="container-fluid">
  <div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Detail Aset</h5></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4"><strong>Nama Aset</strong><div>{{ $asset->nama_asset ?? $asset->nama_aset }}</div></div>
        <div class="col-md-4"><strong>Tanggal Beli</strong><div>{{ optional($asset->tanggal_beli)->format('d M Y') }}</div></div>
        <div class="col-md-4"><strong>Umur Ekonomis</strong><div>{{ $asset->umur_ekonomis }} tahun</div></div>
        <div class="col-md-4"><strong>Harga Perolehan</strong><div>Rp {{ number_format((float)$asset->harga_perolehan, 2, ',', '.') }}</div></div>
        <div class="col-md-4"><strong>Nilai Sisa</strong><div>Rp {{ number_format((float)$asset->nilai_sisa, 2, ',', '.') }}</div></div>
        <div class="col-md-4"><strong>Penyusutan per Tahun</strong><div>Rp {{ number_format((float)$penyusutan_per_tahun, 2, ',', '.') }}</div></div>
        <div class="col-md-4"><strong>Total Penyusutan</strong><div>Rp {{ number_format((float)$total_depreciation, 2, ',', '.') }}</div></div>
        <div class="col-md-4"><strong>Nilai Buku Saat Ini</strong><div>Rp {{ number_format((float)$current_book_value, 2, ',', '.') }}</div></div>
      </div>
      <div class="mt-3">
        <a href="{{ route('aset.depreciation', ['asset' => $asset->id]) }}" class="btn btn-outline-primary">Lihat Jadwal Penyusutan</a>
        <a href="{{ route('aset.index') }}" class="btn btn-secondary">Kembali</a>
      </div>
    </div>
  </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Jadwal Penyusutan')

@section('content')
<div class="container-fluid">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Jadwal Penyusutan — {{ $asset->nama_asset ?? $asset->nama_aset }}</h5>
      <a href="{{ route('aset.show', $asset->id) }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3"><strong>Harga Perolehan</strong><div>Rp {{ number_format((float)$asset->harga_perolehan, 2, ',', '.') }}</div></div>
        <div class="col-md-3"><strong>Nilai Sisa</strong><div>Rp {{ number_format((float)$asset->nilai_sisa, 2, ',', '.') }}</div></div>
        <div class="col-md-3"><strong>Umur Ekonomis</strong><div>{{ $asset->umur_ekonomis }} tahun</div></div>
        <div class="col-md-3"><strong>Tanggal Perolehan</strong><div>{{ optional($asset->tanggal_perolehan)->format('d M Y') }}</div></div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>TAHUN</th>
              <th class="text-end">PENYUSUTAN</th>
              <th class="text-end">AKUMULASI PENY</th>
              <th class="text-end">NILAI BUKU</th>
            </tr>
          </thead>
          <tbody>
            @forelse($depreciation_schedule as $year)
              <tr>
                <td>{{ $year->tahun }}</td>
                <td class="text-end">Rp {{ number_format((float)$year->beban_penyusutan, 2, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format((float)$year->akumulasi_penyusutan, 2, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format((float)$year->nilai_buku_akhir, 2, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">Belum ada jadwal penyusutan</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

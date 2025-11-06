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
        <div class="col-md-3"><strong>Tanggal Beli</strong><div>{{ optional($asset->tanggal_beli)->format('d M Y') }}</div></div>
      </div>

      <div class="accordion" id="accDepr">
        @foreach($depreciation_schedule as $year)
          <div class="accordion-item">
            <h2 class="accordion-header" id="head-{{ $year['tahun'] }}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#col-{{ $year['tahun'] }}" aria-expanded="false" aria-controls="col-{{ $year['tahun'] }}">
                <div class="w-100 d-flex justify-content-between">
                  <span>Tahun {{ $year['tahun'] }} — Periode {{ $year['periode'] }}</span>
                  <span>
                    Beban: Rp {{ number_format((float)$year['biaya_penyusutan'], 2, ',', '.') }} ·
                    Akumulasi: Rp {{ number_format((float)$year['akumulasi_penyusutan'], 2, ',', '.') }} ·
                    Nilai Buku: Rp {{ number_format((float)$year['nilai_buku'], 2, ',', '.') }}
                  </span>
                </div>
              </button>
            </h2>
            <div id="col-{{ $year['tahun'] }}" class="accordion-collapse collapse" aria-labelledby="head-{{ $year['tahun'] }}" data-bs-parent="#accDepr">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Bulan</th>
                        <th class="text-end">Beban Penyusutan</th>
                        <th class="text-end">Akumulasi Penyusutan</th>
                        <th class="text-end">Nilai Buku</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($year['monthly_breakdown'] as $m)
                        <tr>
                          <td>{{ $m['month'] }}</td>
                          <td class="text-end">Rp {{ number_format((float)$m['biaya_penyusutan'], 2, ',', '.') }}</td>
                          <td class="text-end">Rp {{ number_format((float)$m['akumulasi_penyusutan'], 2, ',', '.') }}</td>
                          <td class="text-end">Rp {{ number_format((float)$m['nilai_buku'], 2, ',', '.') }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection

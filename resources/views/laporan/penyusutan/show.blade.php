@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
  <h3 class="text-white mb-3">Jadwal Penyusutan: {{ $aset->nama ?? 'Aset' }}</h3>
  <div class="mb-3 text-white">
    <div>Harga Perolehan: Rp {{ number_format($aset->acquisition_cost ?? 0,0,',','.') }}</div>
    <div>Nilai Residu: Rp {{ number_format($aset->residual_value ?? 0,0,',','.') }}</div>
    <div>Umur Manfaat: {{ $aset->useful_life_years ?? '-' }} tahun</div>
    <div>Mulai: {{ $aset->depr_start_date ?? '-' }}</div>
  </div>

  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sl" type="button" role="tab">Garis Lurus</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ddb" type="button" role="tab">Saldo Menurun Ganda</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#syd" type="button" role="tab">Sum-of-Years-Digits</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uop" type="button" role="tab">Unit Produksi</button>
    </li>
  </ul>

  <div class="tab-content pt-3">
    <div class="tab-pane fade show active" id="sl" role="tabpanel">
      @include('laporan.penyusutan.table', ['rows' => $straight])
    </div>
    <div class="tab-pane fade" id="ddb" role="tabpanel">
      @include('laporan.penyusutan.table', ['rows' => $ddb])
    </div>
    <div class="tab-pane fade" id="syd" role="tabpanel">
      @include('laporan.penyusutan.table', ['rows' => $syd])
    </div>
    <div class="tab-pane fade" id="uop" role="tabpanel">
      @include('laporan.penyusutan.table', ['rows' => $uop])
    </div>
  </div>
</div>
@endsection

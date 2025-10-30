@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
  <h3 class="text-white mb-3">Laporan Penyusutan Aset</h3>
  <div class="card" style="background:#2c2c3e;border:none;">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Nama Aset</th>
              <th>Harga Perolehan</th>
              <th>Nilai Residu</th>
              <th>Umur (th)</th>
              <th>Tanggal Mulai</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($asets as $a)
              <tr>
                <td>{{ $a->nama ?? '-' }}</td>
                <td>Rp {{ number_format($a->acquisition_cost ?? 0,0,',','.') }}</td>
                <td>Rp {{ number_format($a->residual_value ?? 0,0,',','.') }}</td>
                <td>{{ $a->useful_life_years ?? '-' }}</td>
                <td>{{ $a->depr_start_date ?? '-' }}</td>
                <td>
                  <a href="{{ route('laporan.penyusutan.aset.show', $a->id) }}" class="btn btn-sm btn-primary">Lihat Jadwal</a>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center">Belum ada data aset.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

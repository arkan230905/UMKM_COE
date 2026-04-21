@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Laporan Laba Rugi</h3>
    <div class="d-flex gap-2">
      <form method="get" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label">Periode</label>
          <input type="month" name="periode" class="form-control" value="{{ $periode }}" style="min-width: 180px;">
        </div>
        <div>
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-eye"></i> Tampilkan
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-primary text-white">
      <strong>LAPORAN LABA RUGI</strong>
      <div class="float-end">
        <strong>Periode: {{ \Carbon\Carbon::parse($periode . '-01')->isoFormat('MMMM YYYY') }}</strong>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <!-- PENDAPATAN -->
          <thead class="table-success">
            <tr>
              <th colspan="3" class="text-center">
                <i class="bi bi-graph-up me-2"></i>PENDAPATAN
              </th>
            </tr>
            <tr class="table-light">
              <th style="width:15%">Kode Akun</th>
              <th style="width:55%">Nama Akun</th>
              <th class="text-end" style="width:30%">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendapatan as $coa)
              @php
                $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
              @endphp
              <tr>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Tidak ada data pendapatan</td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="table-success">
            <tr>
              <th colspan="2" class="text-end">TOTAL PENDAPATAN</th>
              <th class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
            </tr>
          </tfoot>

          <!-- BEBAN -->
          <thead class="table-danger">
            <tr>
              <th colspan="3" class="text-center">
                <i class="bi bi-graph-down me-2"></i>BEBAN
              </th>
            </tr>
            <tr class="table-light">
              <th>Kode Akun</th>
              <th>Nama Akun</th>
              <th class="text-end">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @forelse($beban as $coa)
              @php
                $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
              @endphp
              <tr>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>
                  {{ $coa->nama_akun }}
                  @if(str_contains(strtolower($coa->nama_akun), 'hpp') || str_contains(strtolower($coa->nama_akun), 'harga pokok'))
                    <small class="badge bg-warning text-dark ms-2">HPP</small>
                  @endif
                </td>
                <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Tidak ada data beban</td>
              </tr>
            @endforelse
          </tbody>
          <tfoot class="table-danger">
            <tr>
              <th colspan="2" class="text-end">TOTAL BEBAN</th>
              <th class="text-end">Rp {{ number_format($totalBeban, 0, ',', '.') }}</th>
            </tr>
          </tfoot>

          <!-- LABA/RUGI -->
          <tfoot class="table-dark">
            <tr>
              <th colspan="2" class="text-end">
                @if($labaRugi >= 0)
                  <i class="bi bi-emoji-smile me-2"></i>LABA BERSIH
                @else
                  <i class="bi bi-emoji-frown me-2"></i>RUGI BERSIH
                @endif
              </th>
              <th class="text-end {{ $labaRugi >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="row">
      <div class="col-md-6">
        <div class="alert alert-info">
          <strong><i class="bi bi-info-circle"></i> Informasi Laba Rugi:</strong>
          <ul class="mb-0 mt-2">
            <li>Laporan laba rugi menunjukkan kinerja keuangan perusahaan</li>
            <li>Laba Bersih = Total Pendapatan - Total Beban</li>
            <li>Periode: {{ \Carbon\Carbon::parse($periode . '-01')->isoFormat('MMMM YYYY') }}</li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert {{ $labaRugi >= 0 ? 'alert-success' : 'alert-warning' }}">
          <strong><i class="bi bi-calculator"></i> Ringkasan:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>Total Pendapatan:</strong> Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</li>
            <li><strong>Total Beban:</strong> Rp {{ number_format($totalBeban, 0, ',', '.') }}</li>
            <li><strong>{{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }} Bersih:</strong> Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

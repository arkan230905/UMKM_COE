@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Neraca Saldo</h3>
    <div class="d-flex gap-2">
      <form method="get" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label">Pilih Periode</label>
          <select name="period_id" class="form-select" onchange="this.form.submit()" style="min-width: 200px;">
            @foreach($periods as $p)
              <option value="{{ $p->id }}" {{ $periode && $periode->id == $p->id ? 'selected' : '' }}>
                {{ \Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY') }}
                {{ $p->is_closed ? '✓' : '' }}
              </option>
            @endforeach
          </select>
        </div>
      </form>
      
      @if($periode && !$periode->is_closed)
        <form method="post" action="{{ route('coa-period.post', $periode->id) }}" onsubmit="return confirm('Yakin ingin menutup periode ini dan posting saldo ke periode berikutnya?')">
          @csrf
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-success d-block">
            <i class="bi bi-check-circle"></i> Post Saldo Akhir
          </button>
        </form>
      @else
        <form method="post" action="{{ route('coa-period.reopen', $periode->id) }}" onsubmit="return confirm('Yakin ingin membuka kembali periode ini?')">
          @csrf
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-warning d-block">
            <i class="bi bi-unlock"></i> Buka Periode
          </button>
        </form>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-header bg-primary text-white">
      <strong>Periode: {{ \Carbon\Carbon::parse($periode->periode.'-01')->isoFormat('MMMM YYYY') }}</strong>
      @if($periode->is_closed)
        <span class="badge bg-success float-end">Periode Ditutup</span>
      @else
        <span class="badge bg-warning float-end">Periode Aktif</span>
      @endif
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Kode Akun</th>
              <th>Nama Akun</th>
              <th class="text-end">Saldo Awal</th>
              <th class="text-end">Debit</th>
              <th class="text-end">Kredit</th>
              <th class="text-end">Saldo Akhir</th>
            </tr>
          </thead>
          <tbody>
            @php 
              $totalSaldoAwal = 0;
              $totalDebit = 0; 
              $totalKredit = 0; 
              $totalSaldoAkhir = 0;
            @endphp
            @foreach($coas as $coa)
              @php 
                $data = $totals[$coa->kode_akun] ?? ['saldo_awal' => 0, 'debit' => 0, 'kredit' => 0, 'saldo_akhir' => 0];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                // Untuk total, hitung berdasarkan saldo normal
                if ($coa->saldo_normal === 'debit') {
                  $totalSaldoAwal += $saldoAwal;
                  $totalSaldoAkhir += $saldoAkhir;
                } else {
                  $totalSaldoAwal -= $saldoAwal;
                  $totalSaldoAkhir -= $saldoAkhir;
                }
                
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td>{{ $coa->kode_akun }}</td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">{{ $saldoAwal != 0 ? 'Rp '.number_format(abs($saldoAwal), 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-' }}</td>
                <td class="text-end">{{ $saldoAkhir != 0 ? 'Rp '.number_format(abs($saldoAkhir), 0, ',', '.') : '-' }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="table-secondary">
            <tr>
              <th colspan="2" class="text-end">Total</th>
              <th class="text-end">Rp {{ number_format(abs($totalSaldoAwal), 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalKredit, 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format(abs($totalSaldoAkhir), 0, ',', '.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="alert alert-info">
      <strong><i class="bi bi-info-circle"></i> Informasi:</strong>
      <ul class="mb-0 mt-2">
        <li>Pilih periode untuk melihat neraca saldo bulan tersebut</li>
        <li>Saldo awal periode berasal dari saldo akhir periode sebelumnya</li>
        <li>Klik "Post Saldo Akhir" untuk menutup periode dan memindahkan saldo akhir ke saldo awal periode berikutnya</li>
        <li>Periode yang sudah ditutup dapat dibuka kembali jika periode berikutnya belum ditutup</li>
      </ul>
    </div>
  </div>
</div>
@endsection

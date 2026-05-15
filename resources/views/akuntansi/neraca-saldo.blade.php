@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Neraca Saldo</h3>
      <small class="text-muted">Data diambil langsung dari Buku Besar (Journal Lines)</small>
    </div>
    <div class="d-flex gap-2 align-items-end">
      <form method="get" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label small">Bulan</label>
          <select name="bulan" class="form-select form-select-sm" style="min-width: 120px;">
            <option value="01" {{ request('bulan', date('m')) == '01' ? 'selected' : '' }}>Januari</option>
            <option value="02" {{ request('bulan', date('m')) == '02' ? 'selected' : '' }}>Februari</option>
            <option value="03" {{ request('bulan', date('m')) == '03' ? 'selected' : '' }}>Maret</option>
            <option value="04" {{ request('bulan', date('m')) == '04' ? 'selected' : '' }}>April</option>
            <option value="05" {{ request('bulan', date('m')) == '05' ? 'selected' : '' }}>Mei</option>
            <option value="06" {{ request('bulan', date('m')) == '06' ? 'selected' : '' }}>Juni</option>
            <option value="07" {{ request('bulan', date('m')) == '07' ? 'selected' : '' }}>Juli</option>
            <option value="08" {{ request('bulan', date('m')) == '08' ? 'selected' : '' }}>Agustus</option>
            <option value="09" {{ request('bulan', date('m')) == '09' ? 'selected' : '' }}>September</option>
            <option value="10" {{ request('bulan', date('m')) == '10' ? 'selected' : '' }}>Oktober</option>
            <option value="11" {{ request('bulan', date('m')) == '11' ? 'selected' : '' }}>November</option>
            <option value="12" {{ request('bulan', date('m')) == '12' ? 'selected' : '' }}>Desember</option>
          </select>
        </div>
        <div>
          <label class="form-label small">Tahun</label>
          <input type="number" name="tahun" class="form-control form-control-sm" value="{{ request('tahun', date('Y')) }}" style="min-width: 90px;" min="2020" max="2030">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
      </form>
      <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">Refresh</button>
      <a href="{{ route('akuntansi.neraca-saldo.pdf', ['bulan' => request('bulan', date('m')), 'tahun' => request('tahun', date('Y'))]) }}" class="btn btn-danger btn-sm" target="_blank">
        <i class="bi bi-file-pdf"></i> Export PDF
      </a>
      <button type="button" class="btn btn-success btn-sm" onclick="alert('Fitur Posting Saldo akan segera hadir!')">
        <i class="bi bi-check-circle"></i> Posting Saldo
      </button>
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

  <div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
      <div class="text-center py-2">
        <h4 class="mb-1 fw-bold">PT MANUFAKTUR COE</h4>
        <p class="mb-1">Laporan Keuangan {{ \Carbon\Carbon::parse(request('tahun', date('Y')) . '-' . request('bulan', date('m')) . '-01')->isoFormat('MMMM YYYY') }}</p>
        <h5 class="mb-0 fw-bold">Neraca Saldo</h5>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width:5%">No</th>
              <th style="width:40%">AKUN</th>
              <th class="text-end" style="width:27.5%">DEBIT (RP)</th>
              <th class="text-end" style="width:27.5%">KREDIT (RP)</th>
            </tr>
          </thead>
          <tbody>
            @php
              $totalDebit = 0;
              $totalKredit = 0;
              $rowNumber = 1;
            @endphp
            
            @forelse($coas as $coa)
              @php
                $data = $totals[$coa->kode_akun] ?? ['saldo_debit' => 0, 'saldo_kredit' => 0];
                $debit = $data['saldo_debit'] ?? 0;
                $kredit = $data['saldo_kredit'] ?? 0;
                
                // Skip if both debit and kredit are 0
                if ($debit == 0 && $kredit == 0) {
                    continue;
                }
                
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td>
                  <strong>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</strong><br>
                  <small class="text-muted">{{ $coa->tipe_akun }}</small>
                </td>
                <td class="text-end">{{ $debit > 0 ? number_format($debit, 0, ',', '.') : '0' }}</td>
                <td class="text-end">{{ $kredit > 0 ? number_format($kredit, 0, ',', '.') : '0' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center py-4">
                  <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                  <p class="text-muted mt-2">Tidak ada data untuk periode ini</p>
                </td>
              </tr>
            @endforelse
          </tbody>
          @if($coas->count() > 0)
          <tfoot class="table-dark">
            <tr>
              <th colspan="2" class="text-end">TOTAL</th>
              <th class="text-end">{{ number_format($totalDebit, 0, ',', '.') }}</th>
              <th class="text-end">{{ number_format($totalKredit, 0, ',', '.') }}</th>
            </tr>
            <tr>
              <th colspan="2" class="text-end">BALANCE CHECK (Saldo Debit vs Kredit):</th>
              <th class="text-end">Rp {{ number_format($totalDebit, 2, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalKredit, 2, ',', '.') }}</th>
            </tr>
            @php
              $balanceDiff = abs($totalDebit - $totalKredit);
              $isBalanced = $balanceDiff < 0.01;
            @endphp
            <tr>
              <th colspan="2" class="text-end">STATUS KESEIMBANGAN:</th>
              <th colspan="2" class="text-end {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                @if($isBalanced)
                  <i class="bi bi-check-circle-fill"></i> SEIMBANG ✓
                @else
                  <i class="bi bi-exclamation-triangle-fill"></i> SELISIH: Rp {{ number_format($balanceDiff, 2, ',', '.') }}
                @endif
              </th>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

  @if($coas->count() > 0)
  <div class="row mt-3">
    <div class="col-md-6">
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <strong><i class="bi bi-info-circle"></i> Informasi Neraca Saldo</strong>
        </div>
        <div class="card-body">
          <ul class="mb-0">
            <li><strong>Sumber Data:</strong> Buku Besar (journal_lines)</li>
            <li><strong>Perhitungan:</strong> Saldo = Saldo Awal + Mutasi periode</li>
            <li><strong>Prinsip:</strong> Total Debit harus sama dengan Total Kredit</li>
            <li><strong>Periode:</strong> {{ \Carbon\Carbon::parse(request('tahun', date('Y')) . '-' . request('bulan', date('m')) . '-01')->isoFormat('MMMM YYYY') }}</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-success">
        <div class="card-header bg-success text-white">
          <strong><i class="bi bi-calculator"></i> Ringkasan</strong>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tr>
              <td><strong>Total Debit:</strong></td>
              <td class="text-end"><strong>Rp {{ number_format($totalDebit, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
              <td><strong>Total Kredit:</strong></td>
              <td class="text-end"><strong>Rp {{ number_format($totalKredit, 0, ',', '.') }}</strong></td>
            </tr>
            <tr class="table-{{ $isBalanced ? 'success' : 'danger' }}">
              <td><strong>Status:</strong></td>
              <td class="text-end"><strong>{{ $isBalanced ? 'SEIMBANG ✓' : 'TIDAK SEIMBANG ✗' }}</strong></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

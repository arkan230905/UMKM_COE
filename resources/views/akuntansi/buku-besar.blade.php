@extends('layouts.app')

@section('title', 'Buku Besar')

@section('content')
<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0">
        <i class="bi bi-book me-2"></i>
        Buku Besar
      </h1>
      <p class="text-muted mb-0">Laporan transaksi per akun dari jurnal umum</p>
    </div>
    <div class="col-md-6 text-end">
      <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('akuntansi.buku-besar.export-excel', ['from' => $from, 'to' => $to]) }}" class="btn btn-success btn-sm">
          <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
        </a>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Cetak
        </button>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold">Pilih Akun</label>
          <select name="account_code" class="form-select" onchange="this.form.submit()">
            <option value="">-- Pilih Akun --</option>
            @foreach($coas as $coa)
              <option value="{{ $coa->kode_akun }}" {{ ($accountCode==$coa->kode_akun)?'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Bulan</label>
          <select name="month" class="form-select">
            <option value="">-- Pilih Bulan --</option>
            <option value="01" {{ ($month ?? '') === '01' ? 'selected' : '' }}>Januari</option>
            <option value="02" {{ ($month ?? '') === '02' ? 'selected' : '' }}>Februari</option>
            <option value="03" {{ ($month ?? '') === '03' ? 'selected' : '' }}>Maret</option>
            <option value="04" {{ ($month ?? '') === '04' ? 'selected' : '' }}>April</option>
            <option value="05" {{ ($month ?? '') === '05' ? 'selected' : '' }}>Mei</option>
            <option value="06" {{ ($month ?? '') === '06' ? 'selected' : '' }}>Juni</option>
            <option value="07" {{ ($month ?? '') === '07' ? 'selected' : '' }}>Juli</option>
            <option value="08" {{ ($month ?? '') === '08' ? 'selected' : '' }}>Agustus</option>
            <option value="09" {{ ($month ?? '') === '09' ? 'selected' : '' }}>September</option>
            <option value="10" {{ ($month ?? '') === '10' ? 'selected' : '' }}>Oktober</option>
            <option value="11" {{ ($month ?? '') === '11' ? 'selected' : '' }}>November</option>
            <option value="12" {{ ($month ?? '') === '12' ? 'selected' : '' }}>Desember</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Tahun</label>
          <input type="number" name="year" value="{{ $year ?? date('Y') }}" class="form-control" min="2020" max="{{ date('Y') + 5 }}">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i> Tampilkan
          </button>
        </div>
        <div class="col-md-3">
          <a href="{{ route('akuntansi.buku-besar') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Card -->
  @if($accountCode)
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-1">Informasi Akun</h5>
              @if($month && $year)
                <small class="text-warning">
                  <i class="bi bi-funnel me-1"></i>
                  Filter aktif: {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                </small>
              @else
                <small class="text-success">
                  <i class="bi bi-check-circle me-1"></i>
                  Menampilkan semua transaksi
                </small>
              @endif
            </div>
            <div class="text-end">
              <h6 class="mb-0">Saldo Awal</h6>
              <h4 class="text-primary">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Period Summary Card -->
  @if($month && $year)
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card border-left border-success border-4">
        <div class="card-body">
          <div class="row text-center">
            <div class="col-md-3">
              <h6 class="mb-1 text-muted">Total Debit (Periode Ini)</h6>
              <h5 class="text-primary">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h5>
            </div>
            <div class="col-md-3">
              <h6 class="mb-1 text-muted">Total Kredit (Periode Ini)</h6>
              <h5 class="text-danger">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h5>
            </div>
            <div class="col-md-3">
              <h6 class="mb-1 text-muted">Saldo Akhir</h6>
              <h5 class="text-success">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</h5>
            </div>
            <div class="col-md-3">
              <h6 class="mb-1 text-muted">Perhitungan</h6>
              <small class="text-muted">
                Saldo Awal + Debit - Kredit<br>
                = {{ number_format($saldoAwal, 0, ',', '.') }} + {{ number_format($totalDebit, 0, ',', '.') }} - {{ number_format($totalKredit, 0, ',', '.') }}<br>
                = {{ number_format($saldoAkhir, 0, ',', '.') }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  @endif

  <!-- Buku Besar Table -->
  @if($accountCode)
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light sticky-top">
            <tr>
              <th class="border-end" style="width:15%">Tanggal</th>
              <th class="border-end" style="width:45%">Deskripsi</th>
              <th class="text-end border-end" style="width:15%">Debit</th>
              <th class="text-end border-end" style="width:15%">Kredit</th>
              <th class="text-end" style="width:10%">Saldo</th>
            </tr>
          </thead>
          <tbody>
            @php 
              $saldo = (float)$saldoAwal;
              $selectedAccountCode = $accountCode; // Gunakan accountCode dari controller
            @endphp
            @foreach($lines as $e)
              @foreach($e->lines as $i => $l)
                @php 
                  $isAccountSelected = ($l->coa->kode_akun == $selectedAccountCode);
                  if ($isAccountSelected) {
                    $saldo += ((float)$l->debit - (float)$l->credit);
                  }
                @endphp
                <tr class="{{ $i % 2 === 0 ? 'bg-light' : '' }}">
                  @if($i===0)
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle">
                      {{ \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y') }}
                    </td>
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle">
                      <strong>{{ $e->memo }}</strong>
                    </td>
                  @endif
                  <td class="text-end">
                    @if($isAccountSelected && $l->debit > 0)
                      Rp {{ number_format($l->debit,0,',','.') }}
                    @else
                      -
                    @endif
                  </td>
                  <td class="text-end">
                    @if($isAccountSelected && $l->credit > 0)
                      Rp {{ number_format($l->credit,0,',','.') }}
                    @else
                      -
                    @endif
                  </td>
                  @if($isAccountSelected)
                    <td class="text-end {{ $saldo >= 0 ? 'text-primary' : 'text-danger' }}">
                      Rp {{ number_format($saldo,0,',','.') }}
                    </td>
                  @else
                    <td></td>
                  @endif
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @else
    <div class="card shadow-sm">
      <div class="card-body text-center py-5">
        <div class="text-muted">
          <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
          <h5>Pilih Akun Terlebih Dahulu</h5>
          <p class="mb-0">Silakan pilih akun dari dropdown untuk melihat buku besar</p>
        </div>
      </div>
    </div>
  @endif
</div>

<style>
  .no-print {
    display: none !important;
  }
  
  @media print {
    .no-print { 
      display: none !important; 
    }
    .table th, .table td { 
      padding: .5rem .5rem !important; 
      font-size: 12px !important;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #dee2e6 !important;
    }
    .badge {
      font-size: 8px !important;
    }
    body { 
      -webkit-print-color-adjust: exact; 
      print-color-adjust: exact; 
    }
  }
</style>

@endsection

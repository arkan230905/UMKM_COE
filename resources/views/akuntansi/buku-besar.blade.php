@extends('layouts.app')

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
          <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
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
          <select name="account_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- Pilih Akun --</option>
            @foreach($coas as $coa)
              <option value="{{ $coa->id }}" {{ ($accountId==$coa->id)?'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
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
  @if($accountId)
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-1">Informasi Akun</h5>
              @php
                $selectedCoa = $coas->where('id', $accountId)->first();
                $tipeAkun = $selectedCoa->tipe_akun ?? '';
                $tipeAkunLabel = match($tipeAkun) {
                  'Asset' => 'Aset',
                  'Liability' => 'Kewajiban',
                  'Equity' => 'Modal',
                  'Revenue' => 'Pendapatan',
                  'Expense' => 'Beban',
                  default => 'Lainnya'
                };
                
                $badgeColor = match($tipeAkun) {
                  'Asset' => 'primary',
                  'Liability' => 'warning',
                  'Equity' => 'info',
                  'Revenue' => 'success',
                  'Expense' => 'danger',
                  default => 'secondary'
                };
                
                // Debug info
                $account = \App\Models\Account::where('code', $selectedCoa->kode_akun)->first();
              @endphp
              <span class="badge bg-{{ $badgeColor }} text-white">
                {{ $tipeAkunLabel }}
              </span>
              <br>
              <small class="text-muted">
                COA ID: {{ $selectedCoa->id }}, Kode: {{ $selectedCoa->kode_akun }}<br>
                Account ID: {{ $account ? $account->id : 'NOT FOUND' }}, Code: {{ $account ? $account->code : 'NOT FOUND' }}<br>
                Lines Count: {{ $lines->count() }}<br>
                Saldo Awal: {{ $saldoAwal }}<br>
                Period: {{ $month ? $month . '/' . $year : 'NOT SELECTED' }}<br>
                @if($from && $to)
                  From: {{ $from }}, To: {{ $to }}<br>
                @endif
                @if($lines->count() > 0)
                  First Line Date: {{ $lines->first()->entry->tanggal ?? 'N/A' }}<br>
                  Last Line Date: {{ $lines->last()->entry->tanggal ?? 'N/A' }}
                @endif
              </small>
            </div>
            <div class="text-end">
              <h6 class="mb-0">Saldo Awal</h6>
              <h4 class="text-primary">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Buku Besar Table -->
  @if($accountId)
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light sticky-top">
            <tr>
              <th class="border-end" style="width:10%">Tanggal</th>
              <th class="border-end" style="width:12%">Ref</th>
              <th class="border-end" style="width:25%">Deskripsi</th>
              <th class="text-end border-end" style="width:12%">Debit</th>
              <th class="text-end border-end" style="width:12%">Kredit</th>
              <th class="text-end" style="width:12%">Saldo</th>
              <th class="text-center" style="width:5%">D/K</th>
            </tr>
          </thead>
          <tbody>
            @php $saldo = (float)$saldoAwal; @endphp
            @foreach($lines as $l)
              @php 
                $saldo += ((float)$l->debit - (float)$l->credit); 
                $tanggal = $l->entry->tanggal ?? '';
                $refType = $l->entry->ref_type ?? '';
                $refId = $l->entry->ref_id ?? '';
                $memo = $l->entry->memo ?? '';
                $debit = $l->debit ?? 0;
                $credit = $l->credit ?? 0;
              @endphp
              <tr class="{{ $loop->index % 2 === 0 ? 'bg-light' : '' }}">
                <td>
                  <div class="text-center">
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($tanggal)->format('H:i') }}</small>
                  </div>
                </td>
                <td>
                  <div>
                    @php
                      $badgeColor = match($refType) {
                        'purchase' => 'danger',
                        'sale' => 'success',
                        'production' => 'warning',
                        'saldo_awal' => 'info',
                        default => 'secondary'
                      };
                    @endphp
                    <span class="badge bg-{{ $badgeColor }} text-white">
                      {{ $refType }}
                    </span>
                    <div class="small text-muted">#{{ $refId }}</div>
                  </div>
                </td>
                <td>
                  <div class="text-truncate" style="max-width: 200px;" title="{{ $memo }}">
                    {{ $memo }}
                  </div>
                </td>
                <td class="text-end">
                  @if($debit > 0)
                    <span class="text-primary fw-semibold">Rp {{ number_format($debit,0,',','.') }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-end">
                  @if($credit > 0)
                    <span class="text-success fw-semibold">Rp {{ number_format($credit,0,',','.') }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-end">
                  <span class="{{ $saldo >= 0 ? 'text-primary' : 'text-danger' }} fw-semibold">
                    Rp {{ number_format($saldo,0,',','.') }}
                  </span>
                </td>
                <td class="text-center">
                  @if($debit > 0)
                    <span class="badge bg-primary rounded-circle p-1" style="font-size: 8px;">D</span>
                  @else
                    <span class="badge bg-success rounded-circle p-1" style="font-size: 8px;">K</span>
                  @endif
                </td>
              </tr>
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

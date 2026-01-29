@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0">
        <i class="bi bi-journal-text me-2"></i>
        Jurnal Umum
      </h1>
      <p class="text-muted mb-0">Catatan transaksi keuangan perusahaan</p>
    </div>
    <div class="col-md-6 text-end">
      <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('akuntansi.jurnal-umum.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm">
          <i class="bi bi-file-pdf me-1"></i> PDF
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
        <div class="col-md-2">
          <label class="form-label fw-semibold">Dari Tanggal</label>
          <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Sampai Tanggal</label>
          <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Tipe Transaksi</label>
          <select name="ref_type" class="form-select">
            <option value="">Semua</option>
            <option value="purchase" {{ $refType === 'purchase' ? 'selected' : '' }}>Pembelian</option>
            <option value="sale" {{ $refType === 'sale' ? 'selected' : '' }}>Penjualan</option>
            <option value="production" {{ $refType === 'production' ? 'selected' : '' }}>Produksi</option>
            <option value="saldo_awal" {{ $refType === 'saldo_awal' ? 'selected' : '' }}>Saldo Awal</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Filter Akun</label>
          <select name="account_code" class="form-select">
            <option value="">Semua Akun</option>
            <option value="5101" {{ request('account_code') === '5101' ? 'selected' : '' }}>HPP (5101)</option>
            <option value="1107" {{ request('account_code') === '1107' ? 'selected' : '' }}>Persediaan Barang Jadi (1107)</option>
            <option value="4101" {{ request('account_code') === '4101' ? 'selected' : '' }}>Penjualan (4101)</option>
            <option value="1101" {{ request('account_code') === '1101' ? 'selected' : '' }}>Kas (1101)</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">ID Transaksi</label>
          <input type="number" name="ref_id" value="{{ $refId ?? '' }}" class="form-control" placeholder="ID">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i> Filter
          </button>
        </div>
        <div class="col-md-2">
          <a href="{{ route('akuntansi.jurnal-umum') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  @if($entries->count() > 0)
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Total Debit</h6>
              <h4 class="mb-0 text-primary">Rp {{ number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.') }}</h4>
            </div>
            <div class="text-primary">
              <i class="bi bi-arrow-up-circle fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left border-success border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Total Kredit</h6>
              <h4 class="mb-0 text-success">Rp {{ number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.') }}</h4>
            </div>
            <div class="text-success">
              <i class="bi bi-arrow-down-circle fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left border-info border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Balance</h6>
              <h4 class="mb-0 text-info">Rp {{ number_format($entries->flatMap->lines->sum('debit') - $entries->flatMap->lines->sum('credit'), 0, ',', '.') }}</h4>
            </div>
            <div class="text-info">
              <i class="bi bi-balance-scale fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Journal Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="border: 2px solid #dee2e6;">
          <thead class="table-light sticky-top">
            <tr>
              <th class="border-end" style="width:8%; border-bottom: 2px solid #dee2e6;">Tanggal</th>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Ref</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Deskripsi</th>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Kode Akun</th>
              <th class="border-end" style="width:20%; border-bottom: 2px solid #dee2e6;">Nama Akun</th>
              <th class="text-end border-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Debit</th>
              <th class="text-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Kredit</th>
              <th class="text-center" style="width:5%; border-bottom: 2px solid #dee2e6;">D/K</th>
            </tr>
          </thead>
          <tbody>
            @forelse($entries as $e)
              @foreach($e->lines as $i => $l)
                <tr class="{{ $i % 2 === 0 ? 'bg-light' : '' }}" style="border-bottom: 1px solid #dee2e6;">
                  @if($i===0)
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div class="text-center">
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($e->tanggal)->format('H:i') }}</small>
                      </div>
                    </td>
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div>
                        @php
                          $badgeColor = match($e->ref_type) {
                            'purchase' => 'danger',
                            'sale' => 'success',
                            'production' => 'warning',
                            'saldo_awal' => 'info',
                            default => 'secondary'
                          };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }} text-white">
                          {{ $e->ref_type }}
                        </span>
                        <div class="small text-muted">#{{ $e->ref_id }}</div>
                      </div>
                    </td>
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div class="text-truncate" style="max-width: 150px;" title="{{ $e->memo }}">
                        {{ $e->memo }}
                        
                        @if($e->ref_type === 'sale')
                          @php
                            $hppTotal = 0;
                            $penjualanTotal = 0;
                            foreach($e->lines as $line) {
                              if($line->account->code === '5101') {
                                $hppTotal = $line->debit;
                              }
                              if($line->account->code === '1101') {
                                $penjualanTotal = $line->debit;
                              }
                            }
                            $margin = $penjualanTotal - $hppTotal;
                            $marginPercent = $penjualanTotal > 0 ? ($margin / $penjualanTotal * 100) : 0;
                          @endphp
                          
                          <div class="mt-2">
                            <small class="text-muted d-block">Detail HPP:</small>
                            <div class="d-flex gap-2 flex-wrap">
                              <small class="badge bg-light text-dark">
                                <i class="bi bi-cash-stack me-1"></i>HPP: Rp {{ number_format($hppTotal,0,',','.') }}
                              </small>
                              <small class="badge bg-light text-dark">
                                <i class="bi bi-graph-up {{ $margin >= 0 ? 'text-success' : 'text-danger' }} me-1"></i>Margin: {{ $margin >= 0 ? '+' : '' }}{{ number_format($marginPercent,1,',','.') }}%
                              </small>
                            </div>
                          </div>
                        @endif
                      </div>
                    </td>
                  @endif
                  <td class="align-middle" style="border-right: 1px solid #dee2e6;">
                    <code class="text-primary">{{ $l->account->code ?? '-' }}</code>
                  </td>
                  <td class="align-middle" style="border-right: 1px solid #dee2e6;">
                    <div>
                      <div class="fw-semibold">{{ $l->account->name ?? 'Akun tidak ditemukan' }}</div>
                      @if($l->account)
                        <small class="text-muted">{{ $l->account->type ?? '' }}</small>
                        
                        @if($e->ref_type === 'sale' && $l->account->code === '5101')
                          <div class="mt-1">
                            <small class="badge bg-warning text-dark">
                              <i class="bi bi-info-circle me-1"></i>HPP Penjualan
                            </small>
                          </div>
                        @endif
                        
                        @if($e->ref_type === 'sale' && $l->account->code === '1107')
                          <div class="mt-1">
                            <small class="badge bg-info text-dark">
                              <i class="bi bi-box-seam me-1"></i>Persediaan Keluar
                            </small>
                          </div>
                        @endif
                      @endif
                    </div>
                  </td>
                  <td class="align-middle text-end" style="border-right: 1px solid #dee2e6;">
                    @if($l->debit > 0)
                      <span class="text-primary fw-semibold">Rp {{ number_format($l->debit,0,',','.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="align-middle text-end" style="border-right: 1px solid #dee2e6;">
                    @if($l->credit > 0)
                      <span class="text-success fw-semibold">Rp {{ number_format($l->credit,0,',','.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    @if($l->debit > 0)
                      <span class="badge bg-primary text-white">D</span>
                    @else
                      <span class="badge bg-success text-white">K</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            @empty
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <h5>Tidak ada data jurnal</h5>
                    <p class="mb-0">Silakan pilih filter yang berbeda atau tambahkan data jurnal terlebih dahulu</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
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

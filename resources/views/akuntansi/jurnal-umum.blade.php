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
        <a href="{{ route('akuntansi.jurnal-umum.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm position-relative overflow-hidden group">
          <span class="absolute inset-0 bg-gradient-to-r from-red-600 to-red-500 opacity-75 group-hover:opacity-100 transition-opacity duration-300"></span>
          <span class="relative flex items-center">
            <i class="bi bi-file-pdf me-2"></i>
            <span class="font-semibold">Export PDF</span>
          </span>
          <div class="absolute inset-0 flex items-center justify-center">
            <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3v6m3-6v-6m-6 18v6m6-6h6m-6 6v6M9 9h6m-6 0v6"></path>
            </svg>
          </div>
        </a>
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
            <option value="production_material" {{ $refType === 'production_material' ? 'selected' : '' }}>Produksi - Material</option>
            <option value="production_labor_overhead" {{ $refType === 'production_labor_overhead' ? 'selected' : '' }}>Produksi - BTKL & BOP</option>
            <option value="production_finish" {{ $refType === 'production_finish' ? 'selected' : '' }}>Produksi - Barang Jadi</option>
            <option value="saldo_awal" {{ $refType === 'saldo_awal' ? 'selected' : '' }}>Saldo Awal</option>
            <option value="pembayaran_beban" {{ $refType === 'pembayaran_beban' ? 'selected' : '' }}>Pembayaran Beban</option>
            <option value="penggajian" {{ $refType === 'penggajian' ? 'selected' : '' }}>Penggajian</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Filter Akun</label>
          <select name="account_code" class="form-select">
            <option value="">Semua Akun</option>
            @php
              $coas = \App\Models\Coa::orderBy('kode_akun')->get();
            @endphp
            @foreach($coas as $coa)
              <option value="{{ $coa->kode_akun }}" {{ request('account_code') === $coa->kode_akun ? 'selected' : '' }}>{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</option>
            @endforeach
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
    <div class="col-md-12">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-6 text-center">
              <i class="bi bi-arrow-up-circle fs-2 text-primary"></i>
              <h6 class="text-muted mb-2">Total Debit</h6>
              <h4 class="mb-0 text-primary">Rp {{ number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.') }}</h4>
            </div>
            <div class="col-6 text-center">
              <i class="bi bi-arrow-down-circle fs-2 text-success"></i>
              <h6 class="text-muted mb-2">Total Kredit</h6>
              <h4 class="mb-0 text-success">Rp {{ number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.') }}</h4>
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
        <table class="table table-hover mb-0" style="border: 2px solid #dee2e6; min-width: 1400px;">
          <thead class="table-light sticky-top">
            <tr>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Tanggal</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Deskripsi</th>
              <th class="border-end" style="width:8%; border-bottom: 2px solid #dee2e6;">Kode Akun</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Nama Akun</th>
              <th class="border-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Keterangan</th>
              <th class="text-end border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Debit</th>
              <th class="text-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Kredit</th>
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
                      </div>
                    </td>
                    <td rowspan="{{ $e->lines->count() }}" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div>
                        {{ $e->memo }}
                      </div>
                    </td>
                  @endif
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-semibold">{{ $l->coa->kode_akun }}</div>
                  </td>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-semibold">
                      {{ $l->coa->nama_akun }}
                    </div>
                    @if($l->coa->tipe_akun)
                      <div class="small text-muted">{{ $l->coa->tipe_akun }}</div>
                    @endif
                  </td>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="text-muted small">
                      {{ $l->memo ?? '-' }}
                    </div>
                  </td>
                  <td class="align-middle text-end" style="border-right: 2px solid #dee2e6;">
                    @if($l->debit > 0)
                      <span class="text-primary fw-semibold">Rp {{ number_format($l->debit,0,',','.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="align-middle text-end">
                    @if($l->credit > 0)
                      <span class="text-success fw-semibold">Rp {{ number_format($l->credit,0,',','.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">
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
  
  /* Ensure table cells don't truncate text */
  .table td, .table th {
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
  }
  
  /* Remove any text truncation */
  .text-truncate {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
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
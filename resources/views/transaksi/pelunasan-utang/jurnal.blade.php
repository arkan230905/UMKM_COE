@extends('layouts.app')

@section('title', 'Jurnal Pelunasan Utang - ' . $pelunasan->kode_transaksi)

@section('content')
<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0">
        <i class="bi bi-journal-text me-2"></i>
        Jurnal Pelunasan Utang
      </h1>
      <p class="text-muted mb-0">{{ $pelunasan->kode_transaksi }}</p>
    </div>
    <div class="col-md-6 text-end">
      <a href="{{ route('transaksi.pelunasan-utang.index', ['tab' => 'pelunasan']) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </div>

  <!-- Pelunasan Info -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <strong>Kode Transaksi:</strong><br>
          {{ $pelunasan->kode_transaksi }}
        </div>
        <div class="col-md-4">
          <strong>Tanggal Pelunasan:</strong><br>
          {{ \Carbon\Carbon::parse($pelunasan->tanggal)->format('d-m-Y') }}
        </div>
        <div class="col-md-4">
          <strong>Jumlah Pembayaran:</strong><br>
          Rp {{ number_format($pelunasan->jumlah, 0, ',', '.') }}
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-4">
          <strong>Pembelian:</strong><br>
          {{ $pelunasan->pembelian->nomor_pembelian ?? '-' }}
        </div>
        <div class="col-md-4">
          <strong>Vendor:</strong><br>
          {{ $pelunasan->pembelian->vendor->nama_vendor ?? '-' }}
        </div>
        <div class="col-md-4">
          <strong>Keterangan:</strong><br>
          {{ $pelunasan->keterangan ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  @if($entries->count() > 0)
  <!-- Summary Cards -->
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

  <!-- Journal Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-hover mb-0" style="border: 2px solid #dee2e6; min-width: 1400px;">
          <thead class="table-light" style="position: sticky; top: 0; z-index: 2; background-color: #f8f9fa;">
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
            @foreach($entries as $e)
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
                  <td class="text-end align-middle" style="border-right: 2px solid #dee2e6;">
                    @if($l->debit > 0)
                      <span class="fw-bold">Rp {{ number_format($l->debit, 0, ',', '.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-end align-middle">
                    @if($l->credit > 0)
                      <span class="fw-bold">Rp {{ number_format($l->credit, 0, ',', '.') }}</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                </tr>
              @endforeach
              <!-- Spacer row between entries -->
              <tr style="height: 4px; background-color: #dee2e6;">
                <td colspan="7" style="padding: 0;"></td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light" style="border-top: 2px solid #dee2e6;">
            <tr>
              <th colspan="5" class="text-end py-3">Total:</th>
              <th class="text-end py-3">
                Rp {{ number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.') }}
              </th>
              <th class="text-end py-3">
                Rp {{ number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.') }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  @else
  <!-- No Journal Entry -->
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Jurnal belum dibuat</strong> untuk transaksi pelunasan ini.
  </div>
  @endif
</div>
@endsection

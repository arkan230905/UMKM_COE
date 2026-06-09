@extends('layouts.app')

@section('title', 'Jurnal Penyesuaian')

@section('content')
<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0">
        <i class="bi bi-journal-text me-2"></i>
        Jurnal Penyesuaian
      </h1>
      <p class="text-muted mb-0">Jurnal penyusutan aset tetap periode {{ $periode->isoFormat('MMMM YYYY') }}</p>
    </div>
    <div class="col-md-6 text-end">
      <div class="d-flex gap-2 justify-content-end">
        @if($isPosted)
          <span class="badge bg-success px-3 py-2 fs-6">
            <i class="fas fa-check-circle me-1"></i>Sudah Diposting
          </span>
        @else
          <button type="button" id="btnPostingJurnal" class="btn btn-primary btn-sm">
            <i class="fas fa-paper-plane me-1"></i>Posting ke Neraca Saldo
          </button>
          <span class="badge bg-warning text-dark px-3 py-2 fs-6">
            <i class="fas fa-exclamation-triangle me-1"></i>Belum Diposting
          </span>
        @endif
        <a href="{{ route('akuntansi.jurnal-penyesuaian-aset.cetak-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
           class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Export PDF
        </a>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('akuntansi.jurnal-penyesuaian-aset') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold">Bulan</label>
          <select name="bulan" class="form-select">
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
              </option>
            @endfor
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Tahun</label>
          <select name="tahun" class="form-select">
            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
              <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i> Filter
          </button>
        </div>
        <div class="col-md-3">
          <a href="{{ route('akuntansi.jurnal-penyesuaian-aset') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Company Header (sama dengan Neraca Saldo) -->
  <div class="mb-4">
    <div class="p-4 bg-white rounded-3 shadow-sm" style="width: 100%;">
      <div class="text-center">
        <h4 class="fw-bold mb-2">PT MANUFAKTUR COE</h4>
        <p class="text-muted mb-2 small">Laporan Keuangan {{ $periode->isoFormat('MMMM YYYY') }}</p>
        <h5 class="fw-bold text-dark mb-0">Jurnal Penyesuaian</h5>
      </div>
    </div>
  </div>

  <!-- Journal Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      @if(count($jurnalEntries) > 0)
        <div class="table-responsive" style="overflow-x: auto;">
          <table class="table table-hover mb-0" style="border: 2px solid #dee2e6; min-width: 1200px;">
            <thead class="table-light" style="position: sticky; top: 0; z-index: 2; background-color: #f8f9fa;">
              <tr>
                <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Tanggal</th>
                <th class="border-end" style="width:30%; border-bottom: 2px solid #dee2e6;">Keterangan</th>
                <th class="border-end text-center" style="width:10%; border-bottom: 2px solid #dee2e6;">REF</th>
                <th class="text-end border-end" style="width:15%; border-bottom: 2px solid #dee2e6;">Debit</th>
                <th class="text-end" style="width:15%; border-bottom: 2px solid #dee2e6;">Kredit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($jurnalEntries as $index => $entry)
                <!-- Debit Row -->
                <tr class="{{ $index % 2 === 0 ? 'bg-light' : '' }}" style="border-bottom: 1px solid #dee2e6;">
                  <td rowspan="2" class="align-middle text-center" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($entry['tanggal'])->format('d/m/Y') }}</div>
                  </td>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-semibold">{{ $entry['keterangan_debit'] }}</div>
                    <small class="text-muted">{{ $entry['kategori'] }}</small>
                  </td>
                  <td class="align-middle text-center" style="border-right: 2px solid #dee2e6;">
                    <code class="bg-light px-2 py-1 rounded">{{ $entry['ref_debit'] }}</code>
                  </td>
                  <td class="align-middle text-end" style="border-right: 2px solid #dee2e6;">
                    <span class="text-primary fw-semibold">Rp {{ number_format($entry['debit'], 0, ',', '.') }}</span>
                  </td>
                  <td class="align-middle text-end">
                    <span class="text-muted">-</span>
                  </td>
                </tr>
                <!-- Kredit Row -->
                <tr class="{{ $index % 2 === 0 ? 'bg-light' : '' }}" style="border-bottom: 2px solid #dee2e6;">
                  <td class="align-middle ps-4 fst-italic" style="border-right: 2px solid #dee2e6;">
                    {{ $entry['keterangan_kredit'] }}
                  </td>
                  <td class="align-middle text-center" style="border-right: 2px solid #dee2e6;">
                    <code class="bg-light px-2 py-1 rounded">{{ $entry['ref_kredit'] }}</code>
                  </td>
                  <td class="align-middle text-end" style="border-right: 2px solid #dee2e6;">
                    <span class="text-muted">-</span>
                  </td>
                  <td class="align-middle text-end">
                    <span class="text-success fw-semibold">Rp {{ number_format($entry['kredit'], 0, ',', '.') }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light" style="border-top: 3px solid #dee2e6;">
              <tr>
                <th colspan="3" class="text-center border-end" style="border-top: 2px solid #dee2e6;">TOTAL</th>
                <th class="text-end border-end" style="border-top: 2px solid #dee2e6;">
                  <span class="text-primary">Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
                </th>
                <th class="text-end" style="border-top: 2px solid #dee2e6;">
                  <span class="text-success">Rp {{ number_format($totalKredit, 0, ',', '.') }}</span>
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      @else
        <div class="text-center py-5">
          <div class="text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <h5>Tidak ada data penyusutan aset</h5>
            <p class="mb-0">Tidak ada aset aktif dengan data penyusutan untuk periode ini.<br>
            Pastikan aset sudah aktif dan memiliki COA Beban Penyusutan serta Akumulasi Penyusutan.</p>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('btnPostingJurnal')?.addEventListener('click', function() {
    if (!confirm('Apakah Anda yakin ingin memposting jurnal penyesuaian ini ke neraca saldo?\n\nSetelah diposting, jurnal akan masuk ke dalam laporan keuangan.')) {
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
    
    fetch('{{ route("akuntansi.jurnal-penyesuaian-aset.post") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            bulan: '{{ $bulan }}',
            tahun: '{{ $tahun }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Posting ke Neraca Saldo';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat memposting jurnal');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Posting ke Neraca Saldo';
    });
});
</script>
@endpush

<style>
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

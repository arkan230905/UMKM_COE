@extends('layouts.app')

@section('title', 'Jadwal Penyusutan')

@section('content')
<div class="container-fluid">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Jadwal Penyusutan — {{ $asset->nama_asset ?? $asset->nama_aset }}</h5>
      <a href="{{ route('aset.show', $asset->id) }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <strong>Harga Perolehan</strong>
          <div>Rp {{ number_format((float)$asset->harga_perolehan, 2, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <strong>Nilai Sisa</strong>
          <div>Rp {{ number_format((float)$asset->nilai_sisa, 2, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <strong>Umur Ekonomis</strong>
          <div>{{ $asset->umur_ekonomis }} tahun</div>
        </div>
        <div class="col-md-3">
          <strong>Tanggal Perolehan</strong>
          <div>{{ optional($asset->tanggal_perolehan)->format('d M Y') }}</div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>TAHUN</th>
              <th class="text-end">PENYUSUTAN</th>
              <th class="text-end">AKUMULASI</th>
              <th class="text-end">NILAI BUKU</th>
              <th>RINCIAN</th>
            </tr>
          </thead>
          <tbody>
            @forelse($depreciation_schedule as $row)
              <tr>
                <td>{{ $row->tahun }}</td>

                <td class="text-end">
                  Rp {{ number_format((float)$row->beban_penyusutan, 2, ',', '.') }}
                </td>

                <td class="text-end">
                  Rp {{ number_format((float)$row->akumulasi_penyusutan, 2, ',', '.') }}
                </td>

                <td class="text-end">
                  Rp {{ number_format((float)$row->nilai_buku_akhir, 2, ',', '.') }}
                </td>

                <td>
                  <button class="btn btn-sm btn-outline-primary"
                    onclick="showMonthlyDetail(
                      {{ $row->tahun }},
                      {{ (float)$row->beban_penyusutan }},
                      {{ (float)$row->akumulasi_penyusutan }},
                      {{ (float)$row->nilai_buku_akhir + $row->beban_penyusutan }}
                    )">
                    Detail
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada jadwal penyusutan</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>


<!-- =============================== -->
<!--     MODAL DETAIL BULANAN        -->
<!-- =============================== -->
<div class="modal fade" id="monthlyDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Penyusutan Bulanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="monthlyDetailContent">
                <!-- hasil perhitungan akan muncul di sini -->
            </div>

        </div>
    </div>
</div>


@push('scripts')
<script>
function showMonthlyDetail(tahun, penyusutanTahunan, akumulasiAwal, nilaiBukuAwal) {
    const modal = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));
    const content = document.getElementById('monthlyDetailContent');

    const startYear = {{ optional($asset->tanggal_perolehan ?? $asset->tanggal_beli)->year ?? 'null' }};
    const startMonth = {{ optional($asset->tanggal_perolehan ?? $asset->tanggal_beli)->month ?? 'null' }};

    // Selalu tampilkan 12 bulan (Januari - Desember)
    const monthsToShow = 12;

    const monthlyDepreciation = penyusutanTahunan / 12;
    let accumulatedDepreciation = akumulasiAwal;
    let currentBookValue = nilaiBukuAwal;

    const monthNames = [
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    let html = `
        <div class="mb-3">
            <h6>Tahun ${tahun}</h6>
            <p><strong>Penyusutan per tahun:</strong> Rp ${numberFormat(penyusutanTahunan)}</p>
            <p><strong>Penyusutan per bulan:</strong> Rp ${numberFormat(monthlyDepreciation)}</p>
        </div>

        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-end">Beban Penyusutan</th>
                        <th class="text-end">Akumulasi</th>
                        <th class="text-end">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
    `;

    for (let i = 0; i < monthsToShow; i++) {
        accumulatedDepreciation += monthlyDepreciation;
        currentBookValue -= monthlyDepreciation;

        // Selalu mulai dari Januari (index 0) sampai Desember (index 11)
        const monthIndex = i;

        html += `
            <tr>
                <td>${monthNames[monthIndex]} ${tahun}</td>
                <td class="text-end">Rp ${numberFormat(monthlyDepreciation)}</td>
                <td class="text-end">Rp ${numberFormat(accumulatedDepreciation)}</td>
                <td class="text-end">Rp ${numberFormat(currentBookValue)}</td>
            </tr>
        `;
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    content.innerHTML = html;
    modal.show();
}

function numberFormat(num) {
    num = Number(num) || 0;
    return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush

@endsection

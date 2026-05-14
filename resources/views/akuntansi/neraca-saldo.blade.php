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
            <option value="01" {{ request('bulan') == '01' ? 'selected' : '' }}>Januari</option>
            <option value="02" {{ request('bulan') == '02' ? 'selected' : '' }}>Februari</option>
            <option value="03" {{ request('bulan') == '03' ? 'selected' : '' }}>Maret</option>
            <option value="04" {{ request('bulan') == '04' ? 'selected' : '' }}>April</option>
            <option value="05" {{ request('bulan') == '05' ? 'selected' : '' }}>Mei</option>
            <option value="06" {{ request('bulan') == '06' ? 'selected' : '' }}>Juni</option>
            <option value="07" {{ request('bulan') == '07' ? 'selected' : '' }}>Juli</option>
            <option value="08" {{ request('bulan') == '08' ? 'selected' : '' }}>Agustus</option>
            <option value="09" {{ request('bulan') == '09' ? 'selected' : '' }}>September</option>
            <option value="10" {{ request('bulan') == '10' ? 'selected' : '' }}>Oktober</option>
            <option value="11" {{ request('bulan') == '11' ? 'selected' : '' }}>November</option>
            <option value="12" {{ request('bulan') == '12' ? 'selected' : '' }}>Desember</option>
          </select>
        </div>
        <div>
          <label class="form-label small">Tahun</label>
          <input type="number" name="tahun" class="form-control form-control-sm" value="{{ request('tahun', date('Y')) }}" style="min-width: 90px;" min="2020" max="2030">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
          Tampilkan
        </button>
      </form>
      <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">
        Refresh
      </button>
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
      <div class="text-center">
        <h4 class="mb-1 fw-bold">PT MANUFAKTUR COE</h4>
        <p class="mb-1">Laporan Keuangan Mei 2026</p>
        <h5 class="mb-0 fw-bold">Neraca Saldo</h5>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.9rem;">
          <thead class="table-light sticky-top">
            <tr>
              <th class="text-center" style="width:5%">No</th>
              <th style="width:35%">AKUN</th>
              <th class="text-end" style="width:30%">DEBIT (RP)</th>
              <th class="text-end" style="width:30%">KREDIT (RP)</th>
            </tr>
          </thead>
          <tbody>
            @php
              $totalSaldoAwal = 0;
              $totalDebit = 0;
              $totalKredit = 0;
              $totalSaldoAkhir = 0;

              // Group accounts by type
              $assetAccounts = [];
              $liabilityAccounts = [];
              $equityAccounts = [];
              $revenueAccounts = [];
              $expenseAccounts = [];

              foreach($coas as $coa) {
                $data = $totals[$coa->kode_akun] ?? ['saldo_awal' => 0, 'debit' => 0, 'kredit' => 0, 'saldo_akhir' => 0];
                $accountData = [
                  'coa' => $coa,
                  'data' => $data
                ];

                // Normalize tipe_akun to handle variations
                $tipeAkun = strtolower($coa->tipe_akun);

                if (in_array($tipeAkun, ['asset', 'aset'])) {
                  $assetAccounts[] = $accountData;
                } elseif (in_array($tipeAkun, ['liability', 'kewajiban'])) {
                  $liabilityAccounts[] = $accountData;
                } elseif (in_array($tipeAkun, ['equity', 'modal', 'ekuitas'])) {
                  $equityAccounts[] = $accountData;
                } elseif (in_array($tipeAkun, ['revenue', 'pendapatan', 'penjualan'])) {
                  $revenueAccounts[] = $accountData;
                } elseif (in_array($tipeAkun, ['expense', 'beban', 'biaya'])) {
                  $expenseAccounts[] = $accountData;
                } else {
                  // Default to asset if unknown type
                  $assetAccounts[] = $accountData;
                }
              }
            @endphp
            
            <!-- ASSETS -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-building me-2"></i>AKTIVA
              </td>
            </tr>
            @php $rowNumber = 1; @endphp
            @foreach($assetAccounts as $item)
              @php
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];

                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
              </tr>
            @endforeach
            
            <!-- LIABILITIES -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-credit-card me-2"></i>PASIVA
              </td>
            </tr>
            @foreach($liabilityAccounts as $item)
              @php
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];

                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
              </tr>
            @endforeach
            
            <!-- EQUITY -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-wallet2 me-2"></i>EKUITAS
              </td>
            </tr>
            @foreach($equityAccounts as $item)
              @php
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];

                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
              </tr>
            @endforeach
            
            <!-- REVENUE -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-graph-up me-2"></i>PENDAPATAN
              </td>
            </tr>
            @foreach($revenueAccounts as $item)
              @php
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];

                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>{{ $coa->nama_akun }}</td>
                <td class="text-end">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
              </tr>
            @endforeach
            
            <!-- EXPENSES -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-graph-down me-2"></i>BEBAN
              </td>
            </tr>
            @foreach($expenseAccounts as $item)
              @php
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];

                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              @endphp
              <tr>
                <td class="text-center">{{ $rowNumber++ }}</td>
                <td><strong>{{ $coa->kode_akun }}</strong></td>
                <td>
                  {{ $coa->nama_akun }}
                  @if($coa->kode_akun === '5101')
                    <small class="badge bg-warning text-dark ms-2">HPP</small>
                  @endif
                </td>
                <td class="text-end">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($kredit, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="table-dark">
            <tr>
              <th colspan="3" class="text-end">TOTAL</th>
              <th class="text-end">Rp {{ number_format($totalSaldoAwal, 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalKredit, 0, ',', '.') }}</th>
              <th class="text-end">Rp {{ number_format($totalSaldoAkhir, 0, ',', '.') }}</th>
            </tr>
            @php
              // Hitung total saldo debit dan kredit untuk balance check
              $totalSaldoDebit = 0;
              $totalSaldoKredit = 0;
              
              foreach($coas as $coa) {
                $data = $totals[$coa->kode_akun] ?? ['saldo_debit' => 0, 'saldo_kredit' => 0];
                $totalSaldoDebit += $data['saldo_debit'];
                $totalSaldoKredit += $data['saldo_kredit'];
              }
              
              $balanceDiff = abs($totalSaldoDebit - $totalSaldoKredit);
              $isBalanced = $balanceDiff < 0.01;
            @endphp
            <tr>
              <th colspan="3" class="text-end">BALANCE CHECK (Saldo Debit vs Kredit):</th>
              <th class="text-end">Total Saldo Debit:</th>
              <th class="text-end">Rp {{ number_format($totalSaldoDebit, 2, ',', '.') }}</th>
              <th class="text-end">Total Saldo Kredit:</th>
              <th class="text-end">Rp {{ number_format($totalSaldoKredit, 2, ',', '.') }}</th>
            </tr>
            <tr>
              <th colspan="6" class="text-end">STATUS:</th>
              <th class="text-end {{ $isBalanced ? 'text-success' : 'text-danger' }}">
                @if($isBalanced)
                  <i class="bi bi-check-circle-fill"></i> BALANCED ✓
                @else
                  <i class="bi bi-exclamation-triangle-fill"></i> SELISIH: Rp {{ number_format($balanceDiff, 2, ',', '.') }}
                @endif
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
          <strong><i class="bi bi-info-circle"></i> Informasi Neraca Saldo:</strong>
          <ul class="mb-0 mt-2">
            <li>Neraca saldo menunjukkan saldo semua akun per periode tertentu</li>
            <li>Aktiva = Pasiva + Ekuitas (Balance Sheet Equation)</li>
            <li>Total Debit = Total Kredit (Trial Balance)</li>
            <li>Saldo akhir periode ini menjadi saldo awal periode berikutnya</li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert alert-warning">
          <strong><i class="bi bi-building"></i> Kategori Akun Manufaktur:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>Aktiva:</strong> Kas, Bank, Persediaan (Bahan Baku, Barang Jadi, dll)</li>
            <li><strong>Pasiva:</strong> Hutang Usaha, Hutang Gaji, Hutang BOP</li>
            <li><strong>Ekuitas:</strong> Modal Pemilik, Laba Ditahan</li>
            <li><strong>Pendapatan:</strong> Penjualan Produk</li>
            <li><strong>Beban:</strong> HPP, Beban Gaji, Beban Listrik, BOP, dll</li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="alert alert-success">
      <strong><i class="bi bi-check-circle"></i> Standar Akuntansi Manufaktur:</strong>
      <div class="row mt-2">
        <div class="col-md-4">
          <h6>📦 Persediaan</h6>
          <ul class="mb-0">
            <li>Persediaan Bahan Baku (102)</li>
            <li>Persediaan Dalam Proses (1104)</li>
            <li>Persediaan Barang Jadi (1107)</li>
          </ul>
        </div>
        <div class="col-md-4">
          <h6>💰 HPP & COGS</h6>
          <ul class="mb-0">
            <li>Harga Pokok Penjualan (5101)</li>
            <li>Beban Overhead Pabrik (5102)</li>
            <li>Beban Penyusutan (5103)</li>
          </ul>
        </div>
        <div class="col-md-4">
          <h6>🏭 Produksi</h6>
          <ul class="mb-0">
            <li>Konsumsi Bahan ke WIP</li>
            <li>BTKL/BOP ke WIP</li>
            <li>Selesai Produksi</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

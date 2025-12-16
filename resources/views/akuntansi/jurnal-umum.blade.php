@extends('layouts.app')

@section('content')
@php
  $businessName = config('app.company_name', config('app.name', 'UMKM COE'));
  $requestMonth = request('month');
  $requestYear = request('year');
  $selectedAccountId = request('account_id');

  $fallbackFrom = request('from');
  if ((!$requestMonth || !$requestYear) && $fallbackFrom) {
      $parsed = \Carbon\Carbon::parse($fallbackFrom);
      $requestMonth = $requestMonth ?: $parsed->format('m');
      $requestYear = $requestYear ?: $parsed->format('Y');
  }
  $selectedMonth = $requestMonth ?: now()->format('m');
  $selectedYear = $requestYear ?: now()->format('Y');

  $months = collect(range(1, 12))->mapWithKeys(function ($month) {
      $carbon = \Carbon\Carbon::create()->month($month);
      return [str_pad((string) $month, 2, '0', STR_PAD_LEFT) => $carbon->translatedFormat('F')];
  });

  $years = \App\Models\JournalEntry::selectRaw('DISTINCT YEAR(tanggal) as year')
      ->orderBy('year', 'desc')
      ->pluck('year');
  if ($years->isEmpty()) {
      $years = collect([(int) $selectedYear]);
  }

  $accounts = \App\Models\Account::orderBy('name')->get();

  $journalLines = $entries->flatMap(function ($entry) use ($selectedAccountId) {
      $entryDate = \Carbon\Carbon::parse($entry->tanggal);
      return $entry->lines
          ->when($selectedAccountId, fn ($lines) => $lines->where('account_id', $selectedAccountId))
          ->map(function ($line) use ($entryDate) {
              return [
                  'date' => $entryDate->format('Y-m-d'),
                  'display_date' => $entryDate->translatedFormat('d F Y'),
                  'account_code' => $line->account->code ?? '-',
                  'account_name' => $line->account->name ?? 'Akun tidak ditemukan',
                  'debit' => (float) ($line->debit ?? 0),
                  'credit' => (float) ($line->credit ?? 0),
              ];
          });
  })->sortBy('date')->values();

  $totalDebit = $journalLines->sum('debit');
  $totalCredit = $journalLines->sum('credit');
  $periodLabel = \Carbon\Carbon::create($selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
@endphp

@push('styles')
<style>
  .journal-table {
    background-color: #ffffff;
    color: #212529;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.5rem;
    overflow: hidden;
  }

  .journal-table thead th {
    background: linear-gradient(135deg, #5a60ff, #7b4dff);
    color: #ffffff;
    font-weight: 700;
    border-bottom: none;
    border-top: none;
  }

  .journal-table tbody tr {
    border-color: #e6e8f0;
  }

  .journal-table tbody tr:nth-child(odd) {
    background-color: #ffffff;
  }

  .journal-table tbody tr:nth-child(even) {
    background-color: #f3f4f8;
  }

  .journal-table tbody td {
    color: #1f2933;
    border-color: #e6e8f0;
  }

  .journal-table tbody td.text-end {
    font-variant-numeric: tabular-nums;
  }

  .journal-table tfoot td {
    background-color: #e6e8f0;
    color: #1f2933;
    font-weight: 700;
    border-top: 1px solid #d0d3dd;
  }

  .journal-table .table-empty {
    color: #6b7280;
  }
</style>
@endpush

<div class="bg-dark py-4 min-vh-100">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0 bg-transparent p-0">
        <li class="breadcrumb-item"><a class="link-light" href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active text-light" aria-current="page">Jurnal Umum</li>
      </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3 text-light">
      <h1 class="h3 fw-bold mb-0">Jurnal Umum</h1>
      <a href="{{ route('akuntansi.jurnal-umum.export-pdf', request()->all()) }}" target="_blank" class="btn btn-outline-light">
        <i class="bi bi-file-earmark-arrow-down"></i> Download PDF
      </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 bg-dark text-light">
      <div class="card-body">
        <form method="get" id="journalFilterForm" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="filterMonth" class="form-label small text-uppercase text-white">Pilih Bulan</label>
            <select class="form-select bg-dark text-light border-secondary" id="filterMonth" name="month">
              @foreach($months as $value => $label)
                <option value="{{ $value }}" {{ $value == $selectedMonth ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label for="filterYear" class="form-label small text-uppercase text-white">Pilih Tahun</label>
            <select class="form-select bg-dark text-light border-secondary" id="filterYear" name="year">
              @foreach($years as $year)
                <option value="{{ $year }}" {{ (int)$year === (int)$selectedYear ? 'selected' : '' }}>{{ $year }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label for="filterAccount" class="form-label small text-uppercase text-white">Filter Nama Akun</label>
            <select class="form-select bg-dark text-light border-secondary" id="filterAccount" name="account_id">
              <option value="">Semua Akun</option>
              @foreach($accounts as $account)
                <option value="{{ $account->id }}" {{ (int) $account->id === (int) $selectedAccountId ? 'selected' : '' }}>
                  {{ $account->code }} - {{ $account->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
          </div>

          <input type="hidden" name="from" id="filterFrom" value="{{ request('from') }}">
          <input type="hidden" name="to" id="filterTo" value="{{ request('to') }}">
          <input type="hidden" name="ref_type" value="{{ request('ref_type') }}">
          <input type="hidden" name="ref_id" value="{{ request('ref_id') }}">
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm bg-dark text-light">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <h4 class="fw-bold mb-1 text-white">UMKM COE</h4>
          <p class="mb-0 text-white-50">Laporan Keuangan {{ $periodLabel }}</p>
          <span class="small text-secondary">Jurnal Umum</span>
        </div>

        <div class="table-responsive">
          <table class="table align-middle mb-0 journal-table">
            <thead>
              <tr class="text-uppercase small">
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 35%;">Nama Akun</th>
                <th style="width: 15%;">Nomor Akun</th>
                <th class="text-end" style="width: 17.5%;">Debit</th>
                <th class="text-end" style="width: 17.5%;">Kredit</th>
              </tr>
            </thead>
            <tbody>
              @forelse($journalLines as $line)
                <tr>
                  <td>{{ $line['display_date'] }}</td>
                  <td>{{ $line['account_name'] }}</td>
                  <td>{{ $line['account_code'] }}</td>
                  <td class="text-end">Rp {{ number_format($line['debit'], 0, ',', '.') }}</td>
                  <td class="text-end">Rp {{ number_format($line['credit'], 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center table-empty py-4">Belum Ada Penjurnalan.</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="fw-semibold">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('journalFilterForm');
    const monthSelect = document.getElementById('filterMonth');
    const yearSelect = document.getElementById('filterYear');
    const fromInput = document.getElementById('filterFrom');
    const toInput = document.getElementById('filterTo');

    form.addEventListener('submit', function () {
      const month = monthSelect.value;
      const year = yearSelect.value;
      if (month && year) {
        const firstDay = new Date(Date.UTC(parseInt(year, 10), parseInt(month, 10) - 1, 1));
        const lastDay = new Date(Date.UTC(parseInt(year, 10), parseInt(month, 10), 0));

        const formatDate = (date) => {
          const month = String(date.getUTCMonth() + 1).padStart(2, '0');
          const day = String(date.getUTCDate()).padStart(2, '0');
          return `${date.getUTCFullYear()}-${month}-${day}`;
        };

        fromInput.value = formatDate(firstDay);
        toInput.value = formatDate(lastDay);
      }
    });
  });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 text-light">
  <div class="bg-dark rounded-3 border border-secondary-subtle p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
      <div class="col-lg-3 col-md-4">
        <label for="period" class="form-label small text-uppercase text-secondary mb-1">Periode</label>
        <input type="month" name="period" id="period" class="form-control bg-dark text-light border-secondary" value="{{ $period }}">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary px-4" type="submit">Terapkan</button>
      </div>
    </form>
  </div>

  <div class="px-lg-3">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-white mb-1">{{ $companyName }}</h2>
      <h4 class="fw-semibold text-white-50 mb-1">Laporan Neraca</h4>
      <span class="text-secondary">Periode {{ $periodLabel }}</span>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="mb-4">
          <div class="text-uppercase text-secondary fw-semibold mb-3">Aktiva</div>
          @php $hasAssetItems = false; @endphp
          @foreach($assetGroups as $group)
            @php $hasAssetItems = $hasAssetItems || !empty($group['items']); @endphp
            <div class="mb-4">
              <div class="fw-semibold text-white mb-2">{{ $group['label'] }}</div>
              @forelse($group['items'] as $item)
                <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                  <div class="me-3">
                    <div class="small text-secondary">{{ $item['code'] }}</div>
                    <div class="fw-semibold text-white">{{ $item['name'] }}</div>
                  </div>
                  <div class="text-end fw-semibold">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                </div>
              @empty
                <div class="text-secondary fst-italic">Tidak ada data</div>
              @endforelse
              <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                <span>Subtotal {{ $group['label'] }}</span>
                <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
              </div>
            </div>
          @endforeach
          @if(!$hasAssetItems)
            <div class="text-secondary fst-italic">Tidak ada data aktiva untuk periode ini.</div>
          @endif
        </div>

        <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold fs-5">
          <span>Total Aktiva</span>
          <span>Rp {{ number_format($totalAssets, 0, ',', '.') }}</span>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="mb-4">
          <div class="text-uppercase text-secondary fw-semibold mb-3">Pasiva</div>

          <div class="mb-4">
            <div class="fw-semibold text-white mb-2">Kewajiban</div>
            @php $hasLiabilityItems = false; @endphp
            @foreach($liabilityGroups as $group)
              @php $hasLiabilityItems = $hasLiabilityItems || !empty($group['items']); @endphp
              <div class="mb-3">
                @forelse($group['items'] as $item)
                  <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                    <div class="me-3">
                      <div class="small text-secondary">{{ $item['code'] }}</div>
                      <div class="fw-semibold text-white">{{ $item['name'] }}</div>
                    </div>
                    <div class="text-end fw-semibold">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                  </div>
                @empty
                  <div class="text-secondary fst-italic">Tidak ada data</div>
                @endforelse
                <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                  <span>Subtotal {{ $group['label'] }}</span>
                  <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                </div>
              </div>
            @endforeach
            @if(!$hasLiabilityItems)
              <div class="text-secondary fst-italic">Tidak ada kewajiban untuk periode ini.</div>
            @endif
            <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold">
              <span>Total Kewajiban</span>
              <span>Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</span>
            </div>
          </div>

          <div class="mb-4">
            <div class="fw-semibold text-white mb-2">Modal &amp; Ekuitas</div>
            @php
              $hasEquityItems = false;
              $runningNote = null;
            @endphp
            @foreach($equityGroups as $group)
              @php
                $hasEquityItems = $hasEquityItems || !empty($group['items']);
                if(isset($group['meta']['net_profit']) && $group['meta']['net_profit'] != 0) {
                    $runningNote = $group['meta']['net_profit'];
                }
              @endphp
              <div class="mb-3">
                @forelse($group['items'] as $item)
                  <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                    <div class="me-3">
                      <div class="small text-secondary">{{ $item['code'] }}</div>
                      <div class="fw-semibold text-white">{{ $item['name'] }}</div>
                    </div>
                    <div class="text-end fw-semibold">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                  </div>
                @empty
                  <div class="text-secondary fst-italic">Tidak ada data</div>
                @endforelse
                <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                  <span>Subtotal {{ $group['label'] }}</span>
                  <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                </div>
              </div>
            @endforeach
            @if(!$hasEquityItems)
              <div class="text-secondary fst-italic">Tidak ada modal/ekuitas untuk periode ini.</div>
            @endif
            @if(!is_null($runningNote))
              <div class="small text-secondary fst-italic">
                Termasuk {{ $runningNote >= 0 ? 'laba' : 'rugi' }} tahun berjalan sebesar Rp {{ number_format(abs($runningNote), 0, ',', '.') }}.
              </div>
            @endif
            <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold">
              <span>Total Modal &amp; Ekuitas</span>
              <span>Rp {{ number_format($totalEquity, 0, ',', '.') }}</span>
            </div>
          </div>

          <div class="d-flex justify-content-between pt-3 mt-4 border-top border-secondary text-uppercase fw-bold fs-5">
            <span>Total Pasiva</span>
            <span>Rp {{ number_format($totalLiabilitiesEquity, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>

      @if(abs($totalAssets - $totalLiabilitiesEquity) >= 1)
        <div class="mt-4 p-3 rounded-3 border border-warning text-warning small">
          Selisih saldo terdeteksi. Mohon periksa jurnal agar neraca seimbang.
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

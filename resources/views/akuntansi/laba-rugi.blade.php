@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Laba Rugi</h3>
    <form method="get" class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label">Dari</label>
        <input type="date" name="from" value="{{ $from }}" class="form-control">
      </div>
      <div class="col-auto">
        <label class="form-label">Sampai</label>
        <input type="date" name="to" value="{{ $to }}" class="form-control">
      </div>
      <div class="col-auto"><button class="btn btn-primary">Terapkan</button></div>
    </form>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-light fw-semibold">Pendapatan</div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            @php $sumRev = 0; @endphp
            @foreach($revenue as $acc)
              @php
                $q = \App\Models\JournalLine::where('account_id',$acc->id)->with('entry');
                if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                $row = $q->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->first();
                $bal = (float)($row->bal ?? 0);
                $sumRev += $bal;
              @endphp
              <li class="list-group-item d-flex justify-content-between"><span>{{ $acc->code }} - {{ $acc->name }}</span><span>Rp {{ number_format($bal,0,',','.') }}</span></li>
            @endforeach
          </ul>
        </div>
        <div class="card-footer d-flex justify-content-between"><strong>Total Pendapatan</strong><strong>Rp {{ number_format($sumRev,0,',','.') }}</strong></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-light fw-semibold">Beban</div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            @php $sumExp = 0; @endphp
            @foreach($expense as $acc)
              @php
                $q = \App\Models\JournalLine::where('account_id',$acc->id)->with('entry');
                if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                $bal = (float)($row->bal ?? 0);
                $sumExp += $bal;
              @endphp
              <li class="list-group-item d-flex justify-content-between"><span>{{ $acc->code }} - {{ $acc->name }}</span><span>Rp {{ number_format($bal,0,',','.') }}</span></li>
            @endforeach
          </ul>
        </div>
        <div class="card-footer d-flex justify-content-between"><strong>Total Beban</strong><strong>Rp {{ number_format($sumExp,0,',','.') }}</strong></div>
      </div>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-body d-flex justify-content-between">
      <h5 class="mb-0">Laba/Rugi Bersih</h5>
      <h5 class="mb-0">Rp {{ number_format($laba,0,',','.') }}</h5>
    </div>
  </div>
</div>
@endsection

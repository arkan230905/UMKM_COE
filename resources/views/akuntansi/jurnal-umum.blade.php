@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Jurnal Umum</h3>
    <form method="get" class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label">Dari</label>
        <input type="date" name="from" value="{{ $from }}" class="form-control">
      </div>
      <div class="col-auto">
        <label class="form-label">Sampai</label>
        <input type="date" name="to" value="{{ $to }}" class="form-control">
      </div>
      <div class="col-auto">
        <label class="form-label">Ref Type</label>
        <input type="text" name="ref_type" value="{{ $refType ?? '' }}" class="form-control" placeholder="mis: purchase/sale/production_*">
      </div>
      <div class="col-auto">
        <label class="form-label">Ref ID</label>
        <input type="number" name="ref_id" value="{{ $refId ?? '' }}" class="form-control" placeholder="ID transaksi">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary">Terapkan</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:12%">Tanggal</th>
          <th>Ref</th>
          <th>Memo</th>
          <th>Akun</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @foreach($entries as $e)
          @foreach($e->lines as $i => $l)
            <tr>
              @if($i===0)
                <td rowspan="{{ $e->lines->count() }}">{{ $e->tanggal }}</td>
                <td rowspan="{{ $e->lines->count() }}">{{ $e->ref_type }}#{{ $e->ref_id }}</td>
                <td rowspan="{{ $e->lines->count() }}">{{ $e->memo }}</td>
              @endif
              <td>
                {{ $l->account->code }} - {{ $l->account->name }}
                <span class="badge bg-secondary ms-1">{{ ($l->debit ?? 0) > 0 ? 'D' : 'K' }}</span>
              </td>
              <td class="text-end">{{ $l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-' }}</td>
              <td class="text-end">{{ $l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-' }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

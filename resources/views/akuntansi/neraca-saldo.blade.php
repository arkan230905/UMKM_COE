@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Neraca Saldo</h3>
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
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Kode</th>
          <th>Akun</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @php $td=0; $tc=0; @endphp
        @foreach($accounts as $a)
          @php $d = $totals[$a->id]['debit'] ?? 0; $c = $totals[$a->id]['credit'] ?? 0; $td+=$d; $tc+=$c; @endphp
          <tr>
            <td>{{ $a->code }}</td>
            <td>{{ $a->name }}</td>
            <td class="text-end">{{ $d>0 ? 'Rp '.number_format($d,0,',','.') : '-' }}</td>
            <td class="text-end">{{ $c>0 ? 'Rp '.number_format($c,0,',','.') : '-' }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="2" class="text-end">Total</th>
          <th class="text-end">Rp {{ number_format($td,0,',','.') }}</th>
          <th class="text-end">Rp {{ number_format($tc,0,',','.') }}</th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection

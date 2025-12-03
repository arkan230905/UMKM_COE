@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Buku Besar</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('akuntansi.buku-besar.export-excel', ['from' => $from, 'to' => $to]) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Export Excel (Semua Akun)
      </a>
    </div>
  </div>

  <form method="get" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
      <label class="form-label">Akun</label>
      <select name="account_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Pilih Akun --</option>
        @foreach($accounts as $a)
          <option value="{{ $a->id }}" {{ ($accountId==$a->id)?'selected':'' }}>{{ $a->code }} - {{ $a->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Dari</label>
      <input type="date" name="from" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-auto">
      <label class="form-label">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Terapkan</button>
    </div>
  </form>

  @if($accountId)
  <div class="mb-2"><strong>Saldo Awal:</strong> Rp {{ number_format($saldoAwal,0,',','.') }}</div>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:12%">Tanggal</th>
          <th>Ref</th>
          <th>Memo</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Kredit</th>
          <th class="text-end">Saldo</th>
        </tr>
      </thead>
      <tbody>
        @php $saldo = (float)$saldoAwal; @endphp
        @foreach($lines as $l)
          @php $saldo += ((float)$l->debit - (float)$l->credit); @endphp
          <tr>
            <td>{{ $l->entry->tanggal ?? '' }}</td>
            <td>{{ $l->entry->ref_type ?? '' }}#{{ $l->entry->ref_id ?? '' }}</td>
            <td>{{ $l->entry->memo ?? '' }}</td>
            <td class="text-end">{{ $l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-' }}</td>
            <td class="text-end">{{ $l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-' }}</td>
            <td class="text-end">Rp {{ number_format($saldo,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection

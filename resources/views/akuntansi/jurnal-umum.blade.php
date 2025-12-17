@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Jurnal Umum</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('akuntansi.jurnal-umum.export-pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
        <i class="bi bi-file-pdf"></i> Download PDF
      </a>
      {{-- Export Excel dinonaktifkan sementara --}}
      {{-- <a href="{{ route('akuntansi.jurnal-umum.export-excel', request()->all()) }}" class="btn btn-success">
        <i class="bi bi-file-excel"></i> Download Excel
      </a> --}}
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
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
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:10%">Tanggal</th>
          <th style="width:12%">Ref</th>
          <th style="width:20%">Memo</th>
          <th style="width:8%">Kode Akun</th>
          <th style="width:22%">Nama Akun</th>
          <th class="text-end" style="width:14%">Debit</th>
          <th class="text-end" style="width:14%">Kredit</th>
        </tr>
      </thead>
      <tbody>
        @forelse($entries as $e)
          @foreach($e->lines as $i => $l)
            <tr>
              @if($i===0)
                <td rowspan="{{ $e->lines->count() }}">{{ \Carbon\Carbon::parse($e->tanggal)->format('d/m/Y') }}</td>
                <td rowspan="{{ $e->lines->count() }}">{{ $e->ref_type }}#{{ $e->ref_id }}</td>
                <td rowspan="{{ $e->lines->count() }}">{{ $e->memo }}</td>
              @endif
              <td>
                <strong>{{ $l->account->code ?? 'N/A' }}</strong>
                <span class="badge bg-{{ ($l->debit ?? 0) > 0 ? 'info' : 'warning' }} ms-1">{{ ($l->debit ?? 0) > 0 ? 'D' : 'K' }}</span>
              </td>
              <td>
                <strong>{{ $l->account->name ?? 'Akun tidak ditemukan' }}</strong>
                @if($l->account)
                  <br><small class="text-muted">{{ $l->account->type ?? '' }}</small>
                @endif
              </td>
              <td class="text-end">{{ $l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-' }}</td>
              <td class="text-end">{{ $l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-' }}</td>
            </tr>
          @endforeach
        @empty
          <tr>
            <td colspan="7" class="text-center">Tidak ada data jurnal</td>
          </tr>
        @endforelse
      </tbody>
      @if($entries->count() > 0)
      <tfoot class="table-secondary">
        <tr>
          <th colspan="5" class="text-end">Total:</th>
          <th class="text-end">
            Rp {{ number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.') }}
          </th>
          <th class="text-end">
            Rp {{ number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.') }}
          </th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Edit Pembayaran Beban</h3>
  
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('transaksi.pembayaran-beban.update', $row->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" value="{{ $row->tanggal }}" required>
    </div>
    
    <div class="mb-3">
      <label class="form-label">COA Beban</label>
      <select name="coa_beban_id" class="form-select" required>
        @foreach($coas as $c)
          <option value="{{ $c->kode_akun }}" {{ $row->coa_beban_id == $c->kode_akun ? 'selected' : '' }}>
            {{ $c->kode_akun }} - {{ $c->nama_akun }}
          </option>
        @endforeach
      </select>
    </div>
    
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash" {{ $row->metode_bayar == 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="bank" {{ $row->metode_bayar == 'bank' ? 'selected' : '' }}>Bank</option>
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">COA Kas/Bank</label>
        <select name="coa_kasbank" class="form-select">
          @foreach($kasbank as $k)
            <option value="{{ $k->kode_akun }}" {{ $row->coa_kasbank == $k->kode_akun ? 'selected' : '' }}>
              {{ $k->kode_akun }} - {{ $k->nama_akun }}
            </option>
          @endforeach
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">Nominal</label>
        <input type="number" step="0.01" min="0" name="nominal" class="form-control" value="{{ $row->nominal }}" required>
      </div>
    </div>
    
    <div class="mb-3 mt-3">
      <label class="form-label">Keterangan</label>
      <input type="text" name="deskripsi" class="form-control" value="{{ $row->deskripsi }}">
    </div>
    
    <button type="submit" class="btn btn-success">Update</button>
    <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection

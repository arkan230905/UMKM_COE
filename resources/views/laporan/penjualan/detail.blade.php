@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
  <h2 class="mb-4">Detail Laporan Penjualan #{{ $penjualan->id }}</h2>
  
  <div class="row mb-3">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Informasi Penjualan</h5>
          <div class="row">
            <div class="col-sm-6">
              <p><strong>Tanggal:</strong></p>
              <p>{{ $penjualan->tanggal }}</p>
            </div>
            <div class="col-sm-6">
              <p><strong>Customer:</strong></p>
              <p>{{ $penjualan->customer->nama_customer ?? '-' }}</p>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4">
              <p><strong>Total:</strong></p>
              <p class="text-success">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</p>
            </div>
            <div class="col-sm-4">
              <p><strong>Status:</strong></p>
              <span class="badge bg-{{ $penjualan->status === 'lunas' ? 'success' : 'warning' }}">
                {{ $penjualan->status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
              </span>
            </div>
            <div class="col-sm-4">
              <p><strong>Keterangan:</strong></p>
              <p>{{ $penjualan->keterangan ?? '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Ringkasan Keuangan</h5>
          <div class="row">
            <div class="col-sm-6">
              <p><strong>HPP Total:</strong></p>
              <p class="text-danger">Rp {{ number_format($penjualan->details->sum(function($item) use ($penjualan) {
                  return $item->produk->getHPPForSaleDate($penjualan->tanggal) * $item->jumlah;
              }), 0, ',', '.') }}</p>
            </div>
            <div class="col-sm-6">
              <p><strong>Gross Margin:</strong></p>
              <p class="text-success">Rp {{ number_format(($penjualan->total_harga - $penjualan->details->sum(function($item) use ($penjualan) {
                  return $item->produk->getHPPForSaleDate($penjualan->tanggal) * $item->jumlah;
              })), 0, ',', '.') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Detail Produk Terjual</h5>
      <div class="table-responsive">
        <table class="table table-dark table-striped mt-3">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Jumlah</th>
              <th>Harga Jual</th>
              <th>HPP</th>
              <th>Margin</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($penjualan->details as $item)
              @php $actualHPP = $item->produk->getHPPForSaleDate($penjualan->tanggal); @endphp
              <tr>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->jumlah }}</td>
                <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                <td>Rp {{ number_format(($item->harga_satuan - $actualHPP) * $item->jumlah, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

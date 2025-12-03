@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pelunasan Utang</h3>
  </div>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">
      <strong>Pembelian Kredit Belum Lunas</strong>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>Vendor</th>
            <th>Item Dibeli</th>
            <th class="text-end">Total Utang</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($openPurchases as $p)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ optional($p->tanggal)->format('d/m/Y') ?? $p->tanggal }}</td>
            <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
            <td>
              @if($p->details && $p->details->count() > 0)
                <small>
                @foreach($p->details as $detail)
                  {{ $detail->bahanBaku->nama_bahan ?? '-' }} 
                  ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }})
                  @if(!$loop->last), @endif
                @endforeach
                </small>
              @else
                -
              @endif
            </td>
            <td class="text-end">
              @php
                // Ambil total_harga dari database atau hitung dari details
                $totalHarga = $p->total_harga ?? 0;
                
                // Jika total_harga masih 0, hitung dari details
                if ($totalHarga == 0 && $p->details && $p->details->count() > 0) {
                    $totalHarga = $p->details->sum(function($detail) {
                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    });
                }
                
                // Hitung sisa utang
                $terbayar = $p->terbayar ?? 0;
                $sisaUtang = $totalHarga - $terbayar;
              @endphp
              <strong class="text-danger">Rp {{ number_format($sisaUtang, 0, ',', '.') }}</strong>
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="{{ route('transaksi.ap-settlement.create', ['pembelian_id'=>$p->id]) }}">
                <i class="fas fa-money-bill-wave"></i> Lunasi
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-3">
              <i class="fas fa-check-circle fa-2x mb-2"></i>
              <p class="mb-0">Tidak ada utang yang perlu dilunasi</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <h5>Riwayat Pelunasan</h5>
  <table class="table table-bordered">
    <thead class="table-dark"><tr><th>#</th><th>Tanggal</th><th>Vendor</th><th>Pembelian</th><th>Dibayar</th><th>Diskon</th><th>Denda</th><th>Aksi</th></tr></thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $r->tanggal }}</td>
        <td>{{ $r->pembelian->vendor->nama_vendor ?? '-' }}</td>
        <td>#{{ $r->pembelian_id }}</td>
        <td>Rp {{ number_format($r->dibayar_bersih,0,',','.') }}</td>
        <td>Rp {{ number_format($r->diskon,0,',','.') }}</td>
        <td>Rp {{ number_format($r->denda_bunga,0,',','.') }}</td>
        <td><a href="{{ route('transaksi.ap-settlement.show', $r->id) }}" class="btn btn-info btn-sm">Invoice</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $rows->links() }}
</div>
@endsection

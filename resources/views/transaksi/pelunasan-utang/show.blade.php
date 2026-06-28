@extends('layouts.app')

@section('title', 'Detail Pelunasan Utang: ' . $pelunasanUtang->kode_transaksi)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i>Detail Pelunasan Utang
        </h2>
        <div>
            <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-white" style="background-color: #8A6B48;">
                    <h4 class="mb-0">Pelunasan Utang</h4>
                    <div class="mt-2">No. {{ $pelunasanUtang->kode_transaksi }}</div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Vendor:</h6>
                            <p class="mb-1"><strong>{{ $pelunasanUtang->pembelian->vendor->nama_vendor }}</strong></p>
                            <p class="mb-1">{{ $pelunasanUtang->pembelian->vendor->alamat }}</p>
                            <p class="mb-0">{{ $pelunasanUtang->pembelian->vendor->telepon }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-2"><strong>Tanggal:</strong> {{ $pelunasanUtang->tanggal->format('d/m/Y') }}</p>
                            <p class="mb-0"><strong>Status:</strong> {!! $pelunasanUtang->status_badge !!}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Detail Pembelian</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">NO</th>
                                    <th>Item</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pelunasanUtang->pembelian->pembelianDetails as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($detail->bahanBaku)
                                            <strong>{{ $detail->bahanBaku->nama_bahan }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ number_format($detail->jumlah, 0, ',', '.') }} {{ $detail->satuan_nama ?? 'unit' }} × 
                                                Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                            </small>
                                        @elseif($detail->bahanPendukung)
                                            <strong>{{ $detail->bahanPendukung->nama_bahan }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ number_format($detail->jumlah, 0, ',', '.') }} {{ $detail->satuan_nama ?? 'unit' }} × 
                                                Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                            </small>
                                        @else
                                            Item {{ $index + 1 }}
                                            <br>
                                            <small class="text-muted">
                                                {{ number_format($detail->jumlah, 0, ',', '.') }} unit × 
                                                Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ $detail->harga_satuan ? 'Rp ' . number_format($detail->harga_satuan, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $detail->subtotal ? 'Rp ' . number_format($detail->subtotal, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Pembelian</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($pelunasanUtang->pembelian->total_harga, 0, ',', '.') }}</strong></td>
                                </tr>
                                
                                @php
                                    $dpPembelian = $pelunasanUtang->pembelian->dp ?? 0;
                                    
                                    // Get pelunasan sebelumnya (before current pelunasan)
                                    $pelunasanSebelumnya = \DB::table('pelunasan_utangs')
                                        ->where('pembelian_id', $pelunasanUtang->pembelian_id)
                                        ->where('user_id', auth()->id())
                                        ->where('id', '<', $pelunasanUtang->id)
                                        ->orderBy('tanggal', 'asc')
                                        ->orderBy('id', 'asc')
                                        ->get();
                                    
                                    $totalPelunasanSebelumnya = $pelunasanSebelumnya->sum('jumlah');
                                @endphp
                                
                                @if($dpPembelian > 0)
                                    <tr style="background-color: #f5f0eb;">
                                        <td colspan="3" class="text-end"><strong>DP</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($dpPembelian, 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endif
                                
                                @if($pelunasanSebelumnya->count() > 0)
                                    <tr>
                                        <td colspan="4" class="pt-3 pb-2">
                                            <div class="border-top pt-2">
                                                <strong class="text-muted">Pelunasan Sebelumnya:</strong>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($pelunasanSebelumnya as $index => $prev)
                                        <tr class="table-light">
                                            <td class="text-center small">{{ $index + 1 }}</td>
                                            <td colspan="2" class="small">
                                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                {{ \Carbon\Carbon::parse($prev->tanggal)->format('d/m/Y') }}
                                                <span class="ms-2 text-muted">{{ $prev->kode_transaksi }}</span>
                                            </td>
                                            <td class="text-end small">
                                                Rp {{ number_format($prev->jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-secondary">
                                        <td colspan="3" class="text-end"><strong>Total Pelunasan Sebelumnya</strong></td>
                                        <td class="text-end"><strong>Rp {{ number_format($totalPelunasanSebelumnya, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="py-1"></td>
                                    </tr>
                                @endif
                                
                                <tr style="background-color: #f5f0eb;">
                                    <td colspan="3" class="text-end"><strong>Jumlah Pelunasan Ini</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($pelunasanUtang->jumlah, 0, ',', '.') }}</strong></td>
                                </tr>
                                @php
                                    // Calculate sisa utang SETELAH pelunasan ini
                                    // Total semua pelunasan untuk pembelian ini
                                    $totalAllPayments = \DB::table('pelunasan_utangs')
                                        ->where('pembelian_id', $pelunasanUtang->pembelian_id)
                                        ->where('user_id', auth()->id())
                                        ->sum('jumlah');
                                    
                                    // Get total refund
                                    $totalRefund = \DB::table('purchase_returns')
                                        ->where('pembelian_id', $pelunasanUtang->pembelian_id)
                                        ->where('user_id', auth()->id())
                                        ->where('jenis_retur', 'refund')
                                        ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
                                        ->sum('total_return_amount');
                                    
                                    $sisaUtangSetelah = $pelunasanUtang->pembelian->total_harga - $dpPembelian - $totalAllPayments - $totalRefund;
                                @endphp
                                <tr class="table-success">
                                    <td colspan="3" class="text-end"><strong>Sisa Utang</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format(max(0, $sisaUtangSetelah), 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">Akun Kas</h6>
                                <p class="mb-0">
                                    @if($pelunasanUtang->akunKas)
                                        <strong>{{ $pelunasanUtang->akunKas->kode_akun }}</strong> - {{ $pelunasanUtang->akunKas->nama_akun }}
                                    @else
                                        <span class="text-muted">Akun Kas tidak ditemukan</span>
                                    @endif
                                </p>
                            </div>
                            
                            @if($pelunasanUtang->coaPelunasan)
                            <div class="mb-3">
                                <h6 class="text-muted">COA Pelunasan</h6>
                                <p class="mb-0">
                                    <strong>{{ $pelunasanUtang->coaPelunasan->kode_akun }}</strong> - {{ $pelunasanUtang->coaPelunasan->nama_akun }}
                                </p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted">Tanggal Pembayaran</h6>
                                <p class="mb-0">{{ $pelunasanUtang->tanggal->format('d F Y') }}</p>
                            </div>
                            
                            @if($pelunasanUtang->keterangan)
                            <div class="mb-3">
                                <h6 class="text-muted">Keterangan</h6>
                                <p class="mb-0">{{ $pelunasanUtang->keterangan }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-1"><small>Dibuat oleh: {{ $pelunasanUtang->user->name }}</small></p>
                            <p class="text-muted mb-0"><small>Dibuat pada: {{ $pelunasanUtang->created_at->format('d/m/Y H:i') }}</small></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if($pelunasanUtang->updated_at->gt($pelunasanUtang->created_at))
                                <p class="text-muted mb-0"><small>Diperbarui pada: {{ $pelunasanUtang->updated_at->format('d/m/Y H:i') }}</small></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .breadcrumb, nav, .sidebar {
            display: none !important;
        }
        .container-fluid {
            padding: 20px;
        }
        .card {
            border: 1px solid #000;
            box-shadow: none;
        }
    }
</style>
@endpush

@endsection

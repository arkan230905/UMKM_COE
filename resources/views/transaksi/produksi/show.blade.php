@extends('layouts.app')
@section('title', 'Detail Produksi')
@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Produksi</h2>
        <div class="d-flex gap-2">
            @if($produksi->status === 'draft')
                <a href="{{ route('transaksi.produksi.edit', $produksi->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
            @if($produksi->status !== 'selesai')
                <a href="{{ route('transaksi.produksi.proses', $produksi->id) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-tasks me-1"></i>Kelola Proses
                </a>
            @endif
            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Info Produksi --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Informasi Produksi</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="fw-bold text-muted small">Produk</label><p class="mb-0">{{ $produksi->produk->nama_produk }}</p></div>
                <div class="col-md-3"><label class="fw-bold text-muted small">Tanggal</label><p class="mb-0">{{ \Carbon\Carbon::parse($produksi->tanggal)->format('d/m/Y') }}</p></div>
                <div class="col-md-2"><label class="fw-bold text-muted small">Qty / Hari</label><p class="mb-0">{{ (int)$produksi->qty_produksi }} pcs</p></div>
                <div class="col-md-2"><label class="fw-bold text-muted small">Produksi Bulanan</label><p class="mb-0">{{ (int)$produksi->jumlah_produksi_bulanan }} pcs / {{ $produksi->hari_produksi_bulanan }} hari</p></div>
                <div class="col-md-2"><label class="fw-bold text-muted small">Status</label><p class="mb-0">{!! $produksi->status_badge !!}</p></div>
            </div>
            @if($produksi->coaPersediaanBarangJadi)
            <div class="row mt-2">
                <div class="col-md-6"><label class="fw-bold text-muted small">COA Persediaan Barang Jadi</label>
                <p class="mb-0">{{ $produksi->coaPersediaanBarangJadi->kode_akun }} - {{ $produksi->coaPersediaanBarangJadi->nama_akun }}</p></div>
            </div>
            @endif
        </div>
    </div>

    {{-- Ringkasan Biaya --}}
    <div class="row mb-4">
        @foreach([['label'=>'Total Bahan','val'=>$produksi->total_bahan,'color'=>'success'],['label'=>'BTKL','val'=>$produksi->total_btkl,'color'=>'warning'],['label'=>'BOP','val'=>$produksi->total_bop,'color'=>'info'],['label'=>'Total Biaya','val'=>$produksi->total_biaya,'color'=>'primary']] as $s)
        <div class="col-md-3">
            <div class="card border-start border-{{ $s['color'] }} border-4">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ $s['label'] }}</div>
                    <div class="fw-bold text-{{ $s['color'] }}">Rp {{ number_format($s['val'],0,',','.') }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Detail Bahan Baku --}}
    @php
        $detailsBahanBaku = $produksi->details->where('bahan_baku_id', '!=', null);
        $detailsBahanPendukung = $produksi->details->where('bahan_pendukung_id', '!=', null);
        $totalBahanBaku = $detailsBahanBaku->sum('subtotal');
        $totalBahanPendukung = $detailsBahanPendukung->sum('subtotal');
    @endphp
    <div class="card mb-3">
        <div class="card-header bg-success text-white"><h6 class="mb-0">Biaya Bahan Baku</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light"><tr><th>No</th><th>Nama Bahan</th><th>Qty Resep</th><th>Harga/Unit</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($detailsBahanBaku as $d)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td>
                        <td>{{ rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',') }} {{ $d->satuan_resep }}</td>
                        <td>Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold">Rp {{ number_format($totalBahanBaku,0,',','.') }}</td></tr></tfoot>
            </table>
        </div>
    </div>

    {{-- Detail Bahan Pendukung --}}
    @if($detailsBahanPendukung->count() > 0)
    <div class="card mb-3">
        <div class="card-header bg-success text-white" style="background-color:#1a7a4a !important;"><h6 class="mb-0">Biaya Bahan Pendukung</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light"><tr><th>No</th><th>Nama Bahan</th><th>Qty Resep</th><th>Harga/Unit</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach($detailsBahanPendukung as $d)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $d->bahanPendukung->nama_bahan ?? '-' }}</td>
                        <td>{{ rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',') }} {{ $d->satuan_resep }}</td>
                        <td>Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold">Rp {{ number_format($totalBahanPendukung,0,',','.') }}</td></tr></tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- BTKL Detail -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                @if($produksi->status === 'draft')
                    Rencana Biaya Tenaga Kerja Langsung (BTKL)
                @else
                    Biaya Tenaga Kerja Langsung (BTKL)
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Proses</th>
                            <th>Biaya per Unit</th>
                            <th>Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($produksi->detail_breakdown) && isset($produksi->detail_breakdown['btkl']))
                            {{-- Show BTKL from breakdown --}}
                            @foreach($produksi->detail_breakdown['btkl'] as $btkl)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $btkl['nama'] }}</td>
                                    <td>Rp {{ number_format($btkl['biaya_per_unit'],0,',','.') }}</td>
                                    <td>Rp {{ number_format($btkl['total_biaya'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data BTKL</td></tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total BTKL:</td>
                            <td class="fw-bold">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Preview Jurnal --}}
    <div class="card mb-4">

        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                @if($produksi->status === 'draft')
                    Rencana Biaya Overhead Pabrik (BOP)
                @else
                    Biaya Overhead Pabrik (BOP)
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Proses / Komponen</th>
                            <th>Biaya per Unit</th>
                            <th>Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($produksi->detail_breakdown) && isset($produksi->detail_breakdown['bop']))
                            {{-- Show BOP with components grouped by process --}}
                            @php
                                $groupedBop = [];
                                foreach($produksi->detail_breakdown['bop'] as $bop) {
                                    $proses = $bop['nama_proses'];
                                    if (!isset($groupedBop[$proses])) {
                                        $groupedBop[$proses] = [];
                                    }
                                    $groupedBop[$proses][] = $bop;
                                }
                            @endphp
                            @foreach($groupedBop as $namaProses => $komponenList)
                                <tr class="table-secondary">
                                    <td colspan="4" class="fw-bold">{{ $namaProses }}</td>
                                </tr>
                                @foreach($komponenList as $komponen)
                                    <tr>
                                        <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                        <td class="ps-4">{{ $komponen['nama_komponen'] }}</td>
                                        <td>Rp {{ number_format($komponen['biaya_per_unit'],0,',','.') }}</td>
                                        <td>Rp {{ number_format($komponen['total_biaya'],0,',','.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @else
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data BOP</td></tr>
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total BOP:</td>
                            <td class="fw-bold">Rp {{ number_format($produksi->total_bop,0,',','.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
</div>
    </div>

</div>
@endsection

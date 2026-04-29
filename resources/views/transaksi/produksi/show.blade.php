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

    {{-- Detail BTKL --}}
    <div class="card mb-3">
        <div class="card-header bg-warning text-dark"><h6 class="mb-0">Biaya Tenaga Kerja Langsung (BTKL)</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light"><tr><th>No</th><th>Proses</th><th>Tarif/Unit</th><th class="text-end">Total</th><th>COA Debit</th><th>COA Kredit</th></tr></thead>
                <tbody>
                    @forelse($produksi->btklDetails as $d)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $d->nama_proses }}</td>
                        <td>Rp {{ number_format($d->harga_per_unit,0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($d->total,0,',','.') }}</td>
                        <td><span class="badge bg-secondary">{{ $d->coa_debit_kode }}</span> {{ $d->coa_debit_nama }}</td>
                        <td><span class="badge bg-secondary">{{ $d->coa_kredit_kode }}</span> {{ $d->coa_kredit_nama }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light"><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</td><td colspan="2"></td></tr></tfoot>
            </table>
        </div>
    </div>

    {{-- Detail BOP --}}
    <div class="card mb-3">
        <div class="card-header bg-info text-white"><h6 class="mb-0">Biaya Overhead Pabrik (BOP)</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light"><tr><th>No</th><th>Proses</th><th>Komponen</th><th>Rate/Unit</th><th class="text-end">Total</th><th>COA Debit</th><th>COA Kredit</th></tr></thead>
                <tbody>
                    @forelse($produksi->bopDetails as $d)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $d->nama_proses }}</td>
                        <td>{{ $d->nama_komponen }}</td>
                        <td>Rp {{ number_format($d->rate_per_unit,0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($d->total,0,',','.') }}</td>
                        <td><span class="badge bg-secondary">{{ $d->coa_debit_kode }}</span> {{ $d->coa_debit_nama }}</td>
                        <td><span class="badge bg-secondary">{{ $d->coa_kredit_kode }}</span> {{ $d->coa_kredit_nama }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold">Rp {{ number_format($produksi->total_bop,0,',','.') }}</td><td colspan="2"></td></tr></tfoot>
            </table>
        </div>
    </div>

    {{-- Preview Jurnal --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white"><h6 class="mb-0"><i class="fas fa-book me-2"></i>Preview Jurnal Akuntansi</h6></div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0" style="table-layout:fixed;width:100%;font-size:12px;">
                <colgroup><col style="width:28%"><col style="width:22%"><col style="width:8%"><col style="width:21%"><col style="width:21%"></colgroup>
                <thead><tr class="table-secondary"><th class="ps-3">Keterangan</th><th>Akun</th><th class="text-center">Ref</th><th class="text-end pe-3">Debit</th><th class="text-end pe-3">Kredit</th></tr></thead>
                <tbody>
                    @php
                        $userId = auth()->id();
                        $coaBdpBbb  = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1171')->first();
                        $coaBdpBtkl = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1172')->first();
                        $coaBdpBop  = \App\Models\Coa::withoutGlobalScopes()->where('user_id',$userId)->where('kode_akun','1173')->first();
                        $bdpBbbKode  = $coaBdpBbb->kode_akun  ?? '1171';
                        $bdpBbbNama  = $coaBdpBbb->nama_akun  ?? 'Pers. Barang Dalam Proses - BBB';
                        $bdpBtklKode = $coaBdpBtkl->kode_akun ?? '1172';
                        $bdpBtklNama = $coaBdpBtkl->nama_akun ?? 'Pers. Barang Dalam Proses - BTKL';
                        $bdpBopKode  = $coaBdpBop->kode_akun  ?? '1173';
                        $bdpBopNama  = $coaBdpBop->nama_akun  ?? 'Pers. Barang Dalam Proses - BOP';
                    @endphp

                    {{-- Jurnal 1: Produksi BBB --}}
                    <tr class="table-primary"><td colspan="5" class="text-center fw-bold py-2">Produksi</td></tr>
                    @foreach($produksi->details->where('bahan_baku_id','!=',null) as $d)
                    @php $bahan = $d->bahanBaku; $coaKode = $bahan->coa_persediaan_id ?? '114'; @endphp
                    <tr>
                        <td class="ps-3">Barang dalam proses - BBB</td>
                        <td><span class="badge bg-secondary me-1">{{ $bdpBbbKode }}</span>{{ $bdpBbbNama }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $bdpBbbKode }}</td>
                        <td class="text-end pe-3 fw-semibold">Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-muted">{{ $bahan->nama_bahan ?? '-' }}</td>
                        <td><span class="badge bg-secondary me-1">{{ $coaKode }}</span>{{ \App\Models\Coa::withoutGlobalScopes()->where('kode_akun',$coaKode)->where('user_id',$userId)->value('nama_akun') ?? 'Pers. Bahan Baku' }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $coaKode }}</td>
                        <td></td>
                        <td class="text-end pe-3">Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach

                    {{-- Jurnal 2a: BTKL WIP --}}
                    <tr class="table-info"><td colspan="5" class="text-center fw-bold py-2">BTKL WIP</td></tr>
                    @if($produksi->btklDetails->count())
                    <tr>
                        <td class="ps-3">Barang dalam proses - BTKL</td>
                        <td><span class="badge bg-secondary me-1">{{ $bdpBtklKode }}</span>{{ $bdpBtklNama }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $bdpBtklKode }}</td>
                        <td class="text-end pe-3 fw-semibold">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</td>
                        <td></td>
                    </tr>
                    @foreach($produksi->btklDetails as $d)
                    <tr>
                        <td class="ps-5 text-muted">Hutang Gaji — {{ $d->nama_proses }}</td>
                        <td><span class="badge bg-secondary me-1">{{ $d->coa_kredit_kode }}</span>{{ $d->coa_kredit_nama }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $d->coa_kredit_kode }}</td>
                        <td></td>
                        <td class="text-end pe-3">Rp {{ number_format($d->total,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- Jurnal 2b: BOP WIP --}}
                    <tr class="table-warning"><td colspan="5" class="text-center fw-bold py-2">BOP WIP</td></tr>
                    @php $bopByProses = $produksi->bopDetails->groupBy('nama_proses'); @endphp
                    @foreach($bopByProses as $namaProses => $items)
                    @php $totalProses = $items->sum('total'); @endphp
                    <tr>
                        <td class="ps-3">Barang dalam proses - BOP</td>
                        <td><span class="badge bg-secondary me-1">{{ $bdpBopKode }}</span>{{ $bdpBopNama }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $bdpBopKode }}</td>
                        <td class="text-end pe-3 fw-semibold">Rp {{ number_format($totalProses,0,',','.') }}</td>
                        <td></td>
                    </tr>
                    @foreach($items as $d)
                    <tr>
                        <td class="ps-5 text-muted">{{ $namaProses }} — {{ $d->nama_komponen }}</td>
                        <td><span class="badge bg-secondary me-1">{{ $d->coa_kredit_kode }}</span>{{ $d->coa_kredit_nama }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $d->coa_kredit_kode }}</td>
                        <td></td>
                        <td class="text-end pe-3">Rp {{ number_format($d->total,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    @endforeach

                    {{-- Jurnal 3: Selesai produksi --}}
                    <tr class="table-success"><td colspan="5" class="text-center fw-bold py-2">Sudah selesai produksi</td></tr>
                    @if($produksi->coaPersediaanBarangJadi)
                    <tr>
                        <td class="ps-3">Persediaan Barang Jadi</td>
                        <td><span class="badge bg-secondary me-1">{{ $produksi->coaPersediaanBarangJadi->kode_akun }}</span>{{ $produksi->coaPersediaanBarangJadi->nama_akun }}</td>
                        <td class="text-center text-muted" style="font-size:10px">{{ $produksi->coaPersediaanBarangJadi->kode_akun }}</td>
                        <td class="text-end pe-3 fw-semibold">Rp {{ number_format($produksi->total_biaya,0,',','.') }}</td>
                        <td></td>
                    </tr>
                    @php
                        $bdpBbb  = $coaBdpBbb;
                        $bdpBtkl = $coaBdpBtkl;
                        $bdpBop  = $coaBdpBop;
                    @endphp
                    @if($produksi->total_bahan > 0)
                    <tr><td class="ps-5 text-muted">BDP - BBB</td><td><span class="badge bg-secondary me-1">{{ $bdpBbbKode }}</span>{{ $bdpBbbNama }}</td><td class="text-center text-muted" style="font-size:10px">{{ $bdpBbbKode }}</td><td></td><td class="text-end pe-3">Rp {{ number_format($produksi->total_bahan,0,',','.') }}</td></tr>
                    @endif
                    @if($produksi->total_btkl > 0)
                    <tr><td class="ps-5 text-muted">BDP - BTKL</td><td><span class="badge bg-secondary me-1">{{ $bdpBtklKode }}</span>{{ $bdpBtklNama }}</td><td class="text-center text-muted" style="font-size:10px">{{ $bdpBtklKode }}</td><td></td><td class="text-end pe-3">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</td></tr>
                    @endif
                    @if($produksi->total_bop > 0)
                    <tr><td class="ps-5 text-muted">BDP - BOP</td><td><span class="badge bg-secondary me-1">{{ $bdpBopKode }}</span>{{ $bdpBopNama }}</td><td class="text-center text-muted" style="font-size:10px">{{ $bdpBopKode }}</td><td></td><td class="text-end pe-3">Rp {{ number_format($produksi->total_bop,0,',','.') }}</td></tr>
                    @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>Detail Produksi
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.produksi.proses', $produksi->id) }}" class="btn btn-info btn-sm">
                <i class="fas fa-tasks me-1"></i>Kelola Proses
            </a>
            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Info Produksi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Produksi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="fw-bold">Produk:</label>
                    <p>{{ $produksi->produk->nama_produk }}</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Tanggal:</label>
                    <p>{{ \Carbon\Carbon::parse($produksi->tanggal)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Qty Produksi:</label>
                    <p>{{ rtrim(rtrim(number_format($produksi->qty_produksi,4,',','.'),'0'),',') }} pcs</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Status:</label>
                    <p>{!! $produksi->status_badge !!}</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-3">
                <label class="fw-bold">Progress Produksi:</label>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped" 
                         role="progressbar" 
                         style="width: {{ $produksi->progress_percentage }}%"
                         aria-valuenow="{{ $produksi->progress_percentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ $produksi->proses_selesai }}/{{ $produksi->total_proses }} Proses ({{ $produksi->progress_percentage }}%)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Biaya -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Bahan</h6>
                            <h4 class="mb-0 text-success">Rp {{ number_format($produksi->total_bahan,0,',','.') }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-boxes fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">BTKL</h6>
                            <h4 class="mb-0 text-warning">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-users fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">BOP</h6>
                            <h4 class="mb-0 text-info">Rp {{ number_format($produksi->total_bop,0,',','.') }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-cogs fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Biaya</h6>
                            <h4 class="mb-0 text-primary">Rp {{ number_format($produksi->total_biaya,0,',','.') }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-calculator fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bahan Terpakai -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                @if($produksi->status === 'draft')
                    Rencana Bahan (Belum Terpakai)
                @else
                    Bahan Terpakai
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Bahan</th>
                            <th>Resep (Total)</th>
                            <th>Konversi ke Satuan Bahan</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($produksi->status === 'draft' && isset($produksi->bomBreakdown))
                            {{-- Show BOM breakdown for draft status --}}
                            @php $counter = 1; @endphp
                            @foreach($produksi->bomBreakdown['biaya_bahan']['bahan_baku'] as $bahan)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td>
                                        {{ $bahan['nama'] }}
                                        <small class="text-muted">(Bahan Baku)</small>
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($bahan['qty_resep'],4,',','.'),'0'),',') }} {{ $bahan['satuan_resep'] }}</td>
                                    <td>
                                        {{ rtrim(rtrim(number_format($bahan['qty_konversi'],4,',','.'),'0'),',') }} {{ $bahan['satuan_bahan'] }}
                                        @if($bahan['satuan_resep'] !== $bahan['satuan_bahan'])
                                            <br><small class="text-info">{{ $bahan['konversi_info'] ?? 'Konversi: ' . $bahan['satuan_resep'] . ' → ' . $bahan['satuan_bahan'] }}</small>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($bahan['harga_satuan'],0,',','.') }} / {{ $bahan['satuan_resep'] }}</td>
                                    <td>Rp {{ number_format($bahan['subtotal'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                            @foreach($produksi->bomBreakdown['biaya_bahan']['bahan_pendukung'] as $bahan)
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td>
                                        {{ $bahan['nama'] }}
                                        <small class="text-muted">(Bahan Pendukung)</small>
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($bahan['qty_resep'],4,',','.'),'0'),',') }} {{ $bahan['satuan_resep'] }}</td>
                                    <td>
                                        {{ rtrim(rtrim(number_format($bahan['qty_konversi'],4,',','.'),'0'),',') }} {{ $bahan['satuan_bahan'] }}
                                        @if($bahan['satuan_resep'] !== $bahan['satuan_bahan'])
                                            <br><small class="text-info">Konversi: {{ $bahan['satuan_resep'] }} → {{ $bahan['satuan_bahan'] }}</small>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($bahan['harga_satuan'],0,',','.') }} / {{ $bahan['satuan_resep'] }}</td>
                                    <td>Rp {{ number_format($bahan['subtotal'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Show actual consumed materials for completed production --}}
                            @foreach($produksi->details as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($d->bahan_baku_id && $d->bahanBaku)
                                            {{ $d->bahanBaku->nama_bahan }}
                                            <small class="text-muted">(Bahan Baku)</small>
                                        @elseif($d->bahan_pendukung_id && $d->bahanPendukung)
                                            {{ $d->bahanPendukung->nama_bahan }}
                                            <small class="text-muted">(Bahan Pendukung)</small>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',') }} {{ $d->satuan_resep }}</td>
                                    <td>{{ rtrim(rtrim(number_format($d->qty_konversi_display ?? $d->qty_konversi,4,',','.'),'0'),',') }} 
                                    @php
                                        // Use the calculated display unit
                                        $satuanKonversi = $d->satuan_bahan_display ?? ($d->satuan ?? 'unit');
                                    @endphp
                                    {{ $satuanKonversi }}
                                    @if($d->satuan_resep !== $satuanKonversi)
                                        <br><small class="text-info">Konversi: {{ $d->satuan_resep }} → {{ $satuanKonversi }}</small>
                                    @endif</td>
                                    <td>Rp {{ number_format($d->harga_satuan,0,',','.') }} / 
                                    @php
                                        // Untuk harga satuan, gunakan satuan resep
                                        $satuanHarga = $d->satuan_resep ?? 'unit';
                                    @endphp
                                    {{ $satuanHarga }}</td>
                                    <td>Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan:</td>
                            <td class="fw-bold">Rp {{ number_format($produksi->total_bahan,0,',','.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

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
                            <th>#</th>
                            <th>Nama Proses</th>
                            @if($produksi->status === 'draft')
                                <th>Biaya per Unit</th>
                                <th>Total Biaya</th>
                            @else
                                <th>Status</th>
                                <th>Biaya BTKL</th>
                                <th>Waktu Mulai</th>
                                <th>Waktu Selesai</th>
                                <th>Durasi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if($produksi->status === 'draft' && isset($produksi->bomBreakdown))
                            {{-- Show planned BTKL for draft status --}}
                            @foreach($produksi->bomBreakdown['btkl'] as $btkl)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $btkl['nama'] }}</td>
                                    <td>Rp {{ number_format($btkl['biaya_per_unit'],0,',','.') }}</td>
                                    <td>Rp {{ number_format($btkl['total_biaya'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Show actual BTKL process for completed production --}}
                            @foreach($produksi->proses->sortBy('urutan') as $proses)
                                <tr>
                                    <td>{{ $proses->urutan }}</td>
                                    <td>{{ $proses->nama_proses }}</td>
                                    <td>{!! $proses->status_badge !!}</td>
                                    <td>Rp {{ number_format($proses->biaya_btkl,0,',','.') }}</td>
                                    <td>
                                        @if($proses->waktu_mulai)
                                            {{ $proses->waktu_mulai->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($proses->waktu_selesai)
                                            {{ $proses->waktu_selesai->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($proses->durasi_menit)
                                            {{ $proses->durasi_menit }} menit
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            @if($produksi->status === 'draft')
                                <td colspan="3" class="text-end fw-bold">Total BTKL:</td>
                            @else
                                <td colspan="3" class="text-end fw-bold">Total BTKL:</td>
                            @endif
                            <td class="fw-bold">Rp {{ number_format($produksi->total_btkl,0,',','.') }}</td>
                            @if($produksi->status !== 'draft')
                                <td colspan="3"></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- BOP Detail -->
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
                            <th>#</th>
                            <th>Nama Proses</th>
                            @if($produksi->status === 'draft')
                                <th>Biaya per Unit</th>
                                <th>Total Biaya</th>
                            @else
                                <th>Status</th>
                                <th>Biaya BOP</th>
                                <th>Total Biaya Proses</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if($produksi->status === 'draft' && isset($produksi->bomBreakdown))
                            {{-- Show planned BOP for draft status --}}
                            @foreach($produksi->bomBreakdown['bop'] as $bop)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $bop['nama'] }}</td>
                                    <td>Rp {{ number_format($bop['biaya_per_unit'],0,',','.') }}</td>
                                    <td>Rp {{ number_format($bop['total_biaya'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Show actual BOP process for completed production --}}
                            @foreach($produksi->proses->sortBy('urutan') as $proses)
                                <tr>
                                    <td>{{ $proses->urutan }}</td>
                                    <td>{{ $proses->nama_proses }}</td>
                                    <td>{!! $proses->status_badge !!}</td>
                                    <td>Rp {{ number_format($proses->biaya_bop,0,',','.') }}</td>
                                    <td>Rp {{ number_format($proses->total_biaya_proses,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            @if($produksi->status === 'draft')
                                <td colspan="3" class="text-end fw-bold">Total BOP:</td>
                                <td class="fw-bold">Rp {{ number_format($produksi->total_bop,0,',','.') }}</td>
                            @else
                                <td colspan="3" class="text-end fw-bold">Total BOP:</td>
                                <td class="fw-bold">Rp {{ number_format($produksi->total_bop,0,',','.') }}</td>
                                <td class="fw-bold">Rp {{ number_format($produksi->total_btkl + $produksi->total_bop,0,',','.') }}</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Jurnal -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Jurnal Akuntansi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-grid">
                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_material', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-success">
                            <i class="fas fa-boxes me-2"></i>Jurnal Material → WIP
                        </a>
                        <small class="text-muted mt-1">Konsumsi bahan dengan COA individual per material ke Barang Dalam Proses</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_labor_overhead', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-warning">
                            <i class="fas fa-users me-2"></i>Jurnal BTKL & BOP → WIP
                        </a>
                        <small class="text-muted mt-1">BTKL & BOP ke Barang Dalam Proses</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_finish', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-check-circle me-2"></i>Jurnal WIP → Barang Jadi
                        </a>
                        <small class="text-muted mt-1">Selesai produksi ke Barang Jadi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left {
    border-left: 4px solid !important;
}
</style>
@endsection

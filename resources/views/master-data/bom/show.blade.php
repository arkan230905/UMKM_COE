@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detail BOM: {{ $bom->produk->nama_produk }} - Process Costing</h3>
        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Informasi Dasar -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Nama Produk</th>
                            <td>{{ $bom->produk->nama_produk }}</td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td>{{ $bom->periode ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td>{{ $bom->created_at->format('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Biaya Bahan Baku (BBB) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-boxes"></i> 1. Biaya Bahan Baku (BBB)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $no = 1; 
                            $totalBBB = 0;
                            $converter = new \App\Support\UnitConverter();
                        @endphp
                        @foreach($bom->details as $detail)
                            @php
                                $bahanBaku = $detail->bahanBaku;
                                if ($bahanBaku) {
                                    $hargaTerbaru = $bahanBaku->harga_satuan ?? 0;
                                    $satuanBase = is_object($bahanBaku->satuan) 
                                        ? $bahanBaku->satuan->nama 
                                        : ($bahanBaku->satuan ?? 'unit');
                                    
                                    try {
                                        $qtyBase = $converter->convert(
                                            (float) $detail->jumlah,
                                            $detail->satuan ?: $satuanBase,
                                            $satuanBase
                                        );
                                        $subtotal = $hargaTerbaru * $qtyBase;
                                    } catch (\Exception $e) {
                                        $subtotal = $hargaTerbaru * $detail->jumlah;
                                    }
                                    
                                    $totalBBB += $subtotal;
                                } else {
                                    $hargaTerbaru = 0;
                                    $subtotal = 0;
                                }
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $bahanBaku->nama_bahan ?? 'Bahan Tidak Ditemukan' }}</td>
                                <td class="text-end">{{ number_format($detail->jumlah, 2, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->satuan }}</td>
                                <td class="text-end">Rp {{ number_format($hargaTerbaru, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Baku (BBB)</td>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 2: Biaya Bahan Pendukung -->
    @php
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)
            ->with(['detailBahanPendukung.bahanPendukung.satuan'])
            ->first();
        $totalBahanPendukung = 0;
    @endphp
    
    @if($bomJobCosting && $bomJobCosting->detailBahanPendukung->count() > 0)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-cubes"></i> 2. Biaya Bahan Pendukung</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Bahan Pendukung</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $noPendukung = 1; @endphp
                        @foreach($bomJobCosting->detailBahanPendukung as $detailPendukung)
                            @php
                                $bahanPendukung = $detailPendukung->bahanPendukung;
                                if ($bahanPendukung) {
                                    $hargaTerbaru = $bahanPendukung->harga_satuan ?? 0;
                                    $subtotal = $detailPendukung->jumlah * $hargaTerbaru;
                                    $totalBahanPendukung += $subtotal;
                                } else {
                                    $subtotal = 0;
                                }
                            @endphp
                            <tr>
                                <td>{{ $noPendukung++ }}</td>
                                <td>{{ $bahanPendukung->nama_bahan ?? 'Bahan Tidak Ditemukan' }}</td>
                                <td class="text-end">{{ number_format($detailPendukung->jumlah, 2, ',', '.') }}</td>
                                <td class="text-center">{{ $detailPendukung->satuan }}</td>
                                <td class="text-end">Rp {{ number_format($hargaTerbaru, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Pendukung</td>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBahanPendukung, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Section 3: Proses Produksi (BTKL + BOP) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-cogs"></i> 3. Proses Produksi (BTKL + BOP)</h5>
        </div>
        <div class="card-body">
            @php
                $hasProses = $bom->proses && $bom->proses->count() > 0;
                $totalBTKL = 0;
                $totalBOP = 0;
                
                if (!empty($btklData)) {
                    $totalBTKL = collect($btklData)->sum('biaya_per_produk');
                } elseif ($bomJobCosting) {
                    $totalBTKL = $bomJobCosting->total_btkl ?? 0;
                    $totalBOP = $bomJobCosting->total_bop ?? 0;
                }
            @endphp
            
            <!-- Tabel BTKL dengan data yang benar -->
            @if(!empty($btklData))
                <h6 class="mb-3"><i class="fas fa-user-clock me-2"></i>Biaya Tenaga Kerja Langsung (BTKL)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Proses</th>
                                <th width="15%">Tarif/Jam</th>
                                <th width="15%">Kapasitas/Jam</th>
                                <th width="15%">Biaya/Unit</th>
                                <th width="15%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $noBtkl = 1; @endphp
                            @foreach($btklData as $btkl)
                                <tr>
                                    <td class="text-center">{{ $noBtkl++ }}</td>
                                    <td>
                                        {{ $btkl['nama_proses'] }}
                                        <small class="text-muted d-block">
                                            {{ number_format($btkl['durasi_jam'], 2) }} jam × Rp {{ number_format($btkl['tarif_per_jam'], 0, ',', '.') }}/jam
                                        </small>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($btkl['tarif_per_jam'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($btkl['kapasitas_per_jam'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($btkl['biaya_per_produk'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($btkl['subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="5" class="text-end fw-bold">Total BTKL</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    Data BTKL tidak tersedia untuk produk ini.
                </div>
            @endif
        </div>
    </div>

    <!-- Section 4: Summary HPP -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> 4. Summary HPP</h5>
        </div>
        <div class="card-body">
            <div class="col-md-8 offset-md-2">
                @php
                    $totalBiayaBahan = $totalBBB + $totalBahanPendukung;
                    $hpp = $totalBiayaBahan + $totalBTKL + $totalBOP;
                    $persenBiayaBahan = $hpp > 0 ? ($totalBiayaBahan / $hpp) * 100 : 0;
                    $persenBBB = $hpp > 0 ? ($totalBBB / $hpp) * 100 : 0;
                    $persenBahanPendukung = $hpp > 0 ? ($totalBahanPendukung / $hpp) * 100 : 0;
                    $persenBTKL = $hpp > 0 ? ($totalBTKL / $hpp) * 100 : 0;
                    $persenBOP = $hpp > 0 ? ($totalBOP / $hpp) * 100 : 0;
                @endphp
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Komponen Biaya</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-light">
                            <th>Total Biaya Bahan Baku</th>
                            <td class="text-end">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBBB, 1, ',', '.') }}%</td>
                        </tr>
                        @if($totalBahanPendukung > 0)
                        <tr class="table-light">
                            <th>Total Biaya Bahan Pendukung</th>
                            <td class="text-end">Rp {{ number_format($totalBahanPendukung, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBahanPendukung, 1, ',', '.') }}%</td>
                        </tr>
                        @endif
                        <tr class="table-warning">
                            <th>Total Biaya Bahan (BBB + Pendukung)</th>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBiayaBahan, 1, ',', '.') }}%</td>
                        </tr>
                        <tr class="table-info">
                            <th>Total BTKL</th>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBTKL, 1, ',', '.') }}%</td>
                        </tr>
                        @if($totalBOP > 0)
                        <tr class="table-secondary">
                            <th>Total BOP</th>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBOP, 1, ',', '.') }}%</td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <th class="fs-5">Total HPP</th>
                            <td class="text-end fw-bold fs-5">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('master-data.bom.edit', $bom->id) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit BOM
        </a>
        <form action="{{ route('master-data.bom.destroy', $bom->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus BOM ini?')">
                <i class="bi bi-trash"></i> Hapus BOM
            </button>
        </form>
    </div>
</div>
@endsection

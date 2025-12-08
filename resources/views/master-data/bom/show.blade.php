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
                        @php $no = 1; $totalBBB = 0; @endphp
                        @foreach($bom->details as $detail)
                            @php $totalBBB += $detail->total_harga; @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $detail->bahanBaku->nama_bahan ?? 'Bahan Tidak Ditemukan' }}</td>
                                <td class="text-end">{{ number_format($detail->jumlah, 2, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->satuan }}</td>
                                <td class="text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
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

    <!-- Section 2: Proses Produksi (BTKL + BOP) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-cogs"></i> 2. Proses Produksi (BTKL + BOP)</h5>
        </div>
        <div class="card-body">
            @if($bom->proses && $bom->proses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Proses</th>
                                <th width="12%">Durasi</th>
                                <th width="8%">Satuan</th>
                                <th width="15%">Biaya BTKL</th>
                                <th width="15%">Biaya BOP</th>
                                <th width="15%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalBTKL = 0; $totalBOP = 0; @endphp
                            @foreach($bom->proses as $proses)
                                @php 
                                    $totalBTKL += $proses->biaya_btkl;
                                    $totalBOP += $proses->biaya_bop;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $proses->urutan }}</td>
                                    <td>
                                        {{ $proses->prosesProduksi->nama_proses ?? '-' }}
                                        <div class="text-muted small">
                                            Tarif: Rp {{ number_format($proses->prosesProduksi->tarif_btkl ?? 0, 0, ',', '.') }}/{{ $proses->satuan_durasi }}
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($proses->durasi, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $proses->satuan_durasi }}</td>
                                    <td class="text-end">Rp {{ number_format($proses->biaya_btkl, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($proses->biaya_bop, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($proses->biaya_btkl + $proses->biaya_bop, 0, ',', '.') }}</td>
                                </tr>
                                
                                <!-- Detail BOP per Proses -->
                                @if($proses->bomProsesBops && $proses->bomProsesBops->count() > 0)
                                    <tr class="table-light">
                                        <td></td>
                                        <td colspan="6">
                                            <small class="text-muted">Detail BOP:</small>
                                            <ul class="mb-0 small">
                                                @foreach($proses->bomProsesBops as $bop)
                                                    <li>
                                                        {{ $bop->komponenBop->nama_komponen ?? '-' }}: 
                                                        {{ number_format($bop->kuantitas, 2, ',', '.') }} × 
                                                        Rp {{ number_format($bop->tarif, 0, ',', '.') }} = 
                                                        Rp {{ number_format($bop->total_biaya, 0, ',', '.') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4" class="text-end fw-bold">Total BTKL</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="table-info">
                                <td colspan="5" class="text-end fw-bold">Total BOP</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i> 
                    BOM ini menggunakan perhitungan persentase (BTKL 60%, BOP 40%) karena belum ada proses produksi yang didefinisikan.
                    <br>
                    <small>BTKL: Rp {{ number_format($bom->total_btkl, 0, ',', '.') }} | BOP: Rp {{ number_format($bom->total_bop, 0, ',', '.') }}</small>
                </div>
                @php $totalBTKL = $bom->total_btkl; $totalBOP = $bom->total_bop; @endphp
            @endif
        </div>
    </div>

    <!-- Section 3: Ringkasan HPP -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> 3. Ringkasan Harga Pokok Produksi (HPP)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    @php
                        $hpp = $totalBBB + $totalBTKL + $totalBOP;
                        $persenBBB = $hpp > 0 ? ($totalBBB / $hpp) * 100 : 0;
                        $persenBTKL = $hpp > 0 ? ($totalBTKL / $hpp) * 100 : 0;
                        $persenBOP = $hpp > 0 ? ($totalBOP / $hpp) * 100 : 0;
                    @endphp
                    <table class="table table-bordered">
                        <tr>
                            <th width="50%">Total Biaya Bahan Baku (BBB)</th>
                            <td class="text-end">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBBB, 1, ',', '.') }}%</td>
                        </tr>
                        <tr>
                            <th>Total Biaya Tenaga Kerja Langsung (BTKL)</th>
                            <td class="text-end">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBTKL, 1, ',', '.') }}%</td>
                        </tr>
                        <tr>
                            <th>Total Biaya Overhead Pabrik (BOP)</th>
                            <td class="text-end">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">{{ number_format($persenBOP, 1, ',', '.') }}%</td>
                        </tr>
                        <tr class="table-success">
                            <th class="fs-5">HARGA POKOK PRODUKSI (HPP)</th>
                            <td class="text-end fw-bold fs-5">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">100%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between">
        <div>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('master-data.bom.edit', $bom->id) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Edit BOM
            </a>
            <a href="{{ route('master-data.bom.print', $bom->id) }}" class="btn btn-info me-2" target="_blank">
                <i class="bi bi-printer"></i> Cetak
            </a>
        </div>
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

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail BOM: {{ $bom->produk->nama_produk }}</h1>
        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">Kembali ke Daftar BOM</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Dasar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Kode BOM</th>
                            <td>{{ $bom->kode_bom }}</td>
                        </tr>
                        <tr>
                            <th>Nama Produk</th>
                            <td>{{ $bom->produk->nama_produk }}</td>
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

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Rincian Bahan Baku</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Kuantitas</th>
                            <th class="text-end">Satuan</th>
                            <th class="text-end">Harga Satuan (Rp)</th>
                            <th class="text-end">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @php
                            $totalBiayaBahanBaku = 0;
                        @endphp
                        @foreach($bom->details as $detail)
                            @php
                                // Harga per KG
                                $hargaPerKg = $detail->harga_per_satuan;
                                
                                // Hitung harga per GR (harga per KG / 1000)
                                $hargaPerGr = $hargaPerKg / 1000;
                                
                                // Hitung subtotal berdasarkan satuan
                                if (strtoupper($detail->satuan) === 'GR') {
                                    $subtotal = $hargaPerGr * $detail->jumlah;
                                } else {
                                    // Untuk KG atau satuan lain, asumsikan sudah dalam KG
                                    $subtotal = $hargaPerKg * $detail->jumlah;
                                }
                                
                                // Akumulasi total biaya bahan baku
                                $totalBiayaBahanBaku += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>
                                    {{ $detail->bahanBaku->nama_bahan ?? $detail->bahanBaku->nama ?? 'Bahan Tidak Ditemukan' }}
                                    <div class="text-muted small">
                                        @if(strtoupper($detail->satuan) === 'GR')
                                            {{ number_format($detail->jumlah / 1000, 3, ',', '.') }} KG
                                        @else
                                            {{ $detail->jumlah }} {{ $detail->satuan }}
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detail->satuan }}</td>
                                <td class="text-end">
                                    @if(strtoupper($detail->satuan) === 'GR')
                                        {{ number_format($hargaPerGr, 0, ',', '.') }}
                                    @else
                                        {{ number_format($hargaPerKg, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Baku</td>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Perhitungan Biaya Produksi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    @php
                        // Hitung total biaya bahan baku dari detail
                        $totalBiayaBahanBaku = 0;
                        foreach($bom->details as $detail) {
                            $hargaPerKg = $detail->harga_per_satuan;
                            $hargaPerGr = $hargaPerKg / 1000;
                            $totalBiayaBahanBaku += (strtoupper($detail->satuan) === 'GR') 
                                ? $hargaPerGr * $detail->jumlah 
                                : $hargaPerKg * $detail->jumlah;
                        }
                        
                        // Hitung BTKL (60% dari total biaya bahan baku)
                        $btkl = $totalBiayaBahanBaku * 0.6;
                        
                        // Hitung BOP (40% dari total biaya bahan baku)
                        $bop = $totalBiayaBahanBaku * 0.4;
                        
                        // Hitung total biaya produksi
                        $totalBiayaProduksi = $totalBiayaBahanBaku + $btkl + $bop;
                    @endphp
                    
                    <table class="table table-bordered">
                        <tr>
                            <th width="60%">1. Total Biaya Bahan Baku</th>
                            <td class="text-end">Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>2. Biaya Tenaga Kerja Langsung (BTKL)</th>
                            <td class="text-end">Rp {{ number_format($btkl, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>3. Biaya Overhead Pabrik (BOP)</th>
                            <td class="text-end">
                                Rp {{ number_format($bop, 0, ',', '.') }}
                                <div class="text-muted small">
                                    BOP Rate: {{ $btkl > 0 ? number_format(($bop / $btkl) * 100, 2, ',', '.') : '0' }}% dari BTKL
                                </div>
                            </td>
                        </tr>
                        <tr class="table-active">
                            <th>Total Biaya Produksi</th>
                            <td class="text-end fw-bold">Rp {{ number_format($totalBiayaProduksi, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <div>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('master-data.bom.edit', $bom->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-1"></i> Edit BOM
            </a>
            <a href="{{ route('master-data.bom.print', $bom->id) }}" class="btn btn-info me-2" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak
            </a>
        </div>
        <form action="{{ route('master-data.bom.destroy', $bom->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus BOM ini?')">
                <i class="fas fa-trash me-1"></i> Hapus BOM
            </button>
        </form>
    </div>
</div>
@endsection

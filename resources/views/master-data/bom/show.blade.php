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

    @php
        // Get BomJobCosting for data bahan pendukung
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)
            ->with(['detailBahanPendukung.bahanPendukung.satuan'])
            ->first();
    @endphp
    
    <!-- Materials Used in Product -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Bahan yang Digunakan dalam Produk
            </h6>
        </div>
        <div class="card-body">
            @php
                // Prepare data like in biaya-bahan controller
                $detailBahanBaku = [];
                if ($bom->details) {
                    $detailBahanBaku = $bom->details->map(function($detail) {
                        $bahanBaku = $detail->bahanBaku;
                        return [
                            'id' => $detail->id,
                            'nama_bahan' => $bahanBaku->nama_bahan ?? 'Unknown',
                            'qty' => $detail->jumlah ?? 0,
                            'satuan' => $detail->satuan ?? 'unit',
                            'harga_satuan' => $bahanBaku->harga_satuan ?? 0,
                            'subtotal' => $detail->total_harga ?? 0,
                            'tipe' => 'Bahan Baku'
                        ];
                    })->toArray() ?? [];
                }
                
                $detailBahanPendukung = [];
                if ($bomJobCosting && $bomJobCosting->detailBahanPendukung) {
                    $detailBahanPendukung = $bomJobCosting->detailBahanPendukung->map(function($detail) {
                        $bahanPendukung = $detail->bahanPendukung;
                        return [
                            'id' => $detail->id,
                            'nama_bahan' => $bahanPendukung->nama_bahan ?? 'Unknown',
                            'qty' => $detail->jumlah ?? 0,
                            'satuan' => $detail->satuan ?? 'unit',
                            'harga_satuan' => $detail->harga_satuan ?? 0,
                            'subtotal' => $detail->subtotal ?? 0,
                            'tipe' => 'Bahan Pendukung'
                        ];
                    })->toArray() ?? [];
                }
                
                $allDetails = array_merge($detailBahanBaku, $detailBahanPendukung);
                $totalBBB = array_sum(array_column($detailBahanBaku, 'subtotal'));
                $totalBahanPendukung = array_sum(array_column($detailBahanPendukung, 'subtotal'));
                $totalBiayaBahan = $totalBBB + $totalBahanPendukung;
            @endphp
            
            <!-- Bahan Baku Section -->
            @if($detailBahanBaku && count($detailBahanBaku) > 0)
                <div class="mb-4">
                    <h6 class="text-info mb-3">
                        <i class="fas fa-cube me-2"></i>Bahan Baku ({{ count($detailBahanBaku) }} item)
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Nama Bahan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailBahanBaku as $index => $bahan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $bahan['nama_bahan'] }}</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ number_format($bahan['qty'], 2, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            {{ $bahan['satuan'] }}
                                        </td>
                                        <td class="text-center">
                                            {{ $bahan['satuan'] }}
                                        </td>
                                        <td class="text-end">
                                            Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <strong>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                
                <!-- Bahan Pendukung Section -->
                @if($detailBahanPendukung && count($detailBahanPendukung) > 0)
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-flask me-2"></i>Bahan Pendukung ({{ count($detailBahanPendukung) }} item)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detailBahanPendukung as $index => $bahan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $bahan['nama_bahan'] }}</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($bahan['qty'], 2, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-center">
                                                {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-end">
                                                Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-end">
                                                <strong>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                <!-- Summary Section -->
                <div class="alert alert-light">
                    <h6 class="alert-heading">Ringkasan Biaya Bahan untuk Produk</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Total Bahan Baku:</strong><br>
                                <span class="text-info fs-5">Rp {{ number_format($totalBBB, 0, ',', '.') }}</span>
                                <br><small class="text-muted">{{ $detailBahanBaku ? count($detailBahanBaku) : 0 }} item</small>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Total Bahan Pendukung:</strong><br>
                                <span class="text-warning fs-5">Rp {{ number_format($totalBahanPendukung, 0, ',', '.') }}</span>
                                <br><small class="text-muted">{{ $detailBahanPendukung ? count($detailBahanPendukung) : 0 }} item</small>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Total Biaya Bahan:</strong><br>
                                <span class="text-success fs-5">Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}</span>
                                <br><small class="text-muted">{{ $allDetails ? count($allDetails) : 0 }} item total</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Total biaya bahan yang digunakan untuk memproduksi <strong>{{ $bom->produk->nama_produk }}</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Proses Produksi (BTKL) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-cogs"></i> Proses Produksi (BTKL)</h5>
        </div>
        <div class="card-body">
            @php
                // Get real-time data dari tabel btkls (sama seperti di halaman BTKL)
                $btklData = [];
                if ($bomJobCosting) {
                    $btklData = \Illuminate\Support\Facades\DB::table('bom_job_btkl')
                        ->join('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                        ->join('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                        ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                        ->select(
                            'bom_job_btkl.*', 
                            'btkls.kode_proses',
                            'btkls.nama_btkl',
                            'btkls.tarif_per_jam', 
                            'btkls.kapasitas_per_jam',
                            'btkls.satuan',
                            'btkls.deskripsi_proses',
                            'jabatans.nama as nama_jabatan',
                            'jabatans.kategori'
                        )
                        ->get()
                        ->map(function($item) {
                            // Hitung jumlah pegawai real-time
                            $jumlahPegawai = \Illuminate\Support\Facades\DB::table('pegawais')
                                ->where('jabatan', $item->nama_jabatan)
                                ->count();
                            
                            // Hitung biaya per produk real-time
                            $biayaPerProduk = $item->kapasitas_per_jam > 0 ? $item->tarif_per_jam / $item->kapasitas_per_jam : 0;
                            $subtotal = $biayaPerProduk * $item->durasi_jam;
                            
                            return [
                                'id' => $item->id,
                                'kode_proses' => $item->kode_proses,
                                'nama_btkl' => $item->nama_btkl,
                                'nama_jabatan' => $item->nama_jabatan,
                                'kategori' => $item->kategori,
                                'jumlah_pegawai' => $jumlahPegawai,
                                'tarif_per_jam' => $item->tarif_per_jam,
                                'satuan' => $item->satuan,
                                'kapasitas_per_jam' => $item->kapasitas_per_jam,
                                'biaya_per_produk' => $biayaPerProduk,
                                'tarif_per_jam_formatted' => 'Rp ' . number_format($item->tarif_per_jam, 0, ',', '.'),
                                'biaya_per_produk_formatted' => 'Rp ' . number_format($biayaPerProduk, 2, ',', '.'),
                                'deskripsi_proses' => $item->deskripsi_proses,
                                'durasi_jam' => $item->durasi_jam,
                                'subtotal' => $subtotal
                            ];
                        });
                }
                
                $totalBTKL = 0;
                if (!empty($btklData)) {
                    $totalBTKL = $btklData->sum('subtotal');
                }
            @endphp
            
            <!-- Tabel BTKL dengan struktur sama seperti halaman BTKL -->
            @if(!empty($btklData))
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0 table-wide">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 8%">Kode</th>
                                <th style="width: 15%">Nama Proses</th>
                                <th style="width: 15%">Jabatan BTKL</th>
                                <th style="width: 10%">Jumlah Pegawai</th>
                                <th style="width: 12%">Tarif BTKL</th>
                                <th style="width: 8%">Satuan</th>
                                <th style="width: 12%">Kapasitas/Jam</th>
                                <th style="width: 12%">Biaya Per Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($btklData as $btkl)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $btkl['kode_proses'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-gear-fill me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-bold">{{ $btkl['nama_btkl'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-workspace me-2 text-info"></i>
                                            <div>
                                                <div class="fw-bold">{{ $btkl['nama_jabatan'] }}</div>
                                                <small class="text-muted">{{ $btkl['kategori'] ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people-fill me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-bold text-primary">{{ $btkl['jumlah_pegawai'] }} orang</div>
                                                <small class="text-muted">{{ $btkl['nama_jabatan'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-stack me-2 text-success"></i>
                                            <div>
                                                <div class="fw-bold text-success">Rp {{ number_format($btkl['tarif_per_jam'], 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $btkl['satuan'] }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($btkl['kapasitas_per_jam']) }} pcs</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calculator me-2 text-warning"></i>
                                            <div>
                                                <div class="fw-bold text-warning">Rp {{ number_format($btkl['biaya_per_produk'], 2, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Total BTKL -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><strong>Total BTKL</strong></h6>
                                    <h5 class="mb-0 text-success">Rp {{ number_format($totalBTKL, 2, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-4 d-block mb-2 text-muted"></i>
                    <p class="text-muted">Belum ada data proses produksi (BTKL)</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Section 4: Biaya Overhead Produksi (BOP) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> 4. Biaya Overhead Produksi (BOP)</h5>
        </div>
        <div class="card-body">
            @php
                $totalBOP = 0;
                if ($bomJobCosting) {
                    $totalBOP = $bomJobCosting->total_bop ?? 0;
                }
            @endphp
            
            @if($bomJobCosting && $bomJobCosting->bomProsesBops && $bomJobCosting->bomProsesBops->count() > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0 table-wide">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Komponen BOP</th>
                                <th width="20%">Jumlah</th>
                                <th width="15%">Satuan</th>
                                <th width="15%">Harga Satuan</th>
                                <th width="20%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $noBop = 1; @endphp
                            @foreach($bomJobCosting->bomProsesBops as $bop)
                                <tr>
                                    <td class="text-center">{{ $noBop++ }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-briefcase-fill me-2 text-warning"></i>
                                            <div>
                                                <div class="fw-bold">{{ $bop->komponenBop->nama_komponen }}</div>
                                                <small class="text-muted">{{ $bop->komponenBop->keterangan }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($bop->jumlah, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $bop->satuan }}</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($bop->harga_satuan, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-stack me-2 text-success"></i>
                                            <div>
                                                <div class="fw-bold text-success">Rp {{ number_format($bop->subtotal, 2, ',', '.') }}</div>
                                                <small class="text-muted">Total</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Total BOP -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><strong>Total BOP</strong></h6>
                                    <h5 class="mb-0 text-warning">Rp {{ number_format($totalBOP, 2, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-4 d-block mb-2 text-muted"></i>
                    <p class="text-muted">Belum ada data Biaya Overhead Produksi (BOP)</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Section 5: Summary HPP -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> 5. Summary HPP</h5>
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
    <div class="d-flex justify-content-end">
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

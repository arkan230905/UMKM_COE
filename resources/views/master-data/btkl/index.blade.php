@section('content')
<div class="btkl-page">
    <div class="btkl-container">
        <div class="btkl-header">
            <div class="btkl-header-left">
                <h2>
                    <div class="icon-custom icon-gear">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    Daftar Proses Produksi (BTKL)
                </h2>
                <p>Kelola data biaya tenaga kerja langsung per unit produk</p>
            </div>
            <a href="{{ route('master-data.btkl.create') }}" class="btn btn-elegant btn-primary-elegant">
                <i class="bi bi-plus-lg me-2"></i> Tambah Proses
            </a>
        </div>

        <div class="btkl-card">
            <div class="table-responsive">
                <table class="table btkl-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10%">Kode</th>
                            <th style="width: 25%">Nama Proses</th>
                            <th style="width: 20%">Jabatan BTKL</th>
                            <th class="text-center" style="width: 15%">Jumlah Pegawai</th>
                            <th style="width: 15%">Tarif BTKL (Per Produk)</th>
                            <th style="width: 15%">Total Biaya Produk</th>
                            <th class="text-center" style="width: 5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($btkls as $btkl)
                        @php
                            // Mengambil jumlah pegawai dari data proses produksi
                            $jumlahPegawai = $btkl->jumlah_pegawai ?? 0;
                            $tarifPerProduk = $btkl->tarif_per_produk ?? 0;
                            // Rumus: Jumlah Pegawai x Tarif Per Produk
                            $totalBiayaUnit = $jumlahPegawai * $tarifPerProduk;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="badge-custom badge-kode">{{ $btkl->kode_proses }}</span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $btkl->nama_proses ?? '-' }}</div>
                                <small class="text-muted">Proses Produksi</small>
                            </td>
                            <td>
                                <div class="icon-wrapper">
                                    <div class="icon-custom icon-person">
                                        <i class="bi bi-person-workspace"></i>
                                    </div>
                                    <div class="fw-bold text-primary-custom">{{ $btkl->jabatan->nama ?? '-' }}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold">{{ $jumlahPegawai }} Orang</div>
                            </td>
                            <td>
                                <div class="fw-bold text-success-custom">
                                    Rp {{ number_format($tarifPerProduk, 0, ',', '.') }}
                                </div>
                                <small class="text-muted">(Pegawai x Tarif)</small>
                            </td>
                            <td>
                                <div class="fw-bold text-warning-custom">
                                    Rp {{ number_format($totalBiayaUnit, 0, ',', '.') }}
                                </div>
                                <small class="text-muted">(Pegawai x Tarif)</small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('master-data.btkl.edit', $btkl->id) }}" class="btn btn-sm btn-warning-elegant">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger-elegant" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $btkl->id }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">Belum ada data proses produksi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="btkl-card mt-4">
            <div class="p-4" style="background: linear-gradient(135deg, #FFFEF7 0%, #FFF8E1 100%);">
                <div class="row g-4 text-center">
                    <div class="col-md-4 border-end">
                        <h6 class="text-muted">Total Proses</h6>
                        <h4 class="fw-bold text-primary-custom">{{ $statistics['total_proses'] }}</h4>
                    </div>
                    <div class="col-md-4 border-end">
                        <h6 class="text-muted">Total Biaya BTKL/Unit</h6>
                        <h4 class="fw-bold text-warning-custom">Rp {{ number_format($statistics['total_biaya_per_produk'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Rata-rata Tarif</h6>
                        <h4 class="fw-bold text-success-custom">Rp {{ number_format($statistics['rata_rata_tarif'], 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
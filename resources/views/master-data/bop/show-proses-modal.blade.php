{{-- Clean and professional modal view for BOP detail --}}
@php
    $kapasitas = $bopProses->kapasitas_per_jam ?? 0;
    $totalBop = $bopProses->total_bop_per_jam ?? 0;
    $btklPerJam = $bopProses->prosesProduksi->tarif_btkl ?? 0;
    $btklPerPcs = $kapasitas > 0 ? $btklPerJam / $kapasitas : 0;
    $bopPerPcs = $kapasitas > 0 ? $totalBop / $kapasitas : 0;
    $biayaPerProduk = $bopProses->biaya_per_produk; // Gunakan accessor untuk konsistensi
    $biayaPerJam = $btklPerJam + $totalBop;
    
    $komponenBop = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
    if (!is_array($komponenBop)) $komponenBop = [];
@endphp

<div class="container-fluid p-0">
    <!-- Section: Informasi Proes -->
    <div class="mb-4">
        <h6 class="mb-3 text-muted">Informasi Proses</h6>
        <div class="row g-3">
            <div class="col-6">
                <div class="d-flex flex-column">
                    <small class="text-muted mb-1">Proses</small>
                    <strong class="fs-6">{{ $bopProses->prosesProduksi->nama_proses ?? 'N/A' }}</strong>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex flex-column">
                    <small class="text-muted mb-1">Kapasitas</small>
                    <strong class="fs-6">{{ $kapasitas }} pcs/jam</strong>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex flex-column">
                    <small class="text-muted mb-1">BTKL / jam</small>
                    <strong class="fs-6 text-primary">Rp {{ number_format($btklPerJam, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex flex-column">
                    <small class="text-muted mb-1">BTKL / pcs</small>
                    <strong class="fs-6 text-primary">Rp {{ number_format($btklPerPcs, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Tabel Komponen BOP -->
    @if(!empty($komponenBop))
    <div class="mb-4">
        <h6 class="mb-3 text-muted">Komponen BOP</h6>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 8%" class="text-center">No</th>
                        <th style="width: 42%">Komponen</th>
                        <th style="width: 25%" class="text-end">Rp / Jam</th>
                        <th style="width: 25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($komponenBop as $index => $komponen)
                        @php
                            $rate = floatval($komponen['rate_per_hour'] ?? 0);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $komponen['component'] ?? 'N/A' }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($rate, 0, ',', '.') }}</td>
                            <td>{{ $komponen['description'] ?? $komponen['keterangan'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">Total BOP / jam</td>
                        <td class="text-end text-success">Rp {{ number_format($totalBop, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="mb-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Belum ada komponen BOP yang didefinisikan untuk proses ini.
        </div>
    </div>
    @endif

    <!-- Section: Ringkasan Biaya -->
    <div class="mb-4">
        <h6 class="mb-3 text-muted">Ringkasan Biaya</h6>
        <div class="row g-3">
            <div class="col-6">
                <div class="card border-0 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Total BOP / jam</small>
                                <strong class="text-primary fs-5">Rp {{ number_format($totalBop, 0, ',', '.') }}</strong>
                            </div>
                            <i class="fas fa-chart-line text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">BOP / pcs</small>
                                <strong class="text-success fs-5">Rp {{ number_format($bopPerPcs, 0, ',', '.') }}</strong>
                            </div>
                            <i class="fas fa-box text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Biaya / produk</small>
                                <strong class="text-warning fs-5">Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}</strong>
                            </div>
                            <i class="fas fa-calculator text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Biaya / jam</small>
                                <strong class="text-danger fs-5">Rp {{ number_format($biayaPerJam, 0, ',', '.') }}</strong>
                            </div>
                            <i class="fas fa-clock text-danger opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Tombol Aksi -->
    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Tutup
        </button>
    </div>
</div>
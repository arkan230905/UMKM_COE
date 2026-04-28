@extends('layouts.app')

@section('title', 'Detail Harga Pokok Produksi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detail Harga Pokok Produksi: {{ $produk->nama_produk }}</h3>
        <div class="d-flex gap-2">
            <!-- Tombol Update dari Laporan Stok disembunyikan untuk presentasi -->
            {{-- 
            <button type="button" class="btn btn-warning" onclick="updateBomFromStockReport()" id="updateBomBtn">
                <i class="fas fa-sync-alt me-2"></i>Update dari Laporan Stok
            </button>
            --}}
            <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Informasi Dasar -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white border-bottom border-3 border-primary">
            <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Produk</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nama Produk:</th>
                            <td>{{ $produk->nama_produk }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi:</th>
                            <td>{{ $produk->deskripsi ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat:</th>
                            <td>{{ $bomJobCosting?->created_at->format('d F Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- BIAYA BAHAN -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white border-bottom border-3 border-success">
            <h5 class="mb-0 fw-bold"><i class="fas fa-cube me-2"></i>Biaya Bahan</h5>
        </div>
        <div class="card-body">
            
            <!-- Bahan Baku -->
            <h6 class="text-success mb-3"><i class="fas fa-box"></i> Bahan Baku</h6>
            @if($detailBahanBaku && count($detailBahanBaku) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-success">
                            <tr>
                                <th class="fw-bold"><i class="fas fa-leaf me-1"></i>Bahan Baku</th>
                                <th class="text-center fw-bold">Jumlah/Quantity</th>
                                <th class="text-center fw-bold">Satuan</th>
                                <th class="text-end fw-bold">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailBahanBaku as $bahan)
                                <tr>
                                    <td>{{ $bahan['nama_bahan'] }}</td>
                                    <td class="text-center">{{ number_format($bahan['qty'], 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $bahan['satuan'] }}</td>
                                    <td class="text-end">
                                        @if($bahan['subtotal'] > 0)
                                            Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Belum ada data bahan baku</div>
            @endif

            <!-- Total Biaya Bahan -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="card bg-light border-2">
                        <div class="card-body">
                            <h6 class="card-title text-primary fw-bold">
                                <i class="fas fa-calculator me-2"></i>Total Biaya Bahan
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="fw-semibold">Bahan Baku:</td>
                                    <td class="text-end data-value" id="total-bbb">
                                        @if($totalBBB > 0)
                                            Rp {{ number_format($totalBBB, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-top border-2 border-primary">
                                    <th class="fw-bold text-primary">SUBTOTAL:</th>
                                    <th class="text-end fw-bold text-primary fs-6 data-value" id="total-biaya-bahan">
                                        @if($totalBBB > 0)
                                            Rp {{ number_format($totalBBB, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BTKL -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white border-bottom border-3 border-info">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Daftar BTKL (Biaya Tenaga Kerja Langsung)</h5>
        </div>
        <div class="card-body">
            @if($btklDataForDisplay && count($btklDataForDisplay) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th class="fw-bold text-center" style="white-space: nowrap; vertical-align: middle;">NO</th>
                                <th class="fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-tag me-1"></i>Kode</th>
                                <th class="fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-cogs me-1"></i>Nama Proses</th>
                                <th class="fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-user-tie me-1"></i>Jabatan BTKL</th>
                                <th class="text-center fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-users me-1"></i>Jumlah Pegawai</th>
                                <th class="text-end fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-money-bill me-1"></i>Tarif BTKL</th>
                                <th class="text-center fw-bold" style="white-space: nowrap; vertical-align: middle;">Satuan</th>
                                <th class="text-center fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-tachometer-alt me-1"></i>Kapasitas/Jam</th>
                                <th class="text-end fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-calculator me-1"></i>Biaya per Produk</th>
                                <th class="fw-bold" style="white-space: nowrap; vertical-align: middle;"><i class="fas fa-info-circle me-1"></i>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($btklDataForDisplay as $index => $btkl)
                                <tr id="btkl-{{ $index }}">
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td>{{ $btkl['kode_proses'] ?? 'N/A' }}</td>
                                    <td>{{ $btkl['nama_proses'] ?? 'N/A' }}</td>
                                    <td>{{ $btkl['nama_jabatan'] ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $btkl['jumlah_pegawai'] ?? 0 }} pegawai @ Rp {{ number_format($btkl['tarif_per_jam'] ?? 0, 0, ',', '.') }}/jam</td>
                                    <td class="text-end tarif data-value">
                                        @php
                                            // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                                            $jumlahPegawai = $btkl['jumlah_pegawai'] ?? 0;
                                            $tarifPerJamJabatan = $btkl['tarif_per_jam'] ?? 0;
                                            $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                                        @endphp
                                        @if($tarifBtkl > 0)
                                            Rp {{ number_format($tarifBtkl, 0, ',', '.') }}
                                            <br>
                                            <small class="text-muted">per jam</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $btkl['satuan'] ?? 'Jam' }}</td>
                                    <td class="text-center">{{ number_format($btkl['kapasitas_per_jam'] ?? 0, 0, ',', '.') }} unit/jam</td>
                                    <td class="text-end subtotal data-value">
                                        @php
                                            // Calculate biaya per produk: Tarif BTKL ÷ Kapasitas per Jam
                                            $jumlahPegawai = $btkl['jumlah_pegawai'] ?? 0;
                                            $tarifPerJamJabatan = $btkl['tarif_per_jam'] ?? 0;
                                            $tarifBtkl = $jumlahPegawai * $tarifPerJamJabatan;
                                            $kapasitasPerJam = $btkl['kapasitas_per_jam'] ?? 1;
                                            $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                                        @endphp
                                        @if($biayaPerProduk > 0)
                                            Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}
                                            <br>
                                            <small class="text-muted">per unit</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>Proses {{ strtolower($btkl['nama_proses'] ?? '') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-warning">
                            <tr class="border-top border-2">
                                <th colspan="8" class="text-end fw-bold">Total Biaya Per Produk:</th>
                                <th class="text-end fw-bold fs-6 data-value" id="total-btkl">
                                    @if($totalBiayaBTKL > 0)
                                        Rp {{ number_format($totalBiayaBTKL, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Belum ada data BTKL</div>
            @endif
        </div>
    </div>

    <!-- BOP -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark border-bottom border-3 border-warning">
            <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i>BOP (Biaya Overhead Pabrik)</h5>
        </div>
        <div class="card-body">
            @if(!empty($bopData) && count($bopData) > 0)
                <!-- Display BOP Data -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th>Proses</th>
                                <th>Komponen BOP</th>
                                <th class="text-end">Tarif</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bopData as $bop)
                                <tr>
                                    <td>{{ $bop['nama_proses'] ?? '-' }}</td>
                                    <td>{{ $bop['nama_komponen'] ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($bop['tarif'] ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $bop['keterangan'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total BOP:</th>
                                <th class="text-end">Rp {{ number_format($totalBiayaBOP, 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Belum ada data BOP (Biaya Overhead Pabrik) untuk produk ini. 
                    Silakan tambahkan data BOP di menu <a href="{{ route('master-data.bop.index') }}" class="alert-link">Master Data BOP</a>.
                </div>
            @endif
        </div>
    </div>

    <!-- Penjumlahan Harga Pokok Produksi -->
    <div class="card shadow-lg border-primary border-3 mb-4">
        <div class="card-header bg-primary text-white border-bottom border-3 border-primary">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-calculator me-2"></i>PENJUMLAHAN HARGA POKOK PRODUKSI
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold" style="width: 50%;">KOMPONEN</th>
                            <th class="text-end fw-bold" style="width: 50%;">NOMINAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">
                                <i class="fas fa-box me-2 text-success"></i>BIAYA BAHAN
                            </td>
                            <td class="text-end fw-bold fs-5" id="total-biaya-bahan">
                                @if($totalBiayaBahan > 0)
                                    Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">
                                <i class="fas fa-users me-2 text-info"></i>BTKL
                            </td>
                            <td class="text-end fw-bold fs-5" id="total-btkl">
                                @if($totalBiayaBTKL > 0)
                                    Rp {{ number_format($totalBiayaBTKL, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">
                                <i class="fas fa-cogs me-2 text-warning"></i>BOP
                            </td>
                            <td class="text-end fw-bold fs-5" id="total-bop">
                                @if($totalBiayaBOP > 0)
                                    Rp {{ number_format($totalBiayaBOP, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="table-success fw-bold">
                            <td class="fw-bold">
                                <i class="fas fa-chart-line me-2"></i>TOTAL BIAYA HARGA POKOK PRODUKSI
                            </td>
                            <td class="text-end fw-bold fs-4" id="grand-total">
                                @if($totalBiayaBOM > 0)
                                    Rp {{ number_format($totalBiayaBOM, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="table-primary fw-bold">
                            <td class="fw-bold text-white">
                                <i class="fas fa-tag me-2"></i>HARGA POKOK PRODUKSI
                            </td>
                            <td class="text-end fw-bold fs-3 text-white" id="harga-pokok-produksi">
                                @if($totalBiayaBOM > 0)
                                    Rp {{ number_format($totalBiayaBOM, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Realtime Update Script - DINONAKTIFKAN UNTUK PRESENTASI -->
<script>
// Auto-refresh dinonaktifkan untuk presentasi
/*
// Auto-refresh data every 30 seconds
setInterval(function() {
    refreshData();
}, 30000);

// Listen for storage events (when other tabs update data)
window.addEventListener('storage', function(e) {
    if (e.key === 'bahan_updated' || e.key === 'btkl_updated' || e.key === 'bop_updated') {
        refreshData();
    }
});
*/

// Function to update total data
function updateTotalData() {
    const productId = {{ $produk->id }};
    
    // Calculate totals from PHP variables
    const totalBiayaBahan = {{ $totalBiayaBahan ?? 0 }};
    const totalBiayaBTKL = {{ $totalBiayaBTKL ?? 0 }};
    const totalBiayaBOP = {{ $totalBiayaBOP ?? 0 }}; // Use actual BOP from database
    const grandTotal = totalBiayaBahan + totalBiayaBTKL + totalBiayaBOP;
    
    // Update display elements
    const totalBahanElement = document.getElementById('total-biaya-bahan');
    const totalBtklElement = document.getElementById('total-btkl');
    const totalBopElement = document.getElementById('total-bop');
    const grandTotalElement = document.getElementById('grand-total');
    const hppElement = document.getElementById('harga-pokok-produksi');
    
    if (totalBahanElement) {
        totalBahanElement.textContent = `Rp ${totalBiayaBahan.toLocaleString('id-ID')}`;
    }
    
    if (totalBtklElement) {
        totalBtklElement.textContent = `Rp ${totalBiayaBTKL.toLocaleString('id-ID')}`;
    }
    
    if (totalBopElement) {
        if (totalBiayaBOP > 0) {
            totalBopElement.textContent = `Rp ${totalBiayaBOP.toLocaleString('id-ID')}`;
        } else {
            totalBopElement.innerHTML = '<span class="text-muted">-</span>';
        }
    }
    
    if (grandTotalElement) {
        grandTotalElement.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
    }
    
    if (hppElement) {
        hppElement.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
    }
    
    // Store HPP value for product page
    localStorage.setItem(`hpp_produk_${productId}`, grandTotal);
    
    // Trigger storage event for other tabs
    window.dispatchEvent(new StorageEvent('storage', {
        key: `hpp_produk_${productId}`,
        newValue: grandTotal.toString(),
        url: window.location.href
    }));
}

// Function to refresh all data
function refreshData() {
    const productId = {{ $produk->id }};
    
    // Show loading indicators
    showLoadingIndicators();
    
    // Fetch updated data
    fetch(`/master-data/harga-pokok-produksi/calculate/${productId}`)
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON. Possible server error.');
                });
            }
        })
        .then(data => {
            if (data.success) {
                updateBTKLData(data.data.btkl);
                updateBOPData(data.data.bop);
                updateTotalData();
                hideLoadingIndicators();
            }
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            hideLoadingIndicators();
        });
}

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotalData();
});

// Function to update BTKL data
function updateBTKLData(btklData) {
    if (btklData && btklData.length > 0) {
        btklData.forEach((btkl, index) => {
            const row = document.querySelector(`#btkl-${index}`);
            if (row) {
                const tarifCell = row.querySelector('.tarif');
                const subtotalCell = row.querySelector('.subtotal');
                
                if (tarifCell) {
                    tarifCell.textContent = `Rp ${formatNumber(btkl.tarif_per_jam)}`;
                }
                if (subtotalCell) {
                    subtotalCell.textContent = `Rp ${formatNumber(btkl.subtotal)}`;
                }
            }
        });
        
        // Update total BTKL
        const totalBTKLElement = document.getElementById('total-biaya-btkl-final');
        if (totalBTKLElement) {
            const totalBTKL = btklData.reduce((sum, item) => sum + (item.subtotal || 0), 0);
            totalBTKLElement.textContent = `Rp ${formatNumber(totalBTKL)}`;
        }
    }
}

// Function to update BOP data
function updateBOPData(bopData) {
    if (bopData && bopData.length > 0) {
        bopData.forEach((bop, index) => {
            const row = document.querySelector(`#bop-${index}`);
            if (row) {
                const rateCell = row.querySelector('.data-value');
                if (rateCell) {
                    rateCell.textContent = `Rp ${formatNumber(bop.tarif)}`;
                }
            }
        });
        
        // Update total BOP
        const totalBOPElement = document.getElementById('total-bop');
        if (totalBOPElement) {
            const totalBOP = bopData.reduce((sum, item) => sum + (item.tarif || 0), 0);
            totalBOPElement.textContent = `Rp ${formatNumber(totalBOP)}`;
        }
    }
}

// Function to update total data
function updateTotalData(totalData) {
    // Update total biaya bahan
    const totalBiayaBahanElement = document.getElementById('total-biaya-bahan-final');
    if (totalBiayaBahanElement) {
        totalBiayaBahanElement.textContent = `Rp ${formatNumber(totalData.total_biaya_bahan)}`;
    }
    
    // Update total BTKL
    const totalBiayaBTKLElement = document.getElementById('total-biaya-btkl-final');
    if (totalBiayaBTKLElement) {
        totalBiayaBTKLElement.textContent = `Rp ${formatNumber(totalData.total_biaya_btkl)}`;
    }
    
    // Update total BOP
    const totalBiayaBOPElement = document.getElementById('total-biaya-bop-final');
    if (totalBiayaBOPElement) {
        totalBiayaBOPElement.textContent = `Rp ${formatNumber(totalData.total_biaya_bop)}`;
    }
    
    // Update total BOM
    const totalBOMElement = document.getElementById('total-bom-final');
    if (totalBOMElement) {
        totalBOMElement.textContent = `Rp ${formatNumber(totalData.total_bom)}`;
    }
}

// Helper function to format numbers
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Show loading indicators
function showLoadingIndicators() {
    const indicators = document.querySelectorAll('.data-value');
    indicators.forEach(element => {
        element.style.opacity = '0.5';
    });
}

// Hide loading indicators
function hideLoadingIndicators() {
    const indicators = document.querySelectorAll('.data-value');
    indicators.forEach(element => {
        element.style.opacity = '1';
    });
}

// Manual refresh button - DISEMBUNYIKAN UNTUK PRESENTASI
document.addEventListener('DOMContentLoaded', function() {
    // Tombol refresh data disembunyikan untuk presentasi
    /*
    // Add refresh button to header
    const header = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
    if (header) {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-outline-primary refresh-btn';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
        refreshBtn.onclick = refreshData;
        header.appendChild(refreshBtn);
    }
    */
    
    // Add data-value class to all monetary values for easier updating
    const monetaryElements = document.querySelectorAll('td:contains("Rp"), th:contains("Rp")');
    monetaryElements.forEach(element => {
        if (element.textContent.includes('Rp')) {
            element.classList.add('data-value');
        }
    });
});

// Function to update BOM from stock report
function updateBomFromStockReport() {
    const productId = {{ $produk->id }};
    const updateBtn = document.getElementById('updateBomBtn');
    
    // Show loading state
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...';
    
    // Show loading indicators
    showLoadingIndicators();
    
    // Call update endpoint
    fetch(`/master-data/harga-pokok-produksi/update-from-stock/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(html => {
                throw new Error('Server returned HTML instead of JSON. Possible server error.');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Update all data with fresh calculations
            updateBahanData(data.data.biaya_bahan);
            updateBTKLData(data.data.btkl);
            updateBOPData(data.data.bop);
            updateTotalData(data.data.total);
            
            // Show success message
            showSuccessMessage('BOM berhasil diupdate dengan harga terbaru dari laporan stok!');
        } else {
            showErrorMessage('Gagal mengupdate BOM: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating BOM from stock report:', error);
        showErrorMessage('Terjadi kesalahan saat mengupdate BOM: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Update dari Laporan Stok';
        hideLoadingIndicators();
    });
}

// Function to update bahan data
function updateBahanData(bahanData) {
    if (bahanData) {
        // Update bahan baku
        if (bahanData.bahan_baku && bahanData.bahan_baku.length > 0) {
            bahanData.bahan_baku.forEach((bahan, index) => {
                const row = document.querySelector(`#bahan-baku-${index}`);
                if (row) {
                    const hargaCell = row.querySelector('.harga-satuan');
                    const subtotalCell = row.querySelector('.subtotal');
                    
                    if (hargaCell) {
                        hargaCell.textContent = `Rp ${formatNumber(bahan.harga_satuan)}`;
                    }
                    if (subtotalCell) {
                        subtotalCell.textContent = `Rp ${formatNumber(bahan.subtotal)}`;
                    }
                }
            });
        }
        
        // Update bahan pendukung
        if (bahanData.bahan_pendukung && bahanData.bahan_pendukung.length > 0) {
            bahanData.bahan_pendukung.forEach((bahan, index) => {
                const row = document.querySelector(`#bahan-pendukung-${index}`);
                if (row) {
                    const hargaCell = row.querySelector('.harga-satuan');
                    const subtotalCell = row.querySelector('.subtotal');
                    
                    if (hargaCell) {
                        hargaCell.textContent = `Rp ${formatNumber(bahan.harga_satuan)}`;
                    }
                    if (subtotalCell) {
                        subtotalCell.textContent = `Rp ${formatNumber(bahan.subtotal)}`;
                    }
                }
            });
        }
    }
}

// Function to show success message
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild.nextSibling);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Function to show error message
function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild.nextSibling);
    
    // Auto-hide after 8 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 8000);
}

// Listen for custom events from other pages - DINONAKTIFKAN UNTUK PRESENTASI
/*
window.addEventListener('message', function(event) {
    if (event.data.type === 'data_updated') {
        if (event.data.source === 'bahan' || event.data.source === 'btkl' || event.data.source === 'bop') {
            refreshData();
        }
    }
});
*/
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📦 Tambah Produksi</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('transaksi.produksi.store') }}">
                @csrf
                
                <!-- Form Input -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">🏷️ Produk</label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $prod)
                                <option value="{{ $prod->id }}">
                                    {{ $prod->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📅 Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ now()->toDateString() }}" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📊 Qty</label>
                        <input type="number" name="qty_produksi" id="qty_produksi" step="0.01" min="0.01" class="form-control form-control-lg" required>
                    </div>
                </div>

                <!-- Informasi Harga Pokok Produksi Produk -->
                <div class="card bg-light mb-4" id="bom-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Informasi Harga Pokok Produksi Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <strong>Harga Pokok Produk:</strong> <span id="harga-pokok">Rp 0</span>
                                    <br>
                                    <small class="text-muted">Harga pokok akan dihitung berdasarkan BOM dan qty produksi</small>
                                </div>
                            </div>
                        </div>

                        <!-- Biaya Bahan -->
                        <div class="card mb-3" id="biaya-bahan-section" style="display: none;">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Biaya Bahan Per Produk</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Bahan Baku -->
                                    <div class="col-md-6">
                                        <h6 class="text-success mb-3">Bahan Baku</h6>
                                        <div id="bahan-baku-list">
                                            <!-- Will be populated by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Bahan Pendukung -->
                                    <div class="col-md-6">
                                        <h6 class="text-warning mb-3">Bahan Pendukung</h6>
                                        <div id="bahan-pendukung-list">
                                            <!-- Will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Total</h5>
                                            <div>
                                                <h5 class="mb-0 text-success" id="total-biaya-bahan">Rp 0</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Biaya Tenaga Kerja Langsung (BTKL) -->
                        <div class="card mb-3" id="btkl-section" style="display: none;">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Biaya Tenaga Kerja Langsung (BTKL)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Proses</th>
                                                <th>Nominal Biaya</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="btkl-list">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Biaya Overhead Pabrik (BOP) -->
                        <div class="card mb-3" id="bop-section" style="display: none;">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">Biaya Overhead Pabrik (BOP)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Proses</th>
                                                <th>Nominal Biaya</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bop-list">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Total Keseluruhan -->
                        <div class="card" id="total-section" style="display: none;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0">Total Biaya Produksi</h4>
                                            <div>
                                                <h4 class="mb-0 text-primary" id="total-keseluruhan">Rp 0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-secondary btn-lg">
                        ✖️ Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                        💾 Simpan Produksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentBomData = null;

function formatRupiah(amount) {
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
}

function calculateCostBreakdown() {
    const produkId = document.getElementById('produk_id').value;
    const qty = parseFloat(document.getElementById('qty_produksi').value) || 0;
    
    if (!produkId || !currentBomData || qty <= 0) {
        hideAllSections();
        return;
    }
    
    // Show all sections
    document.getElementById('bom-info').style.display = 'block';
    document.getElementById('biaya-bahan-section').style.display = 'block';
    document.getElementById('btkl-section').style.display = 'block';
    document.getElementById('bop-section').style.display = 'block';
    document.getElementById('total-section').style.display = 'block';
    
    // Calculate Biaya Bahan
    let totalBiayaBahan = 0;
    
    // Bahan Baku
    const bahanBakuHtml = currentBomData.biaya_bahan.bahan_baku.map((bahan, index) => {
        const totalPerProduksi = bahan.harga_per_unit * qty;
        totalBiayaBahan += totalPerProduksi;
        return `
            <div class="mb-2">
                <strong>${index + 1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}
                <br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per ${bahan.satuan} X ${qty} quantity produksi)</small>
            </div>
        `;
    }).join('');
    
    // Bahan Pendukung
    const bahanPendukungHtml = currentBomData.biaya_bahan.bahan_pendukung.map((bahan, index) => {
        const totalPerProduksi = bahan.harga_per_unit * qty;
        totalBiayaBahan += totalPerProduksi;
        return `
            <div class="mb-2">
                <strong>${index + 1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}
                <br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per ${bahan.satuan} X ${qty} quantity produksi)</small>
            </div>
        `;
    }).join('');
    
    document.getElementById('bahan-baku-list').innerHTML = bahanBakuHtml || '<p class="text-muted">Tidak ada data bahan baku</p>';
    document.getElementById('bahan-pendukung-list').innerHTML = bahanPendukungHtml || '<p class="text-muted">Tidak ada data bahan pendukung</p>';
    document.getElementById('total-biaya-bahan').textContent = formatRupiah(totalBiayaBahan);
    
    // Calculate BTKL
    let totalBtkl = 0;
    const btklHtml = currentBomData.btkl.map(btkl => {
        const totalPerProduksi = btkl.harga_per_unit * qty;
        totalBtkl += totalPerProduksi;
        return `
            <tr>
                <td>${btkl.nama}</td>
                <td>
                    ${formatRupiah(btkl.harga_per_unit)}
                    <br><small class="text-muted">(${formatRupiah(btkl.harga_per_unit)} per unit X ${qty} quantity produksi)</small>
                </td>
                <td class="fw-bold">${formatRupiah(totalPerProduksi)}</td>
            </tr>
        `;
    }).join('');
    
    if (btklHtml) {
        document.getElementById('btkl-list').innerHTML = btklHtml + `
            <tr class="table-info">
                <td colspan="2" class="fw-bold">Total BTKL</td>
                <td class="fw-bold">${formatRupiah(totalBtkl)}</td>
            </tr>
        `;
    } else {
        document.getElementById('btkl-list').innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data BTKL</td></tr>';
    }
    
    // Calculate BOP
    let totalBop = 0;
    const bopHtml = currentBomData.bop.map(bop => {
        const totalPerProduksi = bop.harga_per_unit * qty;
        totalBop += totalPerProduksi;
        return `
            <tr>
                <td>${bop.nama}</td>
                <td>
                    ${formatRupiah(bop.harga_per_unit)}
                    <br><small class="text-muted">(${formatRupiah(bop.harga_per_unit)} per unit X ${qty} quantity produksi)</small>
                </td>
                <td class="fw-bold">${formatRupiah(totalPerProduksi)}</td>
            </tr>
        `;
    }).join('');
    
    if (bopHtml) {
        document.getElementById('bop-list').innerHTML = bopHtml + `
            <tr class="table-warning">
                <td colspan="2" class="fw-bold">Total BOP</td>
                <td class="fw-bold">${formatRupiah(totalBop)}</td>
            </tr>
        `;
    } else {
        document.getElementById('bop-list').innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data BOP</td></tr>';
    }
    
    // Calculate total
    const totalKeseluruhan = totalBiayaBahan + totalBtkl + totalBop;
    document.getElementById('harga-pokok').textContent = formatRupiah(totalKeseluruhan);
    document.getElementById('total-keseluruhan').textContent = formatRupiah(totalKeseluruhan);
    
    // Enable submit button
    document.getElementById('submit-btn').disabled = false;
}

function hideAllSections() {
    document.getElementById('bom-info').style.display = 'none';
    document.getElementById('biaya-bahan-section').style.display = 'none';
    document.getElementById('btkl-section').style.display = 'none';
    document.getElementById('bop-section').style.display = 'none';
    document.getElementById('total-section').style.display = 'none';
    document.getElementById('submit-btn').disabled = true;
}

// Event listeners
document.getElementById('produk_id').addEventListener('change', function() {
    const produkId = this.value;
    
    if (!produkId) {
        currentBomData = null;
        hideAllSections();
        return;
    }
    
    console.log('Fetching BOM data for product ID:', produkId);
    
    // Fetch BOM data via AJAX
    fetch(`/master-data/harga-pokok-produksi/get-bom-details/${produkId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                currentBomData = data.breakdown;
                calculateCostBreakdown();
            } else {
                currentBomData = null;
                hideAllSections();
                alert('Data BOM tidak ditemukan untuk produk ini. Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            currentBomData = null;
            hideAllSections();
            alert('Terjadi kesalahan saat mengambil data BOM. Error: ' + error.message);
        });
});

document.getElementById('qty_produksi').addEventListener('input', calculateCostBreakdown);
</script>
@endpush
@endsection

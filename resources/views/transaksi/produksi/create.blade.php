@extends('layouts.app')

@section('title', 'Tambah Produksi')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📦 Tambah Data Produksi Produk</h4>
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
                    <div class="col-md-6">
                        <label class="form-label fw-bold">🏷️ Produk</label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $prod)
                                <option value="{{ $prod->id }}" data-coa-persediaan="{{ $prod->coa_persediaan_id ?? '' }}">
                                    {{ $prod->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">📋 Akun COA Persediaan Barang Jadi</label>
                        <select name="coa_persediaan_barang_jadi_id" id="coa_persediaan_barang_jadi_id" class="form-select form-select-lg">
                            <option value="">-- Pilih Akun COA --</option>
                            @foreach(\App\Models\Coa::where('kode_akun', 'like', '11%')->orWhere('nama_akun', 'like', '%Persediaan%')->orWhere('nama_akun', 'like', '%Barang Jadi%')->orderBy('kode_akun')->get() as $coa)
                                <option value="{{ $coa->id }}" {{ $coa->kode_akun == '116' ? 'selected' : '' }}>
                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih akun COA untuk persediaan barang jadi yang akan digunakan dalam jurnal</small>
                    </div>
                </div>

                
                <!-- Job Process Costing Fields -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">📊 Data Produksi Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📦 Jumlah Produksi dalam Sebulan</label>
                                <input type="number" name="jumlah_produksi_bulanan" id="jumlah_produksi_bulanan" step="0.01" min="0.01" class="form-control form-control-lg" required placeholder="Contoh: 1000">
                                <small class="text-muted">Total produksi yang direncanakan dalam 1 bulan</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📅 Hari Memproduksi dalam Sebulan</label>
                                <input type="number" name="hari_produksi_bulanan" id="hari_produksi_bulanan" min="1" max="31" class="form-control form-control-lg" required placeholder="Contoh: 25">
                                <small class="text-muted">Jumlah hari kerja produksi dalam 1 bulan</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📈 Jumlah Produksi Per Hari</label>
                                <input type="number" name="qty_produksi" id="qty_produksi" step="0.01" class="form-control form-control-lg" readonly>
                                <small class="text-muted">Otomatis dihitung: Produksi Bulanan ÷ Hari Produksi</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Harga Pokok Produksi Produk -->
                <div class="card bg-light mb-4" id="bom-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Informasi Harga Pokok Produksi Produk (Per Hari)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <strong>Harga Pokok Produk Per Hari:</strong> <span id="harga-pokok">Rp 0</span>
                                    <br>
                                    <small class="text-muted">Harga pokok dihitung berdasarkan BOM dan qty produksi per hari</small>
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
                                <small>Menampilkan kapasitas per jam dan jam yang diperlukan untuk produksi</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Proses & Kapasitas</th>
                                                <th>Nominal Biaya & Tarif</th>
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
                                            <h4 class="mb-0">Total Biaya Produksi Per Hari</h4>
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

// Calculate daily production quantity
function calculateDailyProduction() {
    const jumlahBulanan = parseFloat(document.getElementById('jumlah_produksi_bulanan').value) || 0;
    const hariBulanan = parseFloat(document.getElementById('hari_produksi_bulanan').value) || 0;
    
    if (jumlahBulanan > 0 && hariBulanan > 0) {
        const qtyPerHari = jumlahBulanan / hariBulanan;
        document.getElementById('qty_produksi').value = qtyPerHari.toFixed(2);
        
        // Recalculate cost breakdown with new daily quantity
        calculateCostBreakdown();
    } else {
        document.getElementById('qty_produksi').value = '';
        hideAllSections();
    }
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
        // harga_per_unit now contains the subtotal (total cost for the recipe)
        const totalPerProduksi = bahan.harga_per_unit * qty;
        const totalQtyTerpakai = bahan.qty * qty;
        
        // Calculate stock reduction based on conversion
        let stockReduction = totalQtyTerpakai;
        let stockUnit = bahan.satuan;
        
        // If different units, show conversion
        if (bahan.satuan !== bahan.satuan_bahan) {
            // Show conversion info
            const konversiInfo = bahan.konversi_info || `Konversi: ${bahan.satuan} → ${bahan.satuan_bahan}`;
            stockReduction = `${totalQtyTerpakai} ${bahan.satuan}`;
            stockUnit = bahan.satuan_bahan;
        }
        
        totalBiayaBahan += totalPerProduksi;
        return `
            <div class="mb-2">
                <strong>${index + 1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}
                <br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per produk × ${qty} qty produksi per hari)</small>
                <br><small class="text-info">Resep: ${totalQtyTerpakai} ${bahan.satuan}</small>
                ${bahan.konversi_info ? `<br><small class="text-warning">${bahan.konversi_info}</small>` : ''}
                <br><small class="text-danger">Stok berkurang: ${stockReduction} ${stockUnit}</small>
            </div>
        `;
    }).join('');
    
    // Bahan Pendukung
    const bahanPendukungHtml = currentBomData.biaya_bahan.bahan_pendukung.map((bahan, index) => {
        // harga_per_unit now contains the subtotal (total cost for the recipe)
        const totalPerProduksi = bahan.harga_per_unit * qty;
        const totalQtyTerpakai = bahan.qty * qty;
        totalBiayaBahan += totalPerProduksi;
        return `
            <div class="mb-2">
                <strong>${index + 1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}
                <br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per produk × ${qty} qty produksi per hari)</small>
                <br><small class="text-info">Resep: ${totalQtyTerpakai} ${bahan.satuan}</small>
                <br><small class="text-danger">Stok berkurang: ${totalQtyTerpakai} ${bahan.satuan}</small>
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
        
        // Calculate hours needed for production
        const kapasitasPerJam = btkl.kapasitas_per_jam || 1;
        const jamDiperlukanExact = qty / kapasitasPerJam;
        
        // Round up hours: if < 1 hour = 1 hour, if > 1 hour = round up to next hour
        const jamDiperlukan = jamDiperlukanExact <= 1 ? 1 : Math.ceil(jamDiperlukanExact);
        
        // Add warning if hours required is too high (more than 8 hours)
        let warningClass = '';
        let warningText = '';
        if (jamDiperlukan > 8) {
            warningClass = 'text-danger';
            warningText = ' ⚠️ Melebihi jam kerja normal!';
        } else if (jamDiperlukan > 6) {
            warningClass = 'text-warning';
            warningText = ' ⚠️ Mendekati batas jam kerja';
        }
        
        return `
            <tr>
                <td>
                    <strong>${btkl.nama}</strong>
                    <br><small class="text-info">Kapasitas: ${kapasitasPerJam} unit/jam</small>
                    <br><small class="${warningClass || 'text-success'}">Jam diperlukan: ${jamDiperlukan} jam${warningText}</small>
                </td>
                <td>
                    ${formatRupiah(btkl.harga_per_unit)}
                    <br><small class="text-muted">(${formatRupiah(btkl.harga_per_unit)} per unit × ${qty} qty produksi per hari)</small>
                    ${btkl.tarif_per_jam ? `<br><small class="text-info">Tarif: ${formatRupiah(btkl.tarif_per_jam)}/jam</small>` : ''}
                </td>
                <td class="fw-bold">${formatRupiah(totalPerProduksi)}</td>
            </tr>
        `;
    }).join('');
    
    if (btklHtml) {
        // Calculate total hours required with rounding
        let totalJamDiperlukan = 0;
        currentBomData.btkl.forEach(btkl => {
            const kapasitasPerJam = btkl.kapasitas_per_jam || 1;
            const jamDiperlukanExact = qty / kapasitasPerJam;
            // Apply same rounding logic: if < 1 hour = 1 hour, if > 1 hour = round up
            const jamDiperlukan = jamDiperlukanExact <= 1 ? 1 : Math.ceil(jamDiperlukanExact);
            totalJamDiperlukan += jamDiperlukan;
        });
        
        // Add warning class for total hours
        let totalHoursClass = 'table-info';
        let totalHoursWarning = '';
        if (totalJamDiperlukan > 16) {
            totalHoursClass = 'table-danger';
            totalHoursWarning = ' ⚠️ Total jam kerja sangat tinggi!';
        } else if (totalJamDiperlukan > 12) {
            totalHoursClass = 'table-warning';
            totalHoursWarning = ' ⚠️ Total jam kerja tinggi';
        }
        
        document.getElementById('btkl-list').innerHTML = btklHtml + `
            <tr class="${totalHoursClass}">
                <td colspan="2" class="fw-bold">
                    Total BTKL
                    <br><small>Total jam diperlukan: ${totalJamDiperlukan} jam${totalHoursWarning}</small>
                </td>
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
                    <br><small class="text-muted">(${formatRupiah(bop.harga_per_unit)} per unit × ${qty} qty produksi per hari)</small>
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
    const selectedOption = this.options[this.selectedIndex];
    const coaPersediaanId = selectedOption.getAttribute('data-coa-persediaan');
    
    // Auto-fill COA persediaan field
    const coaSelect = document.getElementById('coa_persediaan_barang_jadi_id');
    if (coaPersediaanId) {
        // Find and select the COA option
        for (let i = 0; i < coaSelect.options.length; i++) {
            if (coaSelect.options[i].value === coaPersediaanId) {
                coaSelect.selectedIndex = i;
                break;
            }
        }
    } else {
        coaSelect.selectedIndex = 0;
    }
    
    if (!produkId) {
        currentBomData = null;
        hideAllSections();
        return;
    }
    
    console.log('Fetching BOM data for product ID:', produkId);
    
    // Fetch BOM data via AJAX
    fetch(`/transaksi/produksi/get-bom-details/${produkId}?t=${Date.now()}`)
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

// Add event listeners for daily production calculation
document.getElementById('jumlah_produksi_bulanan').addEventListener('input', calculateDailyProduction);
document.getElementById('hari_produksi_bulanan').addEventListener('input', calculateDailyProduction);
</script>
@endpush
@endsection

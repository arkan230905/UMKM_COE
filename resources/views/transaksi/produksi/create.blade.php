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
                                    <div class="col-md-12">
                                        <h6 class="text-success mb-3">Bahan Baku</h6>
                                        <div id="bahan-baku-list">
                                            <!-- Will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Total Biaya Bahan</h5>
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
                                <small>Menampilkan detail komponen BOP per proses dengan akun COA otomatis</small>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Komponen</th>
                                                <th>Nominal Biaya & COA</th>
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

                        <!-- Preview Jurnal Akuntansi -->
                        <div class="card mt-3" id="jurnal-preview-section" style="display: none;">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-book me-2"></i>Preview Jurnal Akuntansi</h6>
                                <small class="text-white-50">Jurnal yang akan dibuat saat produksi diproses</small>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0" style="table-layout:fixed; width:100%; font-size:12px;">
                                    <colgroup>
                                        <col style="width:28%">
                                        <col style="width:22%">
                                        <col style="width:8%">
                                        <col style="width:21%">
                                        <col style="width:21%">
                                    </colgroup>
                                    <thead>
                                        <tr class="table-secondary">
                                            <th class="py-2 ps-3">Keterangan</th>
                                            <th class="py-2">Akun</th>
                                            <th class="py-2 text-center">Ref</th>
                                            <th class="py-2 text-end pe-3">Debit</th>
                                            <th class="py-2 text-end pe-3">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-primary">
                                            <td colspan="5" class="text-center fw-bold py-2">Produksi</td>
                                        </tr>
                                        <tbody id="jurnal-produksi-body"></tbody>

                                        <tr class="table-info">
                                            <td colspan="5" class="text-center fw-bold py-2">BTKL WIP</td>
                                        </tr>
                                        <tbody id="jurnal-btkl-body"></tbody>

                                        <tr class="table-warning">
                                            <td colspan="5" class="text-center fw-bold py-2">BOP WIP</td>
                                        </tr>
                                        <tbody id="jurnal-bop-body"></tbody>

                                        <tr class="table-success">
                                            <td colspan="5" class="text-center fw-bold py-2">Sudah selesai produksi</td>
                                        </tr>
                                        <tbody id="jurnal-wip-barangjadi-body"></tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jurnal Produksi Preview -->
                <div class="card mb-4" id="jurnal-section" style="display: none;">
                    <div class="card-header text-white" style="background-color: #2d6a4f;">
                        <h5 class="mb-0">📒 Jurnal Produksi (Preview)</h5>
                        <small class="text-white-50">Jurnal ini akan otomatis masuk ke Jurnal Umum saat produksi disimpan</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="jurnal-table">
                                <thead style="background-color: #95d5b2;">
                                    <tr>
                                        <th style="width:5%">No</th>
                                        <th style="width:35%">Nama Akun</th>
                                        <th style="width:12%">Kode Akun</th>
                                        <th style="width:24%" class="text-end">Debit</th>
                                        <th style="width:24%" class="text-end">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody id="jurnal-tbody">
                                    <!-- Generated by JavaScript -->
                                </tbody>
                                <tfoot style="background-color: #d8f3dc;">
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end" id="jurnal-total-debit">Rp 0</th>
                                        <th class="text-end" id="jurnal-total-kredit">Rp 0</th>
                                    </tr>
                                </tfoot>
                            </table>
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
        document.getElementById('qty_produksi').value = Math.round(qtyPerHari);
        
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
    
    // Calculate Biaya Bahan (hanya bahan baku)
    let totalBiayaBahan = 0;
    
    // Bahan Baku
    const bahanBakuHtml = currentBomData.biaya_bahan.bahan_baku.map((bahan, index) => {
        const totalPerProduksi = bahan.harga_per_unit * qty;
        const totalQtyTerpakai = bahan.qty * qty;
        
<<<<<<< HEAD
        let stockReduction = totalQtyTerpakai;
        let stockUnit = bahan.satuan;
        if (bahan.satuan !== bahan.satuan_bahan) {
            stockReduction = `${totalQtyTerpakai} ${bahan.satuan}`;
            stockUnit = bahan.satuan_bahan;
        }
=======
        // Display stock reduction in recipe unit (satuan resep)
        const stockReductionText = `${totalQtyTerpakai} ${bahan.satuan}`;
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        
        totalBiayaBahan += totalPerProduksi;
        return `
            <div class="mb-2">
                <strong>${index + 1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}
                <br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per produk × ${qty} qty produksi per hari)</small>
                <br><small class="text-info">Resep: ${totalQtyTerpakai} ${bahan.satuan}</small>
                ${bahan.konversi_info ? `<br><small class="text-warning">${bahan.konversi_info}</small>` : ''}
<<<<<<< HEAD
                <br><small class="text-danger">Stok berkurang: ${stockReduction} ${stockUnit}</small>
=======
                <br><small class="text-danger">Stok berkurang: ${stockReductionText}</small>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
            </div>
        `;
    }).join('');
    
    document.getElementById('bahan-baku-list').innerHTML = bahanBakuHtml || '<p class="text-muted">Tidak ada data bahan baku</p>';
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
    
    // Calculate BOP - Group by process and show components
    let totalBop = 0;
<<<<<<< HEAD
    const bopHtml = currentBomData.bop.map(bop => {
        const totalPerProduksi = bop.harga_per_unit * qty;
        totalBop += totalPerProduksi;

        // Render komponen detail jika ada
        let komponenHtml = '';
        if (bop.komponen && bop.komponen.length > 0) {
            komponenHtml = bop.komponen.map(k => {
                const totalK = k.rate_per_hour * qty;
                return `<tr class="table-light">
                    <td class="ps-4 text-muted"><small>↳ ${k.nama}</small></td>
                    <td><small class="text-muted">${formatRupiah(k.rate_per_hour)} × ${qty}</small></td>
                    <td class="text-end"><small>${formatRupiah(totalK)}</small></td>
                </tr>`;
            }).join('');
        }

        return `<tr class="fw-bold">
                <td>${bop.nama}</td>
                <td><small class="text-muted">${formatRupiah(bop.harga_per_unit)} per unit × ${qty}</small></td>
                <td class="fw-bold text-end">${formatRupiah(totalPerProduksi)}</td>
            </tr>${komponenHtml}`;
    }).join('');

=======
    const bopByProcess = {};
    
    // Group BOP components by process
    currentBomData.bop.forEach(bop => {
        const namaProses = bop.nama_proses || 'BOP';
        if (!bopByProcess[namaProses]) {
            bopByProcess[namaProses] = [];
        }
        bopByProcess[namaProses].push(bop);
        totalBop += bop.harga_per_unit;
    });
    
    // Generate HTML for each process and its components
    let bopHtml = '';
    Object.keys(bopByProcess).forEach(namaProses => {
        const komponenList = bopByProcess[namaProses];
        let totalProses = 0;
        
        // Header for process
        bopHtml += `
            <tr class="table-light">
                <td colspan="3" class="fw-bold text-primary">${namaProses}</td>
            </tr>
        `;
        
        // Components for this process
        komponenList.forEach(komponen => {
            const totalPerProduksi = komponen.harga_per_unit * qty;
            totalProses += totalPerProduksi;
            
            bopHtml += `
                <tr>
                    <td class="ps-4">${komponen.nama_komponen}</td>
                    <td>
                        ${formatRupiah(komponen.harga_per_unit)}
                        <br><small class="text-muted">(${formatRupiah(komponen.harga_per_unit)} per unit × ${qty} qty produksi per hari)</small>
                        <br><small class="text-info">COA: ${komponen.coa_kode} - ${komponen.coa_nama}</small>
                    </td>
                    <td class="fw-bold">${formatRupiah(totalPerProduksi)}</td>
                </tr>
            `;
        });
        
        // Subtotal for process
        bopHtml += `
            <tr class="table-secondary">
                <td colspan="2" class="fw-bold ps-4">Subtotal ${namaProses}</td>
                <td class="fw-bold">${formatRupiah(totalProses)}</td>
            </tr>
        `;
    });
    
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
    if (bopHtml) {
        const totalBopPerHari = totalBop * qty;
        document.getElementById('bop-list').innerHTML = bopHtml + `
            <tr class="table-warning">
                <td colspan="2" class="fw-bold">Total BOP</td>
<<<<<<< HEAD
                <td class="fw-bold text-end">${formatRupiah(totalBop)}</td>
=======
                <td class="fw-bold">${formatRupiah(totalBopPerHari)}</td>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
            </tr>
        `;
    } else {
        document.getElementById('bop-list').innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data BOP</td></tr>';
    }
    
    // Calculate total
    const totalBopPerHari = totalBop * qty;
    const totalKeseluruhan = totalBiayaBahan + totalBtkl + totalBopPerHari;
    document.getElementById('harga-pokok').textContent = formatRupiah(totalKeseluruhan);
    document.getElementById('total-keseluruhan').textContent = formatRupiah(totalKeseluruhan);

    // ── Preview Jurnal ──────────────────────────────────────────
    document.getElementById('jurnal-preview-section').style.display = 'block';

    const bom = currentBomData;

    // COA WIP spesifik per kategori
    const bdpBbbKode  = bom.coa_bdp_bbb?.kode  ?? '1171';
    const bdpBbbNama  = bom.coa_bdp_bbb?.nama  ?? 'BDP - BBB';
    const bdpBtklKode = bom.coa_bdp_btkl?.kode ?? '1172';
    const bdpBtklNama = bom.coa_bdp_btkl?.nama ?? 'BDP - BTKL';
    const bdpBopKode  = bom.coa_bdp_bop?.kode  ?? '1173';
    const bdpBopNama  = bom.coa_bdp_bop?.nama  ?? 'BDP - BOP';

    // Helper: baris debit
    const rowD = (ket, akunKode, akunNama, val) =>
        `<tr>
            <td class="ps-3">${ket}</td>
            <td><span class="badge bg-secondary me-1">${akunKode}</span>${akunNama}</td>
            <td class="text-center text-muted" style="font-size:10px">${akunKode}</td>
            <td class="text-end pe-3 fw-semibold text-nowrap">${formatRupiah(val)}</td>
            <td class="text-end pe-3"></td>
        </tr>`;

    // Helper: baris kredit (indent)
    const rowK = (ket, akunKode, akunNama, val) =>
        `<tr>
            <td class="ps-5 text-muted">${ket}</td>
            <td><span class="badge bg-secondary me-1">${akunKode}</span>${akunNama}</td>
            <td class="text-center text-muted" style="font-size:10px">${akunKode}</td>
            <td class="text-end pe-3"></td>
            <td class="text-end pe-3 text-nowrap">${formatRupiah(val)}</td>
        </tr>`;

    const empty5 = `<tr><td colspan="5" class="text-center text-muted ps-3">-</td></tr>`;

    // ── Jurnal 1: Produksi — BBB & Bahan Pendukung → BDP-BBB ───────────────────
    let j1 = '';

    // Bahan Baku
    bom.biaya_bahan.bahan_baku.forEach(b => {
        const total = b.harga_per_unit * qty;
        if (total <= 0) return;
        const persKode = b.coa_persediaan_kode ?? '114';
        const persNama = b.coa_persediaan_nama ?? b.nama;
        j1 += rowD('Barang dalam proses - BBB', bdpBbbKode, bdpBbbNama, total);
        j1 += rowK(b.nama, persKode, persNama, total);
    });

    // Bahan Pendukung
    if (bom.biaya_bahan.bahan_pendukung && bom.biaya_bahan.bahan_pendukung.length > 0) {
        bom.biaya_bahan.bahan_pendukung.forEach(bp => {
            const total = bp.harga_per_unit * qty;
            if (total <= 0) return;
            const persKode = bp.coa_persediaan_kode ?? '115';
            const persNama = bp.coa_persediaan_nama ?? bp.nama;
            j1 += rowD('Barang dalam proses - Bahan Pendukung', bdpBbbKode, bdpBbbNama, total);
            j1 += rowK(bp.nama, persKode, persNama, total);
        });
    }

    document.getElementById('jurnal-produksi-body').innerHTML = j1 || empty5;

    // ── Jurnal 2a: BTKL WIP → BDP-BTKL ──────────────────────
    let j2a = '';
    if (totalBtkl > 0) {
        j2a += rowD('Barang dalam proses - BTKL', bdpBtklKode, bdpBtklNama, totalBtkl);
        bom.btkl.forEach(b => {
            const total = b.harga_per_unit * qty;
            if (total <= 0) return;
            const kreditKode = b.coa_kredit_kode ?? '211';
            const kreditNama = b.coa_kredit_nama ?? 'Hutang Gaji';
            j2a += rowK(`Hutang Gaji — ${b.nama}`, kreditKode, kreditNama, total);
        });
    }
    document.getElementById('jurnal-btkl-body').innerHTML = j2a || empty5;

    // ── Jurnal 2b: BOP WIP → BDP-BOP ─────────────────────────
    let j2b = '';
    bom.bop.forEach(bop => {
        const totalBopProses = bop.harga_per_unit * qty;
        if (totalBopProses <= 0) return;
        j2b += rowD('Barang dalam proses - BOP', bdpBopKode, bdpBopNama, totalBopProses);
        if (bop.komponen && bop.komponen.length > 0) {
            bop.komponen.forEach(k => {
                const totalK = k.rate_per_hour * qty;
                if (totalK <= 0) return;
                const kreditKode = k.kredit_kode ?? k.coa_kode ?? '53';
                const kreditNama = k.kredit_nama ?? k.coa_nama ?? 'BOP';
                j2b += rowK(`${bop.nama} — ${k.nama}`, kreditKode, kreditNama, totalK);
            });
        } else {
            j2b += rowK(bop.nama, '53', 'BOP', totalBopProses);
        }
    });
    document.getElementById('jurnal-bop-body').innerHTML = j2b || empty5;

    // ── Jurnal 3: BDP → Persediaan Barang Jadi ───────────────
    let j3 = '';
    if (totalKeseluruhan > 0) {
        const coaSelect = document.getElementById('coa_persediaan_barang_jadi_id');
        const coaOpt   = coaSelect.options[coaSelect.selectedIndex];
        const coaText  = coaOpt?.text || 'Pers. Barang Jadi';
        const coaParts = coaText.split(' - ');
        const coaKode  = coaParts[0]?.trim() ?? '116';
        const coaNama  = coaParts.slice(1).join(' - ').trim() || coaText;

        j3 += rowD('Persediaan Barang Jadi', coaKode, coaNama, totalKeseluruhan);
        if (totalBiayaBahan > 0) j3 += rowK('BDP - BBB',  bdpBbbKode,  bdpBbbNama,  totalBiayaBahan);
        if (totalBtkl > 0)       j3 += rowK('BDP - BTKL', bdpBtklKode, bdpBtklNama, totalBtkl);
        if (totalBop  > 0)       j3 += rowK('BDP - BOP',  bdpBopKode,  bdpBopNama,  totalBop);
    }
    document.getElementById('jurnal-wip-barangjadi-body').innerHTML = j3 || empty5;

    // Update jurnal 3 saat COA barang jadi diganti
    document.getElementById('coa_persediaan_barang_jadi_id').onchange = () => calculateCostBreakdown();

    // Enable submit button
    document.getElementById('submit-btn').disabled = false;
    
    // Generate jurnal preview
    generateJurnalPreview(totalBiayaBahan, totalBtkl, totalBopPerHari, totalKeseluruhan, qty);
}

function generateJurnalPreview(totalBBB, totalBTKL, totalBOP, totalHPP, qty) {
    if (!currentBomData || qty <= 0) {
        document.getElementById('jurnal-section').style.display = 'none';
        return;
    }
    
    let rows = '';
    let no = 1;
    let totalDebit = 0;
    let totalKredit = 0;
    
    // ===== JURNAL 1: BBB → Pers. Barang Dalam Proses - BBB =====
    if (totalBBB > 0) {
        // DEBIT: Pers. Barang Dalam Proses - BBB (1171)
        rows += `<tr class="table-light">
            <td>${no++}</td>
            <td><strong>Pers. Barang Dalam Proses - BBB</strong></td>
            <td>1171</td>
            <td class="text-end text-success fw-bold">${formatRupiah(totalBBB)}</td>
            <td class="text-end">-</td>
        </tr>`;
        totalDebit += totalBBB;
        
        // KREDIT: Setiap bahan baku
        currentBomData.biaya_bahan.bahan_baku.forEach(bahan => {
            const totalBahan = bahan.harga_per_unit * qty;
            if (totalBahan > 0) {
                rows += `<tr>
                    <td></td>
                    <td class="ps-4 text-muted">${bahan.coa_persediaan_nama ?? bahan.nama}</td>
                    <td class="text-muted">${bahan.coa_persediaan_kode ?? '1141'}</td>
                    <td class="text-end">-</td>
                    <td class="text-end text-danger">${formatRupiah(totalBahan)}</td>
                </tr>`;
                totalKredit += totalBahan;
            }
        });
    }
    
    // ===== JURNAL 2: BTKL → Pers. Barang Dalam Proses - BTKL =====
    if (totalBTKL > 0) {
        // DEBIT: Pers. Barang Dalam Proses - BTKL (1172)
        rows += `<tr class="table-light">
            <td>${no++}</td>
            <td><strong>Pers. Barang Dalam Proses - BTKL</strong></td>
            <td>1172</td>
            <td class="text-end text-success fw-bold">${formatRupiah(totalBTKL)}</td>
            <td class="text-end">-</td>
        </tr>`;
        totalDebit += totalBTKL;
        
        // KREDIT: Hutang Gaji (211)
        rows += `<tr>
            <td></td>
            <td class="ps-4 text-muted">Hutang Gaji</td>
            <td class="text-muted">211</td>
            <td class="text-end">-</td>
            <td class="text-end text-danger">${formatRupiah(totalBTKL)}</td>
        </tr>`;
        totalKredit += totalBTKL;
    }
    
    // ===== JURNAL 3: BOP → Pers. Barang Dalam Proses - BOP =====
    if (totalBOP > 0) {
        // DEBIT: Barang Dalam Proses BOP (1173)
        rows += `<tr class="table-light">
            <td>${no++}</td>
            <td><strong>Barang Dalam Proses BOP</strong></td>
            <td>1173</td>
            <td class="text-end text-success fw-bold">${formatRupiah(totalBOP)}</td>
            <td class="text-end">-</td>
        </tr>`;
        totalDebit += totalBOP;
        
        // KREDIT: Per komponen BOP (dari bop_komponen)
        if (currentBomData.bop_komponen && currentBomData.bop_komponen.length > 0) {
            let totalKreditBOP = 0;
            currentBomData.bop_komponen.forEach(komponen => {
                const totalKomponen = komponen.subtotal * qty;
                if (totalKomponen > 0) {
                    rows += `<tr>
                        <td></td>
                        <td class="ps-4 text-muted">${komponen.coa_nama}</td>
                        <td class="text-muted">${komponen.coa_kode}</td>
                        <td class="text-end">-</td>
                        <td class="text-end text-danger">${formatRupiah(totalKomponen)}</td>
                    </tr>`;
                    totalKredit += totalKomponen;
                    totalKreditBOP += totalKomponen;
                }
            });
            
            // Jika ada selisih (pembulatan), tambahkan ke Hutang Usaha
            const selisih = totalBOP - totalKreditBOP;
            if (Math.abs(selisih) > 1) {
                rows += `<tr>
                    <td></td>
                    <td class="ps-4 text-muted">Hutang Usaha</td>
                    <td class="text-muted">210</td>
                    <td class="text-end">-</td>
                    <td class="text-end text-danger">${formatRupiah(selisih)}</td>
                </tr>`;
                totalKredit += selisih;
            }
        } else {
            // Fallback: Hutang Usaha untuk seluruh BOP
            if (totalBOP > 0) {
                rows += `<tr>
                    <td></td>
                    <td class="ps-4 text-muted">Hutang Usaha (BOP)</td>
                    <td class="text-muted">210</td>
                    <td class="text-end">-</td>
                    <td class="text-end text-danger">${formatRupiah(totalBOP)}</td>
                </tr>`;
                totalKredit += totalBOP;
            }
        }
    }
    
    // ===== JURNAL 4: Transfer ke Barang Jadi =====
    if (totalHPP > 0) {
        const produkNama = currentBomData.produk ? currentBomData.produk.nama : 'Barang Jadi';
        const produkCoaKode = currentBomData.produk ? currentBomData.produk.coa_persediaan_kode : '1161';
        const produkCoaNama = currentBomData.produk ? currentBomData.produk.coa_persediaan_nama : 'Pers. Barang Jadi';
        
        // DEBIT: Pers. Barang Jadi
        rows += `<tr class="table-light">
            <td>${no++}</td>
            <td><strong>${produkCoaNama}</strong></td>
            <td>${produkCoaKode}</td>
            <td class="text-end text-success fw-bold">${formatRupiah(totalHPP)}</td>
            <td class="text-end">-</td>
        </tr>`;
        totalDebit += totalHPP;
        
        // KREDIT: Pers. Barang Dalam Proses - BBB
        if (totalBBB > 0) {
            rows += `<tr>
                <td></td>
                <td class="ps-4 text-muted">Pers. Barang Dalam Proses - BBB</td>
                <td class="text-muted">1171</td>
                <td class="text-end">-</td>
                <td class="text-end text-danger">${formatRupiah(totalBBB)}</td>
            </tr>`;
            totalKredit += totalBBB;
        }
        
        // KREDIT: Pers. Barang Dalam Proses - BTKL
        if (totalBTKL > 0) {
            rows += `<tr>
                <td></td>
                <td class="ps-4 text-muted">Pers. Barang Dalam Proses - BTKL</td>
                <td class="text-muted">1172</td>
                <td class="text-end">-</td>
                <td class="text-end text-danger">${formatRupiah(totalBTKL)}</td>
            </tr>`;
            totalKredit += totalBTKL;
        }
        
        // KREDIT: Pers. Barang Dalam Proses - BOP
        if (totalBOP > 0) {
            rows += `<tr>
                <td></td>
                <td class="ps-4 text-muted">Pers. Barang Dalam Proses - BOP</td>
                <td class="text-muted">1173</td>
                <td class="text-end">-</td>
                <td class="text-end text-danger">${formatRupiah(totalBOP)}</td>
            </tr>`;
            totalKredit += totalBOP;
        }
    }
    
    // Update table
    document.getElementById('jurnal-tbody').innerHTML = rows;
    document.getElementById('jurnal-total-debit').textContent = formatRupiah(totalDebit);
    document.getElementById('jurnal-total-kredit').textContent = formatRupiah(totalKredit);
    document.getElementById('jurnal-section').style.display = 'block';
}

function hideAllSections() {
    document.getElementById('bom-info').style.display = 'none';
    document.getElementById('biaya-bahan-section').style.display = 'none';
    document.getElementById('btkl-section').style.display = 'none';
    document.getElementById('bop-section').style.display = 'none';
    document.getElementById('total-section').style.display = 'none';
<<<<<<< HEAD
    document.getElementById('jurnal-preview-section').style.display = 'none';
=======
    document.getElementById('jurnal-section').style.display = 'none';
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
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
    
    // Recalculate daily production in case fields are already filled
    calculateDailyProduction();
    
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
document.addEventListener('DOMContentLoaded', function() {
    const jumlahBulananInput = document.getElementById('jumlah_produksi_bulanan');
    const hariBulananInput = document.getElementById('hari_produksi_bulanan');
    
    if (jumlahBulananInput) {
        jumlahBulananInput.addEventListener('input', calculateDailyProduction);
        jumlahBulananInput.addEventListener('change', calculateDailyProduction);
    }
    
    if (hariBulananInput) {
        hariBulananInput.addEventListener('input', calculateDailyProduction);
        hariBulananInput.addEventListener('change', calculateDailyProduction);
    }
});
</script>
@endpush
@endsection

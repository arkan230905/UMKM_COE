@extends('layouts.app')

@section('title', 'Hitung Harga Pokok Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
        </h2>
        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('master-data.harga-pokok-produksi.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Product Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2"></i>Pilih Produk
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="produk_id" class="form-label">Produk <span class="text-danger">*</span></label>
                                <select class="form-select" id="produk_id" name="produk_id" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach(\App\Models\Produk::where('user_id', auth()->id())->get() as $produk)
                                        <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Pilih produk yang akan dihitung HPP-nya</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BBB Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-cube me-2"></i>Biaya Bahan Baku
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="bbb-container" class="row">
                            <div class="col-12 text-center">
                                <div class="spinner-border text-muted" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Memuat data bahan baku...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<<<<<<< HEAD
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Pilih proses BTKL yang digunakan untuk produk ini.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">Pilih</th>
                                <th width="15%">Kode</th>
                                <th width="20%">Nama Proses</th>
                                <th width="15%">Jabatan</th>
                                <th width="15%">Tarif BTKL/Jam</th>
                                <th width="10%">Kapasitas</th>
                                <th width="10%">BTKL/pcs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prosesBtkl as $proses)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input proses-checkbox" 
                                           name="proses_ids[]" 
                                           value="{{ $proses['id'] }}"
                                           data-btkl-per-produk="{{ $proses['btkl_per_produk'] }}"
                                           data-nama="{{ $proses['nama_proses'] }}"
                                           onchange="calculateTotal()">
                                </td>
                                <td>{{ $proses['kode_proses'] }}</td>
                                <td>{{ $proses['nama_proses'] }}</td>
                                <td>
                                    {{ $proses['nama_jabatan'] }}<br>
                                    <small class="text-muted">{{ $proses['jumlah_pegawai'] }} pegawai @ Rp {{ number_format($proses['tarif_per_jam_jabatan'], 0, ',', '.') }}/jam</small>
                                </td>
                                <td>Rp {{ number_format($proses['tarif_btkl'], 0, ',', '.') }}</td>
                                <td>{{ $proses['kapasitas_per_jam'] }} pcs/jam</td>
                                <td class="text-success fw-semibold">Rp {{ number_format($proses['btkl_per_produk'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
=======

            <!-- BTKL Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>BTKL (Biaya Tenaga Kerja Langsung)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="btkl-container" class="row">
                            <div class="col-12 text-center">
                                <div class="spinner-border text-muted" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Memuat data proses produksi...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOP Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>BOP (Biaya Overhead Pabrik)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="bop-container" class="row">
                            <div class="col-12 text-center">
                                <div class="spinner-border text-muted" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Memuat data komponen BOP...</p>
                            </div>
                        </div>
                    </div>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                </div>
            </div>

            <!-- Summary Section -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 class="text-success">Rp <span id="total-bbb">0</span></h5>
                                    <small>Biaya Bahan Baku</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 class="text-warning">Rp <span id="total-btkl">0</span></h5>
                                    <small>BTKL</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 class="text-danger">Rp <span id="total-bop">0</span></h5>
                                    <small>BOP</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 class="text-primary">Rp <span id="total-hpp">0</span></h5>
                                    <small>Total HPP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
<<<<<<< HEAD
                </div>
                @endif
            </div>
        </div>

        <!-- Step 3: Pilih Proses BOP -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>3. Pilih Proses BOP yang Digunakan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Pilih proses BOP yang digunakan untuk produk ini.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">Pilih</th>
                                <th width="15%">Kode Proses</th>
                                <th width="20%">Nama BOP Proses</th>
                                <th width="15%">Nama Proses</th>
                                <th width="15%">Total BOP/Jam</th>
                                <th width="10%">Kapasitas</th>
                                <th width="10%">BOP/pcs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prosesBop as $bop)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input bop-checkbox" 
                                           name="bop_ids[]" 
                                           value="{{ $bop['id'] }}"
                                           data-bop-per-produk="{{ $bop['bop_per_unit'] }}"
                                           data-nama="{{ $bop['nama_bop_proses'] }}"
                                           data-komponen-bop="{{ json_encode($bop['komponen_bop']) }}"
                                           onchange="calculateTotal()">
                                </td>
                                <td>{{ $bop['kode_proses'] }}</td>
                                <td>{{ $bop['nama_bop_proses'] }}</td>
                                <td>{{ $bop['nama_proses'] }}</td>
                                <td>Rp {{ number_format($bop['total_bop_per_jam'], 0, ',', '.') }}</td>
                                <td>{{ $bop['kapasitas_per_jam'] }} pcs/jam</td>
                                <td class="text-warning fw-semibold">Rp {{ number_format($bop['bop_per_unit'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($prosesBop->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Belum Ada Data BOP</h5>
                    <p class="text-muted">Silakan buat data BOP terlebih dahulu di halaman Master Data BOP</p>
                    <a href="{{ route('master-data.bop-proses.index') }}" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-2"></i>Ke Halaman BOP
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Step 4: Detail Komponen BOP (Auto-display) -->
        <div class="card shadow-sm mb-4" id="bopDetailCard" style="display: none;">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>4. Detail Komponen BOP (Otomatis)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Komponen BOP ditampilkan otomatis berdasarkan proses BOP yang dipilih.
                </div>
                
                <div id="bopDetailContent"></div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan HPP</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-primary text-white rounded">
                            <h6>Biaya Bahan</h6>
                            <h4 id="summaryBiayaBahan">Rp 0</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <h6>Total BTKL</h6>
                            <h4 id="summaryBtkl">Rp 0</h4>
                            <input type="hidden" name="total_btkl" id="totalBtklInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-warning text-dark rounded">
                            <h6>Total BOP</h6>
                            <h4 id="summaryBop">Rp 0</h4>
                            <input type="hidden" name="total_bop" id="totalBopInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-dark text-white rounded">
                            <h6>Total HPP</h6>
                            <h4 id="summaryHpp">Rp 0</h4>
                            <input type="hidden" name="total_hpp" id="totalHppInput" value="0">
                        </div>
                    </div>
=======
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan HPP
                    </button>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const produkSelect = document.getElementById('produk_id');
    
    produkSelect.addEventListener('change', function() {
        const produkId = this.value;
        
<<<<<<< HEAD
        document.getElementById('displayBiayaBahan').textContent = formatRupiah(biayaBahan);
        document.getElementById('biayaBahanInput').value = biayaBahan;
        document.getElementById('summaryBiayaBahan').textContent = formatRupiah(biayaBahan);
        
        calculateTotal();
    } else {
        document.getElementById('displayBiayaBahan').textContent = 'Rp 0';
        document.getElementById('biayaBahanInput').value = 0;
        document.getElementById('summaryBiayaBahan').textContent = 'Rp 0';
    }
}

// Calculate total when checkboxes change
function calculateTotal() {
    const btklCheckboxes = document.querySelectorAll('.proses-checkbox:checked');
    const bopCheckboxes = document.querySelectorAll('.bop-checkbox:checked');
    const biayaBahan = parseFloat(document.getElementById('biayaBahanInput').value) || 0;
    
    let totalBtkl = 0;
    let totalBop = 0;
    let bopDetails = [];
    
    // Calculate BTKL from BTKL checkboxes
    btklCheckboxes.forEach(checkbox => {
        const btklPerProduk = parseFloat(checkbox.dataset.btklPerProduk) || 0;
        totalBtkl += btklPerProduk;
    });
    
    // Calculate BOP from BOP checkboxes
    bopCheckboxes.forEach(checkbox => {
        const bopPerProduk = parseFloat(checkbox.dataset.bopPerProduk) || 0;
        const namaProses = checkbox.dataset.nama;
        const komponenBop = JSON.parse(checkbox.dataset.komponenBop || '[]');
        
        totalBop += bopPerProduk;
        
        if (komponenBop.length > 0) {
            bopDetails.push({
                nama_proses: namaProses,
                komponen: komponenBop,
                total: bopPerProduk
            });
=======
        if (produkId) {
            loadBBBData(produkId);
            loadBTKLData(produkId);
            loadBOPData();
        } else {
            clearAllData();
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        }
    });
    
    function loadBBBData(produkId) {
        fetch(`/api/get-available-bbb/${produkId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('bbb-container');
                
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="alert alert-warning border-0 shadow-sm">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                <h5 class="text-warning">Belum Ada Data Biaya Bahan Baku</h5>
                                <p class="text-muted mb-0">Produk yang dipilih belum memiliki data biaya bahan baku. Silakan tambahkan data biaya bahan baku terlebih dahulu.</p>
                            </div>
                        </div>
                    `;
                    updateTotals();
                    return;
                }
                
                // Get product name for display
                const produkSelect = document.getElementById('produk_id');
                const selectedOption = produkSelect.options[produkSelect.selectedIndex];
                const produkName = selectedOption ? selectedOption.text : 'Produk Terpilih';
                
                let html = '';
                let totalBBB = 0; // Auto-calculate total
                
                // Add header info
                html += `
                    <div class="col-12 mb-4">
                        <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-info-circle text-white"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-info fw-bold">Biaya Bahan Baku untuk: ${produkName}</h6>
                                            <small class="text-muted">Semua biaya bahan baku akan otomatis dimasukkan dalam perhitungan HPP</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-info text-white px-3 py-2">
                                        <i class="fas fa-magic me-1"></i>
                                        Otomatis Terpilih
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                data.forEach(item => {
                    totalBBB += parseFloat(item.subtotal); // Add to total automatically
                    
                    html += `
                        <div class="col-12 mb-4">
                            <!-- Hidden input to automatically include this item -->
                            <input type="hidden" name="selected_bbb[]" value="${item.id}" data-subtotal="${item.subtotal}">
                            
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%); border-left: 5px solid #28a745 !important;">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success rounded-circle p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-seedling text-white"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-1 text-success fw-bold">${item.nama_bahan}</h5>
                                                    <small class="text-muted">Bahan Baku ${produkName}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="border-end pe-3">
                                                <small class="text-muted d-block">Jumlah</small>
                                                <h6 class="mb-0 fw-bold text-dark">${parseFloat(item.jumlah).toLocaleString('id-ID')}</h6>
                                                <small class="text-success fw-semibold">${item.satuan}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="border-end pe-3">
                                                <small class="text-muted d-block">Harga Satuan</small>
                                                <h6 class="mb-0 fw-bold text-dark">Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}</h6>
                                                <small class="text-muted">per ${item.satuan}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="bg-success bg-opacity-10 rounded p-3">
                                                <small class="text-muted d-block">Subtotal</small>
                                                <h4 class="mb-0 fw-bold text-success">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</h4>
                                                <small class="text-success">Total Biaya</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="badge bg-success text-white px-3 py-2 d-flex align-items-center justify-content-center" style="min-height: 40px;">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span class="text-white">Otomatis</span>
                                            </div>
                                            ${item.keterangan ? `<div class="mt-2"><small class="text-muted">${item.keterangan}</small></div>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                    <div class="col-12">
                        <div class="alert alert-success border-0 shadow-sm" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success rounded-circle p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-calculator text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 text-success fw-bold">Total Biaya Bahan Baku - ${produkName}</h5>
                                            <small class="text-muted">Semua biaya bahan baku sudah otomatis dimasukkan dalam perhitungan HPP</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h3 class="mb-0 fw-bold text-success">Rp ${totalBBB.toLocaleString('id-ID')}</h3>
                                    <small class="text-success">Siap untuk perhitungan HPP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                container.innerHTML = html;
                updateTotals(); // Update the summary totals
            })
            .catch(error => {
                console.error('Error loading BBB data:', error);
                document.getElementById('bbb-container').innerHTML = 
                    '<div class="col-12 text-center text-danger">Gagal memuat data biaya bahan baku</div>';
            });
    }
    
<<<<<<< HEAD
    // Enable/disable submit button
    const produkSelected = document.getElementById('produk_id').value !== '';
    const prosesSelected = btklCheckboxes.length > 0;
    document.getElementById('submitBtn').disabled = !(produkSelected && prosesSelected);
}

function formatRupiah(amount) {
    return 'Rp ' + formatNumber(amount);
}

function formatNumber(amount) {
    return new Intl.NumberFormat('id-ID').format(Math.round(amount));
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to all BTKL checkboxes
    document.querySelectorAll('.proses-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });
    
    // Add change listeners to all BOP checkboxes
    document.querySelectorAll('.bop-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });
    
    // Add form submit listener for debugging
    document.getElementById('hppForm').addEventListener('submit', function(e) {
        const formData = new FormData(this);
        console.log('Form submitting with data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
=======
    function loadBTKLData(produkId) {
        fetch(`/api/get-available-btkl/${produkId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('btkl-container');
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center text-muted">Belum ada data BTKL tersedia</div>';
                    updateTotals();
                    return;
                }
                
                let html = '';
                
                data.forEach(item => {
                    html += `
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff3cd 0%, #fef9e7 100%); border-left: 5px solid #ffc107 !important;">
                                <div class="card-body p-4">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" 
                                               name="selected_btkl[]" value="${item.id}" id="btkl_${item.id}"
                                               data-tarif="${item.biaya_per_produk}"
                                               style="transform: scale(1.2); margin-top: 8px;">
                                        <label class="form-check-label w-100" for="btkl_${item.id}">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-warning rounded-circle p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-users text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-1 text-warning fw-bold">${item.nama_proses}</h5>
                                                            <small class="text-muted">Kode: ${item.kode_proses || '-'}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <div class="border-end pe-3">
                                                        <small class="text-muted d-block">Tarif BTKL</small>
                                                        <h6 class="mb-0 fw-bold text-dark">Rp ${parseFloat(item.tarif_per_jam).toLocaleString('id-ID')}</h6>
                                                        <small class="text-warning fw-semibold">per jam</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <div class="border-end pe-3">
                                                        <small class="text-muted d-block">Kapasitas</small>
                                                        <h6 class="mb-0 fw-bold text-dark">${item.kapasitas_per_jam}</h6>
                                                        <small class="text-muted">${item.satuan}/jam</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <div class="bg-warning bg-opacity-10 rounded p-3">
                                                        <small class="text-muted d-block">Biaya per Produk</small>
                                                        <h4 class="mb-0 fw-bold text-warning">Rp ${parseFloat(item.biaya_per_produk).toLocaleString('id-ID')}</h4>
                                                        <small class="text-warning">BTKL Cost</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <div class="badge bg-warning text-white px-3 py-2 d-flex align-items-center justify-content-center" style="min-height: 40px;">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <span class="text-white">Proses</span>
                                                    </div>
                                                    ${item.deskripsi ? `<div class="mt-2"><small class="text-muted">${item.deskripsi}</small></div>` : ''}
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                updateTotals();
            })
            .catch(error => {
                console.error('Error loading BTKL data:', error);
                document.getElementById('btkl-container').innerHTML = 
                    '<div class="col-12 text-center text-danger">Gagal memuat data BTKL: ' + error.message + '</div>';
            });
    }
    
    function loadBOPData() {
        fetch(`/api/get-available-bop`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('bop-container');
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center text-muted">Belum ada data BOP tersedia</div>';
                    updateTotals();
                    return;
                }
                
                let html = '';
                
                data.forEach(item => {
                    html += `
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f8d7da 0%, #fce4e6 100%); border-left: 5px solid #dc3545 !important;">
                                <div class="card-body p-4">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" 
                                               name="selected_bop[]" value="${item.id}" id="bop_${item.id}"
                                               data-tarif="${item.tarif}"
                                               style="transform: scale(1.2); margin-top: 8px;">
                                        <label class="form-check-label w-100" for="bop_${item.id}">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-danger rounded-circle p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-cogs text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-1 text-danger fw-bold">${item.nama_bop}</h5>
                                                            <small class="text-muted">${item.kategori}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="border-end pe-2">
                                                                <small class="text-muted d-block">Listrik</small>
                                                                <strong class="text-dark">Rp ${parseFloat(item.listrik || 0).toLocaleString('id-ID')}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="border-end pe-2">
                                                                <small class="text-muted d-block">Gas/BBM</small>
                                                                <strong class="text-dark">Rp ${parseFloat(item.gas_bbm || 0).toLocaleString('id-ID')}</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <small class="text-muted d-block">Penyusutan</small>
                                                            <strong class="text-dark">Rp ${parseFloat(item.penyusutan || 0).toLocaleString('id-ID')}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <div class="bg-danger bg-opacity-10 rounded p-3">
                                                        <small class="text-muted d-block">Total BOP</small>
                                                        <h4 class="mb-0 fw-bold text-danger">Rp ${parseFloat(item.tarif).toLocaleString('id-ID')}</h4>
                                                        <small class="text-danger">per Unit</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <div class="badge bg-danger text-white px-3 py-2 d-flex align-items-center justify-content-center" style="min-height: 40px;">
                                                        <i class="fas fa-industry me-1"></i>
                                                        <span class="text-white">BOP</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                updateTotals();
            })
            .catch(error => {
                console.error('Error loading BOP data:', error);
                document.getElementById('bop-container').innerHTML = 
                    '<div class="col-12 text-center text-danger">Gagal memuat data BOP: ' + error.message + '</div>';
            });
    }
    
    function clearAllData() {
        document.getElementById('bbb-container').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-light border-2 border-dashed" style="border-color: #dee2e6 !important;">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Pilih Produk Terlebih Dahulu</h5>
                    <p class="text-muted mb-0">Silakan pilih produk dari dropdown di atas untuk melihat data biaya bahan baku, BTKL, dan BOP</p>
                </div>
            </div>
        `;
        document.getElementById('btkl-container').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-light border-2 border-dashed" style="border-color: #dee2e6 !important;">
                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                    <h6 class="text-muted">Menunggu Pemilihan Produk</h6>
                    <small class="text-muted">Data BTKL akan muncul setelah produk dipilih</small>
                </div>
            </div>
        `;
        document.getElementById('bop-container').innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="alert alert-light border-2 border-dashed" style="border-color: #dee2e6 !important;">
                    <i class="fas fa-cogs fa-2x text-muted mb-3"></i>
                    <h6 class="text-muted">Menunggu Pemilihan Produk</h6>
                    <small class="text-muted">Data BOP akan muncul setelah produk dipilih</small>
                </div>
            </div>
        `;
        updateTotals();
    }
    
    function updateTotals() {
        // Calculate totals based on selected items
        let totalBBB = 0;
        let totalBTKL = 0;
        let totalBOP = 0;
        
        // Calculate BBB total from hidden inputs (automatically included)
        document.querySelectorAll('input[name="selected_bbb[]"]').forEach(input => {
            const subtotal = parseFloat(input.dataset.subtotal) || 0;
            totalBBB += subtotal;
        });
        
        // Calculate BTKL total
        document.querySelectorAll('input[name="selected_btkl[]"]:checked').forEach(checkbox => {
            const tarif = parseFloat(checkbox.dataset.tarif) || 0;
            totalBTKL += tarif;
        });
        
        // Calculate BOP total
        document.querySelectorAll('input[name="selected_bop[]"]:checked').forEach(checkbox => {
            const tarif = parseFloat(checkbox.dataset.tarif) || 0;
            totalBOP += tarif;
        });
        
        // Update display
        document.getElementById('total-bbb').textContent = totalBBB.toLocaleString('id-ID');
        document.getElementById('total-btkl').textContent = totalBTKL.toLocaleString('id-ID');
        document.getElementById('total-bop').textContent = totalBOP.toLocaleString('id-ID');
        document.getElementById('total-hpp').textContent = (totalBBB + totalBTKL + totalBOP).toLocaleString('id-ID');
    }
    
    // Add event listeners for checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox' && 
            (e.target.name === 'selected_btkl[]' || 
             e.target.name === 'selected_bop[]')) {
            updateTotals();
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
        }
    });
});
</script>
@endsection

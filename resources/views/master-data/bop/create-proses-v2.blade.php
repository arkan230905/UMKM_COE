@extends('layouts.app')

@section('title', 'Tambah BOP Proses - Bahan Pendukung')

@section('content')
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4 text-dark"><i class="fas fa-chart-pie me-2"></i>Tambah BOP Proses - Bahan Pendukung</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body" style="color: #333 !important;">
            <style>
                .card-body input, .card-body select, .card-body textarea {
                    color: #333 !important;
                    background-color: #fff !important;
                    border: 1px solid #ddd !important;
                }
                .card-body input[readonly] {
                    background-color: #f5f5f5 !important;
                    cursor: not-allowed;
                    color: #666 !important;
                }
                .card-body input::placeholder {
                    color: #999 !important;
                }
                .card-body .input-group-text {
                    color: #333 !important;
                    background-color: #f8f9fa !important;
                    border-color: #ddd !important;
                }
                .info-card {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                .info-card h6 {
                    color: #495057 !important;
                    font-weight: 600;
                }
                .info-card strong {
                    color: #212529 !important;
                }
                .info-card .text-warning {
                    color: #856404 !important;
                }
                .info-card .text-success {
                    color: #155724 !important;
                }
                .table thead th {
                    background-color: #495057 !important;
                    color: white !important;
                    border-color: #6c757d !important;
                }
                .table tbody td {
                    background-color: #fff !important;
                    color: #333 !important;
                    border-color: #dee2e6 !important;
                }
                .form-label {
                    color: #212529 !important;
                    font-weight: 500;
                }
                h5, h6 {
                    color: #212529 !important;
                }
                small.text-muted, small.text-light {
                    color: #6c757d !important;
                }
            </style>
            
            <form action="{{ route('master-data.bop.store-proses-v2') }}" method="POST" id="createBopForm">
                @csrf
                
                <!-- Nama BOP Proses -->
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="nama_bop_proses" class="form-label">Nama BOP Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_bop_proses" 
                               id="nama_bop_proses" 
                               class="form-control @error('nama_bop_proses') is-invalid @enderror" 
                               value="{{ old('nama_bop_proses') }}"
                               placeholder="Contoh: BOP Proses Produksi A"
                               required>
                        @error('nama_bop_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Jumlah Produksi Per Bulan (Global) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="jumlah_produksi_perbulan" class="form-label">
                            Jumlah Produksi Produk Per Bulan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   name="jumlah_produksi_perbulan" 
                                   id="jumlah_produksi_perbulan" 
                                   class="form-control @error('jumlah_produksi_perbulan') is-invalid @enderror" 
                                   value="{{ old('jumlah_produksi_perbulan') }}"
                                   min="1"
                                   placeholder="0"
                                   required>
                            <span class="input-group-text">unit/bulan</span>
                        </div>
                        <small class="text-muted">Target produksi produk dalam satu bulan</small>
                        @error('jumlah_produksi_perbulan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Bahan Pendukung -->
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="fas fa-boxes me-2"></i>Bahan Pendukung
                        </h5>
                        <small class="text-muted">Pilih bahan pendukung yang digunakan dalam proses produksi</small>
                    </div>

                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="bahanPendukungTable">
                                <thead>
                                    <tr>
                                        <th width="15%">Bahan Pendukung</th>
                                        <th width="8%">Satuan</th>
                                        <th width="12%">Harga Per Satuan</th>
                                        <th width="10%">Qty Penggunaan/Bulan</th>
                                        <th width="12%">Total Nominal/Bulan</th>
                                        <th width="10%">Rp/Produk</th>
                                        <th width="12%">COA Debit</th>
                                        <th width="12%">COA Kredit</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bahanPendukungContainer">
                                    <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="button" id="addBahanBtn" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Tambah Bahan Pendukung
                        </button>
                    </div>
                </div>

                <!-- BOP Proses Lainnya -->
                <div class="row g-3 mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="fas fa-cogs me-2"></i>BOP Proses Lainnya
                        </h5>
                        <small class="text-muted">Komponen BOP lainnya seperti listrik, gas, penyusutan mesin, dll</small>
                    </div>

                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="bopLainnyaTable">
                                <thead>
                                    <tr>
                                        <th width="20%">Nama Komponen</th>
                                        <th width="15%">Nominal Per Bulan</th>
                                        <th width="15%">Rp/Produk</th>
                                        <th width="15%">COA Debit</th>
                                        <th width="15%">COA Kredit</th>
                                        <th width="12%">Keterangan</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bopLainnyaContainer">
                                    <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="button" id="addLainnyaBtn" class="btn btn-success btn-sm mt-2">
                            <i class="fas fa-plus"></i> Tambah Komponen Lainnya
                        </button>
                    </div>
                </div>

                <!-- Ringkasan Perhitungan -->
                <div class="info-card mt-4">
                    <h6 class="mb-3"><i class="fas fa-calculator me-2"></i>Ringkasan Total BOP</h6>
                    <div class="row">
                        <div class="col-md-4 text-center border-end">
                            <strong>BOP Bahan Pendukung:</strong><br>
                            <span class="fs-5 text-primary fw-bold">Rp <span id="totalBopBahanPendukung">0</span></span>
                        </div>
                        <div class="col-md-4 text-center border-end">
                            <strong>BOP Lainnya:</strong><br>
                            <span class="fs-5 text-info fw-bold">Rp <span id="totalBopLainnya">0</span></span>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong>Total BOP Per Produk:</strong><br>
                            <span class="fs-4 text-success fw-bold">Rp <span id="totalBopPerProduk">0</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan BOP Proses
                    </button>
                    <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('bahanPendukungContainer');
    const addBtn = document.getElementById('addBahanBtn');
    const lainnyaContainer = document.getElementById('bopLainnyaContainer');
    const addLainnyaBtn = document.getElementById('addLainnyaBtn');
    const jumlahProduksiInput = document.getElementById('jumlah_produksi_perbulan');
    
    // Bahan Pendukung data from backend
    const bahanPendukungList = @json($bahanPendukungs);
    
    let rowCount = 0;
    let lainnyaRowCount = 0;
    
    // Add bahan row function
    function addBahanRow(data = {}) {
        rowCount++;
        const rowId = `bahan_${rowCount}`;
        
        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'bahan-row';
        row.innerHTML = `
            <td>
                <select name="bahan_pendukung[${rowCount}][bahan_pendukung_id]" 
                        class="form-select form-select-sm bahan-select" 
                        data-row-id="${rowId}"
                        required>
                    <option value="">-- Pilih Bahan --</option>
                    ${bahanPendukungList.map(bahan => 
                        `<option value="${bahan.id}" 
                                 data-satuan="${bahan.satuan?.nama || 'Unit'}" 
                                 data-harga="${bahan.harga_satuan || 0}"
                                 ${data.bahan_pendukung_id == bahan.id ? 'selected' : ''}>
                            ${bahan.nama_bahan}
                        </option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="text" 
                       name="bahan_pendukung[${rowCount}][satuan]" 
                       class="form-control form-control-sm satuan-input" 
                       value="${data.satuan || ''}"
                       readonly 
                       placeholder="-">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" 
                           name="bahan_pendukung[${rowCount}][harga_satuan]" 
                           class="form-control form-control-sm harga-satuan-input" 
                           value="${data.harga_satuan || 0}"
                           readonly 
                           step="0.01"
                           placeholder="0">
                </div>
            </td>
            <td>
                <input type="number" 
                       name="bahan_pendukung[${rowCount}][qty_penggunaan_bulan]" 
                       class="form-control form-control-sm qty-input" 
                       data-row-id="${rowId}"
                       value="${data.qty_penggunaan_bulan || ''}"
                       min="0" 
                       step="0.01" 
                       placeholder="0"
                       required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" 
                           class="form-control form-control-sm total-nominal-input" 
                           value="0"
                           readonly>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" 
                           class="form-control form-control-sm rp-produk-input" 
                           value="0"
                           readonly>
                </div>
            </td>
            <td>
                <select name="bahan_pendukung[${rowCount}][coa_debit]" 
                        class="form-select form-select-sm coa-debit-input" 
                        data-row-id="${rowId}">
                    <option value="">-- Pilih --</option>
                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '117%')->orderBy('kode_akun')->get() as $coa)
                        <option value="{{ $coa->kode_akun }}" {{ $coa->kode_akun == '1173' ? 'selected' : '' }}>
                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="bahan_pendukung[${rowCount}][coa_kredit]" 
                        class="form-select form-select-sm coa-kredit-input" 
                        data-row-id="${rowId}">
                    <option value="">-- Pilih --</option>
                    <optgroup label="BOP">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '53%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Beban Sewa">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '54%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="BOP Lain">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '55%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Harga Pokok Penjualan">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '56%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm delete-row" data-row-id="${rowId}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        container.appendChild(row);
        
        // Add event listeners
        attachRowEvents(row);
        
        return row;
    }
    
    // Attach events to row
    function attachRowEvents(row) {
        const bahanSelect = row.querySelector('.bahan-select');
        const qtyInput = row.querySelector('.qty-input');
        const deleteBtn = row.querySelector('.delete-row');
        
        bahanSelect.addEventListener('change', function() {
            updateBahanInfo(row);
        });
        
        qtyInput.addEventListener('input', function() {
            calculateRow(row);
        });
        
        deleteBtn.addEventListener('click', function() {
            if (container.children.length > 1) {
                row.remove();
                updateTotals();
            } else {
                alert('Minimal harus ada 1 bahan pendukung');
            }
        });
    }
    
    // Update bahan info when selected
    function updateBahanInfo(row) {
        const bahanSelect = row.querySelector('.bahan-select');
        const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
        
        const satuanInput = row.querySelector('.satuan-input');
        const hargaSatuanInput = row.querySelector('.harga-satuan-input');
        
        if (bahanSelect.value) {
            const satuan = selectedOption.dataset.satuan || '-';
            const harga = parseFloat(selectedOption.dataset.harga) || 0;
            
            satuanInput.value = satuan;
            hargaSatuanInput.value = harga;
            
            calculateRow(row);
        } else {
            satuanInput.value = '';
            hargaSatuanInput.value = 0;
            row.querySelector('.total-nominal-input').value = 0;
            row.querySelector('.rp-produk-input').value = 0;
            updateTotals();
        }
    }
    
    // Calculate row values
    function calculateRow(row) {
        const hargaSatuan = parseFloat(row.querySelector('.harga-satuan-input').value) || 0;
        const qtyPenggunaan = parseFloat(row.querySelector('.qty-input').value) || 0;
        const jumlahProduksi = parseFloat(jumlahProduksiInput.value) || 1;
        
        // Total Nominal/Bulan = Harga Satuan × Qty Penggunaan
        const totalNominal = hargaSatuan * qtyPenggunaan;
        
        // Rp/Produk = Total Nominal ÷ Jumlah Produksi
        const rpPerProdukRaw = totalNominal / jumlahProduksi;
        
        // ROUND to nearest integer (>= 0.5 rounds up, < 0.5 rounds down)
        const rpPerProduk = Math.round(rpPerProdukRaw);
        
        row.querySelector('.total-nominal-input').value = formatNumber(totalNominal);
        row.querySelector('.rp-produk-input').value = rpPerProduk.toLocaleString('id-ID');
        
        updateTotals();
    }
    
    // Update totals
    function updateTotals() {
        let totalBopBahanPendukung = 0;
        let totalBopLainnya = 0;
        
        // Sum Bahan Pendukung
        document.querySelectorAll('.bahan-row').forEach(row => {
            const rpProdukInput = row.querySelector('.rp-produk-input');
            
            // Parse the displayed rounded value
            const rpProduk = parseFloat(rpProdukInput.value.replace(/\./g, '').replace(/,/g, '')) || 0;
            
            totalBopBahanPendukung += rpProduk;
        });
        
        // Sum BOP Lainnya
        document.querySelectorAll('.lainnya-row').forEach(row => {
            const rpProdukInput = row.querySelector('.rp-produk-lainnya-input');
            
            // Parse the displayed rounded value
            const rpProduk = parseFloat(rpProdukInput.value.replace(/\./g, '').replace(/,/g, '')) || 0;
            
            totalBopLainnya += rpProduk;
        });
        
        // Total BOP Per Produk = Bahan Pendukung + Lainnya
        const totalBopPerProduk = totalBopBahanPendukung + totalBopLainnya;
        
        // Display as integer (already rounded from individual rows)
        document.getElementById('totalBopBahanPendukung').textContent = totalBopBahanPendukung.toLocaleString('id-ID');
        document.getElementById('totalBopLainnya').textContent = totalBopLainnya.toLocaleString('id-ID');
        document.getElementById('totalBopPerProduk').textContent = totalBopPerProduk.toLocaleString('id-ID');
    }
    
    // Recalculate all rows when jumlah produksi changes
    jumlahProduksiInput.addEventListener('input', function() {
        document.querySelectorAll('.bahan-row').forEach(row => {
            calculateRow(row);
        });
    });
    
    // Add button event
    addBtn.addEventListener('click', function() {
        addBahanRow();
    });
    
    // =========================================
    // BOP LAINNYA SECTION
    // =========================================
    
    // Add lainnya row function
    function addLainnyaRow(data = {}) {
        lainnyaRowCount++;
        const rowId = `lainnya_${lainnyaRowCount}`;
        
        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'lainnya-row';
        row.innerHTML = `
            <td>
                <input type="text" 
                       name="bop_lainnya[${lainnyaRowCount}][nama_komponen]" 
                       class="form-control form-control-sm nama-komponen-input" 
                       value="${data.nama_komponen || ''}"
                       placeholder="Contoh: Listrik, Gas, Penyusutan Mesin"
                       required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" 
                           name="bop_lainnya[${lainnyaRowCount}][nominal_per_bulan]" 
                           class="form-control form-control-sm nominal-bulan-input" 
                           data-row-id="${rowId}"
                           value="${data.nominal_per_bulan || ''}"
                           min="0"
                           step="0.01"
                           placeholder="0"
                           required>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" 
                           class="form-control form-control-sm rp-produk-lainnya-input" 
                           value="0"
                           readonly>
                </div>
            </td>
            <td>
                <select name="bop_lainnya[${lainnyaRowCount}][coa_debit]" 
                        class="form-select form-select-sm coa-debit-lainnya-input" 
                        data-row-id="${rowId}">
                    <option value="">-- Pilih --</option>
                    @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '117%')->orderBy('kode_akun')->get() as $coa)
                        <option value="{{ $coa->kode_akun }}" {{ $coa->kode_akun == '1173' ? 'selected' : '' }}>
                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="bop_lainnya[${lainnyaRowCount}][coa_kredit]" 
                        class="form-select form-select-sm coa-kredit-lainnya-input" 
                        data-row-id="${rowId}">
                    <option value="">-- Pilih --</option>
                    <optgroup label="BOP">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '53%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Beban Sewa">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '54%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="BOP Lain">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '55%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Harga Pokok Penjualan">
                        @foreach(\App\Models\Coa::withoutGlobalScopes()->where('user_id', auth()->id())->where('kode_akun', 'LIKE', '56%')->orderBy('kode_akun')->get() as $coa)
                            <option value="{{ $coa->kode_akun }}">
                                {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" 
                       name="bop_lainnya[${lainnyaRowCount}][keterangan]" 
                       class="form-control form-control-sm" 
                       value="${data.keterangan || ''}"
                       placeholder="Keterangan opsional">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm delete-lainnya-row" data-row-id="${rowId}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        lainnyaContainer.appendChild(row);
        
        // Add event listeners
        attachLainnyaRowEvents(row);
        
        return row;
    }
    
    // Attach events to lainnya row
    function attachLainnyaRowEvents(row) {
        const nominalInput = row.querySelector('.nominal-bulan-input');
        const deleteBtn = row.querySelector('.delete-lainnya-row');
        
        nominalInput.addEventListener('input', function() {
            calculateLainnyaRow(row);
        });
        
        deleteBtn.addEventListener('click', function() {
            if (lainnyaContainer.children.length > 1) {
                row.remove();
                updateTotals();
            } else {
                alert('Minimal harus ada 1 komponen BOP lainnya');
            }
        });
    }
    
    // Calculate lainnya row values
    function calculateLainnyaRow(row) {
        const nominalPerBulan = parseFloat(row.querySelector('.nominal-bulan-input').value) || 0;
        const jumlahProduksi = parseFloat(jumlahProduksiInput.value) || 1;
        
        // Rp/Produk = Nominal Per Bulan ÷ Jumlah Produksi
        const rpPerProdukRaw = nominalPerBulan / jumlahProduksi;
        
        // ROUND to nearest integer
        const rpPerProduk = Math.round(rpPerProdukRaw);
        
        row.querySelector('.rp-produk-lainnya-input').value = rpPerProduk.toLocaleString('id-ID');
        
        updateTotals();
    }
    
    // Add button event for lainnya
    addLainnyaBtn.addEventListener('click', function() {
        addLainnyaRow();
    });
    
    // Recalculate lainnya rows when jumlah produksi changes
    const originalJumlahProduksiListener = jumlahProduksiInput.onchange;
    jumlahProduksiInput.addEventListener('input', function() {
        // Recalculate bahan pendukung rows
        document.querySelectorAll('.bahan-row').forEach(row => {
            calculateRow(row);
        });
        
        // Recalculate BOP lainnya rows
        document.querySelectorAll('.lainnya-row').forEach(row => {
            calculateLainnyaRow(row);
        });
    });
    
    // Add initial empty row for Bahan Pendukung
    addBahanRow();
    
    // Add initial empty row for BOP Lainnya
    addLainnyaRow();
    
    // Format number helper
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    }
});
</script>
@endsection

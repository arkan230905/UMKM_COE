@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-link text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h3 class="d-inline ms-2">Bill of Materials (BOM)</h3>
        </div>
        <button type="button" class="btn btn-primary" id="btnBuatBomBaru">
            <i class="bi bi-plus-circle"></i> Buat BOM Baru
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('master-data.bom.store') }}" method="POST" id="bomForm">
        @csrf
        
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Nama Produk Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3" style="background-color: #f8f9fa;">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label class="form-label mb-0 fw-bold" style="color: #6366f1;">NAMA PRODUK</label>
                                </div>
                                <div class="col">
                                    <select name="produk_id" id="produk_id" class="form-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($produks as $produk)
                                            <option value="{{ $produk->id }}" 
                                                data-nama="{{ $produk->nama_produk }}"
                                                data-kode="{{ $produk->kode_produk }}"
                                                {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                                {{ $produk->nama_produk }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Kode: <span id="displayKodeProduk">-</span></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Bahan Baku -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead style="background-color: #6366f1; color: white;">
                            <tr>
                                <th style="width: 20%;">BAHAN BAKU</th>
                                <th style="width: 10%;">JUMLAH</th>
                                <th style="width: 10%;">SATUAN</th>
                                <th style="width: 20%;">HARGA SATUAN (RP)</th>
                                <th style="width: 15%;">SUBTOTAL (RP)</th>
                                <th style="width: 10%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="bomTableBody">
                            <!-- Baris pertama -->
                            <tr data-row-id="1">
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-select" required>
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($bahanBakus as $bahan)
                                            @php
                                                $namaBahan = $bahan->nama_bahan ?? $bahan->nama ?? 'Bahan Tanpa Nama';
                                                // Cek apakah satuan adalah object atau string
                                                if (is_object($bahan->satuan) && isset($bahan->satuan->kode)) {
                                                    $satuanNama = $bahan->satuan->kode;
                                                } elseif (is_string($bahan->satuan)) {
                                                    $satuanNama = $bahan->satuan;
                                                } else {
                                                    $satuanNama = 'KG';
                                                }
                                            @endphp
                                            <option value="{{ $bahan->id }}" 
                                                data-harga="{{ $bahan->harga_satuan ?? 0 }}" 
                                                data-satuan="{{ $satuanNama }}"
                                                data-nama="{{ $namaBahan }}">
                                                {{ $namaBahan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-input" 
                                        value="1" min="0.01" step="0.01" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuan-select" required>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="harga-display fw-bold text-primary">-</div>
                                    <div class="harga-info text-muted small"></div>
                                </td>
                                <td class="text-end subtotal-display fw-bold">Rp 0</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-hapus-first" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="4" class="text-end fw-bold">Total Bahan Baku</td>
                                <td class="fw-bold" id="totalBahanBaku">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr style="background-color: #f0f0f0;">
                                <td colspan="4" class="text-end">BTKL (12%)</td>
                                <td id="totalBTKL">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr style="background-color: #f0f0f0;">
                                <td colspan="4" class="text-end">BOP (26% of BTKL)</td>
                                <td id="totalBOP">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr style="background-color: #e3f2fd;">
                                <td colspan="4" class="text-end fw-bold">Total HPP</td>
                                <td class="fw-bold" id="totalHPP">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary btn-sm mt-2" id="btnTambahBaris">
                    <i class="bi bi-plus"></i> Tambah Baris
                </button>
                <button type="button" class="btn btn-info btn-sm mt-2 ms-2" onclick="testCalculation()">
                    <i class="bi bi-calculator"></i> Test Perhitungan
                </button>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan BOM
            </button>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </div>
    </form>
    </div>
</div>

@push('styles')
<style>
    .table thead th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody td {
        vertical-align: middle;
    }
    
    .harga-info {
        font-size: 0.75rem;
        color: #6c757d;
        font-style: italic;
    }
    
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    
    #bomTable tfoot tr {
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
    let rowCount = 1;
    const bahanBakuData = @json($bahanBakus);
    const satuanData = @json($satuans);
    
    console.log('=== BAHAN BAKU DATA ===');
    console.log(bahanBakuData);
    console.log('=== SATUAN DATA ===');
    console.log(satuanData);

    // Konversi satuan ke KG (untuk menghitung dari satuan lain ke KG)
    const konversiKeKg = {
        'KG': 1,
        'HG': 0.1,
        'DAG': 0.01,
        'G': 0.001,
        'GR': 0.001,
        'GRAM': 0.001,
        'ONS': 0.1,
        'KW': 100,
        'TON': 1000,
        'PCS': 1,
        'PIECES': 1,
        'UNIT': 1,
        'BUAH': 1,
        'BTL': 1,
        'BOTOL': 1,
        'LITER': 1,
        'L': 1,
        'ML': 0.001
    };
    
    // Konversi dari KG ke satuan lain (untuk menghitung harga per satuan)
    const konversiDariKg = {
        'KG': 1,
        'HG': 10,
        'DAG': 100,
        'G': 1000,
        'GR': 1000,
        'GRAM': 1000,
        'ONS': 10,
        'KW': 0.01,
        'TON': 0.001,
        'PCS': 1,
        'PIECES': 1,
        'UNIT': 1,
        'BUAH': 1,
        'BTL': 1,
        'BOTOL': 1,
        'LITER': 1,
        'L': 1,
        'ML': 1000
    };

    // Format angka ke format rupiah
    function formatRupiah(angka) {
        if (!angka || isNaN(angka)) return 'Rp 0';
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(angka));
    }
    
    // Parse rupiah ke number
    function parseRupiah(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/[^0-9.-]+/g, '')) || 0;
    }
    
    // Konversi jumlah ke KG
    function convertToKg(jumlah, satuan) {
        const satuanUpper = satuan.toUpperCase().trim();
        const faktor = konversiKeKg[satuanUpper] || 1;
        return jumlah * faktor;
    }

    
    // Tambah baris baru
    function tambahBaris() {
        rowCount++;
        console.log('Menambah baris ke-' + rowCount);
        
        const tbody = document.getElementById('bomTableBody');
        if (!tbody) {
            console.error('Tbody tidak ditemukan!');
            return;
        }
        
        const row = document.createElement('tr');
        row.dataset.rowId = rowCount;
        
        let satuanOptions = '';
        satuanData.forEach(satuan => {
            satuanOptions += `<option value="${satuan.kode}">${satuan.kode}</option>`;
        });
        
        let bahanOptions = '<option value="">-- Pilih Bahan Baku --</option>';
        bahanBakuData.forEach(bahan => {
            const namaBahan = bahan.nama_bahan || bahan.nama || 'Bahan Tanpa Nama';
            // Cek apakah satuan adalah object atau string
            let satuanNama = 'KG';
            if (bahan.satuan) {
                if (typeof bahan.satuan === 'object' && bahan.satuan.kode) {
                    satuanNama = bahan.satuan.kode;
                } else if (typeof bahan.satuan === 'string') {
                    satuanNama = bahan.satuan;
                }
            }
            bahanOptions += `<option value="${bahan.id}" 
                data-harga="${bahan.harga_satuan || 0}" 
                data-satuan="${satuanNama}"
                data-nama="${namaBahan}">
                ${namaBahan}
            </option>`;
        });
        
        row.innerHTML = `
            <td>
                <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-select" required>
                    ${bahanOptions}
                </select>
            </td>
            <td>
                <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-input" 
                    value="1" min="0.01" step="0.01" required>
            </td>
            <td>
                <select name="satuan[]" class="form-select form-select-sm satuan-select" required>
                    ${satuanOptions}
                </select>
            </td>
            <td>
                <div class="harga-display">-</div>
                <div class="harga-info"></div>
            </td>
            <td class="text-end subtotal-display">Rp 0</td>
            <td class="text-center">
                <button type="button" class="btn btn-warning btn-sm me-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm btn-hapus" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        // Setup event listeners untuk baris baru
        attachRowEvents(row);
        
        hitungTotal();
        console.log('Baris berhasil ditambahkan');
    }
    
    // Update harga satuan saat bahan baku dipilih
    function updateHargaSatuan(selectElement) {
        console.log('=== UPDATE HARGA SATUAN ===');
        const row = selectElement.closest('tr');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        console.log('Selected option:', selectedOption);
        console.log('Selected value:', selectedOption.value);
        console.log('Dataset harga:', selectedOption.dataset.harga);
        console.log('Dataset satuan:', selectedOption.dataset.satuan);
        console.log('Dataset nama:', selectedOption.dataset.nama);
        
        if (!selectedOption.value) {
            console.log('No value selected, resetting');
            row.querySelector('.harga-display').textContent = '-';
            row.querySelector('.harga-info').textContent = '';
            row.querySelector('.subtotal-display').textContent = 'Rp 0';
            hitungTotal();
            return;
        }
        
        const hargaPerSatuanUtama = parseFloat(selectedOption.dataset.harga) || 0;
        const satuanUtama = (selectedOption.dataset.satuan || 'KG').toUpperCase();
        const namaBahan = selectedOption.dataset.nama || '';
        
        console.log('Parsed - Harga per', satuanUtama + ':', hargaPerSatuanUtama);
        console.log('Parsed - Satuan utama:', satuanUtama);
        console.log('Parsed - Nama bahan:', namaBahan);
        
        // ALERT jika harga = 0
        if (hargaPerSatuanUtama === 0) {
            alert('⚠️ PERHATIAN!\n\nBahan baku "' + namaBahan + '" belum memiliki harga.\n\nSilakan lakukan PEMBELIAN terlebih dahulu untuk bahan baku ini agar harga tersimpan di sistem.');
            row.querySelector('.harga-display').textContent = 'Belum ada harga';
            row.querySelector('.harga-info').textContent = '⚠️ Lakukan pembelian dulu';
            row.querySelector('.subtotal-display').textContent = 'Rp 0';
            hitungTotal();
            return;
        }
        
        // Set satuan default
        const satuanSelect = row.querySelector('.satuan-select');
        satuanSelect.value = satuanUtama;
        
        // Simpan data di row
        row.dataset.hargaPerKg = hargaPerSatuanUtama;
        row.dataset.satuanUtama = satuanUtama;
        row.dataset.namaBahan = namaBahan;
        
        console.log('Row dataset saved:', {
            hargaPerKg: row.dataset.hargaPerKg,
            satuanUtama: row.dataset.satuanUtama,
            namaBahan: row.dataset.namaBahan
        });
        
        console.log('Calling hitungSubtotal...');
        hitungSubtotal(row.querySelector('.jumlah-input'));
        console.log('=== END UPDATE HARGA SATUAN ===\n');
    }
    
    // Hitung subtotal per baris
    function hitungSubtotal(inputElement) {
        console.log('=== HITUNG SUBTOTAL ===');
        const row = inputElement.closest('tr');
        const jumlah = parseFloat(row.querySelector('.jumlah-input').value) || 0;
        const satuan = row.querySelector('.satuan-select').value.toUpperCase();
        const hargaPerSatuanUtama = parseFloat(row.dataset.hargaPerKg) || 0;
        const satuanUtama = (row.dataset.satuanUtama || 'KG').toUpperCase();
        
        console.log('Input - Jumlah:', jumlah, 'Satuan:', satuan);
        console.log('Data - Harga per', satuanUtama + ':', hargaPerSatuanUtama);
        
        if (hargaPerSatuanUtama === 0) {
            console.log('Harga = 0, reset display');
            row.querySelector('.harga-display').textContent = '-';
            row.querySelector('.harga-info').textContent = '';
            row.querySelector('.subtotal-display').textContent = 'Rp 0';
            hitungTotal();
            return;
        }
        
        // LANGKAH 1: Hitung harga per satuan yang dipilih
        // Rumus: Harga per satuan terpilih = Harga per satuan utama / (jumlah satuan terpilih dalam 1 satuan utama)
        let hargaPerSatuanTerpilih;
        let infoText = '';
        
        if (satuan === satuanUtama) {
            // Jika satuan sama dengan satuan utama, harga langsung
            hargaPerSatuanTerpilih = hargaPerSatuanUtama;
            infoText = `Harga per ${satuan}`;
        } else {
            // Konversi harga
            // Contoh: Harga per KG = 10000, mau tau harga per G
            // 1 KG = 1000 G, jadi harga per G = 10000 / 1000 = 10
            const faktorDariKg = konversiDariKg[satuan] || 1;
            const faktorUtamaKeKg = konversiKeKg[satuanUtama] || 1;
            
            // Konversi harga satuan utama ke harga per KG dulu
            const hargaPerKg = hargaPerSatuanUtama / faktorUtamaKeKg;
            
            // Lalu konversi ke satuan yang dipilih
            hargaPerSatuanTerpilih = hargaPerKg / faktorDariKg;
            
            infoText = `(1 ${satuanUtama} = ${formatRupiah(hargaPerSatuanUtama)})`;
            
            console.log('Konversi harga:');
            console.log('- Faktor', satuanUtama, 'ke KG:', faktorUtamaKeKg);
            console.log('- Harga per KG:', hargaPerKg);
            console.log('- Faktor KG ke', satuan + ':', faktorDariKg);
            console.log('- Harga per', satuan + ':', hargaPerSatuanTerpilih);
        }
        
        // LANGKAH 2: Hitung subtotal
        const subtotal = hargaPerSatuanTerpilih * jumlah;
        
        console.log('Hasil:');
        console.log('- Harga per', satuan + ':', hargaPerSatuanTerpilih);
        console.log('- Subtotal:', jumlah, 'x', hargaPerSatuanTerpilih, '=', subtotal);
        
        // LANGKAH 3: Update tampilan
        row.querySelector('.harga-display').textContent = formatRupiah(hargaPerSatuanTerpilih);
        row.querySelector('.harga-info').textContent = infoText;
        row.querySelector('.subtotal-display').textContent = formatRupiah(subtotal);
        
        console.log('=== SELESAI ===\n');
        hitungTotal();
    }
    
    // Hitung total semua
    function hitungTotal() {
        let totalBahanBaku = 0;
        
        document.querySelectorAll('#bomTableBody tr').forEach(row => {
            const subtotalText = row.querySelector('.subtotal-display').textContent;
            const subtotal = parseRupiah(subtotalText);
            totalBahanBaku += subtotal;
        });
        
        // Hitung BTKL (12% dari total bahan baku)
        const btkl = totalBahanBaku * 0.12;
        
        // Hitung BOP (26% dari BTKL)
        const bop = btkl * 0.26;
        
        // Hitung total HPP
        const totalHPP = totalBahanBaku + btkl + bop;
        
        // Update tampilan
        document.getElementById('totalBahanBaku').textContent = formatRupiah(totalBahanBaku);
        document.getElementById('totalBTKL').textContent = formatRupiah(btkl);
        document.getElementById('totalBOP').textContent = formatRupiah(bop);
        document.getElementById('totalHPP').textContent = formatRupiah(totalHPP);
    }
    
    // Edit baris (tidak digunakan lagi, tapi tetap ada untuk kompatibilitas)
    function editBaris(rowId) {
        console.log('Edit baris:', rowId);
    }
    
    // Hapus baris (tidak digunakan lagi, tapi tetap ada untuk kompatibilitas)
    function hapusBaris(rowId) {
        console.log('Hapus baris:', rowId);
    }
    
    // Update display produk
    document.getElementById('produk_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('displayKodeProduk').textContent = selectedOption.dataset.kode || '-';
        } else {
            document.getElementById('displayKodeProduk').textContent = '-';
        }
    });
    
    // Tombol tambah baris
    document.getElementById('btnTambahBaris').addEventListener('click', function() {
        console.log('Tombol tambah baris diklik');
        tambahBaris();
    });
    
    // Tombol buat BOM baru
    document.getElementById('btnBuatBomBaru').addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin membuat BOM baru? Data yang belum disimpan akan hilang.')) {
            location.reload();
        }
    });
    
    // Form submit
    document.getElementById('bomForm').addEventListener('submit', function(e) {
        const produkId = document.getElementById('produk_id').value;
        const tbody = document.getElementById('bomTableBody');
        
        if (!produkId) {
            e.preventDefault();
            alert('Silakan pilih produk terlebih dahulu!');
            return false;
        }
        
        if (tbody.children.length === 0) {
            e.preventDefault();
            alert('Silakan tambahkan minimal satu bahan baku!');
            return false;
        }
        
        // Validasi semua baris terisi
        let valid = true;
        tbody.querySelectorAll('tr').forEach(row => {
            const bahanSelect = row.querySelector('.bahan-select');
            const jumlahInput = row.querySelector('.jumlah-input');
            
            if (!bahanSelect.value || !jumlahInput.value || parseFloat(jumlahInput.value) <= 0) {
                valid = false;
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Pastikan semua bahan baku terisi dengan benar!');
            return false;
        }
    });
    
    // Fungsi untuk attach event listeners ke row
    function attachRowEvents(row) {
        const bahanSelect = row.querySelector('.bahan-select');
        const jumlahInput = row.querySelector('.jumlah-input');
        const satuanSelect = row.querySelector('.satuan-select');
        const btnHapus = row.querySelector('.btn-hapus, .btn-hapus-first');
        
        // Event: Bahan baku berubah
        bahanSelect.addEventListener('change', function() {
            console.log('Bahan baku changed:', this.value);
            updateHargaSatuan(this);
        });
        
        // Event: Jumlah berubah
        jumlahInput.addEventListener('input', function() {
            console.log('Jumlah changed:', this.value);
            hitungSubtotal(this);
        });
        
        // Event: Satuan berubah
        satuanSelect.addEventListener('change', function() {
            console.log('Satuan changed:', this.value);
            hitungSubtotal(jumlahInput);
        });
        
        // Event: Hapus baris
        if (btnHapus) {
            btnHapus.addEventListener('click', function() {
                const tbody = document.getElementById('bomTableBody');
                if (tbody.children.length <= 1) {
                    alert('Minimal harus ada satu bahan baku!');
                    return;
                }
                if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
                    row.remove();
                    hitungTotal();
                }
            });
        }
    }
    
    // Inisialisasi: setup event listener untuk baris pertama
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== DOM LOADED ===');
        console.log('Initializing BOM form...');
        
        // Setup event listener untuk baris pertama
        const firstRow = document.querySelector('#bomTableBody tr[data-row-id="1"]');
        if (firstRow) {
            console.log('First row found, attaching events...');
            attachRowEvents(firstRow);
            console.log('Events attached successfully!');
        } else {
            console.error('First row not found!');
        }
        
        console.log('=== INITIALIZATION COMPLETE ===');
    });
    
    // Fungsi test untuk debugging
    window.testCalculation = function() {
        console.log('=== TEST CALCULATION ===');
        const firstRow = document.querySelector('#bomTableBody tr[data-row-id="1"]');
        if (!firstRow) {
            alert('Baris pertama tidak ditemukan!');
            return;
        }
        
        const bahanSelect = firstRow.querySelector('.bahan-select');
        const jumlahInput = firstRow.querySelector('.jumlah-input');
        const satuanSelect = firstRow.querySelector('.satuan-select');
        
        console.log('Bahan Select:', bahanSelect ? bahanSelect.value : 'NOT FOUND');
        console.log('Jumlah Input:', jumlahInput ? jumlahInput.value : 'NOT FOUND');
        console.log('Satuan Select:', satuanSelect ? satuanSelect.value : 'NOT FOUND');
        
        if (bahanSelect && bahanSelect.value) {
            console.log('Triggering updateHargaSatuan manually...');
            updateHargaSatuan(bahanSelect);
            alert('Perhitungan dipicu! Lihat console untuk detail.');
        } else {
            alert('Silakan pilih bahan baku terlebih dahulu!');
        }
    };
</script>
@endpush

@endsection

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4">Tambah BOM</h1>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('master-data.bom.store')); ?>" method="POST" id="bomForm" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Silakan tambahkan bahan baku yang dibutuhkan. BTKL dan BOP akan dihitung otomatis oleh sistem.
        </div>

        <div class="mb-3">
            <label for="produk_id" class="form-label">Produk</label>
            <select name="produk_id" id="produk_id" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                <?php $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($produk->id); ?>" <?php echo e(old('produk_id') == $produk->id ? 'selected' : ''); ?>>
                        <?php echo e($produk->nama_produk); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="card mb-4">
            <div class="card-header" style="background-color: #2c3e50 !important; border-bottom: 1px solid rgba(0,0,0,.125) !important;">
                <h5 class="mb-0" style="color: #ffffff !important; margin: 0 !important;">Bahan Baku</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead class="table-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga Utama</th>
                                <th>Harga 1</th>
                                <th>Harga 2</th>
                                <th>Harga 3</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select bahanSelect" required>
                                        <option value="">-- Pilih Bahan --</option>
                                        <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $satuanNama = 'Satuan';
                                                $harga = $bahan->harga_satuan ?? 0;
                                                $namaBahan = $bahan->nama ?? $bahan->nama_bahan ?? 'Bahan Tanpa Nama';
                                                
                                                // Cek apakah relasi satuan ada dan valid
                                                if (isset($bahan->satuan) && is_object($bahan->satuan) && property_exists($bahan->satuan, 'nama')) {
                                                    $satuanNama = $bahan->satuan->nama;
                                                    $namaBahan .= ' (' . $bahan->satuan->nama . ')';
                                                }
                                            ?>
                                            <option value="<?php echo e($bahan->id); ?>" 
                                                data-satuan="<?php echo e($satuanNama); ?>"
                                                data-harga-kg="<?php echo e($bahan->harga_satuan); ?>"
                                                data-harga-hg="<?php echo e($bahan->harga_satuan * 0.1); ?>"
                                                data-harga-dag="<?php echo e($bahan->harga_satuan * 0.01); ?>"
                                                data-harga-gr="<?php echo e($bahan->harga_satuan / 1000); ?>"
                                                data-satuan-utama="<?php echo e($satuanNama); ?>">
                                                <?php echo e($namaBahan); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="jumlah[]" class="form-control jumlahInput" value="1" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuanSelect">
                                        <?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($satuan->kode); ?>" data-nama="<?php echo e($satuan->nama); ?>">
                                                <?php echo e($satuan->nama); ?> (<?php echo e($satuan->kode); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="text-center harga-utama">-</td>
                                <td class="text-center harga-1">-</td>
                                <td class="text-center harga-2">-</td>
                                <td class="text-center harga-3">-</td>
                                <td>
                                    <select name="kategori[]" class="form-select form-select-sm">
                                        <option value="BOP">BOP</option>
                                        <option value="BTKL">BTKL</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="addRow">Tambah Baris</button>
            </div>
        </div>


        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Simpan BOM</button>
            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">Batal</a>
        </form>
        
        <!-- Debug Form -->
        <div class="mt-5">
            <h4>Debug Info:</h4>
            <div class="card">
                <div class="card-body">
                    <h5>Form Data:</h5>
                    <pre id="formDataDebug"></pre>
                    
                    <h5 class="mt-3">Response:</h5>
                    <pre id="responseDebug">Belum ada respon</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('master-data.bom.js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Reset all text colors to white */
    body, 
    h1, h2, h3, h4, h5, h6,
    .card, 
    .card-header, 
    .card-body, 
    .card-title,
    .form-label,
    label,
    .text-muted,
    .input-group-text,
    .table,
    .table th, 
    .table td,
    .form-control,
    .form-select,
    select,
    input,
    textarea {
        color: #ffffff !important;
    }
    
    /* Card header styles */
    .card-header {
        background-color: #2c3e50 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    .card-header h5 {
        margin: 0 !important;
        font-weight: 600 !important;
        color: #ffffff !important;
    }

    /* Style untuk dark mode */
    .card {
        background-color: #2c2c3e;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .card-body {
        background-color: #2c2c3e;
        color: #ffffff;
    }
    
    /* Form elements styling */
    .form-control, 
    .form-select, 
    .input-group-text,
    .form-select option,
    select,
    input,
    textarea,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background-color: #3a3b4a !important;
        border-color: #4a4b5a !important;
        color: #ffffff !important;
    }
    
    /* Style for dropdown options */
    .form-select option {
        background-color: #3a3b4a;
        color: #ffffff;
        padding: 8px 12px;
    }
    
    /* Hover state for dropdown options */
    .form-select option:hover,
    .form-select option:focus {
        background-color: #6c63ff !important;
        color: #ffffff !important;
    }
    
    /* Style untuk dropdown yang terbuka */
    .form-select:focus option:checked,
    .form-select option:checked,
    .form-select option:hover {
        background-color: #6c63ff !important;
        color: #ffffff !important;
    }
    
    .form-control:focus, 
    .form-select:focus {
        background-color: #3a3b4a !important;
        border-color: #6c63ff !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25) !important;
    }
    
    .form-control:disabled, 
    .form-control[readonly] {
        background-color: #2a2b3a !important;
        color: #a0a0a0 !important;
    }
    
    /* Style untuk teks pada form */
    .form-text,
    .text-muted {
        color: #b3b3b3 !important;
    }
    
    /* Style untuk card body */
    .card-body {
        color: #ffffff !important;
    }
    
    /* Style untuk table */
    .table {
        color: #ffffff !important;
    }
    
    /* Style untuk input group text */
    .input-group-text {
        background-color: #2c3e50 !important;
        color: #ffffff !important;
        border-color: #4a4b5a !important;
    }

    .table th, .table td {
        vertical-align: middle;
    }
    .form-control:readonly {
        background-color: #f8f9fa;
    }
    /* Perbaikan kontras teks pada form */
    .form-control, .form-select, .input-group-text {
        color: #212529; /* Warna teks lebih gelap */
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    /* Perbaikan kontras pada dropdown */
    .form-select option {
        color: #212529;
        background-color: #fff;
    }
    /* Perbaikan kontras pada input number */
    input[type="number"] {
        -moz-appearance: textfield;
    }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    /* Perbaikan kontras pada card header */
    .card-header {
        background-color: #2c3e50 !important; /* Warna latar lebih gelap */
        border-bottom: 1px solid rgba(0,0,0,.125) !important;
    }
    .card-header h5, .card-header h5 * {
        color: #ffffff !important; /* Warna teks putih untuk judul */
        margin: 0 !important;
    }
    /* Perbaikan kontras pada tombol */
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    /* Perbaikan kontras pada input group */
    .input-group-text {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        color: #212529;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let rowCount = 1;

    // Format angka ke format rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(angka);
    }

    // Fungsi untuk mendapatkan konversi satuan ke KG
    function getKonversiKeKg(satuanKode) {
        const konversi = {
            'KG': 1,
            'HG': 0.1,
            'DAG': 0.01,
            'G': 0.001,
            'GR': 0.001,
            'ONS': 0.1,
            'KW': 100,
            'TON': 1000,
            // Tambahkan konversi satuan lainnya di sini
        };
        
        // Cari satuan yang cocok (case insensitive)
        const satuanUpper = satuanKode.toUpperCase();
        return konversi[satuanUpper] || 1; // Default 1 jika tidak ditemukan
    }
    
    // Update harga saat bahan baku dipilih
    function updateHarga(selectElement) {
        const row = selectElement.closest('tr');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        if (selectedOption.value) {
            const hargaSatuan = parseFloat(selectedOption.getAttribute('data-harga-satuan')) || 0;
            const satuanAsal = selectedOption.getAttribute('data-satuan-utama') || 'KG';
            
            // Simpan data harga dan satuan di row untuk perhitungan
            row.dataset.hargaSatuan = hargaSatuan;
            row.dataset.satuanAsal = satuanAsal;
            
            // Dapatkan semua satuan yang tersedia
            const satuanSelect = row.querySelector('.satuanSelect');
            const satuanOptions = Array.from(satuanSelect.options).map(opt => ({
                kode: opt.value,
                nama: opt.text.split('(')[0].trim()
            }));
            
            // Update harga untuk setiap kolom harga
            satuanOptions.forEach((satuan, index) => {
                if (index < 4) { // Hanya tampilkan 4 harga (utama + 3 lainnya)
                    const hargaElement = row.querySelector(`.harga-${index === 0 ? 'utama' : index}`);
                    if (hargaElement) {
                        const konversiKeKg = getKonversiKeKg(satuan.kode);
                        const hargaDalamSatuan = (hargaSatuan / konversiKeKg).toFixed(2);
                        hargaElement.textContent = `${formatRupiah(hargaDalamSatuan)}/${satuan.kode}`;
                        hargaElement.dataset.harga = hargaDalamSatuan;
                    }
                }
            });
            
            // Set satuan default jika belum dipilih
            if (!satuanSelect.value) {
                satuanSelect.value = satuanAsal;
            }
            
            // Hitung ulang subtotal
            hitungSubtotal(selectElement);
        } else {
            // Reset harga jika tidak ada yang dipilih
            row.querySelectorAll('.harga-utama, .harga-1, .harga-2, .harga-3').forEach(el => {
                el.textContent = '-';
            });
        }
    }
    
    // Hitung subtotal saat jumlah atau satuan berubah
    function hitungSubtotal(inputElement) {
        const row = inputElement.closest('tr');
        const selectBahan = row.querySelector('.bahanSelect');
        const selectedOption = selectBahan.options[selectBahan.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) return;
        
        const jumlah = parseFloat(inputElement.value) || 0;
        const satuanSelect = row.querySelector('.satuanSelect');
        const satuanKode = satuanSelect.value;
        const satuanNama = satuanSelect.options[satuanSelect.selectedIndex].getAttribute('data-nama') || satuanKode;
        
        // Dapatkan harga satuan dan satuan asal dari bahan baku
        const hargaSatuan = parseFloat(row.dataset.hargaSatuan) || 0;
        const satuanAsal = row.dataset.satuanAsal || 'KG';
        
        // Dapatkan konversi ke KG untuk satuan asal dan satuan yang dipilih
        const konversiSatuanAsal = getKonversiKeKg(satuanAsal);
        const konversiSatuanDipilih = getKonversiKeKg(satuanKode);
        
        // Hitung harga dalam satuan yang dipilih
        const hargaDalamSatuanDipilih = (hargaSatuan * konversiSatuanAsal) / konversiSatuanDipilih;
        
        // Hitung subtotal
        const subtotal = jumlah * hargaDalamSatuanDipilih;
        
        // Update tampilan subtotal
        if (row.querySelector('.subtotal')) {
            row.querySelector('.subtotal').textContent = formatRupiah(subtotal);
        }
        
        // Update nilai input hidden untuk perhitungan di server
        const hargaInput = row.querySelector('input[name^="harga_satuan"]') || document.createElement('input');
        hargaInput.type = 'hidden';
        hargaInput.name = 'harga_satuan[]';
        hargaInput.value = hargaDalamSatuanDipilih;
        if (!row.contains(hargaInput)) {
            row.appendChild(hargaInput);
        }
        
        // Update total biaya
        hitungTotalBiaya();
    }
    function formatRupiah(angka) {
        if (!angka) return 'Rp 0';
        
        let number_string = angka.toString().replace(/[^\d]/g, '');
        let reverse = number_string.split('').reverse().join('');
        let ribuan = reverse.match(/\d{1,3}/g);
        
        if (ribuan) {
            let separator = ribuan.join('.').split('').reverse().join('');
            return 'Rp ' + separator;
        }
        
        return 'Rp ' + number_string;
    }

    // Hitung total biaya
    function hitungTotalBiaya() {
        let total = 0;
        
        document.querySelectorAll('#bomTable tbody tr').forEach(row => {
            const kuantitas = parseFloat(row.querySelector('.kuantitas').value) || 0;
        
        // Kode ini akan dihapus karena sudah ada di atas
    }

    // Format input number dengan pemisah ribuan
    function formatNumberInput(input) {
        // Hapus karakter selain angka
        let value = input.value.replace(/\D/g, '');
        
        // Format dengan pemisah ribuan
        value = new Intl.NumberFormat('id-ID').format(value);
        
        // Set nilai input
        input.value = value;
    }

    // Fungsi untuk menambah baris bahan baku
    document.getElementById('addRow').addEventListener('click', function() {
        rowCount++;
        const tbody = document.querySelector('#bomTable tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <select name="bahan_baku_id[]" class="form-select bahan-select" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $namaBahan = $bahan->nama ?? $bahan->nama_bahan ?? 'Bahan Tanpa Nama';
                            $satuanNama = 'pcs';
                            if (isset($bahan->satuan) && is_object($bahan->satuan) && property_exists($bahan->satuan, 'nama')) {
                                $satuanNama = $bahan->satuan->nama;
                                $namaBahan .= ' (' . $satuanNama . ')';
                            }
                        ?>
                        <option value="<?php echo e($bahan->id); ?>" data-satuan="<?php echo e($satuanNama); ?>" data-harga="<?php echo e($bahan->harga_satuan ?? 0); ?>">
                            <?php echo e($namaBahan); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </td>
            <td>
                <input type="number" name="jumlah[]" class="form-control kuantitas" 
                       min="0.01" step="0.0001" value="1" required>
            </td>
            <td>
                <input type="text" name="satuan[]" class="form-control satuan" value="" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        initRowEvents(newRow);
    });

    // Inisialisasi event untuk baris pertama
    document.addEventListener('DOMContentLoaded', function() {
        const firstRow = document.querySelector('#bomTable tbody tr');
        if (firstRow) {
            initRowEvents(firstRow);
        }
    });

    // Fungsi untuk inisialisasi event pada setiap baris
    function initRowEvents(row) {
        // Hapus baris
        const removeBtn = row.querySelector('.removeRow');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const tbody = this.closest('tbody');
                const rows = tbody.querySelectorAll('tr');
                
                // Pastikan setidaknya ada satu baris
                if (rows.length > 1) {
                    this.closest('tr').remove();
                } else {
                    alert('Minimal harus ada satu bahan baku');
                }
            });
        }

        // Update satuan saat bahan baku dipilih
        const select = row.querySelector('.bahan-select');
        if (select) {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const row = this.closest('tr');
                const satuanInput = row.querySelector('.satuan');
                
                if (selectedOption && satuanInput) {
                    satuanInput.value = selectedOption.dataset.satuan || 'pcs';
                }
            });
            
            // Trigger change event untuk mengisi satuan awal jika sudah ada nilai
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }
        }
    }

    // Event listener untuk perubahan bahan baku
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('bahanSelect')) {
            updateHarga(e.target);
        } else if (e.target.classList.contains('jumlahInput') || e.target.classList.contains('satuanSelect')) {
            hitungSubtotal(e.target);
        }
    });

    // Inisialisasi harga untuk baris yang sudah ada
    document.querySelectorAll('.bahanSelect').forEach(select => {
        if (select.value) {
            updateHarga(select);
        }
    });

    // Fungsi untuk mengecek validasi form
    function validateForm() {
        const bahanBakuInputs = document.querySelectorAll('select[name="bahan_baku_id[]"]');
        let isValid = false;
        let hasEmptyBahanBaku = false;
        
        // Cek apakah ada bahan baku yang dipilih
        bahanBakuInputs.forEach(input => {
            if (input.value) {
                isValid = true;
            } else {
                hasEmptyBahanBaku = true;
            }
        });
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Minimal harus memilih satu bahan baku',
                confirmButtonColor: '#3085d6',
            });
            return { valid: false };
        }
        
        return { 
            valid: true,
            hasEmptyBahanBaku: hasEmptyBahanBaku 
        };
    }
    
    // Fungsi untuk menampilkan konfirmasi
    function showConfirmation(callback) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan BOM ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    }
    
    // Fungsi untuk submit form
    function submitForm() {
        const form = document.getElementById('bomForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Tampilkan loading
            }
        });

        // Fungsi untuk inisialisasi event pada setiap baris
        function initRowEvents(row) {
            // Hapus baris
            const removeBtn = row.querySelector('.removeRow');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    const tbody = this.closest('tbody');
                    const rows = tbody.querySelectorAll('tr');
                    
                    // Pastikan setidaknya ada satu baris
                    if (rows.length > 1) {
                        this.closest('tr').remove();
                    } else {
                        alert('Minimal harus ada satu bahan baku');
                    }
                });
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
    
    // Event listener untuk form submission
    document.getElementById('bomForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validasi form
        const validation = validateForm();
        if (!validation.valid) return false;
        
        // Tampilkan konfirmasi
        showConfirmation(submitForm);
        
        return false;
    });
    
    // Fungsi untuk menghitung total biaya
    function hitungTotalBiaya() {
        let total = 0;
        
        document.querySelectorAll('#bomTable tbody tr').forEach(row => {
            const kuantitas = parseFloat(row.querySelector('.kuantitas').value) || 0;
            const hargaSatuan = parseFloat(row.querySelector('.harga-satuan').value) || 0;
            const subtotal = kuantitas * hargaSatuan;
            
            row.querySelector('.subtotal').textContent = formatRupiah(subtotal);
            total += subtotal;
        });
        
        // Hitung BTKL (30% dari total bahan baku)
        const btkl = total * 0.3;
        
        // Hitung BOP (20% dari total bahan baku)
        const bop = total * 0.2;
        
        // Hitung total biaya produksi
        const totalBiayaProduksi = total + btkl + bop;
        
        // Update tampilan
        document.querySelector('.total-bahan-baku').textContent = formatRupiah(total);
        document.querySelector('.total-btkl').textContent = formatRupiah(btkl);
        document.querySelector('.total-bop').textContent = formatRupiah(bop);
        document.querySelector('.total-biaya').textContent = formatRupiah(totalBiayaProduksi);
    }
    
    // Event listener untuk perubahan kuantitas dan harga satuan
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('kuantitas') || e.target.classList.contains('harga-satuan')) {
            hitungTotalBiaya();
        }
    });
    
    // Panggil fungsi hitungTotalBiaya saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotalBiaya();
    });

    // Generate kode BOM
    document.getElementById('generateKode').addEventListener('click', function() {
        fetch('<?php echo e(route("master-data.bom.generate-kode")); ?>')
            .then(response => response.json())
            .then(data => {
                document.getElementById('kode_bom').value = data.kode_bom;
            });
    });

    // Hitung total biaya saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotalBiaya();
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/create.blade.php ENDPATH**/ ?>
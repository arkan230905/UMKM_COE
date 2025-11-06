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

    <form action="<?php echo e(route('master-data.bom.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

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
                                <th width="40%">Bahan Baku</th>
                                <th width="15%">Jumlah</th>
                                <th width="25%">Satuan Resep</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select bahanSelect" required>
                                        <option value="">-- Pilih Bahan --</option>
                                        <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $satuan = $bahan->satuan ? $bahan->satuan->nama : 'Satuan';
                                                $harga = $bahan->harga_satuan ?? 0; // gunakan harga_satuan aktual
                                            ?>
                                            <option value="<?php echo e($bahan->id); ?>" data-satuan="<?php echo e($satuan); ?>" data-harga="<?php echo e($harga); ?>"><?php echo e($bahan->nama ?? $bahan->nama_bahan ?? 'Bahan'); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="jumlah[]" class="form-control jumlahInput" value="1" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuanSelect">
                                        <option value="">(ikuti satuan bahan)</option>
                                        <?php $__currentLoopData = ($satuans ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sat->kode); ?>"><?php echo e($sat->kode); ?> (<?php echo e($sat->nama); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

        <div class="d-flex justify-content-between">
            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-success">Simpan BOM & Hitung Harga Jual</button>
        </div>
    </form>
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
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    // Hitung total biaya
    function hitungTotalBiaya() {
        let total = 0;
        
        document.querySelectorAll('#bomTable tbody tr').forEach(row => {
            const kuantitas = parseFloat(row.querySelector('.kuantitas').value) || 0;
            const hargaSatuan = parseFloat(row.querySelector('.bahan-select option:checked').dataset.harga) || 0;
            total += kuantitas * hargaSatuan;
        });
        
        document.getElementById('totalBiaya').textContent = 'Rp ' + formatRupiah(total.toFixed(0));
        document.getElementById('totalBiayaField').value = 'Rp ' + formatRupiah(total.toFixed(0));
        
        // Hitung harga jual
        hitungHargaJual(total);
        
        return total;
    }
    
    // Hitung harga jual berdasarkan total biaya dan persentase keuntungan
    function hitungHargaJual(totalBiaya) {
        const persentase = parseFloat(document.getElementById('persentase_keuntungan').value) || 0;
        const keuntungan = totalBiaya * (persentase / 100);
        const hargaJual = totalBiaya + keuntungan;
        
        document.getElementById('hargaJual').value = 'Rp ' + formatRupiah(hargaJual.toFixed(0));
    }

    // Fungsi untuk menambah baris bahan baku
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#bomTable tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <select name="details[${rowCount}][bahan_baku_id]" class="form-select bahan-select" required>
                    <option value="">-- Pilih Bahan Baku --</option>
                    <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($bahan->id); ?>" data-harga="<?php echo e($bahan->harga_satuan); ?>">
                            <?php echo e($bahan->nama_bahan); ?> (<?php echo e($bahan->satuan->nama_satuan ?? '-'); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </td>
            <td>
                <input type="number" name="details[${rowCount}][kuantitas]" class="form-control kuantitas" 
                       min="0.01" step="0.0001" value="1" required>
            </td>
            <td>
                <input type="text" class="form-control satuan" 
                       value="<?php echo e($bahanBakus->first() && $bahanBakus->first()->satuan ? $bahanBakus->first()->satuan->nama_satuan : ''); ?>" 
                       readonly>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control harga-satuan" 
                           value="<?php echo e($bahanBakus->first() ? number_format($bahanBakus->first()->harga_satuan, 0, ',', '.') : '0'); ?>" 
                           readonly>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm removeRow">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        rowCount++;
        
        // Inisialisasi event listener untuk baris baru
        initRowEvents(newRow);
    });

    // Fungsi untuk menghapus baris bahan baku
    function initRowEvents(row) {
        // Hapus baris
        const removeBtn = row.querySelector('.removeRow');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (document.querySelectorAll('#bomTable tbody tr').length > 1) {
                    row.remove();
                    hitungTotalBiaya();
                } else {
                    alert('Minimal harus ada satu bahan baku');
                }
            });
        }
        
        // Update satuan dan harga saat bahan baku dipilih
        const select = row.querySelector('.bahan-select');
        if (select) {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const satuan = selectedOption.textContent.match(/\(([^)]+)\)/);
                const harga = parseFloat(selectedOption.dataset.harga) || 0;
                
                const satuanField = row.querySelector('.satuan');
                const hargaField = row.querySelector('.harga-satuan');
                
                if (satuan && satuan[1]) {
                    satuanField.value = satuan[1].trim();
                }
                
                hargaField.value = formatRupiah(harga);
                hitungTotalBiaya();
            });
        }
        
        // Update perhitungan saat kuantitas berubah
        const kuantitasField = row.querySelector('.kuantitas');
        if (kuantitasField) {
            kuantitasField.addEventListener('input', function() {
                hitungTotalBiaya();
            });
        }
    }

    // Inisialisasi event listener untuk baris pertama
    document.querySelectorAll('#bomTable tbody tr').forEach(row => {
        initRowEvents(row);
    });

    // Update perhitungan saat persentase keuntungan berubah
    document.getElementById('persentase_keuntungan').addEventListener('input', function() {
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/bom/create.blade.php ENDPATH**/ ?>
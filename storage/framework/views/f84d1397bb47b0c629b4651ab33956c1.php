

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">
            <i class="bi bi-cart-plus"></i> Tambah Pembelian
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo e(route('pegawai-pembelian.dashboard')); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('pegawai-pembelian.pembelian.index')); ?>">Pembelian</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
</div>

<?php if($errors->any()): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Terjadi kesalahan:</strong>
    <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form action="<?php echo e(route('pegawai-pembelian.pembelian.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-info-circle"></i> Informasi Pembelian
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" id="vendor_id" class="form-select <?php $__errorArgs = ['vendor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($vendor->id); ?>" <?php echo e(old('vendor_id') == $vendor->id ? 'selected' : ''); ?>>
                                <?php echo e($vendor->nama_vendor); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['vendor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" 
                           class="form-control <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           value="<?php echo e(old('tanggal', date('Y-m-d'))); ?>" required>
                    <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" 
                            class="form-select <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="cash" <?php echo e(old('payment_method', 'cash') === 'cash' ? 'selected' : ''); ?>>Tunai</option>
                        <option value="transfer" <?php echo e(old('payment_method') === 'transfer' ? 'selected' : ''); ?>>Transfer Bank</option>
                        <option value="credit" <?php echo e(old('payment_method') === 'credit' ? 'selected' : ''); ?>>Kredit (Hutang)</option>
                    </select>
                    <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-12">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" 
                              class="form-control <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('keterangan')); ?></textarea>
                    <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Detail Pembelian</span>
            <button type="button" class="btn btn-success btn-sm" id="addRow">
                <i class="bi bi-plus-circle"></i> Tambah Baris
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="detailTable">
                    <thead class="table-light">
                        <tr>
                            <th width="35%">Bahan Baku</th>
                            <th width="15%">Jumlah</th>
                            <th width="15%">Satuan</th>
                            <th width="20%">Harga per Satuan</th>
                            <th width="20%">Subtotal</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-baku" required>
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($bahan->id); ?>" data-satuan="<?php echo e($bahan->satuan->nama_satuan ?? ''); ?>">
                                            <?php echo e($bahan->nama_bahan); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah" 
                                       min="0.01" step="0.01" value="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm satuan-display" readonly>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" class="form-control form-control-sm harga" 
                                       min="0" step="0.01" value="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm subtotal" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                            <td colspan="2">
                                <input type="text" id="total" class="form-control form-control-sm fw-bold" readonly>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info py-2 mb-0">
                <i class="bi bi-info-circle"></i>
                <small>
                    <strong>Catatan:</strong> Stok bahan baku akan otomatis bertambah setelah pembelian disimpan. 
                    Pastikan data yang diinput sudah benar.
                </small>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Simpan Pembelian
        </button>
        <a href="<?php echo e(route('pegawai-pembelian.pembelian.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Batal
        </a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }

    function updateSatuan(row) {
        const select = row.querySelector('.bahan-baku');
        const satuanDisplay = row.querySelector('.satuan-display');
        const selectedOption = select.options[select.selectedIndex];
        const satuan = selectedOption.getAttribute('data-satuan') || '';
        satuanDisplay.value = satuan;
    }

    function updateSubtotal(row) {
        const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.harga').value) || 0;
        const subtotal = jumlah * harga;
        row.querySelector('.subtotal').value = formatRupiah(subtotal);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            const value = input.value.replace(/[^0-9]/g, '');
            total += parseFloat(value) || 0;
        });
        document.getElementById('total').value = formatRupiah(total);
    }

    // Tambah baris baru
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#detailTable tbody');
        const newRow = tbody.rows[0].cloneNode(true);
        
        // Reset values
        newRow.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        newRow.querySelector('.jumlah').value = 1;
        newRow.querySelector('.harga').value = 0;
        newRow.querySelector('.satuan-display').value = '';
        newRow.querySelector('.subtotal').value = formatRupiah(0);
        
        tbody.appendChild(newRow);
    });

    // Hapus baris
    document.querySelector('#detailTable').addEventListener('click', function(e) {
        if(e.target && (e.target.classList.contains('removeRow') || e.target.closest('.removeRow'))) {
            const rows = document.querySelectorAll('#detailTable tbody tr');
            if(rows.length > 1) {
                e.target.closest('tr').remove();
                updateTotal();
            } else {
                alert('Minimal harus ada 1 baris item!');
            }
        }
    });

    // Update satuan saat bahan baku dipilih
    document.querySelector('#detailTable').addEventListener('change', function(e) {
        if(e.target && e.target.classList.contains('bahan-baku')) {
            const row = e.target.closest('tr');
            updateSatuan(row);
        }
    });

    // Update subtotal saat input berubah
    document.querySelector('#detailTable').addEventListener('input', function(e) {
        if(e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga'))) {
            const row = e.target.closest('tr');
            updateSubtotal(row);
        }
    });

    // Hitung subtotal awal
    document.querySelectorAll('#detailTable tbody tr').forEach(row => {
        updateSatuan(row);
        updateSubtotal(row);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.pegawai-pembelian', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/pegawai-pembelian/pembelian/create.blade.php ENDPATH**/ ?>
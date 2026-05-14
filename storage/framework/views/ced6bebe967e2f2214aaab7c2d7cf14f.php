<?php $__env->startSection('title', 'Tambah Pelunasan Utang'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-credit-card"></i> Tambah Pelunasan Utang</h1>
        <a href="<?php echo e(route('transaksi.pelunasan-utang.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Tentang Pelunasan Utang</h5>
                <p class="mb-0">
                    Halaman ini digunakan untuk melakukan pembayaran utang dari pembelian yang dilakukan secara kredit atau pembelian yang belum dibayar penuh. 
                    Sistem akan menampilkan daftar pembelian yang masih memiliki sisa utang yang perlu dibayar.
                </p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-credit-card"></i> Form Pelunasan Utang</h4>
        </div>
        <form action="<?php echo e(route('transaksi.pelunasan-utang.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="card-body">
                <div class="form-group">
                    <label>Tanggal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control <?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="tanggal" value="<?php echo e(old('tanggal', date('Y-m-d'))); ?>" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Pembelian <span class="text-danger">*</span></label>
                    <select class="form-control <?php $__errorArgs = ['pembelian_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="pembelian_id" required>
                        <option value="">Pilih Pembelian</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembayarans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pembayaran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $sisaUtang = ($pembayaran->total_harga ?? 0) - ($pembayaran->terbayar ?? 0);
                            ?>
                            <option value="<?php echo e($pembayaran->id); ?>" data-sisa="<?php echo e($sisaUtang); ?>" <?php echo e(old('pembelian_id') == $pembayaran->id ? 'selected' : ''); ?>>
                                <?php echo e($pembayaran->nomor_pembelian ?? 'PB-' . $pembayaran->id); ?> - <?php echo e($pembayaran->vendor->nama_vendor ?? 'Vendor tidak diketahui'); ?> (Sisa: Rp <?php echo e(number_format($sisaUtang, 0, ',', '.')); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="" disabled>Tidak ada pembelian yang belum lunas</option>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pembelian_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembayarans->isEmpty()): ?>
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Saat ini tidak ada pembelian yang memiliki sisa utang. 
                            Pelunasan utang hanya dapat dilakukan untuk pembelian dengan metode pembayaran kredit atau pembelian yang belum dibayar penuh.
                            <br><br>
                            <a href="<?php echo e(route('transaksi.pembelian.create')); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Buat Pembelian Baru
                            </a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Detail Pembelian -->
                <div id="detail-pembelian" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Detail Pembelian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Vendor:</strong>
                                    <p id="vendor-name">-</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Pembelian:</strong>
                                    <p id="total-pembelian">-</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Sisa Utang:</strong>
                                    <p id="sisa-utang-detail" class="text-danger font-weight-bold">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Akun Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['akun_kas_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="akun_kas_id" required>
                                <option value="">Pilih Akun Pembayaran</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $akunKas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($akun->id); ?>" <?php echo e(old('akun_kas_id') == $akun->id ? 'selected' : ''); ?>>
                                        [<?php echo e($akun->kode_akun); ?>] <?php echo e($akun->nama_akun); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['akun_kas_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="form-text text-muted">
                                Pilih akun kas untuk pembayaran tunai atau akun bank untuk pembayaran transfer
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>COA Pelunasan <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['coa_pelunasan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="coa_pelunasan_id" required>
                                <option value="">Pilih COA Pelunasan</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coaPelunasan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($coa->id); ?>" <?php echo e(old('coa_pelunasan_id') == $coa->id ? 'selected' : ''); ?>>
                                        [<?php echo e($coa->kode_akun); ?>] <?php echo e($coa->nama_akun); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['coa_pelunasan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback">
                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="form-text text-muted">
                                Pilih akun COA untuk pelunasan utang (Hutang Usaha)
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        Rp
                                    </div>
                                </div>
                                <input type="text" class="form-control price-input <?php $__errorArgs = ['jumlah'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="jumlah" id="jumlah" value="<?php echo e(old('jumlah')); ?>" placeholder="0" required>
                                <input type="hidden" name="jumlah_raw" id="jumlah_raw" value="<?php echo e(old('jumlah')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jumlah'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <small class="text-muted">Sisa utang: <span id="sisa-utang">Rp 0</span></small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3" placeholder="Keterangan pembayaran (opsional)"><?php echo e(old('keterangan')); ?></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback">
                            <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary" <?php echo e($pembayarans->isEmpty() ? 'disabled' : ''); ?>>
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="<?php echo e(route('transaksi.pelunasan-utang.index')); ?>" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Format price input with thousand separator
        function setupPriceFormatting() {
            const jumlahInput = document.getElementById('jumlah');
            const jumlahRawInput = document.getElementById('jumlah_raw');
            
            if (jumlahInput) {
                // Format on input
                jumlahInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    value = value.replace(/[^0-9]/g, '');
                    const numValue = parseInt(value) || 0;
                    e.target.value = numValue.toLocaleString('id-ID');
                    if (jumlahRawInput) {
                        jumlahRawInput.value = numValue;
                    }
                });
                
                // Initial format if there's a value
                if (jumlahInput.value) {
                    const initialValue = Math.floor(parseFloat(jumlahInput.value) || 0);
                    jumlahInput.value = initialValue.toLocaleString('id-ID');
                    if (jumlahRawInput) {
                        jumlahRawInput.value = initialValue;
                    }
                }
            }
            
            // Before form submission, use raw values
            const form = jumlahInput ? jumlahInput.closest('form') : null;
            if (form) {
                form.addEventListener('submit', function() {
                    if (jumlahInput && jumlahRawInput && jumlahRawInput.value) {
                        jumlahInput.value = jumlahRawInput.value;
                    } else if (jumlahInput) {
                        jumlahInput.value = jumlahInput.value.replace(/\./g, '');
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Setup price formatting
            setupPriceFormatting();
            
            // Handle pembelian selection
            const pembelianSelect = document.querySelector('select[name="pembelian_id"]');
            const detailSection = document.getElementById('detail-pembelian');
            const vendorName = document.getElementById('vendor-name');
            const totalPembelian = document.getElementById('total-pembelian');
            const sisaUtangDetail = document.getElementById('sisa-utang-detail');
            const jumlahInput = document.getElementById('jumlah');
            const jumlahRawInput = document.getElementById('jumlah_raw');
            const sisaUtangSpan = document.getElementById('sisa-utang');
            
            if (pembelianSelect) {
                pembelianSelect.addEventListener('change', function() {
                    const pembelianId = this.value;
                    
                    if (pembelianId) {
                        // Make AJAX call to get purchase details
                        fetch(`/transaksi/pelunasan-utang/get-pembelian/${pembelianId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show detail section
                                    detailSection.style.display = 'block';
                                    
                                    // Fill in the details
                                    vendorName.textContent = data.data.vendor;
                                    totalPembelian.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.data.total_pembelian);
                                    sisaUtangDetail.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.data.sisa_utang);
                                    
                                    // Auto-fill jumlah with sisa utang (formatted)
                                    const sisaUtangValue = Math.floor(data.data.sisa_utang);
                                    jumlahInput.value = sisaUtangValue.toLocaleString('id-ID');
                                    if (jumlahRawInput) {
                                        jumlahRawInput.value = sisaUtangValue;
                                    }
                                    
                                    // Format and display remaining debt
                                    const formatter = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        maximumFractionDigits: 0,
                                    });
                                    
                                    sisaUtangSpan.textContent = formatter.format(data.data.sisa_utang);
                                } else {
                                    alert(data.message);
                                    detailSection.style.display = 'none';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat mengambil data pembelian');
                                detailSection.style.display = 'none';
                            });
                    } else {
                        // Hide detail section if no purchase selected
                        detailSection.style.display = 'none';
                        jumlahInput.value = '';
                        if (jumlahRawInput) {
                            jumlahRawInput.value = '';
                        }
                        sisaUtangSpan.textContent = 'Rp 0';
                    }
                });

                // Trigger change event on page load if there's a selected pembelian
                <?php if(old('pembelian_id')): ?>
                    pembelianSelect.dispatchEvent(new Event('change'));
                <?php endif; ?>
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/pelunasan-utang/create.blade.php ENDPATH**/ ?>
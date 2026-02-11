

<?php $__env->startSection('title', 'Tambah Pembelian'); ?>

<?php $__env->startPush('styles'); ?>
<style>
#vendorSelect {
    position: relative !important;
}

/* Force Bootstrap select dropdown to open downward */
.form-select {
    position: relative !important;
}

.form-select:focus {
    position: relative !important;
    z-index: 1 !important;
}

/* Prevent dropdown from moving up */
select.form-select {
    appearance: none !important;
    position: relative !important;
}

/* Ensure dropdown options stay below */
select.form-select option {
    position: static !important;
}

/* Container to prevent layout shift */
.vendor-select-container {
    position: relative !important;
    min-height: 80px !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Tambah Pembelian
        </h2>
        <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Notifications -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form action="<?php echo e(route('transaksi.pembelian.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <div class="vendor-select-container">
                            <select name="vendor_id" id="vendorSelect" class="form-select" required 
                            style="position: relative !important;"
                            onchange="
                            var bahanBaku = document.getElementById('cardBahanBaku');
                            var bahanPendukung = document.getElementById('cardBahanPendukung');
                            var conversionExamples = document.getElementById('conversionExamples');
                            var selectedOption = this.options[this.selectedIndex];
                            var kategori = (selectedOption.getAttribute('data-kategori') || '').toLowerCase();
                            
                            // Hide all sections first with !important
                            if (bahanBaku) bahanBaku.style.setProperty('display', 'none', 'important');
                            if (bahanPendukung) bahanPendukung.style.setProperty('display', 'none', 'important');
                            if (conversionExamples) conversionExamples.style.setProperty('display', 'none', 'important');
                            
                            // Show appropriate sections based on exact category
                            if (this.value) {
                                // Always show conversion examples when vendor is selected
                                if (conversionExamples) conversionExamples.style.setProperty('display', 'block', 'important');
                                
                                if (kategori === 'bahan pendukung' || kategori === 'pendukung') {
                                    if (bahanPendukung) bahanPendukung.style.setProperty('display', 'block', 'important');
                                } else {
                                    if (bahanBaku) bahanBaku.style.setProperty('display', 'block', 'important');
                                }
                            }
                        ">
                            <option value="" data-kategori="">-- Pilih Vendor --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vendor->id); ?>" data-kategori="<?php echo e($vendor->kategori ?? 'Bahan Baku'); ?>">
                                    <?php echo e($vendor->nama_vendor); ?> (<?php echo e($vendor->kategori ?? 'Bahan Baku'); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Faktur Pembelian</label>
                        <input type="text" name="nomor_faktur" class="form-control" placeholder="Masukkan nomor faktur" value="<?php echo e(old('nomor_faktur')); ?>">
                        <small class="text-muted">Nomor faktur dari vendor (opsional)</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kasbank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kb->nama_akun): ?>
                                    <option value="<?php echo e($kb->id); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kb->nama_akun): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_contains(strtolower($kb->nama_akun), 'kas')): ?>
                                                💵 Kas <?php echo e($kb->nama_akun); ?>

                                            <?php else: ?>
                                                🏦 <?php echo e($kb->nama_akun); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        (Saldo: Rp <?php echo e(number_format($kb->saldo_awal ?? 0, 0, ',', '.')); ?>)
                                    </option>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <option value="credit">💳 Kredit (Hutang)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="conversionExamples" style="display: none !important;">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contoh Konversi Satuan Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Satuan Berat & Volume:</h6>
                        <ul class="list-unstyled small">
                            <li>• 15 Liter = 15 Liter (satuan utama)</li>
                            <li>• 5 Liter = 5 Liter (satuan utama)</li>
                            <li>• 2 Kg = 2 Kg (satuan utama)</li>
                            <li>• 1 Kg = 1 Kg (satuan utama)</li>
                            <li>• 500 Gram = 0.5 Kg</li>
                            <li>• 200 Gram = 0.2 Kg</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">Satuan Kemasan:</h6>
                        <ul class="list-unstyled small">
                            <li>• 1 Tabung = 30 unit (contoh: gas)</li>
                            <li>• 500 Gram = 50 Bungkus (1 bungkus = 10g)</li>
                            <li>• 1 Botol = 0.5 Liter (asumsi)</li>
                            <li>• 1 Kaleng = 0.4 Kg (asumsi)</li>
                            <li>• 1 Sachet = 10 Gram (asumsi)</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-2 mb-0">
                    <small><i class="fas fa-lightbulb me-1"></i> 
                    <strong>Tips:</strong> Sistem akan otomatis mengkonversi satuan pembelian ke satuan utama untuk pencatatan stok dan menghitung harga per satuan utama untuk update harga bahan.
                    </small>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="cardBahanBaku" style="display: none !important;">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Detail Bahan Baku</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanBakuRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanBakuRows">
                    <!-- Dynamic rows will be inserted here -->
                    <div class="row g-3 bahan-baku-row" data-row-index="0">
                        <div class="col-md-4">
                            <label class="form-label">Bahan Baku</label>
                            <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                                <option value="">-- Pilih Bahan Baku --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($bb->id); ?>" 
                                            data-satuan="<?php echo e($bb->satuan->nama ?? 'Tidak Diketahui'); ?>"
                                            data-satuan-id="<?php echo e($bb->satuan_id ?? ''); ?>"
                                            data-satuan-utama="<?php echo e($bb->satuan->nama ?? 'KG'); ?>">
                                        <?php echo e($bb->nama_bahan); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" onchange="updateKonversiDisplay(this); recalculateHarga(this)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan Pembelian</label>
                            <select name="satuan_pembelian[]" class="form-select" onchange="updateKonversiDisplay(this)">
                                <option value="">-- Pilih Satuan --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($satuan->kode); ?>"><?php echo e($satuan->nama); ?> (<?php echo e($satuan->kode); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga per Satuan</label>
                            <input type="text" name="harga_per_satuan_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaPerSatuan(this)" onkeyup="formatHargaPerSatuan(this)">
                            <input type="hidden" name="harga_per_satuan[]" value="0">
                            <small class="text-muted">Harga per satuan pembelian</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Total</label>
                            <input type="text" name="harga_total_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaTotal(this)" onkeyup="formatHargaTotal(this)">
                            <input type="hidden" name="harga_total[]" value="0">
                            <small class="text-muted">Total harga untuk jumlah yang dibeli</small>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <!-- Konversi Section -->
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <label class="form-label small mb-1">Konversi ke Satuan Utama</label>
                                            <div class="d-flex align-items-center">
                                                <span class="konversi-dari me-2">-</span>
                                                <span class="me-2">=</span>
                                                <input type="number" name="jumlah_satuan_utama[]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01">
                                                <span class="ms-2 satuan-utama-label">-</span>
                                            </div>
                                            <small class="text-muted">Isi manual konversi ke satuan utama</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="cardBahanPendukung" style="display: none !important;">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Detail Bahan Pendukung</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanPendukungRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanPendukungRows">
                    <!-- Dynamic rows will be inserted here -->
                    <div class="row g-3 bahan-pendukung-row" data-row-index="0">
                        <div class="col-md-4">
                            <label class="form-label">Bahan Pendukung</label>
                            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                                <option value="">-- Pilih Bahan Pendukung --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($bp->id); ?>" 
                                            data-satuan="<?php echo e($bp->satuan->nama ?? 'Tidak Diketahui'); ?>"
                                            data-satuan-id="<?php echo e($bp->satuan_id ?? ''); ?>">
                                        <?php echo e($bp->nama_bahan); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="updateKonversiPendukungDisplay(this); recalculateHargaPendukung(this)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan Pembelian</label>
                            <select name="satuan_pembelian_pendukung[]" class="form-select" onchange="updateKonversiPendukungDisplay(this)">
                                <option value="">-- Pilih Satuan --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($satuan->kode); ?>"><?php echo e($satuan->nama); ?> (<?php echo e($satuan->kode); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga per Satuan</label>
                            <input type="text" name="harga_per_satuan_pendukung_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaPerSatuanPendukung(this)" onkeyup="formatHargaPerSatuanPendukung(this)">
                            <input type="hidden" name="harga_per_satuan_pendukung[]" value="0">
                            <small class="text-muted">Harga per satuan pembelian</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Total</label>
                            <input type="text" name="harga_total_pendukung_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaTotalPendukung(this)" onkeyup="formatHargaTotalPendukung(this)">
                            <input type="hidden" name="harga_total_pendukung[]" value="0">
                            <small class="text-muted">Total harga untuk jumlah yang dibeli</small>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <!-- Konversi Section untuk Bahan Pendukung -->
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <label class="form-label small mb-1">Konversi ke Satuan Utama</label>
                                            <div class="d-flex align-items-center">
                                                <span class="konversi-dari-pendukung me-2">-</span>
                                                <span class="me-2">=</span>
                                                <input type="number" name="jumlah_satuan_utama_pendukung[]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01">
                                                <span class="ms-2 satuan-utama-label-pendukung">-</span>
                                            </div>
                                            <small class="text-muted">Isi manual konversi ke satuan utama</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Keterangan</h6>
            </div>
            <div class="card-body">
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan (opsional)"></textarea>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>Total Pembelian
                        </h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4 class="mb-0 text-primary" id="totalPembelian">Rp 0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
        </div>
    </form>
</div>

<script>
// Format angka ke format Indonesia
function formatAngka(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Format input harga total
function formatHargaTotal(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Remove non-digits
    if (value) {
        let formatted = 'Rp ' + formatAngka(parseInt(value));
        input.value = formatted;
        
        // Update hidden field
        const hiddenInput = input.parentElement.querySelector('input[name="harga_total[]"]');
        hiddenInput.value = parseInt(value);
        
        // Hitung harga per satuan otomatis
        hitungHargaPerSatuanDariTotal(input);
    } else {
        input.value = '';
        const hiddenInput = input.parentElement.querySelector('input[name="harga_total[]"]');
        hiddenInput.value = 0;
    }
    
    hitungTotal();
}

// Format input harga per satuan
function formatHargaPerSatuan(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Remove non-digits
    if (value) {
        let formatted = 'Rp ' + formatAngka(parseInt(value));
        input.value = formatted;
        
        // Update hidden field
        const hiddenInput = input.parentElement.querySelector('input[name="harga_per_satuan[]"]');
        hiddenInput.value = parseInt(value);
        
        // Hitung harga total otomatis
        hitungHargaTotalDariPerSatuan(input);
    } else {
        input.value = '';
        const hiddenInput = input.parentElement.querySelector('input[name="harga_per_satuan[]"]');
        hiddenInput.value = 0;
    }
    
    hitungTotal();
}

// Hitung harga per satuan dari total
function hitungHargaPerSatuanDariTotal(input) {
    const row = input.closest('.bahan-baku-row');
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    const hargaTotalHidden = row.querySelector('input[name="harga_total[]"]');
    const hargaPerSatuanDisplay = row.querySelector('input[name="harga_per_satuan_display[]"]');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan[]"]');
    
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const hargaTotal = parseFloat(hargaTotalHidden.value) || 0;
    
    if (jumlah > 0 && hargaTotal > 0) {
        const hargaPerSatuan = hargaTotal / jumlah;
        hargaPerSatuanDisplay.value = 'Rp ' + formatAngka(Math.round(hargaPerSatuan));
        hargaPerSatuanHidden.value = hargaPerSatuan;
    } else {
        hargaPerSatuanDisplay.value = 'Rp 0';
        hargaPerSatuanHidden.value = 0;
    }
}

// Hitung harga total dari per satuan
function hitungHargaTotalDariPerSatuan(input) {
    const row = input.closest('.bahan-baku-row');
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan[]"]');
    const hargaTotalDisplay = row.querySelector('input[name="harga_total_display[]"]');
    const hargaTotalHidden = row.querySelector('input[name="harga_total[]"]');
    
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const hargaPerSatuan = parseFloat(hargaPerSatuanHidden.value) || 0;
    
    if (jumlah > 0 && hargaPerSatuan > 0) {
        const hargaTotal = jumlah * hargaPerSatuan;
        hargaTotalDisplay.value = 'Rp ' + formatAngka(Math.round(hargaTotal));
        hargaTotalHidden.value = hargaTotal;
    } else {
        hargaTotalDisplay.value = 'Rp 0';
        hargaTotalHidden.value = 0;
    }
}

// Update konversi display
function updateKonversiDisplay(element) {
    const row = element.closest('.bahan-baku-row');
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const konversiDari = row.querySelector('.konversi-dari');
    
    if (jumlahInput.value && satuanSelect.value) {
        const jumlah = parseFloat(jumlahInput.value);
        const satuan = satuanSelect.options[satuanSelect.selectedIndex].text;
        konversiDari.textContent = `${formatAngka(jumlah)} ${satuan}`;
    } else {
        konversiDari.textContent = '-';
    }
}

// Hitung total pembelian
function hitungTotal() {
    let total = 0;
    
    // Hitung total bahan baku
    const bahanBakuRows = document.querySelectorAll('#bahanBakuRows .bahan-baku-row');
    bahanBakuRows.forEach(row => {
        const hargaTotal = parseFloat(row.querySelector('input[name="harga_total[]"]')?.value || 0);
        total += hargaTotal;
    });
    
    // Hitung total bahan pendukung
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungRows .bahan-pendukung-row');
    bahanPendukungRows.forEach(row => {
        const hargaTotal = parseFloat(row.querySelector('input[name="harga_total_pendukung[]"]')?.value || 0);
        total += hargaTotal;
    });
    
    // Update total display
    document.getElementById('totalPembelian').textContent = 'Rp ' + formatAngka(total);
    
    // Show/hide remove buttons
    updateRemoveButtons();
}

// Add new bahan baku row
function addBahanBakuRow() {
    const container = document.getElementById('bahanBakuRows');
    const rowCount = container.children.length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-baku-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    // Get bahan baku options from first row
    const firstRow = container.querySelector('.bahan-baku-row');
    const firstSelect = firstRow.querySelector('select[name="bahan_baku_id[]"]');
    const bahanBakuOptions = firstSelect.innerHTML;
    
    // Get satuan options from first row
    const firstSatuanSelect = firstRow.querySelector('select[name="satuan_pembelian[]"]');
    const satuanOptions = firstSatuanSelect.innerHTML;
    
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Bahan Baku</label>
            <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                ${bahanBakuOptions}
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" onchange="updateKonversiDisplay(this); recalculateHarga(this)">
        </div>
        <div class="col-md-2">
            <label class="form-label">Satuan Pembelian</label>
            <select name="satuan_pembelian[]" class="form-select" onchange="updateKonversiDisplay(this)">
                ${satuanOptions}
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga per Satuan</label>
            <input type="text" name="harga_per_satuan_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaPerSatuan(this)" onkeyup="formatHargaPerSatuan(this)">
            <input type="hidden" name="harga_per_satuan[]" value="0">
            <small class="text-muted">Harga per satuan pembelian</small>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga Total</label>
            <input type="text" name="harga_total_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaTotal(this)" onkeyup="formatHargaTotal(this)">
            <input type="hidden" name="harga_total[]" value="0">
            <small class="text-muted">Total harga untuk jumlah yang dibeli</small>
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <!-- Konversi Section -->
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <label class="form-label small mb-1">Konversi ke Satuan Utama</label>
                            <div class="d-flex align-items-center">
                                <span class="konversi-dari me-2">-</span>
                                <span class="me-2">=</span>
                                <input type="number" name="jumlah_satuan_utama[]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01">
                                <span class="ms-2 satuan-utama-label">-</span>
                            </div>
                            <small class="text-muted">Isi manual konversi ke satuan utama</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    
    // Show remove button for all rows
    updateRemoveButtons();
    
    // Update total
    hitungTotal();
}

// Add new bahan pendukung row
function addBahanPendukungRow() {
    const container = document.getElementById('bahanPendukungRows');
    const rowCount = container.children.length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-pendukung-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    // Get bahan pendukung options from first row
    const firstRow = container.querySelector('.bahan-pendukung-row');
    const firstSelect = firstRow.querySelector('select[name="bahan_pendukung_id[]"]');
    const bahanPendukungOptions = firstSelect.innerHTML;
    
    // Get satuan options from first row
    const firstSatuanSelect = firstRow.querySelector('select[name="satuan_pembelian_pendukung[]"]');
    const satuanOptions = firstSatuanSelect.innerHTML;
    
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Bahan Pendukung</label>
            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                ${bahanPendukungOptions}
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="updateKonversiPendukungDisplay(this); recalculateHargaPendukung(this)">
        </div>
        <div class="col-md-2">
            <label class="form-label">Satuan Pembelian</label>
            <select name="satuan_pembelian_pendukung[]" class="form-select" onchange="updateKonversiPendukungDisplay(this)">
                ${satuanOptions}
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga per Satuan</label>
            <input type="text" name="harga_per_satuan_pendukung_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaPerSatuanPendukung(this)" onkeyup="formatHargaPerSatuanPendukung(this)">
            <input type="hidden" name="harga_per_satuan_pendukung[]" value="0">
            <small class="text-muted">Harga per satuan pembelian</small>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga Total</label>
            <input type="text" name="harga_total_pendukung_display[]" class="form-control" placeholder="Rp 0" onchange="formatHargaTotalPendukung(this)" onkeyup="formatHargaTotalPendukung(this)">
            <input type="hidden" name="harga_total_pendukung[]" value="0">
            <small class="text-muted">Total harga untuk jumlah yang dibeli</small>
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <!-- Konversi Section untuk Bahan Pendukung -->
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <label class="form-label small mb-1">Konversi ke Satuan Utama</label>
                            <div class="d-flex align-items-center">
                                <span class="konversi-dari-pendukung me-2">-</span>
                                <span class="me-2">=</span>
                                <input type="number" name="jumlah_satuan_utama_pendukung[]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01">
                                <span class="ms-2 satuan-utama-label-pendukung">-</span>
                            </div>
                            <small class="text-muted">Isi manual konversi ke satuan utama</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    
    // Show remove button for all rows
    updateRemoveButtons();
    
    // Update total
    hitungTotal();
}

// Remove bahan baku row
function removeBahanBakuRow(button) {
    const row = button.closest('.bahan-baku-row');
    row.remove();
    updateRemoveButtons();
    hitungTotal();
}

// Remove bahan pendukung row
function removeBahanPendukungRow(button) {
    const row = button.closest('.bahan-pendukung-row');
    row.remove();
    updateRemoveButtons();
    hitungTotal();
}

// Update remove buttons visibility
function updateRemoveButtons() {
    // Update bahan baku remove buttons
    const bahanBakuRows = document.querySelectorAll('#bahanBakuRows .bahan-baku-row');
    bahanBakuRows.forEach((row, index) => {
        const removeBtn = row.querySelector('button');
        if (removeBtn) {
            removeBtn.style.display = bahanBakuRows.length > 1 ? 'block' : 'none';
        }
    });
    
    // Update bahan pendukung remove buttons
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungRows .bahan-pendukung-row');
    bahanPendukungRows.forEach((row, index) => {
        const removeBtn = row.querySelector('button');
        if (removeBtn) {
            removeBtn.style.display = bahanPendukungRows.length > 1 ? 'block' : 'none';
        }
    });
}

function updateBahanBakuInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-baku-row');
    
    // Get the elements in the same row
    const satuanUtamaLabel = row.querySelector('.satuan-utama-label');
    
    if (selectedOption.value) {
        // Update satuan utama label
        const satuanUtama = selectedOption.getAttribute('data-satuan-utama') || selectedOption.getAttribute('data-satuan') || 'KG';
        satuanUtamaLabel.textContent = satuanUtama;
    } else {
        // Clear fields if no selection
        satuanUtamaLabel.textContent = '-';
    }
}

function updateBahanPendukungInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-pendukung-row');
    
    // Get the input fields in the same row
    const satuanUtamaLabel = row.querySelector('.satuan-utama-label-pendukung');
    
    if (selectedOption.value) {
        // Update satuan utama
        const satuanUtama = selectedOption.getAttribute('data-satuan') || 'PCS';
        satuanUtamaLabel.textContent = satuanUtama;
        
        // Update total
        hitungTotal();
    } else {
        // Clear fields if no selection
        satuanUtamaLabel.textContent = '-';
        
        // Update total
        hitungTotal();
    }
}

// Format input harga total untuk bahan pendukung
function formatHargaTotalPendukung(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Remove non-digits
    if (value) {
        let formatted = 'Rp ' + formatAngka(parseInt(value));
        input.value = formatted;
        
        // Update hidden field
        const hiddenInput = input.parentElement.querySelector('input[name="harga_total_pendukung[]"]');
        hiddenInput.value = parseInt(value);
        
        // Hitung harga per satuan otomatis
        hitungHargaPerSatuanDariTotalPendukung(input);
    } else {
        input.value = '';
        const hiddenInput = input.parentElement.querySelector('input[name="harga_total_pendukung[]"]');
        hiddenInput.value = 0;
    }
    
    hitungTotal();
}

// Format input harga per satuan untuk bahan pendukung
function formatHargaPerSatuanPendukung(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Remove non-digits
    if (value) {
        let formatted = 'Rp ' + formatAngka(parseInt(value));
        input.value = formatted;
        
        // Update hidden field
        const hiddenInput = input.parentElement.querySelector('input[name="harga_per_satuan_pendukung[]"]');
        hiddenInput.value = parseInt(value);
        
        // Hitung harga total otomatis
        hitungHargaTotalDariPerSatuanPendukung(input);
    } else {
        input.value = '';
        const hiddenInput = input.parentElement.querySelector('input[name="harga_per_satuan_pendukung[]"]');
        hiddenInput.value = 0;
    }
    
    hitungTotal();
}

// Hitung harga per satuan dari total untuk bahan pendukung
function hitungHargaPerSatuanDariTotalPendukung(input) {
    const row = input.closest('.bahan-pendukung-row');
    const jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
    const hargaTotalHidden = row.querySelector('input[name="harga_total_pendukung[]"]');
    const hargaPerSatuanDisplay = row.querySelector('input[name="harga_per_satuan_pendukung_display[]"]');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan_pendukung[]"]');
    
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const hargaTotal = parseFloat(hargaTotalHidden.value) || 0;
    
    if (jumlah > 0 && hargaTotal > 0) {
        const hargaPerSatuan = hargaTotal / jumlah;
        hargaPerSatuanDisplay.value = 'Rp ' + formatAngka(Math.round(hargaPerSatuan));
        hargaPerSatuanHidden.value = hargaPerSatuan;
    } else {
        hargaPerSatuanDisplay.value = 'Rp 0';
        hargaPerSatuanHidden.value = 0;
    }
}

// Hitung harga total dari per satuan untuk bahan pendukung
function hitungHargaTotalDariPerSatuanPendukung(input) {
    const row = input.closest('.bahan-pendukung-row');
    const jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan_pendukung[]"]');
    const hargaTotalDisplay = row.querySelector('input[name="harga_total_pendukung_display[]"]');
    const hargaTotalHidden = row.querySelector('input[name="harga_total_pendukung[]"]');
    
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const hargaPerSatuan = parseFloat(hargaPerSatuanHidden.value) || 0;
    
    if (jumlah > 0 && hargaPerSatuan > 0) {
        const hargaTotal = jumlah * hargaPerSatuan;
        hargaTotalDisplay.value = 'Rp ' + formatAngka(Math.round(hargaTotal));
        hargaTotalHidden.value = hargaTotal;
    } else {
        hargaTotalDisplay.value = 'Rp 0';
        hargaTotalHidden.value = 0;
    }
}

// Recalculate harga ketika jumlah berubah
function recalculateHarga(input) {
    const row = input.closest('.bahan-baku-row');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan[]"]');
    
    // Jika ada harga per satuan, hitung ulang total
    if (hargaPerSatuanHidden && parseFloat(hargaPerSatuanHidden.value) > 0) {
        hitungHargaTotalDariPerSatuan(row.querySelector('input[name="harga_per_satuan_display[]"]'));
    }
}

// Recalculate harga untuk bahan pendukung ketika jumlah berubah
function recalculateHargaPendukung(input) {
    const row = input.closest('.bahan-pendukung-row');
    const hargaPerSatuanHidden = row.querySelector('input[name="harga_per_satuan_pendukung[]"]');
    
    // Jika ada harga per satuan, hitung ulang total
    if (hargaPerSatuanHidden && parseFloat(hargaPerSatuanHidden.value) > 0) {
        hitungHargaTotalDariPerSatuanPendukung(row.querySelector('input[name="harga_per_satuan_pendukung_display[]"]'));
    }
}
function updateKonversiPendukungDisplay(element) {
    const row = element.closest('.bahan-pendukung-row');
    const jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
    const satuanSelect = row.querySelector('select[name="satuan_pembelian_pendukung[]"]');
    const konversiDari = row.querySelector('.konversi-dari-pendukung');
    
    if (jumlahInput.value && satuanSelect.value) {
        const jumlah = parseFloat(jumlahInput.value);
        const satuan = satuanSelect.options[satuanSelect.selectedIndex].text;
        konversiDari.textContent = `${formatAngka(jumlah)} ${satuan}`;
    } else {
        konversiDari.textContent = '-';
    }
}

// Add event listeners for input changes to update total
document.addEventListener('DOMContentLoaded', function() {
    // Add change event listeners to all input fields
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="harga_total_display[]"], input[name="harga_total_pendukung_display[]"]')) {
            hitungTotal();
        }
    });
    
    // Add input event listeners for real-time updates
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="harga_total_display[]"], input[name="harga_total_pendukung_display[]"]')) {
            hitungTotal();
        }
    });
});
</script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/pembelian/create.blade.php ENDPATH**/ ?>
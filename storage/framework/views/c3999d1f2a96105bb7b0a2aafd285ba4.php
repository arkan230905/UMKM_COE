<?php $__env->startSection('content'); ?>
<div class="container text-light">
    <h2 class="mb-4 text-white">Tambah Aset Baru</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('master-data.aset.store')); ?>" method="POST" id="asetForm">
                <?php echo csrf_field(); ?>
                
                <div class="mb-3">
                    <label for="nama_aset" class="form-label text-white">Nama Aset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-dark text-white <?php $__errorArgs = ['nama_aset'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           id="nama_aset" name="nama_aset" value="<?php echo e(old('nama_aset')); ?>" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nama_aset'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="jenis_aset_id" class="form-label text-white">Jenis Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-dark text-dark <?php $__errorArgs = ['jenis_aset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            id="jenis_aset_id" name="jenis_aset_id" required onchange="loadKategoriAset()">
                        <option value="" disabled selected>-- Pilih Jenis Aset --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jenisAsets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($jenis->id); ?>" <?php echo e(old('jenis_aset_id') == $jenis->id ? 'selected' : ''); ?>>
                                <?php echo e($jenis->nama); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jenis_aset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="kategori_aset_id" class="form-label text-white">Kategori Aset <span class="text-danger">*</span></label>
                    <select class="form-select bg-dark text-dark <?php $__errorArgs = ['kategori_aset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                            id="kategori_aset_id" name="kategori_aset_id" required onchange="checkPenyusutan()">
                        <option value="" disabled selected>-- Pilih Jenis Aset terlebih dahulu --</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kategori_aset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga_perolehan" class="form-label text-white">Harga Perolehan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control bg-dark text-white <?php $__errorArgs = ['harga_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="harga_perolehan" name="harga_perolehan" value="<?php echo e(old('harga_perolehan', 0)); ?>" 
                               required oninput="hitungTotal()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['harga_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="biaya_perolehan" class="form-label text-white">Biaya Perolehan (Rp)</label>
                        <input type="number" step="0.01" class="form-control bg-dark text-white <?php $__errorArgs = ['biaya_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="biaya_perolehan" name="biaya_perolehan" value="<?php echo e(old('biaya_perolehan', 0)); ?>" 
                               required oninput="hitungTotal()">
                        <small class="text-muted">Biaya tambahan seperti ongkir, instalasi, dll</small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['biaya_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Total Perolehan</label>
                    <div class="form-control bg-secondary text-white" id="total_perolehan_display">Rp 0</div>
                </div>

                <!-- Section Penyusutan - Hanya muncul untuk aset yang disusutkan -->
                <div id="section_penyusutan" style="display: none;">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Aset ini mengalami penyusutan.</strong> Silakan isi informasi penyusutan di bawah.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="metode_penyusutan" class="form-label text-white">Metode Penyusutan <span class="text-danger">*</span></label>
                            <select class="form-select bg-dark text-white <?php $__errorArgs = ['metode_penyusutan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="metode_penyusutan" name="metode_penyusutan" onchange="hitungPenyusutan()">
                                <option value="" disabled selected>-- Pilih Metode --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metodePenyusutan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(old('metode_penyusutan') == $key ? 'selected' : ''); ?>>
                                        <?php echo e($value); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['metode_penyusutan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="umur_manfaat" class="form-label text-white">Umur Manfaat (tahun) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control bg-dark text-white <?php $__errorArgs = ['umur_manfaat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="umur_manfaat" name="umur_manfaat" value="<?php echo e(old('umur_manfaat')); ?>" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Perkiraan umur ekonomis aset</small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['umur_manfaat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Tarif Penyusutan Per Tahun (hanya untuk saldo menurun) -->
                        <div class="col-md-4 mb-3" id="tarif_penyusutan_container" style="display: none;">
                            <label for="tarif_penyusutan" class="form-label text-white">Tarif Penyusutan Per Tahun (%)</label>
                            <input type="number" step="0.1" min="0" max="200" class="form-control bg-dark text-white <?php $__errorArgs = ['tarif_penyusutan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="tarif_penyusutan" name="tarif_penyusutan" value="<?php echo e(old('tarif_penyusutan')); ?>" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Tarif penyusutan dalam persen (contoh: 50 untuk 50%)</small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tarif_penyusutan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Bulan Mulai (hanya untuk saldo menurun) -->
                        <div class="col-md-4 mb-3" id="bulan_mulai_container" style="display: none;">
                            <label for="bulan_mulai" class="form-label text-white">Bulan Mulai</label>
                            <select class="form-select bg-dark text-white <?php $__errorArgs = ['bulan_mulai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="bulan_mulai" name="bulan_mulai" onchange="hitungPenyusutan()">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                            <small class="text-muted">Bulan pembelian aset</small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bulan_mulai'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Tanggal Perolehan (hanya untuk jumlah angka tahun) -->
                        <div class="col-md-4 mb-3" id="tanggal_perolehan_container" style="display: none;">
                            <label for="tanggal_perolehan" class="form-label text-white">Tanggal Perolehan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-dark text-white <?php $__errorArgs = ['tanggal_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="tanggal_perolehan" name="tanggal_perolehan" value="<?php echo e(old('tanggal_perolehan')); ?>" 
                                   onchange="hitungPenyusutan()">
                            <small class="text-muted">Tanggal pembelian aset</small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal_perolehan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="nilai_residu" class="form-label text-white">Nilai Residu (Rp)</label>
                            <input type="number" step="0.01" class="form-control bg-dark text-white <?php $__errorArgs = ['nilai_residu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="nilai_residu" name="nilai_residu" value="<?php echo e(old('nilai_residu', 0)); ?>" 
                                   oninput="hitungPenyusutan()">
                            <small class="text-muted">Nilai sisa di akhir umur manfaat</small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nilai_residu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Ringkasan Penyusutan -->
                    <div class="card border-0 shadow-sm mb-4 bg-dark">
                        <div class="card-header bg-primary text-light">
                            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Informasi Penyusutan</h5>
                        </div>
                        <div class="card-body">
                            <!-- Informasi Metode Penyusutan -->
                            <div id="info_metode_penyusutan" class="mb-3" style="display: none;">
                                <div class="alert alert-info bg-dark border-info">
                                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
                                    <div id="rumus_penyusutan"></div>
                                    <div id="tarif_penyusutan"></div>
                                    <div id="keterangan_penyusutan"></div>
                                </div>
                            </div>
                            
                                                        
                            <!-- Hasil Perhitungan -->
                            <h6 class="text-light mb-3" id="hasil_perhitungan_header"><i class="bi bi-calculator me-2"></i>Hasil Perhitungan Penyusutan</h6>
                            <div class="table-responsive" id="hasil_perhitungan_container">
                                <table class="table table-bordered mb-0 table-dark">
                                    <tbody>
                                        <tr>
                                            <td class="bg-secondary text-white fw-bold" width="50%">Nilai yang Disusutkan</td>
                                            <td class="text-end text-white" id="nilai_disusutkan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-success bg-opacity-25">
                                            <td class="fw-bold text-white">Penyusutan Per Tahun</td>
                                            <td class="text-end fw-bold text-success" id="penyusutan_tahunan_display">Rp 0</td>
                                        </tr>
                                        <tr class="bg-info bg-opacity-25">
                                            <td class="fw-bold text-white">Penyusutan Per Bulan</td>
                                            <td class="text-end fw-bold text-info" id="penyusutan_bulanan_display">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Tabel Perhitungan Per Tahun (hanya untuk saldo menurun) -->
                            <div id="tabel_perhitungan_tahunan" class="mt-4" style="display: none;">
                                <h6 class="text-light mb-3"><i class="bi bi-table me-2"></i>Perhitungan Penyusutan Per Tahun</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-dark table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">TAHUN</th>
                                                <th class="text-end">PENYUSUTAN</th>
                                                <th class="text-end">AKUMULASI PENY</th>
                                                <th class="text-end">NILAI BUKU</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabel_perhitungan_body">
                                            <!-- Akan diisi oleh JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Perhitungan Jumlah Angka Tahun (hanya untuk metode jumlah angka tahun) -->
                            <div id="perhitungan_jumlah_angka_tahun" class="mt-4" style="display: none;">
                                <h6 class="text-light mb-3"><i class="bi bi-calculator me-2"></i>Perhitungan Jumlah Angka Tahun</h6>
                                <div class="card bg-dark">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="text-white mb-2"><strong>Umur Manfaat:</strong> <span id="umur_manfaat_display">-</span> tahun</p>
                                                <p class="text-white mb-2"><strong>Rumus:</strong> <span id="rumus_jumlah_angka">-</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="text-white mb-2"><strong>Hasil Perhitungan:</strong></p>
                                                <h4 class="text-success" id="hasil_jumlah_angka">-</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <small><i class="bi bi-info-circle me-1"></i> Perhitungan ini adalah estimasi berdasarkan metode yang dipilih</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert untuk aset yang tidak disusutkan -->
                <div id="alert_tidak_disusutkan" class="alert alert-warning mb-4" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Aset ini tidak mengalami penyusutan.</strong> 
                    <span id="alasan_tidak_disusutkan"></span>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_beli" class="form-label text-white">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control bg-dark text-white <?php $__errorArgs = ['tanggal_beli'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="tanggal_beli" name="tanggal_beli" value="<?php echo e(old('tanggal_beli', date('Y-m-d'))); ?>" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal_beli'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akuisisi" class="form-label text-white">Tanggal Mulai Penyusutan</label>
                        <input type="date" class="form-control bg-dark text-white <?php $__errorArgs = ['tanggal_akuisisi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="tanggal_akuisisi" name="tanggal_akuisisi" value="<?php echo e(old('tanggal_akuisisi')); ?>">
                        <small class="text-muted">Kosongkan jika sama dengan tanggal pembelian</small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tanggal_akuisisi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label text-white">Keterangan</label>
                    <textarea class="form-control bg-dark text-white <?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                              id="keterangan" name="keterangan" rows="3"><?php echo e(old('keterangan')); ?></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['keterangan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Aset
                    </button>
                    <a href="<?php echo e(route('master-data.aset.index')); ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data kategori aset per jenis
const kategoriData = <?php echo json_encode($jenisAsets, 15, 512) ?>;

// Fungsi untuk format number otomatis
function formatNumber(input) {
    // Hapus semua karakter kecuali digit dan titik
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Jika kosong, set ke 0
    if (value === '') {
        input.value = '0';
        return;
    }
    
    // Format dengan titik setiap 3 digit
    const parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    // Update input value
    input.value = parts.join('.');
}

// Load kategori aset berdasarkan jenis yang dipilih
function loadKategoriAset() {
    const jenisId = document.getElementById('jenis_aset_id').value;
    const kategoriSelect = document.getElementById('kategori_aset_id');
    
    kategoriSelect.innerHTML = '<option value="" disabled selected>-- Pilih Kategori Aset --</option>';
    
    if (jenisId) {
        const jenis = kategoriData.find(j => j.id == jenisId);
        if (jenis && jenis.kategories) {
            jenis.kategories.forEach(kategori => {
                const option = document.createElement('option');
                option.value = kategori.id;
                option.textContent = kategori.nama;
                option.dataset.disusutkan = kategori.disusutkan ? '1' : '0';
                option.dataset.jenisNama = jenis.nama;
                if ('<?php echo e(old("kategori_aset_id")); ?>' == kategori.id) {
                    option.selected = true;
                }
                kategoriSelect.appendChild(option);
            });
        }
    }
    
    // Check penyusutan after loading
    checkPenyusutan();
}

// Check apakah kategori aset yang dipilih disusutkan atau tidak
function checkPenyusutan() {
    const kategoriSelect = document.getElementById('kategori_aset_id');
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    
    const sectionPenyusutan = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    const alasanTidakDisusutkan = document.getElementById('alasan_tidak_disusutkan');
    
    // Fields penyusutan
    const metodePenyusutan = document.getElementById('metode_penyusutan');
    const umurManfaat = document.getElementById('umur_manfaat');
    const nilaiResidu = document.getElementById('nilai_residu');
    
    if (selectedOption && selectedOption.value) {
        const disusutkan = selectedOption.dataset.disusutkan === '1';
        const jenisNama = selectedOption.dataset.jenisNama || '';
        const kategoriNama = selectedOption.textContent;
        
        if (disusutkan) {
            // Aset DISUSUTKAN - tampilkan form penyusutan
            sectionPenyusutan.style.display = 'block';
            alertTidakDisusutkan.style.display = 'none';
            
            // Set required
            metodePenyusutan.required = true;
            umurManfaat.required = true;
            nilaiResidu.required = true;
            
        } else {
            // Aset TIDAK DISUSUTKAN - sembunyikan form penyusutan
            sectionPenyusutan.style.display = 'none';
            alertTidakDisusutkan.style.display = 'block';
            
            // Remove required
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            nilaiResidu.required = false;
            
            // Set nilai default untuk aset yang tidak disusutkan
            metodePenyusutan.value = '';
            umurManfaat.value = 0;
            nilaiResidu.value = 0;
            
            // Tampilkan alasan
            let alasan = '';
            if (jenisNama.includes('Lancar')) {
                alasan = 'Aset lancar tidak mengalami penyusutan karena bersifat likuid dan akan dikonversi menjadi kas dalam waktu dekat.';
            } else if (kategoriNama.includes('Tanah')) {
                alasan = 'Tanah tidak mengalami penyusutan karena memiliki umur manfaat tidak terbatas dan cenderung meningkat nilainya.';
            } else if (jenisNama.includes('Tak Berwujud')) {
                alasan = 'Aset tak berwujud tidak disusutkan, tetapi diamortisasi dengan metode yang berbeda.';
            } else if (jenisNama.includes('Investasi')) {
                alasan = 'Investasi jangka panjang tidak disusutkan karena nilainya mengikuti nilai pasar.';
            } else {
                alasan = 'Aset ini tidak mengalami penyusutan sesuai standar akuntansi.';
            }
            alasanTidakDisusutkan.textContent = alasan;
        }
    } else {
        // Belum ada kategori dipilih
        sectionPenyusutan.style.display = 'none';
        alertTidakDisusutkan.style.display = 'none';
    }
}

// Hitung total perolehan
function hitungTotal() {
    const harga = parseFloat(document.getElementById('harga_perolehan').value) || 0;
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    
    document.getElementById('total_perolehan_display').textContent = 'Rp ' + formatRupiah(total);
    
    hitungPenyusutan();
}

// Hitung perhitungan jumlah angka tahun
function hitungPerhitunganJumlahAngkaTahun(umur) {
    const container = document.getElementById('perhitungan_jumlah_angka_tahun');
    const umurDisplay = document.getElementById('umur_manfaat_display');
    const rumusDisplay = document.getElementById('rumus_jumlah_angka');
    const hasilDisplay = document.getElementById('hasil_jumlah_angka');
    
    if (!umur || umur <= 0) {
        container.style.display = 'none';
        return;
    }
    
    // Hitung jumlah angka tahun: 5+4+3+2+1 = 15
    const sumOfYears = (umur * (umur + 1)) / 2;
    
    // Buat rumus string
    let rumusString = '';
    for (let i = umur; i >= 1; i--) {
        rumusString += i;
        if (i > 1) rumusString += ' + ';
    }
    rumusString += ' = ' + sumOfYears;
    
    // Tampilkan hasil
    umurDisplay.textContent = umur;
    rumusDisplay.textContent = rumusString;
    hasilDisplay.textContent = sumOfYears;
    
    container.style.display = 'block';
}

// Hitung penyusutan per tahun untuk metode saldo menurun
function hitungPerhitunganTahunan(total, residu, umur, tarifPersen, bulanMulai) {
    const tabelContainer = document.getElementById('tabel_perhitungan_tahunan');
    const tabelBody = document.getElementById('tabel_perhitungan_body');
    
    if (!tarifPersen || tarifPersen <= 0) {
        tabelContainer.style.display = 'none';
        return;
    }
    
    const rate = tarifPersen / 100;
    let bookValue = total;
    let totalPenyusutan = 0;
    
    let html = '';
    
    // Hitung sisa bulan di tahun pertama
    const sisaBulanTahun1 = 13 - bulanMulai; // Jika mulai Februari (2), sisa = 11 bulan
    
    for (let tahun = 1; tahun <= umur; tahun++) {
        let penyusutan = 0;
        
        if (tahun === 1) {
            // Tahun pertama: partial year sesuai rumus Excel
            // Rumus: (Tarif% * sisa bulan) / 12 * nilai buku awal
            penyusutan = (rate * sisaBulanTahun1) / 12 * bookValue;
        } else {
            // Tahun berikutnya: full year
            penyusutan = bookValue * rate;
        }
        
        // Tahun terakhir: pastikan menyentuh nilai sisa
        if (tahun === umur || bookValue - penyusutan <= residu) {
            penyusutan = bookValue - residu; // Sisakan sesuai nilai residu yang diinput
        }
        
        const maxDepreciable = Math.max(bookValue - residu, 0);
        const penyusunanActual = Math.min(penyusutan, maxDepreciable);
        
        bookValue -= penyusunanActual;
        totalPenyusutan += penyusunanActual;
        
        // Tambahkan keterangan untuk tahun pertama
        let tahunLabel = tahun;
        if (tahun === 1 && sisaBulanTahun1 < 12) {
            tahunLabel = `${tahun} (${sisaBulanTahun1} bulan)`;
        }
        
        html += `
            <tr>
                <td class="text-center">${tahunLabel}</td>
                <td class="text-end">Rp ${formatRupiah(penyusunanActual)}</td>
                <td class="text-end">Rp ${formatRupiah(totalPenyusutan)}</td>
                <td class="text-end">Rp ${formatRupiah(bookValue)}</td>
            </tr>
        `;
        
        // Jangan break, tetap lanjutkan hingga umur manfaat selesai
        // untuk menampilkan semua tahun sesuai umur manfaat
    }
    
    tabelBody.innerHTML = html;
    tabelContainer.style.display = 'block';
}

// Hitung penyusutan
function hitungPenyusutan() {
    const harga = parseFloat(document.getElementById('harga_perolehan').value) || 0;
    const biaya = parseFloat(document.getElementById('biaya_perolehan').value) || 0;
    const total = harga + biaya;
    const residu = parseFloat(document.getElementById('nilai_residu').value) || 0;
    const umur = parseFloat(document.getElementById('umur_manfaat').value) || 1;
    const metode = document.getElementById('metode_penyusutan').value;
    
    // Tampilkan/sembunyikan kolom tarif penyusutan dan bulan mulai
    const tarifContainer = document.getElementById('tarif_penyusutan_container');
    const bulanMulaiContainer = document.getElementById('bulan_mulai_container');
    const tanggalPerolehanContainer = document.getElementById('tanggal_perolehan_container');
    
    if (metode === 'saldo_menurun') {
        tarifContainer.style.display = 'block';
        bulanMulaiContainer.style.display = 'block';
        tanggalPerolehanContainer.style.display = 'none';
        // Auto-fill tarif saat umur manfaat diubah
        const tarifInput = document.getElementById('tarif_penyusutan');
        if (umur > 0 && document.getElementById('umur_manfaat').value !== '') {
            const calculatedTarif = ((100 / umur) * 2).toFixed(1);
            tarifInput.value = Math.min(calculatedTarif, 100); // Maksimal 100%
            updateDepreciationInfo();
        }
    } else if (metode === 'garis_lurus') {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'block';
        tanggalPerolehanContainer.style.display = 'none';
    } else if (metode === 'sum_of_years_digits') {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'block';
    } else {
        tarifContainer.style.display = 'none';
        bulanMulaiContainer.style.display = 'none';
        tanggalPerolehanContainer.style.display = 'none';
    }
    
    const nilaiDisusutkan = total - residu;
    let penyusutanTahunan = 0;
    
    if (metode === 'garis_lurus') {
        // Metode garis lurus
        penyusutanTahunan = nilaiDisusutkan / umur;
        
        // Tambahkan perhitungan proporsional untuk bulan pertama
        const bulanMulai = parseInt(document.getElementById('bulan_mulai').value) || 1;
        const sisaBulanTahun1 = 13 - bulanMulai;
        const penyusutanTahunPertama = (penyusutanTahunan / 12) * sisaBulanTahun1;
        
        // Tampilkan info metode garis lurus
        const infoDiv = document.getElementById('info_metode_penyusutan');
        infoDiv.style.display = 'block';
        infoDiv.innerHTML = `
            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
            <div><strong>Rumus:</strong> (Harga Perolehan - Nilai Residu) / Umur Manfaat</div>
            <div><strong>Perhitungan:</strong> (Rp ${formatRupiah(total)} - Rp ${formatRupiah(residu)}) / ${umur} tahun = Rp ${formatRupiah(penyusutanTahunan)} per tahun</div>
            <div><strong>Penyusutan tahun pertama (${sisaBulanTahun1} bulan):</strong> Rp ${formatRupiah(penyusutanTahunPertama)}</div>
        `;
        
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
        // Tampilkan hasil perhitungan untuk garis lurus
        document.getElementById('hasil_perhitungan_header').style.display = 'block';
        document.getElementById('hasil_perhitungan_container').style.display = 'block';
    } else if (metode === 'saldo_menurun') {
        // Metode saldo menurun (double declining balance) - gunakan tarif yang diinput
        const tarifPersen = parseFloat(document.getElementById('tarif_penyusutan').value) || 0;
        const bulanMulai = parseInt(document.getElementById('bulan_mulai').value) || 1;
        const rate = tarifPersen / 100; // Konversi persen ke desimal
        
        // Sembunyikan perhitungan jumlah angka tahun
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
        
        // Hitung sisa bulan di tahun pertama
        const sisaBulanTahun1 = 13 - bulanMulai;
        
        // First year depreciation (sesuai rumus partial year)
        if (sisaBulanTahun1 < 12) {
            // Partial year: (Tarif% * sisa bulan) / 12 * nilai buku awal
            penyusutanTahunan = (rate * sisaBulanTahun1) / 12 * total;
        } else {
            // Full year
            penyusutanTahunan = total * rate;
        }
        
        // Pastikan tidak melebihi nilai yang bisa disusutkan
        penyusutanTahunan = Math.min(penyusutanTahunan, nilaiDisusutkan);
        
        // Tampilkan perhitungan per tahun
        hitungPerhitunganTahunan(total, residu, umur, tarifPersen, bulanMulai);
        
        // Tampilkan rumus dan tarif penyusutan
        updateDepreciationInfo('saldo_menurun', tarifPersen);
        
        // Sembunyikan hasil perhitungan untuk saldo menurun
        document.getElementById('hasil_perhitungan_header').style.display = 'none';
        document.getElementById('hasil_perhitungan_container').style.display = 'none';
        
        // Note: Gunakan tarif yang diinput user dan partial year calculation
    } else if (metode === 'sum_of_years_digits') {
        // Metode jumlah angka tahun (tahun pertama)
        const sumOfYears = (umur * (umur + 1)) / 2;
        penyusutanTahunan = (nilaiDisusutkan * umur) / sumOfYears;
        
        // Tampilkan perhitungan jumlah angka tahun
        hitungPerhitunganJumlahAngkaTahun(umur);
        
        updateDepreciationInfo('sum_of_years_digits');
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        
        // Sembunyikan hasil perhitungan untuk jumlah angka tahun
        document.getElementById('hasil_perhitungan_header').style.display = 'none';
        document.getElementById('hasil_perhitungan_container').style.display = 'none';
    } else {
        // Sembunyikan info jika tidak ada metode yang dipilih
        document.getElementById('info_metode_penyusutan').style.display = 'none';
        document.getElementById('tabel_perhitungan_tahunan').style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
    }
    
    const penyusutanBulanan = penyusutanTahunan / 12;
    
    document.getElementById('nilai_disusutkan_display').textContent = 'Rp ' + formatRupiah(nilaiDisusutkan);
    document.getElementById('penyusutan_tahunan_display').textContent = 'Rp ' + formatRupiah(penyusutanTahunan);
    document.getElementById('penyusutan_bulanan_display').textContent = 'Rp ' + formatRupiah(penyusutanBulanan);
}

// Update informasi metode penyusutan
function updateDepreciationInfo(metode, ratePersen = 0) {
    const infoDiv = document.getElementById('info_metode_penyusutan');
    const rumusDiv = document.getElementById('rumus_penyusutan');
    const tarifDiv = document.getElementById('tarif_penyusutan');
    const keteranganDiv = document.getElementById('keterangan_penyusutan');
    
    if (metode === 'saldo_menurun') {
        infoDiv.style.display = 'block';
        const umur = parseFloat(document.getElementById('umur_manfaat').value) || 1;
        const tarifInput = parseFloat(document.getElementById('tarif_penyusutan').value) || ratePersen;
        const bulanMulai = parseInt(document.getElementById('bulan_mulai').value) || 1;
        const sisaBulan = 13 - bulanMulai;
        
        infoDiv.innerHTML = `
            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Detail Metode Penyusutan</h6>
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-primary me-2">Rumus</span>
                    <span class="text-white">(100% / UMUR MANFAAT) × 2 = (100% / ${umur}) × 2 = ${((100 / umur) * 2).toFixed(1)}%</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-2">Tarif</span>
                    <span class="text-white">${tarifInput.toFixed(1)}% per tahun</span>
                </div>
            </div>
            ${sisaBulan < 12 ? 
                `<div><small class="text-muted">Metode Saldo Menurun Ganda dengan partial year. Tahun pertama hanya ${sisaBulan} bulan.</small></div>` : 
                `<div><small class="text-muted">Metode Saldo Menurun Ganda menghitung penyusutan berdasarkan tarif persentase otomatis dari nilai buku awal setiap tahun.</small></div>`
            }
        `;
    } else if (metode === 'garis_lurus') {
        // Sembunyikan info untuk metode garis lurus (rumus berbeda)
        infoDiv.style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'none';
    } else if (metode === 'sum_of_years_digits') {
        infoDiv.style.display = 'none';
        document.getElementById('perhitungan_jumlah_angka_tahun').style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(angka);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load kategori if jenis already selected
    if ('<?php echo e(old("jenis_aset_id")); ?>') {
        loadKategoriAset();
    }
    
    // Calculate initial values
    hitungTotal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/aset/create.blade.php ENDPATH**/ ?>
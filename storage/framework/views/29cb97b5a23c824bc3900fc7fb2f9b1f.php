<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-user-clock me-2"></i>Tambah Proses Produksi (BTKL)
        </h2>
        <a href="<?php echo e(route('master-data.btkl.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('master-data.btkl.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kode_proses" class="form-label">Kode Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="kode_proses" 
                               id="kode_proses" 
                               class="form-control <?php $__errorArgs = ['kode_proses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               value="<?php echo e(old('kode_proses', $nextKode)); ?>"
                               readonly
                               required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kode_proses'];
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

                    <div class="col-md-6">
                        <label for="nama_btkl" class="form-label">Nama Proses <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_btkl" 
                               id="nama_btkl" 
                               class="form-control <?php $__errorArgs = ['nama_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               value="<?php echo e(old('nama_btkl')); ?>"
                               placeholder="Contoh: Penggorengan Adonan"
                               required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nama_btkl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <small class="form-text text-muted">Nama proses produksi (contoh: Penggorengan Adonan, Pencampuran Bahan, dll)</small>
                    </div>

                    <div class="col-md-6">
                        <label for="jabatan_id" class="form-label">Jabatan BTKL <span class="text-danger">*</span></label>
                        <select name="jabatan_id" 
                                id="jabatan_id" 
                                class="form-select <?php $__errorArgs = ['jabatan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jabatanBtkl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jabatan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($jabatan->id); ?>" <?php echo e(old('jabatan_id') == $jabatan->id ? 'selected' : ''); ?>>
                                    <?php echo e($jabatan->nama); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jabatan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <small class="form-text text-muted">Jabatan yang mengolah proses BTKL ini</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tarif BTKL per Jam <span class="text-info">(Otomatis)</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/jam</span>
                            <input type="text" 
                                   id="tarif_per_jam_display" 
                                   class="form-control" 
                                   value="0"
                                   readonly>
                        </div>
                        <small class="form-text text-muted">Dihitung otomatis: Tarif Jabatan × Jumlah Pegawai</small>
                        
                        <div id="tarifCalculationDisplay" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2">
                                <span id="tarifCalculationText">Rp 0 x 0 pegawai = Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="satuan" 
                                id="satuan" 
                                class="form-select <?php $__errorArgs = ['satuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuanOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($satuan); ?>" <?php echo e(old('satuan') == $satuan ? 'selected' : ''); ?>>
                                    <?php echo e($satuan); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['satuan'];
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

                    <div class="col-md-6">
                        <label for="kapasitas_per_jam" class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="kapasitas_per_jam" 
                               id="kapasitas_per_jam" 
                               class="form-control <?php $__errorArgs = ['kapasitas_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               value="<?php echo e(old('kapasitas_per_jam')); ?>"
                               min="0"
                               required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['kapasitas_per_jam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <small class="form-text text-muted">Berapa pcs bisa diproduksi per jam</small>
                    </div>

                    <div class="col-md-12">
                        <label for="deskripsi_proses" class="form-label">Deskripsi Proses</label>
                        <textarea name="deskripsi_proses" 
                                  id="deskripsi_proses" 
                                  class="form-control <?php $__errorArgs = ['deskripsi_proses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  rows="3"
                                  placeholder="Deskripsi detail proses produksi"><?php echo e(old('deskripsi_proses')); ?></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['deskripsi_proses'];
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

                    <div class="col-md-6">
                        <label class="form-label">Biaya Per Produk <span class="text-info">(Otomatis)</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp/pcs</span>
                            <input type="text" 
                                   id="biaya_per_produk_display" 
                                   class="form-control" 
                                   value="0"
                                   readonly>
                        </div>
                        <small class="form-text text-muted">Dihitung otomatis: Tarif BTKL ÷ Kapasitas/Jam</small>
                        
                        <div id="biayaPerProdukDisplay" class="mt-2" style="display: none;">
                            <div class="alert alert-warning py-2">
                                <span id="biayaPerProdukText">Rp 0 ÷ 0 pcs = Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Data
                            </button>
                            <a href="<?php echo e(route('master-data.btkl.index')); ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Employee data
const employeeData = <?php echo json_encode($jabatanBtkl ?? [], 15, 512) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const jabatanSelect = document.getElementById('jabatan_id');
    const tarifDisplay = document.getElementById('tarif_per_jam_display');
    const tarifCalculationDisplay = document.getElementById('tarifCalculationDisplay');
    const tarifCalculationText = document.getElementById('tarifCalculationText');
    const kapasitasInput = document.getElementById('kapasitas_per_jam');
    const biayaPerProdukDisplay = document.getElementById('biaya_per_produk_display');
    const biayaPerProdukText = document.getElementById('biayaPerProdukText');
    
    let currentTarifBtkl = 0;

    function updateTarifCalculation(jabatan) {
        if (jabatan) {
            const jumlahPegawai = jabatan.pegawai_count || 0;
            const tarifPerJam = jabatan.tarif || 0;
            currentTarifBtkl = tarifPerJam * jumlahPegawai;
            
            tarifDisplay.value = currentTarifBtkl.toLocaleString('id-ID');
            tarifCalculationText.textContent = 'Rp ' + tarifPerJam.toLocaleString('id-ID') + ' x ' + jumlahPegawai + ' pegawai = Rp ' + currentTarifBtkl.toLocaleString('id-ID');
            tarifCalculationDisplay.style.display = 'block';
        } else {
            tarifDisplay.value = '0';
            tarifCalculationDisplay.style.display = 'none';
            currentTarifBtkl = 0;
        }
        
        updateBiayaPerProduk();
    }

    function updateBiayaPerProduk() {
        const kapasitas = parseInt(kapasitasInput.value) || 0;
        const tarif = currentTarifBtkl;
        
        if (kapasitas > 0 && tarif > 0) {
            const biayaPerProduk = tarif / kapasitas;
            biayaPerProdukDisplay.value = biayaPerProduk.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            biayaPerProdukText.textContent = 'Rp ' + tarif.toLocaleString('id-ID') + ' / ' + kapasitas + ' pcs = Rp ' + biayaPerProduk.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            biayaPerProdukDisplay.parentElement.nextElementSibling.style.display = 'block';
        } else {
            biayaPerProdukDisplay.value = '0';
            biayaPerProdukDisplay.parentElement.nextElementSibling.style.display = 'none';
        }
    }

    jabatanSelect.addEventListener('change', function() {
        const selectedJabatanId = parseInt(this.value);
        
        if (selectedJabatanId) {
            const jabatan = employeeData.find(j => j.id === selectedJabatanId);
            updateTarifCalculation(jabatan);
        } else {
            updateTarifCalculation(null);
        }
    });
    
    kapasitasInput.addEventListener('input', updateBiayaPerProduk);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/btkl/create.blade.php ENDPATH**/ ?>
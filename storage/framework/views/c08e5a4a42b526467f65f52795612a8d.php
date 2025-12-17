<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <h2 class="mb-4"><i class="bi bi-briefcase me-2"></i>Edit Jabatan</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        .jabatan-form .form-label,
        .jabatan-form small { color: #fff !important; }
    </style>
    <div class="card border-0 shadow-sm">
        <div class="card-body jabatan-form">
            <form method="POST" action="<?php echo e(route('master-data.jabatan.update', $jabatan->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Jabatan</label>
                        <input type="text" name="nama" class="form-control" value="<?php echo e(old('nama',$jabatan->nama)); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="btkl" <?php echo e(old('kategori',$jabatan->kategori)==='btkl' ? 'selected' : ''); ?>>BTKL</option>
                            <option value="btktl" <?php echo e(old('kategori',$jabatan->kategori)==='btktl' ? 'selected' : ''); ?>>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tunjangan Jabatan (Rp)</label>
                        <input type="text" name="tunjangan" class="form-control money-input" value="<?php echo e(old('tunjangan',$jabatan->tunjangan)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Rp)</label>
                        <input type="text" name="asuransi" class="form-control money-input" value="<?php echo e(old('asuransi',$jabatan->asuransi)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji (Rp)</label>
                        <input type="text" name="gaji" class="form-control money-input" value="<?php echo e(old('gaji',$jabatan->gaji)); ?>">
                        <small class="text-white">BTKTL: gaji per bulan. BTKL: isi 0.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarif / Jam (Rp)</label>
                        <input type="text" name="tarif" class="form-control money-input" value="<?php echo e(old('tarif',$jabatan->tarif)); ?>">
                        <small class="text-white">BTKL: tarif per jam. BTKTL: isi 0.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="<?php echo e(route('master-data.jabatan.index')); ?>" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function(){
            const formatID = (val) => {
                if (val === null || val === undefined) return '';
                let v = String(val).replace(/[^0-9,.]/g, '');
                if (!v) return '';
                const commaIndex = v.indexOf(',');
                let rawInt = commaIndex >= 0 ? v.slice(0, commaIndex) : v;
                let rawDec = commaIndex >= 0 ? v.slice(commaIndex + 1) : '';
                rawInt = rawInt.replace(/\D/g, '');
                rawDec = rawDec.replace(/\D/g, '').slice(0, 2);
                let intPart = rawInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (!rawDec) return intPart;
                if (/^0{1,2}$/.test(rawDec)) return intPart;
                return intPart + ',' + rawDec;
            };
            const inputs = document.querySelectorAll('.money-input');
            inputs.forEach((inp) => {
                inp.value = formatID(inp.value);
                inp.addEventListener('input', () => { inp.value = formatID(inp.value); });
                inp.addEventListener('blur', () => { inp.value = formatID(inp.value); });
            });

        })();
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/jabatan/edit.blade.php ENDPATH**/ ?>
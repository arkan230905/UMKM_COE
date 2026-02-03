<?php $__env->startSection('title', 'Edit Kualifikasi Tenaga Kerja'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Edit Kualifikasi Tenaga Kerja
        </h2>
        <a href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Kualifikasi: <?php echo e($jabatan->nama); ?>

            </h5>
        </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body jabatan-form">
            <form method="POST" action="/master-data/kualifikasi-tenaga-kerja/<?php echo e($jabatan->id); ?>" name="editForm">
                <?php echo csrf_field(); ?>

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
                        <label class="form-label">Tunjangan (Rp)</label>
                        <input type="text" name="tunjangan" class="form-control money-input" value="<?php echo e(old('tunjangan',$jabatan->tunjangan)); ?>">
                        <small class="text-white money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Rp)</label>
                        <input type="text" name="asuransi" class="form-control money-input" value="<?php echo e(old('asuransi',$jabatan->asuransi)); ?>">
                        <small class="text-white money-hint"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji Pokok (Rp)</label>
                        <input type="text" name="gaji" class="form-control money-input" value="<?php echo e(old('gaji',$jabatan->gaji)); ?>">
                        <small class="text-white money-hint"></small>
                        <small class="text-white d-block">BTKTL: gaji per bulan. BTKL: isi 0.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tarif/Jam (Rp)</label>
                        <input type="text" name="tarif" class="form-control money-input" value="<?php echo e(old('tarif',$jabatan->tarif)); ?>">
                        <small class="text-white money-hint"></small>
                        <small class="text-white d-block">BTKL: tarif per jam. BTKTL: isi 0.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.index')); ?>" class="btn btn-secondary">Kembali</a>
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
            const toNumber = (formatted) => {
                if (!formatted) return 0;
                let s = String(formatted).trim();
                s = s.replace(/\./g,'').replace(',', '.');
                let n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            };
            const compactID = (n) => {
                const u = [
                    {v:1e12, s:' triliun'},
                    {v:1e9, s:' miliar'},
                    {v:1e6, s:' juta'},
                    {v:1e3, s:' ribu'},
                ];
                for (const it of u) {
                    if (n >= it.v) {
                        const val = (n / it.v).toFixed(2).replace(/\.00$/,'');
                        return val + it.s;
                    }
                }
                return '';
            };
            const inputs = document.querySelectorAll('.money-input');
            inputs.forEach((inp) => {
                inp.value = formatID(inp.value);
                const hint = inp.parentElement.querySelector('.money-hint');
                const updateHint = () => {
                    const num = toNumber(inp.value);
                    const text = compactID(num);
                    if (hint) hint.textContent = text ? '(' + text + ')' : '';
                };
                updateHint();
                inp.addEventListener('input', () => {
                    inp.value = formatID(inp.value);
                    updateHint();
                });
                inp.addEventListener('blur', () => { inp.value = formatID(inp.value); updateHint(); });
            });
        })();
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/jabatan/edit.blade.php ENDPATH**/ ?>
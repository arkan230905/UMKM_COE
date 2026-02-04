<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h2>Edit COA</h2>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></ul></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form action="<?php echo e(route('master-data.coa.update',$coa->kode_akun)); ?>" method="POST">
        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipe Akun</label>
                <select name="tipe_akun" id="tipe_akun" class="form-select" required>
                    <?php ($tipeList=['Asset','Liability','Equity','Revenue','Expense']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tipeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t); ?>" <?php echo e($coa->tipe_akun===$t?'selected':''); ?>><?php echo e($t); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kode Akun</label>
                <input type="text" name="kode_akun" class="form-control" value="<?php echo e($coa->kode_akun); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saldo Normal</label>
                <select name="saldo_normal" class="form-select">
                    <option value="">-</option>
                    <option value="debit" <?php echo e($coa->saldo_normal==='debit'?'selected':''); ?>>Debit</option>
                    <option value="kredit" <?php echo e($coa->saldo_normal==='kredit'?'selected':''); ?>>Kredit</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Akun</label>
                <input type="text" name="nama_akun" class="form-control" value="<?php echo e($coa->nama_akun); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori Akun</label>
                <input type="text" name="kategori_akun" class="form-control" value="<?php echo e($coa->kategori_akun); ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Akun Induk</label>
                <select name="kode_induk" class="form-select">
                    <option value="">- Tidak Ada -</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->kode_akun); ?>" <?php echo e($coa->kode_induk===$p->kode_akun?'selected':''); ?>><?php echo e($p->kode_akun); ?> - <?php echo e($p->nama_akun); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Akun Header?</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_akun_header" value="1" id="is_header" <?php echo e($coa->is_akun_header? 'checked':''); ?>>
                    <label class="form-check-label" for="is_header">Ya</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Saldo Awal</label>
                <input type="text" id="saldo_awal_view" class="form-control" inputmode="decimal" placeholder="0" value="<?php echo e($coa->saldo_awal ? number_format((float)$coa->saldo_awal,0,',','.') : ''); ?>">
                <input type="hidden" name="saldo_awal" id="saldo_awal" value="<?php echo e((float)($coa->saldo_awal ?? 0)); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Saldo Awal</label>
                <input type="date" name="tanggal_saldo_awal" class="form-control" value="<?php echo e($coa->tanggal_saldo_awal); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Posted Saldo Awal?</label>
                <select name="posted_saldo_awal" class="form-select">
                    <option value="0" <?php echo e(!$coa->posted_saldo_awal? 'selected':''); ?>>Belum</option>
                    <option value="1" <?php echo e($coa->posted_saldo_awal? 'selected':''); ?>>Posted</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"><?php echo e($coa->keterangan); ?></textarea>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-success">Update</button>
            <a href="<?php echo e(route('master-data.coa.index')); ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
    </div>

    <script>
    // Money formatting (IDR) for saldo_awal in edit
    (function(){
        const view = document.getElementById('saldo_awal_view');
        const hidden = document.getElementById('saldo_awal');
        const nf = new Intl.NumberFormat('id-ID');
        const parseIdr = (str)=>{
            if (!str) return 0;
            let s = String(str).replace(/[^0-9,\.]/g,'').replace(/\./g,'').replace(',', '.');
            const num = parseFloat(s);
            return isNaN(num) ? 0 : num;
        };
        view.addEventListener('input', ()=>{
            const raw = view.value; const val = parseIdr(raw);
            hidden.value = val; view.value = raw === '' ? '' : nf.format(val);
            view.selectionStart = view.selectionEnd = view.value.length;
        });
        document.querySelector('form[action="<?php echo e(route('master-data.coa.update',$coa->kode_akun)); ?>"]').addEventListener('submit', ()=>{
            hidden.value = parseIdr(view.value);
        });
    })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/coa/edit.blade.php ENDPATH**/ ?>
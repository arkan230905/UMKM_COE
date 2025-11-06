<?php $__env->startSection('title', 'Tambah Data Pegawai'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">➕ Tambah Data Pegawai</h3>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('master-data.pegawai.store')); ?>" method="POST" id="pegawai-form">
        <?php echo csrf_field(); ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?php echo e(old('nama')); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="<?php echo e(old('no_telepon')); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2" required><?php echo e(old('alamat')); ?></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="nama_bank" class="form-label">Nama Bank</label>
                <input type="text" name="nama_bank" id="nama_bank" class="form-control" value="<?php echo e(old('nama_bank')); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_rekening" class="form-label">No. Rekening</label>
                <input type="text" name="no_rekening" id="no_rekening" class="form-control" value="<?php echo e(old('no_rekening')); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select name="jabatan_id" id="jabatan_id" class="form-select" required>
                    <option value="">-- Pilih Jabatan --</option>
                    <?php $__currentLoopData = $jabatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($j->id); ?>"
                                data-nama="<?php echo e($j->nama); ?>"
                                data-kategori="<?php echo e($j->kategori); ?>"
                                data-tunjangan="<?php echo e($j->tunjangan); ?>"
                                data-asuransi="<?php echo e($j->asuransi); ?>"
                                data-gaji="<?php echo e($j->gaji); ?>"
                                data-tarif="<?php echo e($j->tarif); ?>"
                                <?php echo e(old('jabatan_id')==$j->id?'selected':''); ?>>
                            <?php echo e($j->nama); ?> (<?php echo e(strtoupper($j->kategori)); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Hidden fields auto-filled -->
            <input type="hidden" name="jabatan" id="jabatan" value="<?php echo e(old('jabatan')); ?>">
            <input type="hidden" name="kategori" id="kategori" value="<?php echo e(old('kategori')); ?>">
            <input type="hidden" name="tunjangan" id="tunjangan" value="<?php echo e(old('tunjangan')); ?>">
            <input type="hidden" name="asuransi" id="asuransi" value="<?php echo e(old('asuransi')); ?>">
            <input type="hidden" name="gaji" id="gaji" value="<?php echo e(old('gaji')); ?>">
            <input type="hidden" name="tarif" id="tarif" value="<?php echo e(old('tarif')); ?>">

            <!-- Preview otomatis dari Jabatan -->
            <div class="col-12">
                <div class="alert alert-secondary small" id="preview-box" style="display:none">
                    <div><strong>Kategori:</strong> <span id="pv-kategori">-</span></div>
                    <div><strong>Tunjangan:</strong> Rp <span id="pv-tunjangan">0</span></div>
                    <div><strong>Asuransi:</strong> Rp <span id="pv-asuransi">0</span></div>
                    <div><strong>Gaji (BTKTL/bulan):</strong> Rp <span id="pv-gaji">0</span></div>
                    <div><strong>Tarif / Jam (BTKL):</strong> Rp <span id="pv-tarif">0</span></div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="<?php echo e(route('master-data.pegawai.index')); ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
    (function(){
        const fmt = (n)=> new Intl.NumberFormat('id-ID').format(Number(n||0));
        const dd = document.getElementById('jabatan_id');
        const mapFromSelect = () => {
            const opt = dd.options[dd.selectedIndex];
            if (!opt) { document.getElementById('preview-box').style.display='none'; return; }
            const ds = opt.dataset;
            const data = {
                nama: ds.nama || '',
                kategori: ds.kategori || '',
                tunjangan: ds.tunjangan || 0,
                asuransi: ds.asuransi || 0,
                gaji: ds.gaji || 0,
                tarif: ds.tarif || 0,
            };
            // set hidden
            document.getElementById('jabatan').value = data.nama;
            document.getElementById('kategori').value = (data.kategori||'').toUpperCase();
            document.getElementById('tunjangan').value = data.tunjangan;
            document.getElementById('asuransi').value = data.asuransi;
            document.getElementById('gaji').value = data.gaji;
            document.getElementById('tarif').value = data.tarif;
            // preview
            document.getElementById('pv-kategori').textContent = (data.kategori||'').toUpperCase();
            document.getElementById('pv-tunjangan').textContent = fmt(data.tunjangan);
            document.getElementById('pv-asuransi').textContent = fmt(data.asuransi);
            document.getElementById('pv-gaji').textContent = fmt(data.gaji);
            document.getElementById('pv-tarif').textContent = fmt(data.tarif);
            document.getElementById('preview-box').style.display='block';
        };
        dd.addEventListener('change', mapFromSelect);
        if (dd.value) mapFromSelect();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/pegawai/create.blade.php ENDPATH**/ ?>
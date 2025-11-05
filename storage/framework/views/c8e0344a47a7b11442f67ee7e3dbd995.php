<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h2>Tambah COA</h2>

    <form action="<?php echo e(route('master-data.coa.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label for="tipe_akun" class="form-label">Tipe Akun</label>
            <select name="tipe_akun" id="tipe_akun" class="form-select" onchange="generateKode()" required>
                <option value="">Pilih tipe</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Revenue">Revenue</option>
                <option value="Expense">Expense</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="nama_akun" class="form-label">Nama Akun</label>
            <input type="text" name="nama_akun" id="nama_akun" class="form-control" required>
        </div>

        <input type="hidden" name="kode_akun" id="kode_akun">

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
function generateKode() {
    const tipe = document.getElementById('tipe_akun').value;
    if (!tipe) return;

    fetch(`/master-data/coa/generate-kode?tipe=${tipe}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('kode_akun').value = data.kode_akun;
        });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/coa/create.blade.php ENDPATH**/ ?>
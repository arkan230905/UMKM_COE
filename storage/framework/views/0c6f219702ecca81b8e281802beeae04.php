<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-3">Tambah Penjualan</h3>

    <?php if($errors->any()): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
    <?php endif; ?>

    <form action="<?php echo e(route('transaksi.penjualan.store')); ?>" method="POST" id="form-penjualan">
        <?php echo csrf_field(); ?>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" class="form-select" required>
                    <option value="cash" selected>Tunai</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-dark">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%">
                            <button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>" data-price="<?php echo e($p->harga_jual ?? 0); ?>"><?php echo e($p->nama_produk ?? $p->nama); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>
                        <td><input type="number" step="0.0001" min="0.0001" name="jumlah[]" class="form-control jumlah" value="1" required></td>
                        <td><input type="number" step="0.01" min="0" name="harga_satuan[]" class="form-control harga" value="0" required></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0"></td>
                        <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row g-3">
            <div class="col-md-4 ms-auto">
                <label class="form-label">Total</label>
                <input type="text" name="total" class="form-control" value="0" readonly>
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="<?php echo e(route('transaksi.penjualan.index')); ?>" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('detailTableJual');
    const addBtn = document.getElementById('addRowJual');
    const totalInput = document.querySelector('input[name="total"]');

    function recalcRow(tr) {
        const q = parseFloat(tr.querySelector('.jumlah').value) || 0;
        const p = parseFloat(tr.querySelector('.harga').value) || 0;
        const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
        const sub = q * p;
        const dNom = sub * (dPct/100.0);
        const line = Math.max(sub - dNom, 0);
        tr.querySelector('.subtotal').value = line.toLocaleString();
    }

    function recalcTotal() {
        let sum = 0;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const val = (tr.querySelector('.subtotal').value || '0').replace(/,/g,'');
            sum += parseFloat(val) || 0;
        });
        totalInput.value = sum.toLocaleString();
    }

    function setPriceFromSelect(tr) {
        const sel = tr.querySelector('.produk-select');
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.getAttribute('data-price') || '0') || 0;
        tr.querySelector('.harga').value = price.toFixed(2);
        recalcRow(tr); recalcTotal();
    }

    addBtn.addEventListener('click', () => {
        const tbody = table.querySelector('tbody');
        const clone = tbody.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = 0;
            else if (inp.classList.contains('diskon')) inp.value = 0;
            else if (inp.classList.contains('subtotal')) inp.value = 0;
        });
        clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        table.querySelector('tbody').appendChild(clone);
    });

    table.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            const tr = e.target.closest('tr');
            setPriceFromSelect(tr);
        }
    });
    table.addEventListener('input', (e) => {
        if (e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga') || e.target.classList.contains('diskon'))) {
            const tr = e.target.closest('tr');
            recalcRow(tr); recalcTotal();
        }
    });
    table.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('removeRow')) {
            const rows = table.querySelectorAll('tbody tr');
            if (rows.length > 1) e.target.closest('tr').remove();
            recalcTotal();
        }
    });

    // Init first row
    setPriceFromSelect(table.querySelector('tbody tr'));
    recalcRow(table.querySelector('tbody tr')); recalcTotal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/penjualan/create.blade.php ENDPATH**/ ?>
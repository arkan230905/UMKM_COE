<?php $__env->startSection('content'); ?>
<div class="container">
    <h3>Edit BOM: <?php echo e($produk->nama_produk); ?></h3>

    <form action="<?php echo e(route('master-data.bom.update', $bom->id)); ?>" method="POST" id="bomForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="produk_id" value="<?php echo e($bom->produk_id); ?>">

        <div class="card mb-4">
            <div class="card-header" style="background-color: #2c3e50 !important; border-bottom: 1px solid rgba(0,0,0,.125) !important;">
                <h5 class="mb-0" style="color: #ffffff !important; margin: 0 !important;">Bahan Baku</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead class="table-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga Utama</th>
                                <th>Harga 1</th>
                                <th>Harga 2</th>
                                <th>Harga 3</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $bomDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $bahanBaku = $detail->bahanBaku;
                                $satuanNama = $bahanBaku->satuan->nama ?? 'KG';
                                $hargaKg = $bahanBaku->harga_satuan;
                                $hargaHg = $hargaKg * 0.1;
                                $hargaDag = $hargaKg * 0.01;
                                $hargaGr = $hargaKg / 1000;
                            ?>
                            <tr>
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select bahanSelect" required>
                                        <option value="">-- Pilih Bahan --</option>
                                        <?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $bahanSatuanNama = $bahan->satuan->nama ?? 'KG';
                                                $namaBahan = $bahan->nama ?? $bahan->nama_bahan ?? 'Bahan Tanpa Nama';
                                                $namaBahan .= ' (' . $bahanSatuanNama . ')';
                                            ?>
                                            <option value="<?php echo e($bahan->id); ?>" 
                                                data-satuan="<?php echo e($bahanSatuanNama); ?>"
                                                data-harga-kg="<?php echo e($bahan->harga_satuan); ?>"
                                                data-harga-hg="<?php echo e($bahan->harga_satuan * 0.1); ?>"
                                                data-harga-dag="<?php echo e($bahan->harga_satuan * 0.01); ?>"
                                                data-harga-gr="<?php echo e($bahan->harga_satuan / 1000); ?>"
                                                data-satuan-utama="<?php echo e($bahanSatuanNama); ?>"
                                                <?php if($bahan->id == $detail->bahan_baku_id): ?> selected <?php endif; ?>>
                                                <?php echo e($namaBahan); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlahInput" 
                                           value="<?php echo e($detail->jumlah); ?>" min="0.01" step="0.01" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuanSelect">
                                        <option value="KG" <?php echo e($detail->satuan == 'KG' ? 'selected' : ''); ?>>KG</option>
                                        <option value="HG" <?php echo e($detail->satuan == 'HG' ? 'selected' : ''); ?>>HG</option>
                                        <option value="DAG" <?php echo e($detail->satuan == 'DAG' ? 'selected' : ''); ?>>DAG</option>
                                        <option value="GR" <?php echo e($detail->satuan == 'GR' ? 'selected' : ''); ?>>GR</option>
                                    </select>
                                </td>
                                <td class="text-center harga-utama">
                                    <?php echo e(number_format($hargaKg, 0, ',', '.')); ?><br>
                                    <small class="text-muted">/KG</small>
                                </td>
                                <td class="text-center harga-1">
                                    <?php echo e(number_format($hargaHg, 0, ',', '.')); ?><br>
                                    <small class="text-muted">/HG</small>
                                </td>
                                <td class="text-center harga-2">
                                    <?php echo e(number_format($hargaDag, 0, ',', '.')); ?><br>
                                    <small class="text-muted">/DAG</small>
                                </td>
                                <td class="text-center harga-3">
                                    <?php echo e(number_format($hargaGr, 0, ',', '.')); ?><br>
                                    <small class="text-muted">/GR</small>
                                </td>
                                <td>
                                    <select name="kategori[]" class="form-select form-select-sm">
                                        <option value="BOP" <?php echo e($detail->kategori == 'BOP' ? 'selected' : ''); ?>>BOP</option>
                                        <option value="BTKL" <?php echo e($detail->kategori == 'BTKL' ? 'selected' : ''); ?>>BTKL</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="addRow">Tambah Baris</button>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update BOM</button>
            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php echo $__env->make('master-data.bom.js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/edit.blade.php ENDPATH**/ ?>
<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Daftar Aset</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(route('master-data.aset.create')); ?>" class="btn btn-primary">Tambah Aset</a>
            <form action="<?php echo e(route('laporan.penyusutan.aset.post')); ?>" method="POST" class="d-inline ms-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-secondary">Posting Penyusutan Bulan Ini</button>
            </form>
        </div>
    </div>

    <?php if($message = Session::get('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e($message); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('master-data.aset.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="jenis_aset" class="form-label">Jenis Aset</label>
                    <select name="jenis_aset" id="jenis_aset" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Jenis --</option>
                        <?php $__currentLoopData = $jenisAsets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($jenis->id); ?>" <?php echo e(request('jenis_aset') == $jenis->id ? 'selected' : ''); ?>>
                                <?php echo e($jenis->nama); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="kategori_aset_id" class="form-label">Kategori Aset</label>
                    <select name="kategori_aset_id" id="kategori_aset_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        <?php if(request('jenis_aset') && $kategoriAsets->count() > 0): ?>
                            <?php $__currentLoopData = $kategoriAsets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kategori->id); ?>" <?php echo e(request('kategori_aset_id') == $kategori->id ? 'selected' : ''); ?>>
                                    <?php echo e($kategori->nama); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif" <?php echo e(request('status') == 'aktif' ? 'selected' : ''); ?>>Aktif</option>
                        <option value="nonaktif" <?php echo e(request('status') == 'nonaktif' ? 'selected' : ''); ?>>Nonaktif</option>
                        <option value="dijual" <?php echo e(request('status') == 'dijual' ? 'selected' : ''); ?>>Dijual</option>
                        <option value="hilang" <?php echo e(request('status') == 'hilang' ? 'selected' : ''); ?>>Hilang</option>
                        <option value="rusak" <?php echo e(request('status') == 'rusak' ? 'selected' : ''); ?>>Rusak</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari aset..." value="<?php echo e(request('search')); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <?php if(request()->has('jenis_aset') || request()->has('kategori') || request()->has('status') || request()->has('search')): ?>
                            <a href="<?php echo e(route('master-data.aset.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode Aset</th>
                            <th>Nama Aset</th>
                            <th>Jenis Aset</th>
                            <th>Kategori</th>
                            <th>Harga Perolehan (Rp)</th>
                            <th>Tanggal Beli</th>
                            <th>Nilai Buku (Rp)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $asets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $aset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($key + 1); ?></td>
                                <td><?php echo e($aset->kode_aset); ?></td>
                                <td><?php echo e($aset->nama_aset); ?></td>
                                <td><?php echo e($aset->kategori->jenisAset->nama ?? '-'); ?></td>
                                <td><?php echo e($aset->kategori->nama ?? '-'); ?></td>
                                <td class="text-end"><?php echo e(number_format($aset->harga_perolehan, 0, ',', '.')); ?></td>
                                <td><?php echo e(is_string($aset->tanggal_beli) ? \Carbon\Carbon::parse($aset->tanggal_beli)->format('d/m/Y') : $aset->tanggal_beli->format('d/m/Y')); ?></td>
                                <td class="text-end"><?php echo e(number_format($aset->nilai_buku, 0, ',', '.')); ?></td>
                                <td>
                                    <?php
                                        $badgeClass = [
                                            'aktif' => 'bg-success',
                                            'disewakan' => 'bg-info',
                                            'dioperasikan' => 'bg-primary',
                                            'dihapus' => 'bg-danger'
                                        ][$aset->status] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo e($badgeClass); ?>">
                                        <?php echo e(ucfirst($aset->status)); ?>

                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('master-data.aset.edit', $aset->id)); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('master-data.aset.destroy', $aset->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="<?php echo e(route('master-data.aset.show', $aset->id)); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada data aset</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            
            <?php if($asets->hasPages()): ?>
                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($asets->withQueryString()->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .table th {
        white-space: nowrap;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .table td {
        vertical-align: middle;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Format mata uang
    document.addEventListener('DOMContentLoaded', function() {
        // Format input harga
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        };

        // Format harga saat halaman dimuat
        document.querySelectorAll('.harga-format').forEach(element => {
            if (element.textContent.trim() !== '') {
                element.textContent = formatRupiah(parseInt(element.textContent));
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/aset/index.blade.php ENDPATH**/ ?>
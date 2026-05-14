<?php $__env->startSection('title', 'Transaksi Produksi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-industry me-2"></i>Transaksi Produksi
        </h2>
        <a href="<?php echo e(route('transaksi.produksi.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Data Produksi Produk
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('transaksi.produksi.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="<?php echo e(request('tanggal_mulai')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="<?php echo e(request('tanggal_selesai')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Produk</label>
                        <select name="produk_id" class="form-select">
                            <option value="">Semua Produk</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $produks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($produk->id); ?>" <?php echo e(request('produk_id') == $produk->id ? 'selected' : ''); ?>>
                                    <?php echo e($produk->nama_produk); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="siap_produksi" <?php echo e(request('status') == 'siap_produksi' ? 'selected' : ''); ?>>Siap Produksi</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="wip" <?php echo e(request('status') == 'wip' ? 'selected' : ''); ?>>Proses</option>
                            <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Start Production Again Section -->
    <?php
        $completedProductions = $produksis->filter(function($p) {
            return $p->status === 'selesai';
        });
        $completedProductIds = $completedProductions->pluck('produk_id')->unique();
        $availableProducts = \App\Models\Produk::whereIn('id', $completedProductIds)
            ->whereHas('boms', function($query) {
                $query->has('details');
            })->get();
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availableProducts->count() > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-redo me-2"></i>Mulai Produksi Lagi Hari Ini
            </h6>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <p class="text-muted mb-3">Pilih produk yang sudah pernah diproduksi untuk memulai produksi baru dengan data yang sama:</p>
            
            <form action="<?php echo e(route('transaksi.produksi.mulai-lagi')); ?>" method="POST" class="row g-3" id="mulaiLagiForm">
                <?php echo csrf_field(); ?>
                <div class="col-md-8">
                    <label class="form-label">Produk</label>
                    <select name="produk_id" class="form-select" required>
                        <option value="">Pilih Produk</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $lastProduction = $completedProductions->where('produk_id', $produk->id)->sortByDesc('tanggal')->first();
                            ?>
                            <option value="<?php echo e($produk->id); ?>" data-qty="<?php echo e($lastProduction->qty_produksi ?? 0); ?>" data-bulanan="<?php echo e($lastProduction->jumlah_produksi_bulanan ?? 0); ?>" data-hari="<?php echo e($lastProduction->hari_produksi_bulanan ?? 0); ?>">
                                <?php echo e($produk->nama_produk); ?> 
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastProduction): ?>
                                    (Terakhir: <?php echo e(number_format($lastProduction->qty_produksi, 2)); ?> pcs)
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-success d-block w-100">
                        <i class="fas fa-play me-2"></i>Mulai Produksi Lagi
                    </button>
                </div>
            </form>
            
            <div id="productionInfo" class="mt-3" style="display: none;">
                <div class="alert alert-info">
                    <strong>Data Produksi:</strong>
                    <div class="row">
                        <div class="col-md-4">Produksi Bulanan: <span id="infoBulanan">-</span></div>
                        <div class="col-md-4">Hari Kerja: <span id="infoHari">-</span> hari</div>
                        <div class="col-md-4">Qty Hari Ini: <span id="infoQty">-</span> pcs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Produksi
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'produk_id', 'status'])): ?>
                    <small class="text-muted">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-end">Produksi Bulanan</th>
                            <th class="text-center">Hari Kerja</th>
                            <th class="text-end">Qty Per Hari</th>
                            <th class="text-end">Total Biaya</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $produksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($p->tanggal)->format('d/m/Y')); ?></td>
                                <td><?php echo e($p->produk->nama_produk); ?></td>
                                <td class="text-end"><?php echo e(number_format($p->jumlah_produksi_bulanan ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-center"><?php echo e($p->hari_produksi_bulanan ?? '-'); ?> hari</td>
                                <td class="text-end"><?php echo e(number_format($p->qty_produksi, 2, ',', '.')); ?></td>
                                <td class="text-end fw-semibold">Rp <?php echo e(number_format($p->total_biaya, 0, ',', '.')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->status === 'draft'): ?>
                                        <span class="badge bg-info">Siap Produksi</span>
                                    <?php elseif($p->status === 'dalam_proses'): ?>
                                        <span class="badge bg-primary">Dalam Proses</span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->proses_saat_ini): ?>
                                            <br><small class="text-muted"><?php echo e($p->proses_saat_ini); ?></small>
                                            <br><small class="text-info"><?php echo e($p->proses_selesai); ?>/<?php echo e($p->total_proses); ?> proses</small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($p->status === 'selesai'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php elseif($p->status === 'draft'): ?>
                                        <span class="badge bg-secondary">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e($p->status); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo e(route('transaksi.produksi.show', $p->id)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->status === 'draft'): ?>
                                        <?php
                                            // Check stock availability from produksi_details
                                            $stockSufficient = true;
                                            $shortageMessages = [];
                                            
                                            foreach ($p->details as $detail) {
                                                if ($detail->bahanBaku) {
                                                    $bahan = $detail->bahanBaku;
                                                    $qtyNeeded = $detail->qty_resep;
                                                    $available = (float)($bahan->stok ?? 0);
                                                    
                                                    // Convert if needed
                                                    $satuanResep = $detail->satuan_resep;
                                                    $satuanBahan = $bahan->satuan->nama ?? $bahan->satuan;
                                                    
                                                    if ($satuanResep !== $satuanBahan) {
                                                        $qtyNeeded = $bahan->konversiBerdasarkanProduksi($qtyNeeded, $satuanResep, $satuanBahan);
                                                    }
                                                    
                                                    if ($available < $qtyNeeded) {
                                                        $stockSufficient = false;
                                                        $shortageMessages[] = "{$bahan->nama_bahan}: butuh " . number_format($qtyNeeded, 2) . " {$satuanBahan}, tersedia " . number_format($available, 2);
                                                    }
                                                }
                                            }
                                        ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stockSufficient): ?>
                                            <form action="<?php echo e(route('transaksi.produksi.mulai-produksi', $p->id)); ?>" method="POST" style="display: inline;">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mulai produksi untuk <?php echo e($p->produk->nama_produk); ?>?')">
                                                    <i class="fas fa-play"></i> Mulai Produksi
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled 
                                                    title="Stok tidak cukup: <?php echo e(implode(', ', $shortageMessages)); ?>"
                                                    data-bs-toggle="tooltip" data-bs-placement="top">
                                                <i class="fas fa-exclamation-triangle"></i> Stok Kurang
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->status === 'dalam_proses'): ?>
                                        <a href="<?php echo e(route('transaksi.produksi.proses', $p->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-tasks"></i> Kelola Proses
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                <?php echo e($produksis->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Show production info when product is selected
    const produkSelect = document.querySelector('#mulaiLagiForm select[name="produk_id"]');
    const productionInfo = document.getElementById('productionInfo');
    const infoBulanan = document.getElementById('infoBulanan');
    const infoHari = document.getElementById('infoHari');
    const infoQty = document.getElementById('infoQty');
    
    if (produkSelect && productionInfo) {
        produkSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const qty = selectedOption.dataset.qty;
                const bulanan = selectedOption.dataset.bulanan;
                const hari = selectedOption.dataset.hari;
                
                infoBulanan.textContent = parseFloat(bulanan).toLocaleString('id-ID');
                infoHari.textContent = hari;
                infoQty.textContent = parseFloat(qty).toLocaleString('id-ID');
                
                productionInfo.style.display = 'block';
            } else {
                productionInfo.style.display = 'none';
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/produksi/index.blade.php ENDPATH**/ ?>
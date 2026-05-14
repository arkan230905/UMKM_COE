<?php $__env->startSection('title', 'Daftar Produk'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.produk-page { background:#f8f9fa; min-height:100vh; padding:28px 24px; font-family:'Poppins',sans-serif; }
.produk-container { max-width:1400px; margin:0 auto; }
.produk-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.produk-header-left h1 { font-size:22px; font-weight:700; color:#212529; margin:0 0 3px 0; }
.produk-header-left p { font-size:12px; color:#6c757d; margin:0; }
.produk-header-right { display:flex; gap:10px; align-items:center; }
.btn-cetak { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; background:white; border:1.5px solid #dee2e6; border-radius:8px; color:#495057; font-size:13px; font-weight:500; text-decoration:none; transition:all 0.2s; }
.btn-cetak:hover { background:#f8f9fa; border-color:#6c757d; color:#212529; }
.btn-tambah-prod { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:#a0826d; border:none; border-radius:8px; color:white; font-size:13px; font-weight:600; text-decoration:none; transition:all 0.2s; box-shadow:0 2px 8px rgba(160,130,109,0.3); }
.btn-tambah-prod:hover { background:#8b6f5c; color:white; transform:translateY(-1px); }
.produk-layout { display:flex; gap:20px; align-items:flex-start; }
.produk-sidebar { width:240px; flex-shrink:0; }
.sidebar-card { background:white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden; margin-bottom:12px; }
.sidebar-card-header { padding:16px 18px 12px; border-bottom:1px solid #e9ecef; }
.sidebar-card-header h3 { font-size:14px; font-weight:700; color:#212529; margin:0; }
.kat-all { display:flex; align-items:center; gap:10px; padding:11px 18px; text-decoration:none; transition:all 0.2s; border-bottom:1px solid #f1f3f5; }
.kat-all:hover { background:#f8f9fa; }
.kat-all.active { background:#a0826d; }
.kat-icon { width:30px; height:30px; border-radius:7px; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-size:12px; color:#495057; flex-shrink:0; }
.kat-all.active .kat-icon { background:rgba(255,255,255,0.2); color:white; }
.kat-all .kat-label { flex:1; font-size:13px; font-weight:500; color:#495057; }
.kat-all.active .kat-label { color:white; }
.kat-all .kat-count { font-size:11px; font-weight:600; color:#6c757d; background:#e9ecef; padding:2px 8px; border-radius:20px; }
.kat-all.active .kat-count { background:rgba(255,255,255,0.25); color:white; }
.kat-list { list-style:none; padding:0; margin:0; }
.kat-list li { border-bottom:1px solid #f1f3f5; }
.kat-list li:last-child { border-bottom:none; }
.kat-link { display:flex; align-items:center; gap:10px; padding:11px 18px; text-decoration:none; transition:all 0.2s; }
.kat-link:hover { background:#f8f9fa; }
.kat-link.active { background:#f5ebe5; }
.kat-link .kat-icon { background:#f1f3f5; color:#6c757d; }
.kat-link.active .kat-icon { background:#e8d5c9; color:#a0826d; }
.kat-link .kat-label { flex:1; font-size:13px; color:#495057; }
.kat-link.active .kat-label { color:#8b6f5c; font-weight:600; }
.kat-link .kat-count { font-size:11px; font-weight:600; color:#6c757d; background:#e9ecef; padding:2px 8px; border-radius:20px; }
.kat-link.active .kat-count { background:#d4bfb0; color:#8b6f5c; }
.btn-add-kat { display:flex; align-items:center; justify-content:center; gap:7px; padding:11px 16px; background:white; border:1.5px dashed #adb5bd; border-radius:8px; color:#495057; font-size:12px; font-weight:500; text-decoration:none; transition:all 0.2s; width:100%; }
.btn-add-kat:hover { background:#f8f9fa; border-color:#495057; border-style:solid; color:#212529; }
.produk-content { flex:1; min-width:0; }
.filter-card { background:white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:16px 20px; margin-bottom:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.search-wrap { flex:1; min-width:220px; position:relative; }
.search-wrap .si { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#adb5bd; font-size:13px; }
.search-input { width:100%; padding:9px 12px 9px 36px; border:1.5px solid #dee2e6; border-radius:8px; font-size:13px; color:#495057; background:white; outline:none; transition:all 0.2s; }
.search-input:focus { border-color:#a0826d; background:white; box-shadow:0 0 0 3px rgba(160,130,109,0.1); }
.search-input::placeholder { color:#adb5bd; }
.f-select { padding:9px 12px; border:1.5px solid #dee2e6; border-radius:8px; font-size:13px; color:#495057; background:white; cursor:pointer; min-width:140px; outline:none; }
.f-select:focus { border-color:#a0826d; }
.btn-cari { padding:9px 16px; background:#a0826d; border:none; border-radius:8px; color:white; font-size:13px; cursor:pointer; display:flex; align-items:center; gap:6px; }
.btn-cari:hover { background:#8b6f5c; }
.btn-reset { padding:9px 14px; background:white; border:1.5px solid #dee2e6; border-radius:8px; color:#6c757d; font-size:13px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:6px; }
.btn-reset:hover { background:#f8f9fa; color:#495057; }
.table-card { background:white; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden; }
.table-card-head { padding:16px 20px; border-bottom:1px solid #e9ecef; display:flex; align-items:center; justify-content:space-between; }
.table-card-head h4 { font-size:14px; font-weight:600; color:#212529; margin:0; }
.table-card-head span { font-size:12px; color:#6c757d; }
.table-wrap { overflow-x:auto; }
.pt { width:100%; border-collapse:collapse; font-size:13px; }
.pt thead tr { background:#f8f9fa; border-bottom:1.5px solid #dee2e6; }
.pt th { padding:12px 14px; text-align:left; font-weight:600; color:#495057; white-space:nowrap; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; }
.pt th.tc { text-align:center; } .pt th.tr { text-align:right; }
.pt tbody tr { border-bottom:1px solid #f1f3f5; transition:background 0.15s; }
.pt tbody tr:last-child { border-bottom:none; }
.pt tbody tr:hover { background:#f8f9fa; }
.pt td { padding:13px 14px; vertical-align:middle; color:#212529; }
.td-no { text-align:center; color:#6c757d; font-size:12px; width:40px; }
.td-foto { text-align:center; width:70px; }
.pimg { width:48px; height:48px; border-radius:8px; object-fit:cover; cursor:pointer; border:2px solid #e9ecef; transition:all 0.2s; }
.pimg:hover { border-color:#a0826d; transform:scale(1.08); }
.pimg-ph { width:48px; height:48px; border-radius:8px; background:#f1f3f5; display:flex; align-items:center; justify-content:center; color:#adb5bd; font-size:16px; margin:0 auto; }
.td-bc { text-align:center; width:110px; }
.bc-svg { height:32px; display:block; margin:0 auto 2px; }
.bc-num { font-size:9px; color:#6c757d; letter-spacing:0.5px; }
.td-nama { min-width:140px; }
.pname { font-weight:600; color:#212529; font-size:13px; }
.td-kat { width:110px; }
.kb { display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }
.kb-mkn { background:#FFF3E0; color:#E65100; }
.kb-mnm { background:#E3F2FD; color:#1565C0; }
.kb-pkt { background:#E8F5E9; color:#2E7D32; }
.kb-lain { background:#F5F5F5; color:#757575; }
.td-harga { text-align:right; min-width:110px; white-space:nowrap; }
.hval { font-weight:600; color:#212529; font-size:13px; }
.td-stok { text-align:center; width:60px; }
.sval { font-weight:700; font-size:14px; color:#212529; }
.td-status { text-align:center; width:80px; }
.sbadge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; }
.s-aktif { background:#E8F5E9; color:#2E7D32; }
.s-habis { background:#FFEBEE; color:#C62828; }
.td-aksi { text-align:center; width:100px; white-space:nowrap; }
.ba { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:7px; border:none; cursor:pointer; font-size:12px; transition:all 0.2s; margin:0 2px; text-decoration:none; }
.ba-bc { background:#E3F2FD; color:#1565C0; } .ba-bc:hover { background:#BBDEFB; }
.ba-ed { background:#FFF8E1; color:#E65100; } .ba-ed:hover { background:#FFE0B2; }
.ba-del { background:#FFEBEE; color:#C62828; } .ba-del:hover { background:#FFCDD2; }
.empty-st { text-align:center; padding:60px 20px; color:#6c757d; }
.empty-st i { font-size:40px; margin-bottom:12px; display:block; color:#adb5bd; }
.pg-bar { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-top:1px solid #e9ecef; flex-wrap:wrap; gap:10px; }
.pg-info { font-size:12px; color:#6c757d; }
.pg-btns { display:flex; gap:4px; align-items:center; }
.pgb { width:32px; height:32px; border:1.5px solid #dee2e6; background:white; border-radius:7px; cursor:pointer; font-size:12px; font-weight:500; color:#495057; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
.pgb:hover { background:#f8f9fa; border-color:#adb5bd; }
.pgb.active { background:#a0826d; border-color:#a0826d; color:white; font-weight:700; }
.pg-sel { padding:5px 10px; border:1.5px solid #dee2e6; border-radius:7px; font-size:12px; color:#495057; background:white; cursor:pointer; outline:none; }
@media(max-width:1024px){ .produk-layout{flex-direction:column;} .produk-sidebar{width:100%;} .filter-card{flex-direction:column;align-items:stretch;} }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="produk-page">
<div class="produk-container">


<div class="produk-header">
    <div class="produk-header-left">
        <h1><i class="fas fa-box-open" style="color:#a0826d;margin-right:8px;"></i>Daftar Produk</h1>
        <p>Kelola semua produk yang tersedia dalam sistem</p>
    </div>
    <div class="produk-header-right">
        <a href="<?php echo e(route('master-data.produk.print-barcode-all')); ?>" class="btn-cetak" target="_blank">
            <i class="fas fa-barcode"></i> Cetak Barcode Semua
        </a>
        <a href="<?php echo e(route('master-data.produk.create')); ?>" class="btn-tambah-prod">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>
</div>


<div class="produk-layout">

    
    <div class="produk-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <h3><i class="fas fa-tags" style="color:#a0826d;margin-right:6px;"></i>Kategori Produk</h3>
            </div>
        <a href="<?php echo e(route('master-data.produk.index')); ?>"
               class="kat-all <?php echo e(!isset($kategoriFilter) || !$kategoriFilter ? 'active' : ''); ?>">
                <span class="kat-icon"><i class="fas fa-th-large"></i></span>
                <span class="kat-label">Semua Kategori</span>
                <span class="kat-count"><?php echo e($produks->count()); ?></span>
            </a>
            <ul class="kat-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategoris ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <a href="<?php echo e(route('master-data.produk.index', ['kategori' => $kategori->id])); ?>"
                       class="kat-link <?php echo e(isset($kategoriFilter) && $kategoriFilter == $kategori->id ? 'active' : ''); ?>">
                        <span class="kat-icon">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($kategori->kode_kategori):
                                case ('MKN'): ?> <i class="fas fa-utensils"></i> <?php break; ?>
                                <?php case ('MNM'): ?> <i class="fas fa-coffee"></i> <?php break; ?>
                                <?php case ('PKT'): ?> <i class="fas fa-gift"></i> <?php break; ?>
                                <?php default: ?>     <i class="fas fa-cube"></i>
                            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                        <span class="kat-label"><?php echo e($kategori->nama); ?></span>
                        <span class="kat-count"><?php echo e($kategori->produks_count); ?></span>
                    </a>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
        <button type="button" class="btn-add-kat" onclick="openModalAturKategori()">
            <i class="fas fa-cog"></i> Atur Kategori Produk
        </button>
    </div>

    
    <div class="produk-content">

        
        <form method="GET" action="<?php echo e(route('master-data.produk.index')); ?>" class="filter-card">
            <div class="search-wrap">
                <i class="fas fa-search si"></i>
                <input type="text" name="search" value="<?php echo e($search ?? ''); ?>"
                       placeholder="Cari nama produk atau barcode..." class="search-input">
            </div>
            <select name="status" class="f-select">
                <option value="">Semua Status</option>
                <option value="aktif"  <?php echo e(isset($statusFilter) && $statusFilter == 'aktif'  ? 'selected' : ''); ?>>Aktif</option>
                <option value="habis"  <?php echo e(isset($statusFilter) && $statusFilter == 'habis'  ? 'selected' : ''); ?>>Habis</option>
            </select>
            <button type="submit" class="btn-cari"><i class="fas fa-search"></i> Cari</button>
            <a href="<?php echo e(route('master-data.produk.index')); ?>" class="btn-reset"><i class="fas fa-undo"></i> Reset</a>
        </form>

        
        <div class="table-card">
            <div class="table-card-head">
                <h4>Data Produk</h4>
                <span><?php echo e($produks->count()); ?> produk ditemukan</span>
            </div>
            <div class="table-wrap">
                <table class="pt">
                    <thead>
                        <tr>
                            <th class="tc">No</th>
                            <th class="tc">Foto</th>
                            <th class="tc">Barcode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="tr">Harga Pokok</th>
                            <th class="tr">Harga Jual</th>
                            <th class="tc">Stok</th>
                            <th class="tc">Status</th>
                            <th class="tc">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $hpp   = $hargaBom[$produk->id] ?? 0;
                            $hjual = $produk->harga_jual ?? 0;
                            $stok  = (float) $produk->stok;
                            $kb = 'kb-lain';
                            if ($produk->kategori) {
                                switch ($produk->kategori->kode_kategori) {
                                    case 'MKN': $kb = 'kb-mkn'; break;
                                    case 'MNM': $kb = 'kb-mnm'; break;
                                    case 'PKT': $kb = 'kb-pkt'; break;
                                    case 'SNK': $kb = 'kb-snk'; break;
                                    case 'BMB': $kb = 'kb-bmb'; break;
                                }
                            }
                        ?>
                        <tr>
                            <td class="td-no"><?php echo e($loop->iteration); ?></td>
                            <td class="td-foto">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->foto): ?>
                                    <img src="<?php echo e(Storage::url($produk->foto)); ?>" alt="<?php echo e($produk->nama_produk); ?>"
                                         class="pimg"
                                         onclick="showImg('<?php echo e(Storage::url($produk->foto)); ?>','<?php echo e(addslashes($produk->nama_produk)); ?>')"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22%3E%3Crect fill=%22%23f0ebe4%22 width=%2248%22 height=%2248%22/%3E%3C/svg%3E';">
                                <?php else: ?>
                                    <div class="pimg-ph"><i class="fas fa-image"></i></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td-bc">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->barcode): ?>
                                    <svg class="bc-svg" data-barcode="<?php echo e($produk->barcode); ?>"></svg>
                                    <div class="bc-num"><?php echo e($produk->barcode); ?></div>
                                <?php else: ?>
                                    <span style="color:#D4C4B0;">—</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td-nama"><div class="pname"><?php echo e($produk->nama_produk); ?></div></td>
                            <td class="td-kat">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->kategori): ?>
                                    <span class="kb <?php echo e($kb); ?>"><?php echo e($produk->kategori->nama); ?></span>
                                <?php else: ?>
                                    <span style="color:#D4C4B0;font-size:12px;">—</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td-harga"><span class="hval">Rp <?php echo e(number_format($hpp,0,',','.')); ?></span></td>
                            <td class="td-harga"><span class="hval">Rp <?php echo e(number_format($hjual,0,',','.')); ?></span></td>
                            <td class="td-stok">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->is_unlimited_stok): ?>
                                    <span class="sval" style="font-size:18px;color:#8B7355;" title="Stok tidak terbatas (mengikuti stok produk dalam paket)">∞</span>
                                <?php else: ?>
                                    <span class="sval"><?php echo e(number_format($stok,0,',','.')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td-status">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->is_unlimited_stok): ?>
                                    <span class="sbadge s-aktif">Aktif</span>
                                <?php elseif($stok > 0): ?>
                                    <span class="sbadge s-aktif">Aktif</span>
                                <?php else: ?>
                                    <span class="sbadge s-habis">Habis</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="td-aksi">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->barcode): ?>
                                <a href="<?php echo e(route('master-data.produk.print-barcode', $produk->id)); ?>"
                                   class="ba ba-bc" title="Cetak Barcode" target="_blank">
                                    <i class="fas fa-barcode"></i>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <a href="<?php echo e(route('master-data.produk.edit', $produk->id)); ?>"
                                   class="ba ba-ed" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="<?php echo e(route('master-data.produk.destroy', $produk->id)); ?>"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="ba ba-del" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-st">
                                    <i class="fas fa-box-open"></i>
                                    <p>Belum ada data produk</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="pg-bar">
                <span class="pg-info">Menampilkan <?php echo e($produks->count()); ?> produk</span>
                <div class="pg-btns">
                    <button class="pgb" type="button"><i class="fas fa-chevron-left"></i></button>
                    <button class="pgb active" type="button">1</button>
                    <button class="pgb" type="button">2</button>
                    <button class="pgb" type="button">3</button>
                    <button class="pgb" type="button"><i class="fas fa-chevron-right"></i></button>
                </div>
                <select class="pg-sel">
                    <option>10 / halaman</option>
                    <option>25 / halaman</option>
                    <option>50 / halaman</option>
                </select>
            </div>
        </div>

    </div>
</div>
</div>
</div>


<div id="modalAturKategori" style="display:none;position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:18px;width:100%;max-width:680px;margin:20px;box-shadow:0 24px 64px rgba(0,0,0,0.18);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">

        
        <div style="padding:22px 24px 16px;border-bottom:1px solid #F0EAE2;display:flex;align-items:flex-start;justify-content:space-between;flex-shrink:0;">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <div style="width:36px;height:36px;background:#F5EFE8;border-radius:9px;display:flex;align-items:center;justify-content:center;color:#8B7355;font-size:16px;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span style="font-size:17px;font-weight:700;color:#2C1810;">Atur Kategori Produk</span>
                </div>
                <p style="margin:0 0 0 46px;font-size:12px;color:#A89080;">Kelola kategori produk (tambah, ubah, hapus).</p>
            </div>
            <button onclick="closeModalAturKategori()" style="width:34px;height:34px;background:#F5F5F5;border:none;border-radius:8px;cursor:pointer;font-size:18px;color:#666;display:flex;align-items:center;justify-content:center;flex-shrink:0;">&times;</button>
        </div>

        
        <div id="atur-kat-alert" style="display:none;margin:12px 24px 0;padding:10px 14px;border-radius:8px;font-size:13px;"></div>

        
        <div style="overflow-y:auto;flex:1;padding:16px 24px;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid #F0EAE2;">
                        <th style="padding:10px 12px;text-align:left;font-weight:600;color:#7A6349;width:44px;">No</th>
                        <th style="padding:10px 12px;text-align:left;font-weight:600;color:#7A6349;">Nama Kategori</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#7A6349;width:110px;">Jumlah Produk</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#7A6349;width:80px;">Status</th>
                        <th style="padding:10px 12px;text-align:center;font-weight:600;color:#7A6349;width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="atur-kat-tbody">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategoris ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $kat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr id="kat-row-<?php echo e($kat->id); ?>" style="border-bottom:1px solid #F5F0EB;">
                        
                        <td style="padding:13px 12px;color:#B0A090;font-size:12px;"><?php echo e($i + 1); ?></td>
                        <td style="padding:13px 12px;">
                            <span id="kat-view-nama-<?php echo e($kat->id); ?>" style="font-weight:500;color:#2C1810;"><?php echo e($kat->nama); ?></span>
                            <input id="kat-edit-nama-<?php echo e($kat->id); ?>" type="text" value="<?php echo e($kat->nama); ?>"
                                   style="display:none;width:100%;padding:7px 10px;border:1.5px solid #8B7355;border-radius:7px;font-size:13px;color:#2C1810;outline:none;box-sizing:border-box;">
                        </td>
                        <td style="padding:13px 12px;text-align:center;color:#4A3728;font-weight:600;"><?php echo e($kat->produks_count); ?></td>
                        <td style="padding:13px 12px;text-align:center;">
                            <span style="background:#DCFCE7;color:#15803D;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Aktif</span>
                        </td>
                        <td style="padding:13px 12px;text-align:center;">
                            
                            <div id="kat-view-btns-<?php echo e($kat->id); ?>" style="display:flex;gap:6px;justify-content:center;">
                                <button onclick="startEditKat(<?php echo e($kat->id); ?>)"
                                        style="width:30px;height:30px;background:#FFF8E1;border:1.5px solid #FDE68A;border-radius:7px;cursor:pointer;color:#D97706;font-size:12px;display:flex;align-items:center;justify-content:center;"
                                        title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button onclick="hapusKat(<?php echo e($kat->id); ?>, '<?php echo e(addslashes($kat->nama)); ?>')"
                                        style="width:30px;height:30px;background:#FFF1F2;border:1.5px solid #FECDD3;border-radius:7px;cursor:pointer;color:#BE123C;font-size:12px;display:flex;align-items:center;justify-content:center;"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div id="kat-edit-btns-<?php echo e($kat->id); ?>" style="display:none;gap:6px;justify-content:center;">
                                <button onclick="simpanEditKat(<?php echo e($kat->id); ?>, '<?php echo e($kat->kode_kategori); ?>')"
                                        style="width:30px;height:30px;background:#DCFCE7;border:1.5px solid #86EFAC;border-radius:7px;cursor:pointer;color:#15803D;font-size:12px;display:flex;align-items:center;justify-content:center;"
                                        title="Simpan">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="cancelEditKat(<?php echo e($kat->id); ?>)"
                                        style="width:30px;height:30px;background:#F5F5F5;border:1.5px solid #E0E0E0;border-radius:7px;cursor:pointer;color:#666;font-size:14px;display:flex;align-items:center;justify-content:center;"
                                        title="Batal">
                                    &times;
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <tr style="background:#FDFAF7;border-top:2px dashed #E8DDD0;">
                        <td style="padding:13px 12px;text-align:center;color:#8B7355;font-size:18px;font-weight:700;">+</td>
                        <td style="padding:13px 12px;">
                            <input type="text" id="new-kat-nama" placeholder="Nama kategori baru..."
                                   style="width:100%;padding:8px 12px;border:1.5px solid #E8DDD0;border-radius:8px;font-size:13px;color:#2C1810;background:white;outline:none;box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#8B7355'" onblur="this.style.borderColor='#E8DDD0'"
                                   onkeydown="if(event.key==='Enter') simpanKatBaru()">
                        </td>
                        <td style="padding:13px 12px;text-align:center;color:#C0B0A0;">—</td>
                        <td style="padding:13px 12px;text-align:center;">
                            <span style="background:#DCFCE7;color:#15803D;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Aktif</span>
                        </td>
                        <td style="padding:13px 12px;text-align:center;">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <button onclick="simpanKatBaru()" id="btn-simpan-baru"
                                        style="width:30px;height:30px;background:#15803D;border:none;border-radius:7px;cursor:pointer;color:white;font-size:12px;display:flex;align-items:center;justify-content:center;"
                                        title="Simpan">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="document.getElementById('new-kat-nama').value='';document.getElementById('new-kat-kode').value='';"
                                        style="width:30px;height:30px;background:#F5F5F5;border:1.5px solid #E0E0E0;border-radius:7px;cursor:pointer;color:#666;font-size:14px;display:flex;align-items:center;justify-content:center;"
                                        title="Batal">
                                    &times;
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        
        <div style="padding:14px 24px;border-top:1px solid #F0EAE2;display:flex;justify-content:flex-end;flex-shrink:0;">
            <button onclick="closeModalAturKategori()"
                    style="padding:9px 22px;background:white;border:1.5px solid #E8DDD0;border-radius:8px;color:#7A6349;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='#F5EFE8'" onmouseout="this.style.background='white'">
                Tutup
            </button>
        </div>
    </div>
</div>


<div id="imgLb" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:9999;cursor:pointer;" onclick="closeImg()">
    <div style="position:absolute;top:20px;right:28px;color:white;font-size:36px;font-weight:bold;cursor:pointer;" onclick="closeImg()">&times;</div>
    <div style="position:absolute;top:20px;left:28px;color:rgba(255,255,255,0.7);font-size:14px;" id="imgLbTitle"></div>
    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:60px 20px 20px;">
        <img id="imgLbSrc" src="" alt="" style="max-width:90%;max-height:90%;object-fit:contain;border-radius:12px;">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bc-svg').forEach(function (svg) {
        var val = svg.getAttribute('data-barcode');
        if (!val) return;
        try {
            JsBarcode(svg, val, { format:'EAN13', width:1.2, height:32, displayValue:false, margin:0, background:'transparent' });
        } catch(e) {
            try { JsBarcode(svg, val, { format:'CODE128', width:1, height:32, displayValue:false, margin:0, background:'transparent' }); } catch(e2){}
        }
    });
});
window.showImg = function(url, name) {
    document.getElementById('imgLbSrc').src = url;
    document.getElementById('imgLbTitle').textContent = name;
    document.getElementById('imgLb').style.display = 'block';
};
window.closeImg = function() { document.getElementById('imgLb').style.display = 'none'; };
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeImg(); });

// ===== MODAL ATUR KATEGORI =====
window.openModalAturKategori = function() {
    var m = document.getElementById('modalAturKategori');
    m.style.display = 'flex';
    document.getElementById('atur-kat-alert').style.display = 'none';
    document.getElementById('new-kat-nama').value = '';
    document.getElementById('new-kat-kode').value = '';
};

window.closeModalAturKategori = function() {
    document.getElementById('modalAturKategori').style.display = 'none';
};

// Show alert inside modal
function showAturAlert(msg, type) {
    var el = document.getElementById('atur-kat-alert');
    el.textContent = msg;
    el.style.display = 'block';
    if (type === 'success') {
        el.style.background = '#DCFCE7'; el.style.color = '#15803D'; el.style.border = '1px solid #86EFAC';
    } else {
        el.style.background = '#FFEBEE'; el.style.color = '#C62828'; el.style.border = '1px solid #FFCDD2';
    }
    setTimeout(function(){ el.style.display = 'none'; }, 3000);
}

// ── TAMBAH KATEGORI BARU ──────────────────────────────────
window.simpanKatBaru = function() {
    var nama = document.getElementById('new-kat-nama').value.trim();
    if (!nama) { document.getElementById('new-kat-nama').focus(); showAturAlert('Nama kategori wajib diisi.', 'error'); return; }

    var btn = document.getElementById('btn-simpan-baru');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    fetch('<?php echo e(route("master-data.kategori-produk.store")); ?>', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' },
        body: JSON.stringify({ nama: nama })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.disabled = false;
        if (data.success || data.id) {
            showAturAlert('Kategori "' + nama + '" berhasil ditambahkan!', 'success');
            setTimeout(function(){ window.location.reload(); }, 800);
        } else {
            var msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal menyimpan.');
            showAturAlert(msg, 'error');
        }
    })
    .catch(function(){ btn.innerHTML = '<i class="fas fa-check"></i>'; btn.disabled = false; showAturAlert('Terjadi kesalahan.', 'error'); });
};

// ── EDIT KATEGORI ─────────────────────────────────────────
window.startEditKat = function(id) {
    document.getElementById('kat-view-nama-' + id).style.display = 'none';
    document.getElementById('kat-edit-nama-' + id).style.display = 'block';
    document.getElementById('kat-view-btns-' + id).style.display = 'none';
    document.getElementById('kat-edit-btns-' + id).style.display = 'flex';
    document.getElementById('kat-edit-nama-' + id).focus();
};

window.cancelEditKat = function(id) {
    var orig = document.getElementById('kat-view-nama-' + id).textContent;
    document.getElementById('kat-edit-nama-' + id).value = orig;
    document.getElementById('kat-view-nama-' + id).style.display = '';
    document.getElementById('kat-edit-nama-' + id).style.display = 'none';
    document.getElementById('kat-view-btns-' + id).style.display = 'flex';
    document.getElementById('kat-edit-btns-' + id).style.display = 'none';
};

window.simpanEditKat = function(id, kode) {
    var nama = document.getElementById('kat-edit-nama-' + id).value.trim();
    if (!nama) { showAturAlert('Nama kategori tidak boleh kosong.', 'error'); return; }

    fetch('<?php echo e(url("master-data/kategori-produk")); ?>/' + id, {
        method: 'PUT',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' },
        body: JSON.stringify({ nama: nama, kode_kategori: kode })
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('kat-view-nama-' + id).textContent = nama;
            cancelEditKat(id);
            showAturAlert('Kategori berhasil diperbarui!', 'success');
            setTimeout(function(){ window.location.reload(); }, 800);
        } else {
            showAturAlert(data.message || 'Gagal memperbarui.', 'error');
        }
    })
    .catch(function(){ showAturAlert('Terjadi kesalahan.', 'error'); });
};

// ── HAPUS KATEGORI ────────────────────────────────────────
window.hapusKat = function(id, nama) {
    if (!confirm('Yakin ingin menghapus kategori "' + nama + '"?\nProduk yang terkait tidak akan terhapus.')) return;

    fetch('<?php echo e(url("master-data/kategori-produk")); ?>/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = document.getElementById('kat-row-' + id);
            if (row) { row.style.opacity = '0'; row.style.transition = 'opacity 0.3s'; setTimeout(function(){ row.remove(); }, 300); }
            showAturAlert('Kategori "' + nama + '" berhasil dihapus.', 'success');
            setTimeout(function(){ window.location.reload(); }, 800);
        } else {
            showAturAlert(data.message || 'Gagal menghapus.', 'error');
        }
    })
    .catch(function(){ showAturAlert('Terjadi kesalahan.', 'error'); });
};

// Close modal on backdrop click
document.getElementById('modalAturKategori').addEventListener('click', function(e) {
    if (e.target === this) closeModalAturKategori();
});

// Close on ESC
document.addEventListener('keydown', function(e){ if(e.key==='Escape') { closeModalAturKategori(); closeImg(); } });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/produk/index.blade.php ENDPATH**/ ?>
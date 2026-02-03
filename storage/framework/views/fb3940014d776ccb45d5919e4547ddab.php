<?php $__env->startSection('content'); ?>
<style>
    /* Horizontal Scroll Table - Force scroll */
    .card-body {
        padding: 1rem;
    }
    
    .table-scroll-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar {
        height: 10px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 4px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
    }
    
    #dataTable {
        min-width: 1400px !important;
        width: 100%;
        margin-bottom: 0;
    }
    
    #dataTable th, #dataTable td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.5rem 0.75rem;
    }
    
    .scroll-hint {
        text-align: center;
        padding: 5px;
        background: #e9ecef;
        color: #666;
        font-size: 12px;
        border-radius: 0 0 4px 4px;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Produk</h1>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.produk.print-barcode-all')); ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-barcode"></i> Cetak Semua Barcode
            </a>
            <a href="<?php echo e(route('master-data.produk.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="scroll-hint">← Geser tabel ke kiri/kanan untuk melihat semua kolom →</div>
            <div class="table-scroll-wrapper">
                <table class="table table-bordered table-hover" id="dataTable" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Foto</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Harga BOM</th>
                            <th class="text-center">Margin</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-center">Stok</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $hargaBomProduk = $produk->harga_bom ?? 0;
                                $margin = (float) ($produk->margin_percent ?? 30);
                                $hargaJual = $produk->harga_jual ?? $hargaBomProduk * (1 + ($margin / 100));
                                $stok = (float) $produk->stok;
                            ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->foto): ?>
                                        <div class="product-image-wrapper" 
                                             onclick="showImageModal('<?php echo e(Storage::url($produk->foto)); ?>', '<?php echo e(addslashes($produk->nama_produk)); ?>')"
                                             style="width: 35px !important; height: 35px !important; cursor: pointer; position: relative; display: inline-block;">
                                            <img src="<?php echo e(Storage::url($produk->foto)); ?>" 
                                                 alt="<?php echo e($produk->nama_produk); ?>" 
                                                 class="product-thumbnail"
                                                 style="width: 35px !important; height: 35px !important; object-fit: cover; border-radius: 4px;"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                            <div class="image-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; border-radius: 4px;">
                                                <i class="fas fa-search-plus" style="color: white; font-size: 14px;"></i>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-image-placeholder" style="width: 35px !important; height: 35px !important;">
                                            <i class="fas fa-image" style="font-size: 12px;"></i>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="barcode-cell text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->barcode): ?>
                                        <div class="barcode-wrapper">
                                            <svg class="barcode-svg" data-barcode="<?php echo e($produk->barcode); ?>"></svg>
                                            <div class="barcode-number"><?php echo e($produk->barcode); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e($produk->nama_produk); ?></td>
                                <td><?php echo e($produk->deskripsi ? \Illuminate\Support\Str::limit($produk->deskripsi, 50) : '-'); ?></td>
                                <td class="text-right">Rp <?php echo e(number_format($hargaBomProduk, 0, ',', '.')); ?></td>
                                <td class="text-center"><?php echo e(number_format($margin, 0, ',', '.')); ?>%</td>
                                <td class="text-right font-weight-bold">Rp <?php echo e(number_format($hargaJual, 0, ',', '.')); ?></td>
                                <td class="text-center <?php echo e($stok <= 0 ? 'text-danger font-weight-bold' : ''); ?>">
                                    <?php echo e(number_format($stok, 0, ',', '.')); ?>

                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->barcode): ?>
                                        <a href="<?php echo e(route('master-data.produk.print-barcode', $produk->id)); ?>" 
                                           class="btn btn-sm btn-info" 
                                           data-bs-toggle="tooltip" 
                                           title="Cetak Label Barcode"
                                           target="_blank">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <a href="<?php echo e(route('master-data.produk.edit', $produk->id)); ?>" 
                                           class="btn btn-sm btn-warning" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.produk.destroy', $produk->id)); ?>" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data produk</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    /* Horizontal Scroll Table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    #dataTable {
        min-width: 1200px;
        white-space: nowrap;
    }
    
    .table th, .table td {
        vertical-align: middle;
        padding: 0.5rem 0.75rem;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .text-right {
        text-align: right !important;
    }
    .text-center {
        text-align: center !important;
    }
    
    /* Product Image Styling */
    .product-image-wrapper {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 40px;
        overflow: hidden;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .product-image-wrapper:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 10px rgba(0,0,0,0.25);
        z-index: 10;
    }
    
    .product-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .product-image-wrapper:hover .product-thumbnail {
        opacity: 0.85;
    }
    
    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        border-radius: 4px;
    }
    
    .product-image-wrapper:hover .image-overlay {
        opacity: 1 !important;
    }
    
    .image-overlay i {
        color: white;
        font-size: 14px;
    }
    
    .no-image-placeholder {
        width: 40px;
        height: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 4px;
        color: #6c757d;
    }
    
    .no-image-placeholder i {
        font-size: 14px;
        margin-bottom: 0;
    }
    
    .no-image-placeholder .small {
        font-size: 7px;
        line-height: 1;
    }
    
    /* Barcode Styling - Supermarket Style */
    .barcode-cell {
        min-width: 120px;
    }
    .barcode-wrapper {
        display: inline-block;
        background: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        text-align: center;
    }
    .barcode-svg {
        display: block;
        margin: 0 auto;
    }
    .barcode-number {
        font-family: 'Courier New', monospace;
        font-size: 10px;
        color: #333;
        margin-top: 2px;
        letter-spacing: 1px;
    }
    
    /* Modal Image Styling */
    #imageModal .modal-dialog {
        max-width: 90vw;
    }
    
    #imageModal .modal-body {
        background: #000;
        padding: 20px;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #imageModal .modal-content {
        background: transparent;
        border: none;
    }
    
    #imageModal .modal-header {
        background: #fff;
        border-bottom: 1px solid #dee2e6;
    }
    
    #modalImage {
        border-radius: 4px;
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        box-shadow: 0 4px 20px rgba(255,255,255,0.1);
    }
</style>
<?php $__env->stopPush(); ?>

<!-- Lightbox untuk preview foto fullscreen -->
<div id="imageLightbox" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; cursor: pointer;" onclick="closeLightbox()">
    <div style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; z-index: 10000;" onclick="closeLightbox()">&times;</div>
    <div style="position: absolute; top: 20px; left: 30px; color: white; font-size: 20px; z-index: 10000;" id="lightboxTitle"></div>
    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 60px 20px 20px 20px;">
        <img id="lightboxImage" src="" alt="Foto Produk" style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 30px rgba(255,255,255,0.3);">
    </div>
</div>

<!-- jQuery dan DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- JsBarcode Library untuk tampilan barcode seperti supermarket -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Initialize Barcodes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate visual barcodes
    document.querySelectorAll('.barcode-svg').forEach(function(svg) {
        const barcodeValue = svg.getAttribute('data-barcode');
        if (barcodeValue) {
            try {
                JsBarcode(svg, barcodeValue, {
                    format: "EAN13",
                    width: 1.2,
                    height: 30,
                    displayValue: false,
                    margin: 0,
                    background: "transparent"
                });
            } catch (e) {
                // Fallback untuk barcode yang tidak valid EAN-13
                JsBarcode(svg, barcodeValue, {
                    format: "CODE128",
                    width: 1,
                    height: 30,
                    displayValue: false,
                    margin: 0,
                    background: "transparent"
                });
            }
        }
    });
    console.log('✅ Barcodes generated');
});
</script>

<script>
    // Fungsi global untuk lightbox - HARUS di atas agar bisa dipanggil dari onclick
    window.showImageModal = function(imageUrl, productName) {
        console.log('🖼️ Opening lightbox for:', productName, imageUrl);
        
        const lightbox = document.getElementById('imageLightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxTitle = document.getElementById('lightboxTitle');
        
        if (!lightbox || !lightboxImage || !lightboxTitle) {
            console.error('❌ Lightbox elements not found!');
            return;
        }
        
        // Set image dan title
        lightboxImage.src = imageUrl;
        lightboxTitle.textContent = 'Foto Produk: ' + productName;
        
        // Tampilkan lightbox dengan fade in
        lightbox.style.display = 'block';
        setTimeout(() => {
            lightbox.style.opacity = '1';
        }, 10);
        
        console.log('✅ Lightbox opened successfully');
    };
    
    // Fungsi global untuk menutup lightbox
    window.closeLightbox = function() {
        console.log('🔒 Closing lightbox');
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox) {
            lightbox.style.opacity = '0';
            setTimeout(() => {
                lightbox.style.display = 'none';
            }, 300);
        }
    };
    
    // Tutup lightbox dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeLightbox();
        }
    });

    // Initialize setelah DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 Initializing DataTable and Tooltips');
        
        // Initialize tooltips Bootstrap 5
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize DataTable
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }
        
        // Get actual column count
        const headerCount = $('#dataTable thead th').length;
        const lastColIndex = headerCount - 1;
        
        console.log('📊 DataTable initializing with ' + headerCount + ' columns');
        
        const table = $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [0, lastColIndex] 
                },
                { 
                    "searchable": false, 
                    "targets": [0, lastColIndex] 
                },
                {
                    "className": "text-end",
                    "targets": [5, 7]
                },
                {
                    "className": "text-center",
                    "targets": [1, 2, 6, 8, lastColIndex]
                }
            ],
            "order": [[3, 'asc']],
            "pageLength": 25
        });
        
        // Update nomor urut otomatis setiap kali tabel di-render ulang
        table.on('order.dt search.dt', function () {
            let i = 1;
            table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
                this.data(i++);
            });
        }).draw();
        
        console.log('✅ DataTable initialized');
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/produk/index.blade.php ENDPATH**/ ?>
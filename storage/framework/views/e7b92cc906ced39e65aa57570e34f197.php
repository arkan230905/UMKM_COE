<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detail BOM: <?php echo e($bom->produk->nama_produk); ?> - Process Costing</h3>
        <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Informasi Dasar -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Nama Produk</th>
                            <td><?php echo e($bom->produk->nama_produk); ?></td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td><?php echo e($bom->periode ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td><?php echo e($bom->created_at->format('d F Y H:i')); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Biaya Bahan Baku (BBB) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-boxes"></i> 1. Biaya Bahan Baku (BBB)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $no = 1; 
                            $totalBBB = 0;
                            $converter = new \App\Support\UnitConverter();
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bom->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                // Ambil harga TERBARU dari bahan baku
                                $bahanBaku = $detail->bahanBaku;
                                if ($bahanBaku) {
                                    $hargaTerbaru = $bahanBaku->harga_satuan ?? 0;
                                    
                                    // Konversi satuan untuk perhitungan
                                    $satuanBase = is_object($bahanBaku->satuan) 
                                        ? $bahanBaku->satuan->nama 
                                        : ($bahanBaku->satuan ?? 'unit');
                                    
                                    try {
                                        $qtyBase = $converter->convert(
                                            (float) $detail->jumlah,
                                            $detail->satuan ?: $satuanBase,
                                            $satuanBase
                                        );
                                        $subtotal = $hargaTerbaru * $qtyBase;
                                    } catch (\Exception $e) {
                                        $subtotal = $hargaTerbaru * $detail->jumlah;
                                    }
                                    
                                    $totalBBB += $subtotal;
                                } else {
                                    $hargaTerbaru = 0;
                                    $subtotal = 0;
                                }
                            ?>
                            <tr>
                                <td><?php echo e($no++); ?></td>
                                <td><?php echo e($bahanBaku->nama_bahan ?? 'Bahan Tidak Ditemukan'); ?></td>
                                <td class="text-end"><?php echo e(number_format($detail->jumlah, 2, ',', '.')); ?></td>
                                <td class="text-center"><?php echo e($detail->satuan); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($hargaTerbaru, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Baku (BBB)</td>
                            <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBBB, 0, ',', '.')); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 2: Biaya Bahan Pendukung -->
    <?php
        // Ambil data Bahan Pendukung dari BomJobCosting
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $bom->produk_id)
            ->with(['detailBahanPendukung.bahanPendukung.satuan'])
            ->first();
        $totalBahanPendukung = 0;
    ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bomJobCosting && $bomJobCosting->detailBahanPendukung->count() > 0): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-cubes"></i> 2. Biaya Bahan Pendukung</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Bahan Pendukung</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $noPendukung = 1; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bomJobCosting->detailBahanPendukung; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detailPendukung): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                // Ambil harga TERBARU dari bahan pendukung
                                $bahanPendukung = $detailPendukung->bahanPendukung;
                                if ($bahanPendukung) {
                                    $hargaTerbaru = $bahanPendukung->harga_satuan ?? 0;
                                    
                                    // Konversi satuan untuk perhitungan
                                    $satuanBase = is_object($bahanPendukung->satuan) 
                                        ? $bahanPendukung->satuan->nama 
                                        : ($bahanPendukung->satuan ?? 'unit');
                                    
                                    try {
                                        $qtyBase = $converter->convert(
                                            (float) $detailPendukung->jumlah,
                                            $detailPendukung->satuan ?: $satuanBase,
                                            $satuanBase
                                        );
                                        $subtotal = $hargaTerbaru * $qtyBase;
                                    } catch (\Exception $e) {
                                        $subtotal = $hargaTerbaru * $detailPendukung->jumlah;
                                    }
                                    
                                    $totalBahanPendukung += $subtotal;
                                } else {
                                    $hargaTerbaru = 0;
                                    $subtotal = 0;
                                }
                            ?>
                            <tr>
                                <td><?php echo e($noPendukung++); ?></td>
                                <td><?php echo e($bahanPendukung->nama_bahan ?? 'Bahan Tidak Ditemukan'); ?></td>
                                <td class="text-end"><?php echo e(number_format($detailPendukung->jumlah, 2, ',', '.')); ?></td>
                                <td class="text-center"><?php echo e($detailPendukung->satuan); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($hargaTerbaru, 0, ',', '.')); ?></td>
                                <td class="text-end">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <td colspan="5" class="text-end fw-bold">Total Biaya Bahan Pendukung</td>
                            <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBahanPendukung, 0, ',', '.')); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Section 3: Proses Produksi (BTKL + BOP) -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-cogs"></i> 3. Proses Produksi (BTKL + BOP)</h5>
        </div>
        <div class="card-body">
            <?php
                // Cek apakah ada proses produksi
                $hasProses = $bom->proses && $bom->proses->count() > 0;
                $totalBTKL = 0;
                $totalBOP = 0;
                
                // Jika tidak ada proses, ambil dari BomJobCosting
                if (!$hasProses && $bomJobCosting) {
                    $totalBTKL = $bomJobCosting->total_btkl ?? 0;
                    $totalBOP = $bomJobCosting->total_bop ?? 0;
                }
            ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasProses): ?>
                <!-- Tabel BTKL -->
                <h6 class="mb-3"><i class="fas fa-user-clock me-2"></i>Biaya Tenaga Kerja Langsung (BTKL)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">No</th>
                                <th width="35%">Proses</th>
                                <th width="15%">Durasi</th>
                                <th width="15%">Satuan</th>
                                <th width="25%">Biaya BTKL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bom->proses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php 
                                    $totalBTKL += $proses->biaya_btkl;
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo e($proses->urutan); ?></td>
                                    <td>
                                        <?php echo e($proses->prosesProduksi->nama_proses ?? '-'); ?>

                                        <div class="text-muted small">
                                            Tarif: Rp <?php echo e(number_format($proses->prosesProduksi->tarif_btkl ?? 0, 0, ',', '.')); ?>/<?php echo e($proses->satuan_durasi); ?>

                                        </div>
                                    </td>
                                    <td class="text-end"><?php echo e(number_format($proses->durasi, 2, ',', '.')); ?></td>
                                    <td class="text-center"><?php echo e($proses->satuan_durasi); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($proses->biaya_btkl, 0, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4" class="text-end fw-bold">Total BTKL</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBTKL, 0, ',', '.')); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Tabel BOP -->
                <h6 class="mb-3"><i class="fas fa-cogs me-2"></i>Biaya Overhead Pabrik (BOP)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">No</th>
                                <th width="35%">Komponen BOP</th>
                                <th width="25%">Proses</th>
                                <th width="30%">Biaya BOP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $noBop = 1; ?>
                            <?php $__currentLoopData = $bom->proses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php 
                                    $totalBOP += $proses->biaya_bop;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bomProsesBops && $proses->bomProsesBops->count() > 0): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $proses->bomProsesBops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            // Support sistem lama (bop_id) dan baru (komponen_bop_id)
                                            $namaBop = $bop->nama_bop; // Menggunakan accessor
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo e($noBop++); ?></td>
                                            <td><?php echo e($namaBop); ?></td>
                                            <td><?php echo e($proses->prosesProduksi->nama_proses ?? '-'); ?></td>
                                            <td class="text-end text-muted">
                                                <small>Manual input</small>
                                            </td>
                                            <td class="text-end">Rp <?php echo e(number_format($bop->total_biaya, 0, ',', '.')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($noBop == 1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada komponen BOP</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="3" class="text-end fw-bold">Total BOP</td>
                                <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBOP, 0, ',', '.')); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php elseif($bomJobCosting && ($bomJobCosting->total_btkl > 0 || $bomJobCosting->total_bop > 0)): ?>
                <!-- Tampilkan BTKL dan BOP dari BomJobCosting jika tidak ada proses -->
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    BOM ini belum memiliki detail proses produksi. Data BTKL dan BOP ditampilkan dari perhitungan Job Costing.
                </div>
                
                <!-- Tabel BTKL -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bomJobCosting->detailBTKL && $bomJobCosting->detailBTKL->count() > 0): ?>
                    <h6 class="mb-3"><i class="fas fa-user-clock me-2"></i>Biaya Tenaga Kerja Langsung (BTKL)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">No</th>
                                    <th width="50%">Keterangan</th>
                                    <th width="40%">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $noBtkl = 1; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bomJobCosting->detailBTKL; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $btkl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($noBtkl++); ?></td>
                                        <td>
                                            <?php echo e($btkl->nama_proses ?? ($btkl->keterangan ?? 'BTKL')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($btkl->durasi_jam && $btkl->tarif_per_jam): ?>
                                                <small class="text-muted d-block">
                                                    <?php echo e(number_format($btkl->durasi_jam, 2)); ?> jam × Rp <?php echo e(number_format($btkl->tarif_per_jam, 0, ',', '.')); ?>/jam
                                                </small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="text-end">Rp <?php echo e(number_format($btkl->subtotal ?? 0, 0, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <td colspan="2" class="text-end fw-bold">Total BTKL</td>
                                    <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBTKL, 0, ',', '.')); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- Tabel BOP -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bomJobCosting->detailBOP && $bomJobCosting->detailBOP->count() > 0): ?>
                    <h6 class="mb-3"><i class="fas fa-cogs me-2"></i>Biaya Overhead Pabrik (BOP)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">No</th>
                                    <th width="50%">Komponen BOP</th>
                                    <th width="40%">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $noBop = 1; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bomJobCosting->detailBOP; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($noBop++); ?></td>
                                        <td>
                                            <?php echo e($bop->nama_bop ?? ($bop->bop->nama_bop ?? ($bop->keterangan ?? 'BOP'))); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bop->jumlah && $bop->tarif): ?>
                                                <small class="text-muted d-block">
                                                    <?php echo e(number_format($bop->jumlah, 2)); ?> × Rp <?php echo e(number_format($bop->tarif, 0, ',', '.')); ?>

                                                </small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="text-end">Rp <?php echo e(number_format($bop->subtotal ?? 0, 0, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <td colspan="2" class="text-end fw-bold">Total BOP</td>
                                    <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBOP, 0, ',', '.')); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i> 
                    BOM ini menggunakan perhitungan persentase (BTKL 60%, BOP 40%) karena belum ada proses produksi yang didefinisikan.
                </div>
                <?php
                    $totalBTKL = $bom->total_btkl ?? 0;
                    $totalBOP = $bom->total_bop ?? 0;
                ?>
                
                <!-- Tabel BTKL -->
                <h6 class="mb-3"><i class="fas fa-user-clock me-2"></i>Biaya Tenaga Kerja Langsung (BTKL)</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td width="70%">BTKL (60% dari BBB)</td>
                                <td width="30%" class="text-end fw-bold">Rp <?php echo e(number_format($totalBTKL, 0, ',', '.')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Tabel BOP -->
                <h6 class="mb-3"><i class="fas fa-cogs me-2"></i>Biaya Overhead Pabrik (BOP)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td width="70%">BOP (40% dari BBB)</td>
                                <td width="30%" class="text-end fw-bold">Rp <?php echo e(number_format($totalBOP, 0, ',', '.')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Section 4: Ringkasan HPP -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-calculator"></i> 4. Ringkasan Harga Pokok Produksi (HPP)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <?php
                        $totalBiayaBahan = $totalBBB + $totalBahanPendukung;
                        $hpp = $totalBiayaBahan + $totalBTKL + $totalBOP;
                        $persenBiayaBahan = $hpp > 0 ? ($totalBiayaBahan / $hpp) * 100 : 0;
                        $persenBBB = $hpp > 0 ? ($totalBBB / $hpp) * 100 : 0;
                        $persenBahanPendukung = $hpp > 0 ? ($totalBahanPendukung / $hpp) * 100 : 0;
                        $persenBTKL = $hpp > 0 ? ($totalBTKL / $hpp) * 100 : 0;
                        $persenBOP = $hpp > 0 ? ($totalBOP / $hpp) * 100 : 0;
                    ?>
                    <table class="table table-bordered">
                        <tr class="table-light">
                            <th width="50%">Total Biaya Bahan Baku (BBB)</th>
                            <td class="text-end">Rp <?php echo e(number_format($totalBBB, 0, ',', '.')); ?></td>
                            <td class="text-end text-muted"><?php echo e(number_format($persenBBB, 1, ',', '.')); ?>%</td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBahanPendukung > 0): ?>
                        <tr class="table-light">
                            <th>Total Biaya Bahan Pendukung</th>
                            <td class="text-end">Rp <?php echo e(number_format($totalBahanPendukung, 0, ',', '.')); ?></td>
                            <td class="text-end text-muted"><?php echo e(number_format($persenBahanPendukung, 1, ',', '.')); ?>%</td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <tr class="table-warning">
                            <th>Total Biaya Bahan (BBB + Pendukung)</th>
                            <td class="text-end fw-bold">Rp <?php echo e(number_format($totalBiayaBahan, 0, ',', '.')); ?></td>
                            <td class="text-end text-muted fw-bold"><?php echo e(number_format($persenBiayaBahan, 1, ',', '.')); ?>%</td>
                        </tr>
                        <tr>
                            <th>Total Biaya Tenaga Kerja Langsung (BTKL)</th>
                            <td class="text-end">Rp <?php echo e(number_format($totalBTKL, 0, ',', '.')); ?></td>
                            <td class="text-end text-muted"><?php echo e(number_format($persenBTKL, 1, ',', '.')); ?>%</td>
                        </tr>
                        <tr>
                            <th>Total Biaya Overhead Pabrik (BOP)</th>
                            <td class="text-end">Rp <?php echo e(number_format($totalBOP, 0, ',', '.')); ?></td>
                            <td class="text-end text-muted"><?php echo e(number_format($persenBOP, 1, ',', '.')); ?>%</td>
                        </tr>
                        <tr class="table-success">
                            <th class="fs-5">HARGA POKOK PRODUKSI (HPP)</th>
                            <td class="text-end fw-bold fs-5">Rp <?php echo e(number_format($hpp, 0, ',', '.')); ?></td>
                            <td class="text-end fw-bold">100%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between">
        <div>
            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="<?php echo e(route('master-data.bom.edit', $bom->id)); ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Edit BOM
            </a>
            <a href="<?php echo e(route('master-data.bom.print', $bom->id)); ?>" class="btn btn-info me-2" target="_blank">
                <i class="bi bi-printer"></i> Cetak
            </a>
        </div>
        <form action="<?php echo e(route('master-data.bom.destroy', $bom->id)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus BOM ini?')">
                <i class="bi bi-trash"></i> Hapus BOM
            </button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/show.blade.php ENDPATH**/ ?>
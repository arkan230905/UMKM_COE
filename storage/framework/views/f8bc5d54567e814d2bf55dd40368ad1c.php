<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP (Biaya Overhead Pabrik)
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBopProsesModal">
            <i class="fas fa-plus me-2"></i>Tambah BOP Proses
        </button>
    </div>

    <!-- Main BOP Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-cogs me-2"></i>BOP per Proses Produksi
            </h5>
            <small class="text-muted">Budget dan aktual BOP berdasarkan proses produksi</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama BOP</th>
                            <th class="text-end">Budget BOP</th>
                            <th class="text-center">Kuantitas/Jam</th>
                            <th class="text-end">Total BOP/Jam</th>
                            <th class="text-end">BOP/pcs</th>
                            <th class="text-end">Biaya/produk</th>
                            <th class="text-end">Biaya/Jam</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $prosesProduksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $bop = $proses->bopProses;
                                $kapasitasPerJam = $proses->kapasitas_per_jam ?? 0;
                                $hasBop = $bop !== null;
                                
                                // Get BOP per jam from new structure (total_bop_per_jam field)
                                if ($hasBop) {
                                    $bopPerJam = $bop->total_bop_per_jam ?? 0;
                                    $budget = $bop->budget ?? 0;
                                    $aktual = $bop->aktual ?? 0;
                                    
                                    // Fallback: calculate from JSON if total_bop_per_jam is null
                                    if ($bopPerJam == 0 && !empty($bop->komponen_bop)) {
                                        $komponenBop = is_array($bop->komponen_bop) ? $bop->komponen_bop : json_decode($bop->komponen_bop, true);
                                        if (is_array($komponenBop)) {
                                            $bopPerJam = array_sum(array_column($komponenBop, 'rate_per_hour'));
                                        }
                                    }
                                } else {
                                    $bopPerJam = 0;
                                    $budget = 0;
                                    $aktual = 0;
                                }
                                
                                // Calculate values
                                $bopPerPcs = $kapasitasPerJam > 0 ? $bopPerJam / $kapasitasPerJam : 0;
                                $biayaPerProduk = $bopPerPcs; // Same as BOP per pcs
                                $biayaPerJam = $bopPerJam;
                                $selisih = $budget - $aktual;
                                
                                // Define status variables
                                if ($hasBop) {
                                    $statusClass = $selisih >= 0 ? 'success' : 'danger';
                                    $statusText = $selisih >= 0 ? 'Sudah Setup' : 'Over Budget';
                                } else {
                                    $statusClass = 'secondary';
                                    $statusText = 'Belum Setup';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo e($proses->nama_proses); ?></div>
                                    <small class="text-muted"><?php echo e($proses->kode_proses); ?></small>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold">Rp <?php echo e(number_format($budget, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo e($kapasitasPerJam); ?> unit/jam</span>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold text-primary">Rp <?php echo e(number_format($bopPerJam, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold text-success">Rp <?php echo e(number_format($bopPerPcs, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold text-warning">Rp <?php echo e(number_format($biayaPerProduk, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold text-info">Rp <?php echo e(number_format($biayaPerJam, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold">Rp <?php echo e(number_format($aktual, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <span class="fw-semibold text-<?php echo e($statusClass); ?>">
                                            Rp <?php echo e(number_format(abs($selisih), 0, ',', '.')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selisih < 0): ?> (Over) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBop): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" onclick="viewBopDetail(<?php echo e($bop->id); ?>)" title="Detail BOP">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" onclick="editBopProses(<?php echo e($bop->id); ?>)" title="Edit BOP">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="setupBopProses(<?php echo e($proses->id); ?>)" title="Setup BOP">
                                            <i class="fas fa-plus"></i> Setup
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada proses produksi</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- Modal Tambah BOP Proses -->
<div class="modal fade" id="addBopProsesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bopProsesForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Proses Produksi *</label>
                                <select class="form-select" name="proses_produksi_id" required>
                                    <option value="">Pilih Proses Produksi</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $prosesProduksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$proses->bopProses): ?>
                                            <option value="<?php echo e($proses->id); ?>"><?php echo e($proses->nama_proses); ?> (<?php echo e($proses->kapasitas_per_jam); ?> unit/jam)</option>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Budget BOP *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="budget" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total BOP per Jam *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="total_bop_per_jam" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Periode *</label>
                                <input type="month" class="form-control" name="periode" value="<?php echo e(date('Y-m')); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveBopProses()">
                    <i class="fas fa-save me-2"></i>Simpan BOP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit BOP Proses -->
<div class="modal fade" id="editBopProsesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBopProsesForm">
                    <input type="hidden" name="id" id="editBopProsesId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Proses Produksi</label>
                                <input type="text" class="form-control" id="editNamaProses" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Budget BOP *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="budget" id="editBudgetProses" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total BOP per Jam *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="total_bop_per_jam" id="editTotalBopPerJam" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Aktual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="aktual" id="editAktualProses" min="0" step="1000">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="editKeteranganProses" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateBopProses()">
                    <i class="fas fa-save me-2"></i>Update BOP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail BOP -->
<div class="modal fade" id="detailBopModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail BOP Proses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBopContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function saveBopProses() {
    const form = document.getElementById('bopProsesForm');
    const formData = new FormData(form);
    
    fetch('/master-data/bop/store-proses-simple', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function setupBopProses(prosesId) {
    // Pre-fill modal with process data
    document.querySelector('select[name="proses_produksi_id"]').value = prosesId;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addBopProsesModal'));
    modal.show();
}

function editBopProses(id) {
    // Load BOP Proses data dan tampilkan di modal edit
    fetch(`/master-data/bop/get-proses/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bop = data.bop;
                
                // Isi form edit dengan data existing
                document.getElementById('editBopProsesId').value = bop.id;
                document.getElementById('editNamaProses').value = bop.proses_produksi.nama_proses;
                document.getElementById('editBudgetProses').value = bop.budget;
                document.getElementById('editTotalBopPerJam').value = bop.total_bop_per_jam;
                document.getElementById('editAktualProses').value = bop.aktual || 0;
                document.getElementById('editKeteranganProses').value = bop.keterangan || '';
                
                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('editBopProsesModal'));
                modal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function updateBopProses() {
    const form = document.getElementById('editBopProsesForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    
    fetch(`/master-data/bop/update-proses-simple/${id}`, {
        method: 'PUT',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function viewBopDetail(id) {
    // Load BOP detail modal content
    fetch(`/master-data/bop/show-proses-modal/${id}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detailBopContent').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('detailBopModal'));
            modal.show();
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/index.blade.php ENDPATH**/ ?>
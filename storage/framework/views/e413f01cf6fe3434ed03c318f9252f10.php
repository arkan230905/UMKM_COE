<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-wallet"></i> Laporan Kas dan Bank</h3>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('laporan.kas-bank.export-pdf', request()->all())); ?>" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <a href="<?php echo e(route('laporan.kas-bank.export-excel', request()->all())); ?>" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Download Excel
            </a>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('laporan.kas-bank')); ?>" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo e($startDate); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo e($endDate); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('today')">Hari Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('week')">Minggu Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('month')">Bulan Ini</button>
                        <button type="button" class="btn btn-secondary" onclick="setQuickFilter('year')">Tahun Ini</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Total Keseluruhan -->
    <div class="card shadow-sm mb-3 bg-primary text-white">
        <div class="card-body">
            <h5 class="mb-0">Total Kas dan Bank</h5>
            <h2 class="mb-0">Rp <?php echo e(number_format($totalKeseluruhan, 0, ',', '.')); ?></h2>
        </div>
    </div>

    <!-- Tabel Saldo per Akun -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Transaksi Masuk</th>
                            <th class="text-end">Transaksi Keluar</th>
                            <th class="text-end">Saldo Akhir</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $dataKasBank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($data['kode_akun']); ?></td>
                            <td><?php echo e($data['nama_akun']); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($data['saldo_awal'], 0, ',', '.')); ?></td>
                            <td class="text-end text-success fw-bold">Rp <?php echo e(number_format($data['transaksi_masuk'], 0, ',', '.')); ?></td>
                            <td class="text-end text-danger fw-bold">Rp <?php echo e(number_format($data['transaksi_keluar'], 0, ',', '.')); ?></td>
                            <td class="text-end fw-bold">Rp <?php echo e(number_format($data['saldo_akhir'], 0, ',', '.')); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-success" onclick="showDetailMasuk(<?php echo e($data['id']); ?>, '<?php echo e($data['nama_akun']); ?>')">
                                    <i class="fas fa-arrow-down"></i> Masuk
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="showDetailKeluar(<?php echo e($data['id']); ?>, '<?php echo e($data['nama_akun']); ?>')">
                                    <i class="fas fa-arrow-up"></i> Keluar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data akun kas/bank</td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Masuk -->
<div class="modal fade" id="modalDetailMasuk" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-down"></i> Detail Transaksi Masuk - <span id="namaAkunMasuk"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-success">
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="tableDetailMasuk">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Keluar -->
<div class="modal fade" id="modalDetailKeluar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-up"></i> Detail Transaksi Keluar - <span id="namaAkunKeluar"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-danger">
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="tableDetailKeluar">
                            <tr>
                                <td colspan="5" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Filter
function setQuickFilter(period) {
    const today = new Date();
    let startDate, endDate;
    
    switch(period) {
        case 'today':
            startDate = endDate = today.toISOString().split('T')[0];
            break;
        case 'week':
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate = firstDay.toISOString().split('T')[0];
            endDate = new Date().toISOString().split('T')[0];
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'year':
            startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];
            break;
    }
    
    document.querySelector('input[name="start_date"]').value = startDate;
    document.querySelector('input[name="end_date"]').value = endDate;
    document.getElementById('filterForm').submit();
}

// Show Detail Transaksi Masuk
function showDetailMasuk(coaId, namaAkun) {
    document.getElementById('namaAkunMasuk').textContent = namaAkun;
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    const url = `<?php echo e(url('laporan/kas-bank')); ?>/${coaId}/detail-masuk?start_date=${startDate}&end_date=${endDate}`;
    console.log('Fetching URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            console.log('Data length:', data.length);
            
            let html = '';
            let total = 0;
            
            if (!data || data.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Tidak ada transaksi masuk</td></tr>';
            } else {
                data.forEach(item => {
                    total += parseFloat(item.nominal);
                    html += `
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.nomor_transaksi}</td>
                            <td>${item.jenis}</td>
                            <td>${item.keterangan}</td>
                            <td class="text-end">Rp ${parseInt(item.nominal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                html += `
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">Rp ${parseInt(total).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            }
            
            document.getElementById('tableDetailMasuk').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetailMasuk')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tableDetailMasuk').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: ' + error.message + '</td></tr>';
            new bootstrap.Modal(document.getElementById('modalDetailMasuk')).show();
        });
}

// Show Detail Transaksi Keluar
function showDetailKeluar(coaId, namaAkun) {
    document.getElementById('namaAkunKeluar').textContent = namaAkun;
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    const url = `<?php echo e(url('laporan/kas-bank')); ?>/${coaId}/detail-keluar?start_date=${startDate}&end_date=${endDate}`;
    console.log('Fetching URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            console.log('Data length:', data.length);
            
            let html = '';
            let total = 0;
            
            if (!data || data.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Tidak ada transaksi keluar</td></tr>';
            } else {
                data.forEach(item => {
                    total += parseFloat(item.nominal);
                    html += `
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.nomor_transaksi}</td>
                            <td>${item.jenis}</td>
                            <td>${item.keterangan}</td>
                            <td class="text-end">Rp ${parseInt(item.nominal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                html += `
                    <tr class="table-danger fw-bold">
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">Rp ${parseInt(total).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            }
            
            document.getElementById('tableDetailKeluar').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetailKeluar')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tableDetailKeluar').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: ' + error.message + '</td></tr>';
            new bootstrap.Modal(document.getElementById('modalDetailKeluar')).show();
        });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/laporan/kas-bank/index.blade.php ENDPATH**/ ?>
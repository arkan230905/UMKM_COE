<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah Penggajian</h3>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <form action="<?php echo e(route('transaksi.penggajian.store')); ?>" method="POST" id="formPenggajian">
                <?php echo csrf_field(); ?>

                <!-- Informasi Pegawai -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="pegawai_id" class="form-label fw-bold">
                            <i class="bi bi-person-badge"></i> Pilih Pegawai *
                        </label>
                        <select name="pegawai_id" id="pegawai_id" class="form-select form-select-lg" required onchange="loadPegawaiData()">
                            <option value="">-- Pilih Pegawai --</option>
                            <?php $__currentLoopData = $pegawais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pegawai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($pegawai->id); ?>" 
                                        data-jenis="<?php echo e($pegawai->jenis_pegawai ?? 'btktl'); ?>"
                                        data-gaji-pokok="<?php echo e($pegawai->gaji_pokok ?? 0); ?>"
                                        data-tarif="<?php echo e($pegawai->tarif_per_jam ?? 0); ?>"
                                        data-tunjangan="<?php echo e($pegawai->tunjangan ?? 0); ?>"
                                        data-asuransi="<?php echo e($pegawai->asuransi ?? 0); ?>">
                                    <?php echo e($pegawai->nama); ?> - <?php echo e($pegawai->jabatan ?? 'Staff'); ?> (<?php echo e(strtoupper($pegawai->jenis_pegawai ?? 'BTKTL')); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="tanggal_penggajian" class="form-label fw-bold">
                            <i class="bi bi-calendar-check"></i> Tanggal Penggajian *
                        </label>
                        <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" 
                               class="form-control form-control-lg" value="<?php echo e(date('Y-m-d')); ?>" required onchange="loadJamKerja()">
                    </div>

                    <div class="col-md-2">
                        <label for="coa_kasbank" class="form-label fw-bold">
                            <i class="bi bi-wallet2"></i> Bayar dari *
                        </label>
                        <select name="coa_kasbank" id="coa_kasbank" class="form-select form-select-lg" required>
                            <?php $__currentLoopData = $kasbank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kb->kode_akun); ?>" <?php echo e($kb->kode_akun == '1101' ? 'selected' : ''); ?>>
                                    <?php echo e($kb->nama_akun); ?> (<?php echo e($kb->kode_akun); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <!-- Komponen Gaji (Otomatis dari Data Pegawai) -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Komponen Gaji (Otomatis dari Data Pegawai)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- BTKTL Fields -->
                            <div class="col-md-6" id="field-gaji-pokok">
                                <label for="display_gaji_pokok" class="form-label">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_gaji_pokok" class="form-control" readonly value="0">
                                </div>
                                <small class="text-muted">Untuk pegawai BTKTL</small>
                            </div>

                            <!-- BTKL Fields -->
                            <div class="col-md-6" id="field-tarif" style="display:none;">
                                <label for="display_tarif" class="form-label">Tarif per Jam</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tarif" class="form-control" readonly value="0">
                                </div>
                                <small class="text-muted">Untuk pegawai BTKL</small>
                            </div>

                            <div class="col-md-6" id="field-jam-kerja" style="display:none;">
                                <label for="display_jam_kerja" class="form-label">Total Jam Kerja (Bulan Ini)</label>
                                <div class="input-group">
                                    <input type="text" id="display_jam_kerja" class="form-control" readonly value="0">
                                    <span class="input-group-text">Jam</span>
                                </div>
                                <small class="text-muted">Dari data presensi</small>
                            </div>

                            <div class="col-md-6">
                                <label for="display_tunjangan" class="form-label">Tunjangan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_tunjangan" class="form-control" readonly value="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="display_asuransi" class="form-label">Asuransi / BPJS</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="display_asuransi" class="form-control" readonly value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Manual (Bonus & Potongan) -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Input Manual</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bonus" class="form-label">Bonus</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" name="bonus" id="bonus" 
                                           class="form-control" value="0" onchange="hitungTotal()">
                                </div>
                                <small class="text-muted">Bonus kinerja, lembur, dll</small>
                            </div>

                            <div class="col-md-6">
                                <label for="potongan" class="form-label">Potongan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" name="potongan" id="potongan" 
                                           class="form-control" value="0" onchange="hitungTotal()">
                                </div>
                                <small class="text-muted">Keterlambatan, pinjaman, dll</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Gaji -->
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Total Gaji Bersih</h5>
                                <small id="formula-display" class="text-white-50"></small>
                            </div>
                            <div class="col-md-6 text-end">
                                <h3 class="mb-0" id="display_total">Rp 0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="<?php echo e(route('transaksi.penggajian.index')); ?>" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-save"></i> Simpan Penggajian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data pegawai
let pegawaiData = {
    jenis: 'btktl',
    gajiPokok: 0,
    tarif: 0,
    tunjangan: 0,
    asuransi: 0,
    jamKerja: 0
};

// Load data pegawai
function loadPegawaiData() {
    const select = document.getElementById('pegawai_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        pegawaiData.jenis = option.dataset.jenis || 'btktl';
        pegawaiData.gajiPokok = parseFloat(option.dataset.gajiPokok) || 0;
        pegawaiData.tarif = parseFloat(option.dataset.tarif) || 0;
        pegawaiData.tunjangan = parseFloat(option.dataset.tunjangan) || 0;
        pegawaiData.asuransi = parseFloat(option.dataset.asuransi) || 0;
        
        // Update display
        document.getElementById('display_gaji_pokok').value = pegawaiData.gajiPokok.toLocaleString('id-ID');
        document.getElementById('display_tarif').value = pegawaiData.tarif.toLocaleString('id-ID');
        document.getElementById('display_tunjangan').value = pegawaiData.tunjangan.toLocaleString('id-ID');
        document.getElementById('display_asuransi').value = pegawaiData.asuransi.toLocaleString('id-ID');
        
        // Show/hide fields based on jenis pegawai
        if (pegawaiData.jenis === 'btkl') {
            document.getElementById('field-gaji-pokok').style.display = 'none';
            document.getElementById('field-tarif').style.display = 'block';
            document.getElementById('field-jam-kerja').style.display = 'block';
        } else {
            document.getElementById('field-gaji-pokok').style.display = 'block';
            document.getElementById('field-tarif').style.display = 'none';
            document.getElementById('field-jam-kerja').style.display = 'none';
        }
        
        // Load jam kerja
        loadJamKerja();
    }
}

// Load jam kerja dari presensi
function loadJamKerja() {
    const pegawaiId = document.getElementById('pegawai_id').value;
    const tanggal = document.getElementById('tanggal_penggajian').value;
    
    if (pegawaiId && tanggal && pegawaiData.jenis === 'btkl') {
        // Parse tanggal untuk mendapatkan bulan dan tahun
        const date = new Date(tanggal);
        const month = date.getMonth() + 1;
        const year = date.getFullYear();
        
        // Fetch jam kerja dari server
        fetch(`/api/presensi/jam-kerja?pegawai_id=${pegawaiId}&month=${month}&year=${year}`)
            .then(response => response.json())
            .then(data => {
                pegawaiData.jamKerja = parseFloat(data.total_jam) || 0;
                document.getElementById('display_jam_kerja').value = pegawaiData.jamKerja.toLocaleString('id-ID');
                hitungTotal();
            })
            .catch(error => {
                console.error('Error loading jam kerja:', error);
                pegawaiData.jamKerja = 0;
                document.getElementById('display_jam_kerja').value = '0';
                hitungTotal();
            });
    } else {
        hitungTotal();
    }
}

// Hitung total gaji
function hitungTotal() {
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const potongan = parseFloat(document.getElementById('potongan').value) || 0;
    
    let total = 0;
    let formula = '';
    
    if (pegawaiData.jenis === 'btkl') {
        // BTKL = (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
        const gajiDasar = pegawaiData.tarif * pegawaiData.jamKerja;
        total = gajiDasar + pegawaiData.asuransi + pegawaiData.tunjangan + bonus - potongan;
        formula = `(${pegawaiData.tarif.toLocaleString('id-ID')} × ${pegawaiData.jamKerja}) + ${pegawaiData.asuransi.toLocaleString('id-ID')} + ${pegawaiData.tunjangan.toLocaleString('id-ID')} + ${bonus.toLocaleString('id-ID')} - ${potongan.toLocaleString('id-ID')}`;
    } else {
        // BTKTL = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
        total = pegawaiData.gajiPokok + pegawaiData.asuransi + pegawaiData.tunjangan + bonus - potongan;
        formula = `${pegawaiData.gajiPokok.toLocaleString('id-ID')} + ${pegawaiData.asuransi.toLocaleString('id-ID')} + ${pegawaiData.tunjangan.toLocaleString('id-ID')} + ${bonus.toLocaleString('id-ID')} - ${potongan.toLocaleString('id-ID')}`;
    }
    
    document.getElementById('display_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('formula-display').textContent = formula;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    hitungTotal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/penggajian/create.blade.php ENDPATH**/ ?>
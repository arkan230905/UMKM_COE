<?php $__env->startSection('title', 'Tambah Data Pegawai'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3 class="mb-4">➕ Tambah Data Pegawai</h3>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form action="<?php echo e(route('master-data.pegawai.store')); ?>" method="POST" id="pegawai-form">
        <?php echo csrf_field(); ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?php echo e(old('nama')); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="no_telepon" class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" id="no_telepon" class="form-control" value="<?php echo e(old('no_telepon')); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2" required><?php echo e(old('alamat')); ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Jenis Kelamin</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki_laki" value="L" <?php echo e(old('jenis_kelamin') == 'L' ? 'checked' : ''); ?> required>
                    <label class="form-check-label" for="laki_laki">Laki-laki</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="P" <?php echo e(old('jenis_kelamin') == 'P' ? 'checked' : ''); ?> required>
                    <label class="form-check-label" for="perempuan">Perempuan</label>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kategori" class="form-label">Kategori Pegawai</label>
                <select name="kategori" id="kategori" class="form-select" required onchange="loadJabatanByKategori()">
                    <option value="">-- Pilih Kategori --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>" <?php echo e(old('kategori') == $k ? 'selected' : ''); ?>>
                            <?php echo e(strtoupper($k)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select name="jabatan_id" id="jabatan_id" class="form-select" required onchange="loadJabatanDetail()">
                    <option value="">-- Pilih Jabatan --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jabatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($j->id); ?>"
                                data-nama="<?php echo e($j->nama); ?>"
                                data-kategori="<?php echo e($j->kategori); ?>"
                                data-tunjangan="<?php echo e($j->tunjangan); ?>"
                                data-asuransi="<?php echo e($j->asuransi); ?>"
                                data-gaji="<?php echo e($j->gaji); ?>"
                                data-tarif="<?php echo e($j->tarif); ?>"
                                <?php echo e(old('jabatan_id')==$j->id?'selected':''); ?>>
                            <?php echo e($j->nama); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <!-- Preview otomatis dari Jabatan -->
            <div class="col-12">
                <div class="alert alert-secondary small" id="preview-box" style="display:none">
                    <h6>Detail Kualifikasi Jabatan:</h6>
                    <div class="row">
                        <div class="col-md-4"><strong>Kategori:</strong> <span id="pv-kategori">-</span></div>
                        <div class="col-md-4"><strong>Tunj. Jabatan:</strong> Rp <span id="pv-tunjangan">0</span></div>
                        <div class="col-md-4"><strong>Tunj. Transport:</strong> Rp <span id="pv-tunjangan-transport">0</span></div>
                        <div class="col-md-4"><strong>Tunj. Konsumsi:</strong> Rp <span id="pv-tunjangan-konsumsi">0</span></div>
                        <div class="col-md-4"><strong>Asuransi:</strong> Rp <span id="pv-asuransi">0</span></div>
                        <div class="col-md-4"><strong>Gaji Pokok (BTKTL):</strong> Rp <span id="pv-gaji-pokok">0</span></div>
                        <div class="col-md-4"><strong>Tarif / Jam (BTKL):</strong> Rp <span id="pv-tarif-per-jam">0</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Rekening Bank -->
        <div class="col-12 mt-4">
            <h5>Informasi Rekening Bank</h5>
            <hr>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" name="bank" id="bank" class="form-control" value="<?php echo e(old('bank')); ?>" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" name="nomor_rekening" id="nomor_rekening" class="form-control" value="<?php echo e(old('nomor_rekening')); ?>" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="nama_rekening" class="form-label">Nama Rekening</label>
                <input type="text" name="nama_rekening" id="nama_rekening" class="form-control" value="<?php echo e(old('nama_rekening')); ?>" required>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Data Pegawai
                </button>
                <a href="<?php echo e(route('master-data.pegawai.index')); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </form>
</div>

<script>
// Global variables
let jabatanData = {};

// Format number untuk Indonesia
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(Number(num || 0));
}

// Load jabatan berdasarkan kategori
function loadJabatanByKategori() {
    const kategori = document.getElementById('kategori').value;
    const jabatanSelect = document.getElementById('jabatan_id');
    
    // Reset jabatan dropdown
    jabatanSelect.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
    document.getElementById('preview-box').style.display = 'none';
    
    if (kategori) {
        fetch(`/master-data/api/jabatan/by-kategori?kategori=${encodeURIComponent(kategori)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(jabatan => {
                        const option = document.createElement('option');
                        option.value = jabatan.id;
                        option.setAttribute('data-nama', jabatan.nama);
                        option.setAttribute('data-kategori', jabatan.kategori);
                        option.setAttribute('data-tunjangan', jabatan.tunjangan);
                        option.setAttribute('data-tunjangan-transport', jabatan.tunjangan_transport);
                        option.setAttribute('data-tunjangan-konsumsi', jabatan.tunjangan_konsumsi);
                        option.setAttribute('data-asuransi', jabatan.asuransi);
                        option.setAttribute('data-gaji', jabatan.gaji_pokok);
                        option.setAttribute('data-tarif', jabatan.tarif);
                        option.textContent = jabatan.nama;
                        jabatanSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading jabatan:', error);
            });
    }
}

// Load detail jabatan
function loadJabatanDetail() {
    const jabatanId = document.getElementById('jabatan_id').value;
    const selectedOption = document.getElementById('jabatan_id').options[document.getElementById('jabatan_id').selectedIndex];
    
    if (jabatanId && selectedOption) {
        jabatanData = {
            nama: selectedOption.getAttribute('data-nama'),
            kategori: selectedOption.getAttribute('data-kategori'),
            tunjangan: parseFloat(selectedOption.getAttribute('data-tunjangan')) || 0,
            tunjangan_transport: parseFloat(selectedOption.getAttribute('data-tunjangan-transport')) || 0,
            tunjangan_konsumsi: parseFloat(selectedOption.getAttribute('data-tunjangan-konsumsi')) || 0,
            asuransi: parseFloat(selectedOption.getAttribute('data-asuransi')) || 0,
            gaji_pokok: parseFloat(selectedOption.getAttribute('data-gaji')) || 0,
            tarif: parseFloat(selectedOption.getAttribute('data-tarif')) || 0
        };
        
        updatePreview();
    } else {
        document.getElementById('preview-box').style.display = 'none';
    }
}

// Update preview box
function updatePreview() {
    if (jabatanData.nama) {
        document.getElementById('pv-kategori').textContent = jabatanData.kategori ? jabatanData.kategori.toUpperCase() : '-';
        document.getElementById('pv-tunjangan').textContent = formatNumber(jabatanData.tunjangan);
        document.getElementById('pv-tunjangan-transport').textContent = formatNumber(jabatanData.tunjangan_transport);
        document.getElementById('pv-tunjangan-konsumsi').textContent = formatNumber(jabatanData.tunjangan_konsumsi);
        document.getElementById('pv-asuransi').textContent = formatNumber(jabatanData.asuransi);
        document.getElementById('pv-gaji-pokok').textContent = formatNumber(jabatanData.gaji_pokok);
        document.getElementById('pv-tarif-per-jam').textContent = formatNumber(jabatanData.tarif);
        document.getElementById('preview-box').style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load initial jabatan if kategori is pre-selected
    const kategori = document.getElementById('kategori').value;
    if (kategori) {
        loadJabatanByKategori();
    }
    
    // Load initial jabatan detail if jabatan is pre-selected
    const jabatanId = document.getElementById('jabatan_id').value;
    if (jabatanId) {
        loadJabatanDetail();
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/pegawai/create.blade.php ENDPATH**/ ?>
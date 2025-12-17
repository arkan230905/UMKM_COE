@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> Edit Data Penggajian</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <form action="{{ route('transaksi.penggajian.update', $penggajian->id) }}" method="POST" id="formEditPenggajian">
                @csrf
                @method('PUT')

                <!-- Informasi Pegawai -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pegawai</label>
                        <input type="text" value="{{ $penggajian->pegawai->nama }}" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Penggajian</label>
                        <input type="date" value="{{ $penggajian->tanggal_penggajian->format('Y-m-d') }}" class="form-control" disabled>
                    </div>
                </div>

                <!-- Komponen Gaji Otomatis -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Komponen Gaji (Otomatis)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Gaji Pokok / Tarif</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($penggajian->gaji_pokok ?: $penggajian->tarif_per_jam, 0, ',', '.') }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tunjangan Jabatan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($penggajian->tunjangan, 0, ',', '.') }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Asuransi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($penggajian->asuransi, 0, ',', '.') }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bonus Tambahan -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Bonus Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <div id="bonus-tambahan-container">
                            @forelse($penggajian->bonusTambahans as $bonus)
                            <div class="bonus-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Bonus</label>
                                    <input type="text" name="bonus_tambahan_names[]" class="form-control" value="{{ $bonus->nama }}" placeholder="Contoh: Bonus Kinerja">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="bonus_tambahan_values[]" class="form-control" value="{{ $bonus->nominal }}" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-bonus-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="bonus-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Bonus</label>
                                    <input type="text" name="bonus_tambahan_names[]" class="form-control" placeholder="Contoh: Bonus Kinerja">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="bonus_tambahan_values[]" class="form-control" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-bonus-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-success" id="btn-add-bonus-tambahan">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Bonus
                        </button>
                    </div>
                </div>

                <!-- Tunjangan Tambahan -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tunjangan Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <div id="tunjangan-tambahan-container">
                            @forelse($penggajian->tunjanganTambahans as $tunjangan)
                            <div class="tunjangan-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Tunjangan</label>
                                    <input type="text" name="tunjangan_tambahan_names[]" class="form-control" value="{{ $tunjangan->nama }}" placeholder="Contoh: Tunjangan Makan">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="tunjangan_tambahan_values[]" class="form-control" value="{{ $tunjangan->nominal }}" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-tunjangan-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="tunjangan-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Tunjangan</label>
                                    <input type="text" name="tunjangan_tambahan_names[]" class="form-control" placeholder="Contoh: Tunjangan Makan">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="tunjangan_tambahan_values[]" class="form-control" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-tunjangan-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-success" id="btn-add-tunjangan-tambahan">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Tunjangan
                        </button>
                    </div>
                </div>

                <!-- Potongan Tambahan -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-dash-circle"></i> Potongan Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <div id="potongan-tambahan-container">
                            @forelse($penggajian->potonganTambahans as $potongan)
                            <div class="potongan-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Potongan</label>
                                    <input type="text" name="potongan_tambahan_names[]" class="form-control" value="{{ $potongan->nama }}" placeholder="Contoh: Keterlambatan">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="potongan_tambahan_values[]" class="form-control" value="{{ $potongan->nominal }}" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-potongan-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="potongan-tambahan-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Nama Potongan</label>
                                    <input type="text" name="potongan_tambahan_names[]" class="form-control" placeholder="Contoh: Keterlambatan">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nominal (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="potongan_tambahan_values[]" class="form-control" placeholder="0" onchange="hitungTotal()">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-potongan-tambahan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-outline-success" id="btn-add-potongan-tambahan">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Potongan
                        </button>
                    </div>
                </div>

                <!-- Total Gaji -->
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Total Gaji Bersih</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <h3 class="mb-0" id="display_total">Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="card bg-secondary mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Catatan</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ $penggajian->catatan }}</textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Update Penggajian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Hitung total tunjangan tambahan
function hitungTotalTunjanganTambahan() {
    let total = 0;
    const inputs = document.querySelectorAll('input[name="tunjangan_tambahan_values[]"]');
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

// Hitung total bonus tambahan
function hitungTotalBonusTambahan() {
    let total = 0;
    const inputs = document.querySelectorAll('input[name="bonus_tambahan_values[]"]');
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

// Hitung total potongan tambahan
function hitungTotalPotonganTambahan() {
    let total = 0;
    const inputs = document.querySelectorAll('input[name="potongan_tambahan_values[]"]');
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

// Hitung total gaji
function hitungTotal() {
    const bonusTambahan = hitungTotalBonusTambahan();
    const tunjanganTambahan = hitungTotalTunjanganTambahan();
    const potonganTambahan = hitungTotalPotonganTambahan();
    
    // Ambil nilai dari display (sudah diformat)
    const gajiPokok = {{ $penggajian->gaji_pokok ?: $penggajian->tarif_per_jam }};
    const tunjangan = {{ $penggajian->tunjangan }};
    const asuransi = {{ $penggajian->asuransi }};
    
    const total = gajiPokok + asuransi + tunjangan + tunjanganTambahan + bonusTambahan - potonganTambahan;
    
    document.getElementById('display_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Generic repeater factory untuk menghindari duplikasi kode
function createRepeater(containerId, buttonId, rowClass, nameFieldName, valueFieldName, removeButtonClass) {
    const container = document.getElementById(containerId);
    const button = document.getElementById(buttonId);
    
    if (!button) return;
    
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const newRow = document.createElement('div');
        newRow.className = `${rowClass} row g-3 mb-3`;
        newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="${nameFieldName}[]" class="form-control" placeholder="Masukkan nama">
            </div>
            <div class="col-md-5">
                <input type="number" step="0.01" min="0" name="${valueFieldName}[]" class="form-control" placeholder="0" onchange="hitungTotal()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 ${removeButtonClass}">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
        `;
        container.appendChild(newRow);
        attachRemoveListener(newRow.querySelector(`.${removeButtonClass}`), rowClass);
    });
}

function attachRemoveListener(btn, rowClass) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest(`.${rowClass}`).remove();
        hitungTotal();
    });
}

// Initialize repeaters
createRepeater('bonus-tambahan-container', 'btn-add-bonus-tambahan', 'bonus-tambahan-row', 'bonus_tambahan_names', 'bonus_tambahan_values', 'btn-remove-bonus-tambahan');
createRepeater('tunjangan-tambahan-container', 'btn-add-tunjangan-tambahan', 'tunjangan-tambahan-row', 'tunjangan_tambahan_names', 'tunjangan_tambahan_values', 'btn-remove-tunjangan-tambahan');
createRepeater('potongan-tambahan-container', 'btn-add-potongan-tambahan', 'potongan-tambahan-row', 'potongan_tambahan_names', 'potongan_tambahan_values', 'btn-remove-potongan-tambahan');

// Attach listeners to existing remove buttons
document.querySelectorAll('.btn-remove-bonus-tambahan').forEach(btn => {
    attachRemoveListener(btn, 'bonus-tambahan-row');
});

document.querySelectorAll('.btn-remove-tunjangan-tambahan').forEach(btn => {
    attachRemoveListener(btn, 'tunjangan-tambahan-row');
});

document.querySelectorAll('.btn-remove-potongan-tambahan').forEach(btn => {
    attachRemoveListener(btn, 'potongan-tambahan-row');
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    hitungTotal();
});
</script>
@endsection

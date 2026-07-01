@extends('layouts.app')

@section('title', 'Buat Target Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Buat Target Produksi</h2>
            <p class="text-muted mb-0">Tentukan target produksi tahunan dan distribusi bulanan</p>
        </div>
        <a href="{{ route('master-data.target-produksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('master-data.target-produksi.store') }}" method="POST" id="formTargetProduksi">
        @csrf
        
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Target</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tahun Target <span class="text-danger">*</span></label>
                            <select name="tahun" id="tahun" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ old('tahun', now()->year) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Produk <span class="text-danger">*</span></label>
                            <select name="produk_id" id="produk_id" class="form-select" required>
                                <option value="">Pilih Produk</option>
                                @foreach($produks as $produk)
                                    <option value="{{ $produk->id }}" {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                        {{ $produk->nama_produk }} ({{ $produk->kode_produk }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Target Tahunan <span class="text-danger">*</span></label>
                            <input type="text" name="total_target_tahunan_display" id="total_target_tahunan_display" 
                                   class="form-control" placeholder="0" required>
                            <input type="hidden" name="total_target_tahunan" id="total_target_tahunan" value="{{ old('total_target_tahunan') }}">
                            <small class="text-muted">Total target produksi untuk 1 tahun (dalam unit)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Distribusi Target Bulanan</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-light" onclick="generateRata()">
                        <i class="fas fa-equals me-1"></i>Bagi Rata
                    </button>
                    <button type="button" class="btn btn-sm btn-light" onclick="clearAll()">
                        <i class="fas fa-eraser me-1"></i>Kosongkan
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Bulan</th>
                                <th width="20%">Target Bulanan (Unit)</th>
                                <th width="15%">Hari Kerja</th>
                                <th width="20%">Target Per Hari</th>
                                <th width="25%" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $bulanNames = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @for($i = 1; $i <= 12; $i++)
                                <tr>
                                    <td class="text-center">{{ $i }}</td>
                                    <td><strong>{{ $bulanNames[$i] }}</strong></td>
                                    <td>
                                        <input type="hidden" name="details[{{ $i - 1 }}][bulan]" value="{{ $i }}">
                                        <input type="text" 
                                               name="details_display[{{ $i - 1 }}][target_bulanan]" 
                                               id="target_display_{{ $i }}"
                                               class="form-control target-display" 
                                               placeholder="0"
                                               onkeyup="formatNumber(this, {{ $i }})"
                                               required>
                                        <input type="hidden" 
                                               name="details[{{ $i - 1 }}][target_bulanan]" 
                                               id="target_{{ $i }}"
                                               class="target-input" 
                                               value="{{ old('details.' . ($i - 1) . '.target_bulanan', 0) }}">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="details[{{ $i - 1 }}][hari_kerja]" 
                                               id="hari_kerja_{{ $i }}"
                                               class="form-control hari-kerja-input" 
                                               placeholder="Hari"
                                               min="1"
                                               max="31"
                                               onchange="hitungTargetPerHari({{ $i }})"
                                               required>
                                        <small class="text-muted">1-31 hari</small>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" 
                                                   id="target_per_hari_{{ $i }}"
                                                   class="form-control bg-light" 
                                                   readonly
                                                   placeholder="0">
                                            <span class="input-group-text">unit/hari</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Editable</span>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="2" class="text-end"><strong>Total Target Bulanan:</strong></td>
                                <td><strong id="totalBulanan">0</strong> Unit</td>
                                <td class="text-center">
                                    <span id="statusValidasi" class="badge bg-secondary">-</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Catatan:</strong> Total target bulanan harus sama dengan total target tahunan agar dapat disimpan.
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.target-produksi.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" id="btnSubmit" class="btn btn-primary" disabled>
                        <i class="fas fa-save me-2"></i>Simpan Target Produksi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Format number with thousand separator (dot)
function formatNumberWithDot(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Remove dot separator and convert to number
function parseNumberFromFormat(str) {
    return parseInt(str.replace(/\./g, '')) || 0;
}

// Format input field
function formatNumber(input, monthIndex) {
    let value = input.value.replace(/\./g, ''); // Remove existing dots
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
        value = value.replace(/\D/g, '');
    }
    
    // Update display input with formatted number
    input.value = value ? formatNumberWithDot(value) : '';
    
    // Update hidden input with raw number
    document.getElementById('target_' + monthIndex).value = value || 0;
    
    hitungTotal();
    hitungTargetPerHari(monthIndex);
}

// Hitung target per hari
function hitungTargetPerHari(monthIndex) {
    const targetBulanan = parseInt(document.getElementById('target_' + monthIndex).value) || 0;
    const hariKerja = parseInt(document.getElementById('hari_kerja_' + monthIndex).value) || 0;
    
    if (hariKerja > 0 && targetBulanan > 0) {
        const targetPerHari = Math.round(targetBulanan / hariKerja); // Pembulatan otomatis
        document.getElementById('target_per_hari_' + monthIndex).value = targetPerHari;
    } else {
        document.getElementById('target_per_hari_' + monthIndex).value = '0';
    }
}

// Format total target tahunan
document.getElementById('total_target_tahunan_display').addEventListener('input', function() {
    let value = this.value.replace(/\./g, ''); // Remove existing dots
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
        value = value.replace(/\D/g, '');
    }
    
    // Update display with formatted number
    this.value = value ? formatNumberWithDot(value) : '';
    
    // Update hidden input with raw number
    document.getElementById('total_target_tahunan').value = value || 0;
    
    hitungTotal();
});

function hitungTotal() {
    let total = 0;
    const inputs = document.querySelectorAll('.target-input');
    
    inputs.forEach(input => {
        total += parseInt(input.value) || 0;
    });
    
    document.getElementById('totalBulanan').textContent = formatNumberWithDot(total);
    
    const targetTahunan = parseInt(document.getElementById('total_target_tahunan').value) || 0;
    const statusValidasi = document.getElementById('statusValidasi');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (total === targetTahunan && total > 0) {
        statusValidasi.textContent = '✓ Sesuai';
        statusValidasi.className = 'badge bg-success';
        btnSubmit.disabled = false;
    } else if (total === 0) {
        statusValidasi.textContent = '-';
        statusValidasi.className = 'badge bg-secondary';
        btnSubmit.disabled = true;
    } else {
        statusValidasi.textContent = '✗ Tidak Sesuai (Total: ' + formatNumberWithDot(total) + ')';
        statusValidasi.className = 'badge bg-danger';
        btnSubmit.disabled = true;
    }
}

function generateRata() {
    const targetTahunan = parseInt(document.getElementById('total_target_tahunan').value) || 0;
    
    if (targetTahunan === 0) {
        alert('Mohon isi Total Target Tahunan terlebih dahulu!');
        return;
    }
    
    const perBulan = Math.floor(targetTahunan / 12);
    const sisa = targetTahunan % 12;
    
    // Set default hari kerja = 22 hari (rata-rata hari kerja per bulan)
    const defaultHariKerja = 22;
    
    for (let i = 1; i <= 12; i++) {
        const value = perBulan + (i <= sisa ? 1 : 0);
        
        // Update hidden input
        document.getElementById('target_' + i).value = value;
        
        // Update display input with formatted number
        document.getElementById('target_display_' + i).value = formatNumberWithDot(value);
        
        // Set default hari kerja
        document.getElementById('hari_kerja_' + i).value = defaultHariKerja;
        
        // Hitung target per hari
        hitungTargetPerHari(i);
    }
    
    hitungTotal();
}

function clearAll() {
    if (!confirm('Yakin ingin mengosongkan semua target bulanan?')) return;
    
    for (let i = 1; i <= 12; i++) {
        document.getElementById('target_' + i).value = 0;
        document.getElementById('target_display_' + i).value = '';
    }
    
    hitungTotal();
}

// Initial calculation and formatting
document.addEventListener('DOMContentLoaded', function() {
    // Format total target tahunan if has old value
    const oldTargetTahunan = document.getElementById('total_target_tahunan').value;
    if (oldTargetTahunan && oldTargetTahunan > 0) {
        document.getElementById('total_target_tahunan_display').value = formatNumberWithDot(oldTargetTahunan);
    }
    
    // Format all monthly targets if has old values
    for (let i = 1; i <= 12; i++) {
        const oldValue = document.getElementById('target_' + i).value;
        if (oldValue && oldValue > 0) {
            document.getElementById('target_display_' + i).value = formatNumberWithDot(oldValue);
        }
    }
    
    hitungTotal();
});
</script>
@endpush
@endsection

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
                            <input type="number" name="total_target_tahunan" id="total_target_tahunan" 
                                   class="form-control" min="1" value="{{ old('total_target_tahunan') }}" required>
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
                                <th width="25%">Bulan</th>
                                <th width="35%">Target Bulanan (Unit)</th>
                                <th width="35%" class="text-center">Status</th>
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
                                        <input type="number" 
                                               name="details[{{ $i - 1 }}][target_bulanan]" 
                                               id="target_{{ $i }}"
                                               class="form-control target-input" 
                                               min="0" 
                                               value="{{ old('details.' . ($i - 1) . '.target_bulanan', 0) }}"
                                               onchange="hitungTotal()"
                                               required>
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
function hitungTotal() {
    let total = 0;
    const inputs = document.querySelectorAll('.target-input');
    
    inputs.forEach(input => {
        total += parseInt(input.value) || 0;
    });
    
    document.getElementById('totalBulanan').textContent = total.toLocaleString('id-ID');
    
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
        statusValidasi.textContent = '✗ Tidak Sesuai';
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
    
    for (let i = 1; i <= 12; i++) {
        const input = document.getElementById('target_' + i);
        input.value = perBulan + (i <= sisa ? 1 : 0);
    }
    
    hitungTotal();
}

function clearAll() {
    if (!confirm('Yakin ingin mengosongkan semua target bulanan?')) return;
    
    for (let i = 1; i <= 12; i++) {
        document.getElementById('target_' + i).value = 0;
    }
    
    hitungTotal();
}

// Event listeners
document.getElementById('total_target_tahunan').addEventListener('input', hitungTotal);

// Initial calculation
document.addEventListener('DOMContentLoaded', function() {
    hitungTotal();
});
</script>
@endpush
@endsection

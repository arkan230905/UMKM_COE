@extends('layouts.app')

@section('title', 'Hitung Harga Pokok Produksi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
        </h3>
        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="{{ route('master-data.harga-pokok-produksi.store') }}" method="POST" id="hppForm">
        @csrf
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Step 1: Pilih Produk -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>1. Pilih Produk</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Produk yang Sudah Memiliki Biaya Bahan *</label>
                        <select name="produk_id" id="produk_id" class="form-select" required onchange="loadProdukData()">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $produk)
                                <option value="{{ $produk->id }}" 
                                        data-biaya-bahan="{{ $produk->bomJobCosting->total_bbb + $produk->bomJobCosting->total_bahan_pendukung }}">
                                    {{ $produk->nama_produk }} 
                                    (Biaya Bahan: Rp {{ number_format($produk->bomJobCosting->total_bbb + $produk->bomJobCosting->total_bahan_pendukung, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya produk yang sudah memiliki biaya bahan yang dapat dipilih</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Biaya Bahan</label>
                        <div class="form-control-plaintext bg-light p-3 rounded border">
                            <h4 class="mb-0 text-primary" id="displayBiayaBahan">Rp 0</h4>
                            <input type="hidden" name="biaya_bahan" id="biayaBahanInput" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Pilih Proses BTKL -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>2. Pilih Proses BTKL yang Digunakan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Pilih proses BTKL yang digunakan untuk produk ini. BOP akan otomatis terinput karena terikat dengan setiap proses BTKL.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">Pilih</th>
                                <th width="15%">Kode</th>
                                <th width="20%">Nama Proses</th>
                                <th width="15%">Jabatan</th>
                                <th width="15%">Tarif BTKL/Jam</th>
                                <th width="10%">Kapasitas</th>
                                <th width="10%">BTKL/pcs</th>
                                <th width="10%">BOP/pcs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prosesBtkl as $proses)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input proses-checkbox" 
                                           name="proses_ids[]" 
                                           value="{{ $proses['id'] }}"
                                           data-btkl-per-produk="{{ $proses['btkl_per_produk'] }}"
                                           data-bop-per-produk="{{ $proses['bop_per_produk'] }}"
                                           data-nama="{{ $proses['nama_proses'] }}"
                                           data-komponen-bop="{{ json_encode($proses['komponen_bop']) }}"
                                           onchange="calculateTotal()">
                                </td>
                                <td>{{ $proses['kode_proses'] }}</td>
                                <td>{{ $proses['nama_proses'] }}</td>
                                <td>
                                    {{ $proses['nama_jabatan'] }}<br>
                                    <small class="text-muted">{{ $proses['jumlah_pegawai'] }} pegawai @ Rp {{ number_format($proses['tarif_per_jam_jabatan'], 0, ',', '.') }}/jam</small>
                                </td>
                                <td>Rp {{ number_format($proses['tarif_btkl'], 0, ',', '.') }}</td>
                                <td>{{ $proses['kapasitas_per_jam'] }} pcs/jam</td>
                                <td class="text-success fw-semibold">Rp {{ number_format($proses['btkl_per_produk'], 0, ',', '.') }}</td>
                                <td class="text-warning fw-semibold">
                                    @if($proses['has_bop'])
                                        Rp {{ number_format($proses['bop_per_produk'], 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($prosesBtkl->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Belum Ada Data BTKL</h5>
                    <p class="text-muted">Silakan buat data BTKL terlebih dahulu di halaman Master Data BTKL</p>
                    <a href="{{ route('master-data.proses-produksi.index') }}" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-2"></i>Ke Halaman BTKL
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Step 3: Detail Komponen BOP (Auto-display) -->
        <div class="card shadow-sm mb-4" id="bopDetailCard" style="display: none;">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>3. Detail Komponen BOP (Otomatis)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Komponen BOP ditampilkan otomatis berdasarkan proses BTKL yang dipilih.
                </div>
                
                <div id="bopDetailContent"></div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan HPP</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-primary text-white rounded">
                            <h6>Biaya Bahan</h6>
                            <h4 id="summaryBiayaBahan">Rp 0</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <h6>Total BTKL</h6>
                            <h4 id="summaryBtkl">Rp 0</h4>
                            <input type="hidden" name="total_btkl" id="totalBtklInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-warning text-dark rounded">
                            <h6>Total BOP</h6>
                            <h4 id="summaryBop">Rp 0</h4>
                            <input type="hidden" name="total_bop" id="totalBopInput" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-dark text-white rounded">
                            <h6>Total HPP</h6>
                            <h4 id="summaryHpp">Rp 0</h4>
                            <input type="hidden" name="total_hpp" id="totalHppInput" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                <i class="fas fa-save me-2"></i>Simpan Harga Pokok Produksi
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .proses-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .table th {
        vertical-align: middle;
    }
    
    .bop-komponen-table {
        font-size: 0.9rem;
    }
</style>
@endpush

@push('scripts')
<script>
// Load produk data when selected
function loadProdukData() {
    const select = document.getElementById('produk_id');
    const option = select.selectedOptions[0];
    
    if (option && option.value) {
        const biayaBahan = parseFloat(option.dataset.biayaBahan) || 0;
        
        document.getElementById('displayBiayaBahan').textContent = formatRupiah(biayaBahan);
        document.getElementById('biayaBahanInput').value = biayaBahan;
        document.getElementById('summaryBiayaBahan').textContent = formatRupiah(biayaBahan);
        
        calculateTotal();
    } else {
        document.getElementById('displayBiayaBahan').textContent = 'Rp 0';
        document.getElementById('biayaBahanInput').value = 0;
        document.getElementById('summaryBiayaBahan').textContent = 'Rp 0';
    }
}

// Calculate total when checkboxes change
function calculateTotal() {
    const checkboxes = document.querySelectorAll('.proses-checkbox:checked');
    const biayaBahan = parseFloat(document.getElementById('biayaBahanInput').value) || 0;
    
    let totalBtkl = 0;
    let totalBop = 0;
    let bopDetails = [];
    
    checkboxes.forEach(checkbox => {
        const btklPerProduk = parseFloat(checkbox.dataset.btklPerProduk) || 0;
        const bopPerProduk = parseFloat(checkbox.dataset.bopPerProduk) || 0;
        const namaProses = checkbox.dataset.nama;
        const komponenBop = JSON.parse(checkbox.dataset.komponenBop || '[]');
        
        totalBtkl += btklPerProduk;
        totalBop += bopPerProduk;
        
        if (komponenBop.length > 0) {
            bopDetails.push({
                nama_proses: namaProses,
                komponen: komponenBop,
                total: bopPerProduk
            });
        }
    });
    
    const totalHpp = biayaBahan + totalBtkl + totalBop;
    
    // Update summary
    document.getElementById('summaryBiayaBahan').textContent = formatRupiah(biayaBahan);
    document.getElementById('summaryBtkl').textContent = formatRupiah(totalBtkl);
    document.getElementById('summaryBop').textContent = formatRupiah(totalBop);
    document.getElementById('summaryHpp').textContent = formatRupiah(totalHpp);
    
    // Update hidden inputs
    document.getElementById('totalBtklInput').value = totalBtkl;
    document.getElementById('totalBopInput').value = totalBop;
    document.getElementById('totalHppInput').value = totalHpp;
    
    // Show/hide BOP detail
    const bopDetailCard = document.getElementById('bopDetailCard');
    const bopDetailContent = document.getElementById('bopDetailContent');
    
    if (bopDetails.length > 0) {
        bopDetailCard.style.display = 'block';
        
        let html = '';
        bopDetails.forEach(detail => {
            html += `
                <div class="mb-3">
                    <h6 class="fw-bold">${detail.nama_proses}</h6>
                    <table class="table table-sm table-bordered bop-komponen-table">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen</th>
                                <th width="20%">Rp/Produk</th>
                                <th width="20%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            detail.komponen.forEach(komp => {
                // Calculate rate per produk based on proportion of total BOP per produk
                const ratePerHour = parseFloat(komp.rate_per_hour || komp.tarif || 0);
                const totalRatePerHour = detail.komponen.reduce((sum, k) => sum + parseFloat(k.rate_per_hour || k.tarif || 0), 0);
                const ratePerProduk = totalRatePerHour > 0 ? (ratePerHour / totalRatePerHour) * detail.total : 0;
                
                html += `
                    <tr>
                        <td>${komp.component || komp.component_name || komp.nama_komponen || '-'}</td>
                        <td>Rp ${formatNumber(ratePerProduk)}</td>
                        <td>${komp.description || komp.notes || komp.keterangan || '-'}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <th>Total BOP/pcs</th>
                                <th colspan="2">Rp ${formatNumber(detail.total)}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
        });
        
        bopDetailContent.innerHTML = html;
    } else {
        bopDetailCard.style.display = 'none';
    }
    
    // Enable/disable submit button
    const produkSelected = document.getElementById('produk_id').value !== '';
    const prosesSelected = checkboxes.length > 0;
    document.getElementById('submitBtn').disabled = !(produkSelected && prosesSelected);
}

function formatRupiah(amount) {
    return 'Rp ' + formatNumber(amount);
}

function formatNumber(amount) {
    return new Intl.NumberFormat('id-ID').format(Math.round(amount));
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to all checkboxes
    document.querySelectorAll('.proses-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });
    
    // Add form submit listener for debugging
    document.getElementById('hppForm').addEventListener('submit', function(e) {
        const formData = new FormData(this);
        console.log('Form submitting with data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
    });
});
</script>
@endpush
@endsection

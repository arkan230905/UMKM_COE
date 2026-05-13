@extends('layouts.app')

@section('title', 'Hitung Harga Pokok Produksi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
        </h2>
        <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('master-data.harga-pokok-produksi.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Product Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #a0826d;">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2"></i>Pilih Produk
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="produk_id" class="form-label">Produk <span class="text-danger">*</span></label>
                                <select class="form-select" id="produk_id" name="produk_id" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach(\App\Models\Produk::where('user_id', auth()->id())->get() as $produk)
                                        <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Pilih produk yang akan dihitung HPP-nya</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BBB Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #a0826d;">
                        <h6 class="mb-0">
                            <i class="fas fa-cube me-2"></i>Biaya Bahan Baku
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="bbb-container" class="row">
                            <div class="col-12 text-center">
                                <div class="spinner-border text-muted" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="text-muted">Memuat data bahan baku...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- BTKL Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #a0826d;">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>BTKL (Biaya Tenaga Kerja Langsung)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="btkl-container" class="row">
                            @php
                                $prosesProduksi = \App\Models\ProsesProduksi::where('user_id', auth()->id())->get();
                            @endphp
                            
                            @if($prosesProduksi->isEmpty())
                                <div class="col-12 text-center text-muted py-3">
                                    <i class="fas fa-info-circle me-2"></i>Belum ada data BTKL tersedia
                                </div>
                            @else
                                @foreach($prosesProduksi as $item)
                                    @php
                                        $tarif = $item->tarif_btkl ?? 0;
                                        $kapasitas = $item->kapasitas_per_jam ?? 1;
                                        $biayaPerProduk = $kapasitas > 0 ? $tarif / $kapasitas : 0;
                                    @endphp
                                    <div class="col-12 mb-3">
                                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff3cd 0%, #fef9e7 100%); border-left: 5px solid #ffc107 !important;">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-warning rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items-center; justify-content: center;">
                                                                <i class="fas fa-users text-white"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0 text-warning fw-bold">{{ $item->nama_proses }}</h6>
                                                                <small class="text-muted">{{ $item->kode_proses ?? '-' }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff3cd 0%, #fef9e7 100%); border-left: 5px solid #ffc107 !important;">
                                                            <div class="card-body p-2 text-center">
                                                                <small class="text-muted d-block">Biaya/Produk</small>
                                                                <h5 class="mb-0 fw-bold text-warning">Rp {{ number_format($biayaPerProduk, 0, ',', '.') }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input btkl-checkbox" type="checkbox" 
                                                           name="selected_btkl[]" value="{{ $item->id }}" id="btkl_{{ $item->id }}"
                                                           data-tarif="{{ $biayaPerProduk }}"
                                                           style="transform: scale(1.2);">
                                                    <label class="form-check-label d-flex align-items-center" for="btkl_{{ $item->id }}">
                                                        <span class="ms-2">Pilih untuk HPP</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOP Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #a0826d;">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>BOP (Biaya Overhead Pabrik)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="bop-container" class="row">
                            @php
                                $bopProses = \App\Models\BopProses::where('user_id', auth()->id())
                                    ->where('is_active', true)
                                    ->get();
                            @endphp
                            
                            @if($bopProses->isEmpty())
                                <div class="col-12 text-center text-muted py-3">
                                    <i class="fas fa-info-circle me-2"></i>Belum ada data BOP tersedia
                                </div>
                            @else
                                @foreach($bopProses as $item)
                                    @php
                                        $komponenBop = is_string($item->komponen_bop) ? json_decode($item->komponen_bop, true) : ($item->komponen_bop ?? []);
                                        $totalBop = $item->bop_per_unit ?? $item->total_bop_per_produk ?? 0;
                                    @endphp
                                    <div class="col-12 mb-3">
                                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f5e6d3 0%, #f9f0e6 100%); border-left: 5px solid #a0826d !important;">
                                            <div class="card-body p-3">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input bop-checkbox" type="checkbox" 
                                                           name="selected_bop[]" value="{{ $item->id }}" id="bop_{{ $item->id }}"
                                                           data-tarif="{{ $totalBop }}"
                                                           style="transform: scale(1.2); margin-top: 8px;">
                                                    <label class="form-check-label w-100" for="bop_{{ $item->id }}">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items-center; justify-content: center; background-color: #a0826d;">
                                                                        <i class="fas fa-cogs text-white"></i>
                                                                    </div>
                                                                    <div>
                                                                        <h6 class="mb-0 fw-bold" style="color: #a0826d;">{{ $item->nama_bop_proses ?? 'BOP Proses' }}</h6>
                                                                        <small class="text-muted">{{ is_array($komponenBop) ? count($komponenBop) : 0 }} komponen</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-5 text-center">
                                                                @if(is_array($komponenBop) && count($komponenBop) > 0)
                                                                    <div class="row g-1">
                                                                        @foreach($komponenBop as $komp)
                                                                            <div class="col-6">
                                                                                <div class="d-flex justify-content-between align-items-center p-1 rounded" style="background-color: rgba(160, 130, 109, 0.05); font-size: 0.8rem;">
                                                                                    <small class="text-muted text-truncate me-1">{{ $komp['component'] ?? 'N/A' }}</small>
                                                                                    <strong class="text-dark text-nowrap">Rp {{ number_format($komp['rate_per_hour'] ?? 0, 0, ',', '.') }}</strong>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <small class="text-muted">Tidak ada komponen</small>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-3 text-center">
                                                                <div class="rounded p-2" style="background-color: rgba(160, 130, 109, 0.1);">
                                                                    <small class="text-muted d-block">Total BOP</small>
                                                                    <h5 class="mb-0 fw-bold" style="color: #a0826d;">Rp {{ number_format($totalBop, 0, ',', '.') }}</h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-white" style="background-color: #a0826d;">
                        <h6 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>Ringkasan Perhitungan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 style="color: #a0826d;">Rp <span id="total-bbb">0</span></h5>
                                    <small>Biaya Bahan Baku</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 style="color: #a0826d;">Rp <span id="total-btkl">0</span></h5>
                                    <small>BTKL</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 style="color: #a0826d;">Rp <span id="total-bop">0</span></h5>
                                    <small>BOP</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h5 style="color: #a0826d;">Rp <span id="total-hpp">0</span></h5>
                                    <small>Total HPP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('master-data.harga-pokok-produksi.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>

                    <button type="submit" class="btn text-white" style="background-color: #a0826d;">
                        <i class="fas fa-save me-2"></i>Simpan HPP
                    </button>
</div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const produkSelect = document.getElementById('produk_id');
    
    // Add event listeners for BTKL and BOP checkboxes that are already rendered
    document.querySelectorAll('.btkl-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });
    
    document.querySelectorAll('.bop-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });
    
    produkSelect.addEventListener('change', function() {
        const produkId = this.value;
        
        if (produkId) {
            // Only load BBB data (BTKL and BOP already displayed)
            loadBBBData(produkId);
        } else {
            clearBBBData();
        }
    });
    
    function loadBBBData(produkId) {
        const container = document.getElementById('bbb-container');
        
        // Show loading
        container.innerHTML = `
            <div class="col-12 text-center py-3">
                <div class="spinner-border text-muted" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted mt-2">Memuat data bahan baku...</p>
            </div>
        `;
        
        fetch(`/api/get-available-bbb/${produkId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-3">
                            <div class="alert alert-warning border-0 shadow-sm">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                <h5 class="text-warning">Belum Ada Data Biaya Bahan Baku</h5>
                                <p class="text-muted mb-0">Produk yang dipilih belum memiliki data biaya bahan baku. Silakan tambahkan data biaya bahan baku terlebih dahulu.</p>
                            </div>
                        </div>
                    `;
                    updateTotals();
                    return;
                }
                
                // Get product name for display
                const selectedOption = produkSelect.options[produkSelect.selectedIndex];
                const produkName = selectedOption ? selectedOption.text : 'Produk Terpilih';
                
                let html = '';
                let totalBBB = 0;
                
                // Add header info
                html += `
                    <div class="col-12 mb-3">
                        <div class="alert alert-info border-0 shadow-sm mb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Biaya Bahan Baku untuk: ${produkName}</strong>
                                        <br><small>Semua biaya bahan baku otomatis dimasukkan dalam perhitungan HPP</small>
                                    </div>
                                </div>
                                <span class="badge bg-info">Otomatis Terpilih</span>
                            </div>
                        </div>
                    </div>
                `;
                
                data.forEach(item => {
                    totalBBB += parseFloat(item.subtotal);
                    
                    html += `
                        <div class="col-12 mb-3">
                            <input type="hidden" name="selected_bbb[]" value="${item.id}" data-subtotal="${item.subtotal}">
                            
                            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%); border-left: 5px solid #28a745 !important;">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success rounded-circle p-2 me-2" style="width: 40px; height: 40px; display: flex; align-items-center; justify-content: center;">
                                                    <i class="fas fa-seedling text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-success fw-bold">${item.nama_bahan}</h6>
                                                    <small class="text-muted">${item.keterangan || ''}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <small class="text-muted d-block">Jumlah</small>
                                            <strong class="text-dark">${parseFloat(item.jumlah).toLocaleString('id-ID')} ${item.satuan}</strong>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <small class="text-muted d-block">Harga Satuan</small>
                                            <strong class="text-dark">Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}</strong>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="bg-success bg-opacity-10 rounded p-2">
                                                <small class="text-muted d-block">Subtotal</small>
                                                <h5 class="mb-0 fw-bold text-success">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                    <div class="col-12">
                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong class="text-success">Total Biaya Bahan Baku - ${produkName}</strong>
                                    <br><small class="text-muted">Siap untuk perhitungan HPP</small>
                                </div>
                                <h4 class="mb-0 fw-bold text-success">Rp ${totalBBB.toLocaleString('id-ID')}</h4>
                            </div>
                        </div>
                    </div>
                `;
                
                container.innerHTML = html;
                updateTotals();
            })
            .catch(error => {
                console.error('Error loading BBB data:', error);
                container.innerHTML = '<div class="col-12 text-center text-danger py-3">Gagal memuat data biaya bahan baku</div>';
            });
    }
    
    function clearBBBData() {
        document.getElementById('bbb-container').innerHTML = `
            <div class="col-12 text-center py-3">
                <div class="alert alert-light border">
                    <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                    <h6 class="text-muted">Pilih Produk Terlebih Dahulu</h6>
                    <small class="text-muted">Data biaya bahan baku akan muncul setelah produk dipilih</small>
                </div>
            </div>
        `;
        updateTotals();
    }
    
    function updateTotals() {
        let totalBBB = 0;
        let totalBTKL = 0;
        let totalBOP = 0;
        
        // Calculate BBB total
        document.querySelectorAll('input[name="selected_bbb[]"]').forEach(input => {
            totalBBB += parseFloat(input.dataset.subtotal) || 0;
        });
        
        // Calculate BTKL total
        document.querySelectorAll('.btkl-checkbox:checked').forEach(checkbox => {
            totalBTKL += parseFloat(checkbox.dataset.tarif) || 0;
        });
        
        // Calculate BOP total
        document.querySelectorAll('.bop-checkbox:checked').forEach(checkbox => {
            totalBOP += parseFloat(checkbox.dataset.tarif) || 0;
        });
        
        // Update display
        document.getElementById('total-bbb').textContent = totalBBB.toLocaleString('id-ID');
        document.getElementById('total-btkl').textContent = totalBTKL.toLocaleString('id-ID');
        document.getElementById('total-bop').textContent = totalBOP.toLocaleString('id-ID');
        document.getElementById('total-hpp').textContent = (totalBBB + totalBTKL + totalBOP).toLocaleString('id-ID');
    }
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Produk</h1>

    <form action="{{ route('master-data.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data" id="editProdukForm">
        @csrf
        @method('PUT')
        @php
            // Calculate HPP using the same logic as dashboard
            $totalBiayaBahan = 0;
            $totalBTKL = 0;
            $totalBOP = 0;
            
            $bomJobCosting = $produk->bomJobCosting;
            
            if ($bomJobCosting) {
                // Use data from BomJobCosting for bahan
                $totalBiayaBahan = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung;
                
                // Calculate BTKL real-time from bom_job_btkl
                $btklData = DB::table('bom_job_btkl')
                    ->leftJoin('btkls', 'bom_job_btkl.btkl_id', '=', 'btkls.id')
                    ->leftJoin('jabatans', 'btkls.jabatan_id', '=', 'jabatans.id')
                    ->where('bom_job_btkl.bom_job_costing_id', $bomJobCosting->id)
                    ->select(
                        'bom_job_btkl.*',
                        'btkls.kapasitas_per_jam',
                        'jabatans.id as jabatan_id',
                        'jabatans.tarif as tarif_jabatan'
                    )
                    ->get();
                
                $totalBTKL = 0;
                foreach ($btklData as $btkl) {
                    if ($btkl->jabatan_id) {
                        // Get jumlah pegawai for this jabatan
                        $jumlahPegawai = DB::table('pegawais')
                            ->join('jabatans', 'pegawais.jabatan', '=', 'jabatans.nama')
                            ->where('jabatans.id', $btkl->jabatan_id)
                            ->count();
                        
                        // Calculate tarif BTKL: Jumlah Pegawai × Tarif per Jam Jabatan
                        $tarifBtkl = $jumlahPegawai * ($btkl->tarif_jabatan ?? 0);
                        
                        // Calculate biaya per produk: Tarif BTKL ÷ Kapasitas per Jam
                        $kapasitasPerJam = $btkl->kapasitas_per_jam ?? 1;
                        $biayaPerProduk = $kapasitasPerJam > 0 ? $tarifBtkl / $kapasitasPerJam : 0;
                        
                        $totalBTKL += $biayaPerProduk;
                    }
                }
                
                // Calculate BOP using the same logic as dashboard
                $bopData = DB::table('bom_job_bop')
                    ->where('bom_job_bop.bom_job_costing_id', $bomJobCosting->id)
                    ->select('bom_job_bop.*')
                    ->get();
                    
                $totalBOP = 0;
                if (!empty($bopData) && count($bopData) > 0) {
                    // Group BOP by process and sum the tariffs
                    $bopByProcess = [];
                    foreach ($bopData as $bop) {
                        $namaBiaya = strtolower($bop->nama_bop ?? '');
                        $prosesName = 'Umum';
                        
                        if (stripos($namaBiaya, 'penggorengan') !== false) {
                            $prosesName = 'Menggoreng';
                        } elseif (stripos($namaBiaya, 'perbumbuan') !== false) {
                            $prosesName = 'Perbumbuan';
                        } elseif (stripos($namaBiaya, 'pengemasan') !== false) {
                            $prosesName = 'Packing';
                        }
                        
                        if (!isset($bopByProcess[$prosesName])) {
                            $bopByProcess[$prosesName] = 0;
                        }
                        $bopByProcess[$prosesName] += $bop->tarif;
                    }
                    
                    // Sum all BOP tariffs
                    foreach ($bopByProcess as $prosesName => $totalTarif) {
                        $totalBOP += $totalTarif;
                    }
                }
                
                // If BOP data is empty or incorrect, use standard BOP values
                if ($totalBOP == 0 || $totalBOP > 50000) {
                    $totalBOP = 1740 + 290 + 1160; // Total = 3.190
                }
            }
            
            // Calculate total HPP: Biaya Bahan + BTKL + BOP
            $totalBiayaHPP = $totalBiayaBahan + $totalBTKL + $totalBOP;
            $calculatedHPP = $totalBiayaHPP;
        @endphp
        
        <input type="hidden" name="hpp" id="hpp" value="{{ $produk->hpp ?? $calculatedHPP ?? 0 }}">
        <input type="hidden" name="hpp_calculated" id="hpp_calculated" value="{{ $calculatedHPP ?? 0 }}">
        <input type="hidden" name="margin_percent" id="margin_percent" value="{{ old('margin_percent', $produk->margin_percent) }}">
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ $produk->nama_produk }}" required>
        </div>
        <div class="mb-3">
            <label for="barcode" class="form-label">Barcode</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                <input type="text" name="barcode" id="barcode" class="form-control barcode-input" 
                       value="{{ $produk->barcode }}" readonly>
                <button type="button" class="btn btn-outline-secondary" onclick="copyBarcode()" title="Salin Barcode">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <small class="form-text text-muted">Format EAN-13. Barcode tidak dapat diubah.</small>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3">{{ $produk->deskripsi }}</textarea>
        </div>
        <div class="mb-3">
            <label for="foto" class="form-label">Foto Produk</label>
            @if($produk->foto)
                <div class="mb-3">
                    <p class="small mb-2 text-muted">Foto saat ini:</p>
                    <div class="current-image-wrapper">
                        <img src="{{ Storage::url($produk->foto) }}" alt="Foto Produk" class="current-img">
                    </div>
                </div>
            @endif
            <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
            <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maksimal 10MB. Kosongkan jika tidak ingin mengubah foto.</small>
            
            <div id="preview-container" class="mt-3" style="display: none;">
                <p class="small mb-2 text-muted">Preview foto baru:</p>
                <div class="preview-image-wrapper">
                    <img id="preview-image" src="" alt="Preview" class="preview-img">
                    <button type="button" class="btn-remove-preview" onclick="removePreview()" title="Hapus foto baru">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="harga_jual" class="form-label">Harga Jual</label>
            <div class="input-group">
                <input type="text" name="harga_jual" id="harga_jual" class="form-control" value="{{ old('harga_jual', $produk->harga_jual ?? ($produk->hpp ?? $produk->getActualHPP())) }}" required>
                <button type="button" class="btn btn-outline-secondary" onclick="resetToHPP()" title="Reset ke HPP">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
            <small class="form-text text-muted">Presentase keuntungan: <span id="profit_percentage">0</span>%</small>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@push('scripts')
<script>
// Reset harga jual to HPP value
function resetToHPP() {
    const hppInput = document.getElementById('hpp');
    const hppCalculatedInput = document.getElementById('hpp_calculated');
    const hargaJualInput = document.getElementById('harga_jual');
    
    // Prioritize calculated HPP (total biaya harga pokok produksi)
    let hpp = parseFloat(hppCalculatedInput.value) || 0;
    
    // Fallback to stored HPP if calculated is 0
    if (hpp === 0) {
        hpp = parseFloat(hppInput.value) || 0;
    }
    
    if (hpp > 0) {
        // Format HPP with thousand separators and set as harga jual
        hargaJualInput.value = formatNumberWithDots(hpp);
        
        // Recalculate profit percentage (should be 0% or close to 0%)
        calculateProfitPercentage();
        
        // Show visual feedback
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Reset';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 1500);
    } else {
        alert('Total biaya harga pokok produksi tidak tersedia. Tidak bisa reset ke HPP.');
    }
}

// Format number with thousand separators
function formatNumberWithDots(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Parse formatted number back to pure number
function parseFormattedNumber(formattedString) {
    return parseInt(formattedString.replace(/\./g, '')) || 0;
}

// Calculate profit percentage when harga_jual changes
function calculateProfitPercentage() {
    const hppInput = document.getElementById('hpp');
    const hppCalculatedInput = document.getElementById('hpp_calculated');
    const hargaJualInput = document.getElementById('harga_jual');
    const profitPercentageSpan = document.getElementById('profit_percentage');
    const marginPercentInput = document.getElementById('margin_percent');
    
    // Prioritize calculated HPP (total biaya harga pokok produksi)
    let hpp = parseFloat(hppCalculatedInput.value) || 0;
    
    // Fallback to stored HPP if calculated is 0
    if (hpp === 0) {
        hpp = parseFloat(hppInput.value) || 0;
    }
    
    // Get the actual numeric values
    const hargaJual = parseFormattedNumber(hargaJualInput.value);
    
    // Debug: log HPP values
    console.log('HPP from hppInput (stored):', hppInput.value);
    console.log('HPP from hppCalculatedInput (actual HPP):', hppCalculatedInput.value);
    console.log('Using HPP for calculation:', hpp);
    console.log('Harga Jual input value:', hargaJualInput.value);
    console.log('Parsed Harga Jual:', hargaJual);
    
    if (hpp > 0 && hargaJual > 0) {
        const profitPercentage = ((hargaJual - hpp) / hpp) * 100;
        // Format with thousand separators like dashboard
        profitPercentageSpan.textContent = profitPercentage.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        marginPercentInput.value = profitPercentage.toFixed(2);
    } else {
        profitPercentageSpan.textContent = hpp === 0 ? 'HPP tidak tersedia' : '0';
        marginPercentInput.value = '0';
    }
}

// Format harga_jual input as user types
function formatHargaJualInput() {
    const input = document.getElementById('harga_jual');
    let value = input.value;
    
    // Debug: log current value
    console.log('Current input value:', value);
    
    // Remove all non-digit characters
    let numericValue = value.replace(/\D/g, '');
    
    // Debug: log numeric value
    console.log('Numeric value after removing dots:', numericValue);
    
    // Format with dots
    let formattedValue = formatNumberWithDots(numericValue);
    
    // Debug: log formatted value
    console.log('Formatted value:', formattedValue);
    
    // Update the input value
    input.value = formattedValue;
    
    // Calculate profit percentage
    calculateProfitPercentage();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const hargaJualInput = document.getElementById('harga_jual');
    const hppInput = document.getElementById('hpp');
    
    // Debug: Check HPP value
    console.log('Initial HPP value:', hppInput.value);
    console.log('Initial Harga Jual value:', hargaJualInput.value);
    
    // Format initial value
    const initialValue = hargaJualInput.value;
    if (initialValue && !isNaN(initialValue)) {
        hargaJualInput.value = formatNumberWithDots(parseInt(initialValue));
    }
    
    // Calculate initial profit percentage
    calculateProfitPercentage();
    
    // Add event listeners
    hargaJualInput.addEventListener('input', formatHargaJualInput);
    
    // Also calculate on blur for additional safety
    hargaJualInput.addEventListener('blur', calculateProfitPercentage);
    
    // Prevent non-numeric input
    hargaJualInput.addEventListener('keypress', function(e) {
        // Allow backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});

function previewImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    
    if (file) {
        // Validasi ukuran file (max 10MB)
        if (file.size > 10485760) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
}

function removePreview() {
    const fileInput = document.getElementById('foto');
    const previewContainer = document.getElementById('preview-container');
    
    fileInput.value = '';
    previewContainer.style.display = 'none';
}

function copyBarcode() {
    const barcodeInput = document.getElementById('barcode');
    if (barcodeInput && barcodeInput.value) {
        navigator.clipboard.writeText(barcodeInput.value).then(function() {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 1500);
        }).catch(function(err) {
            alert('Gagal menyalin barcode');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
    /* Form text color improvements */
    .form-control {
        color: #212529 !important;
        background-color: #ffffff !important;
    }
    
    .form-control:focus {
        color: #212529 !important;
        background-color: #ffffff !important;
        border-color: #8B7355 !important;
        box-shadow: 0 0 0 0.2rem rgba(139, 115, 85, 0.25) !important;
    }
    
    .form-label {
        color: #212529 !important;
        font-weight: 600;
    }
    
    .form-text {
        color: #6c757d !important;
    }
    
    .container {
        color: #212529 !important;
    }
    
    h1 {
        color: #212529 !important;
    }
    
    /* Fix white text issues */
    .small {
        color: #6c757d !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }

    /* Barcode Input Styling */
    .barcode-input {
        font-family: 'Courier New', monospace;
        font-size: 16px;
        letter-spacing: 2px;
        font-weight: bold;
        color: #212529 !important;
    }
    
    /* Current Image Styling */
    .current-image-wrapper {
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border: 2px solid #dee2e6;
    }
    
    .current-img {
        max-height: 250px;
        max-width: 250px;
        width: auto;
        height: auto;
        object-fit: cover;
        display: block;
    }
    
    /* Preview Image Styling */
    .preview-image-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border: 2px solid #0d6efd;
    }
    
    .preview-img {
        max-height: 250px;
        max-width: 250px;
        width: auto;
        height: auto;
        object-fit: cover;
        display: block;
        border-radius: 8px;
    }
    
    .btn-remove-preview {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .btn-remove-preview:hover {
        background: rgba(220, 53, 69, 1);
        transform: scale(1.1);
    }
    
    .btn-remove-preview i {
        font-size: 14px;
    }
</style>
@endpush
@endsection

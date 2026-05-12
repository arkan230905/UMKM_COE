@extends('layouts.app')

@section('title', 'Konversi Satuan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Konversi Satuan - Pengecekan Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Info Section -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Alat Pengecekan Konversi Satuan
                        </h6>
                        <p class="mb-2">
                            Gunakan tool ini untuk mengecek konversi satuan secara cepat. 
                            Tool ini tidak mengubah data atau stok, hanya untuk referensi informasi.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <strong>Cara menggunakan:</strong> Masukkan jumlah, pilih satuan asal dan tujuan, 
                            hasil akan muncul otomatis.
                        </p>
                    </div>

                    <!-- Konversi Tool -->
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <form id="konversiChecker">
                                        <!-- Input Jumlah -->
                                        <div class="mb-4">
                                            <label for="jumlah" class="form-label fw-bold">
                                                <i class="fas fa-hashtag me-2"></i>
                                                Jumlah
                                            </label>
                                            <input 
                                                type="number" 
                                                id="jumlah" 
                                                class="form-control form-control-lg text-center"
                                                step="0.01"
                                                min="0"
                                                placeholder="Masukkan jumlah"
                                                value="1"
                                            >
                                            <div class="form-text">Masukkan angka yang ingin dikonversi</div>
                                        </div>

                                        <!-- Satuan Asal dan Tujuan -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="satuan_asal" class="form-label fw-bold">
                                                    <i class="fas fa-arrow-right me-2"></i>
                                                    Dari Satuan
                                                </label>
                                                <select id="satuan_asal" class="form-select form-select-lg">
                                                    <option value="">-- Pilih Satuan --</option>
                                                </select>
                                                <div class="form-text">Satuan asal yang akan dikonversi</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="satuan_tujuan" class="form-label fw-bold">
                                                    <i class="fas fa-arrow-left me-2"></i>
                                                    Ke Satuan
                                                </label>
                                                <select id="satuan_tujuan" class="form-select form-select-lg">
                                                    <option value="">-- Pilih Satuan --</option>
                                                </select>
                                                <div class="form-text">Satuan tujuan konversi</div>
                                            </div>
                                        </div>

                                        <!-- Hasil Konversi -->
                                        <div class="mb-4">
                                            <label for="hasil" class="form-label fw-bold">
                                                <i class="fas fa-calculator me-2"></i>
                                                Hasil Konversi
                                            </label>
                                            <input 
                                                type="text" 
                                                id="hasil" 
                                                class="form-control form-control-lg text-center bg-light"
                                                readonly
                                                placeholder="Hasil akan muncul otomatis"
                                            >
                                            <div class="form-text">Hasil konversi otomatis</div>
                                        </div>

                                        <!-- Info Konversi -->
                                        <div id="infoKonversi" class="alert alert-secondary" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <span id="infoText"></span>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reference -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-book me-2"></i>
                                        Referensi Konversi Umum
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-primary">Berat</h6>
                                            <ul class="list-unstyled">
                                                <li>1 kg = 1.000 gram</li>
                                                <li>1 kg = 10 ons</li>
                                                <li>1 ons = 100 gram</li>
                                                <li>1 ton = 1.000 kg</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-success">Volume</h6>
                                            <ul class="list-unstyled">
                                                <li>1 liter = 1.000 ml</li>
                                                <li>1 liter = 1 kg (air)</li>
                                                <li>1 galon = 3,785 liter</li>
                                                <li>1 gelas = 250 ml</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="fw-bold text-warning">Pieces</h6>
                                            <ul class="list-unstyled">
                                                <li>1 lusin = 12 buah</li>
                                                <li>1 kodi = 20 buah</li>
                                                <li>1 gross = 144 buah</li>
                                                <li>1 rim = 500 lembar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Data satuan yang tersedia
const satuanData = @json($satuans ?? []);

// Konversi faktor ke satuan dasar (kg/gram/liter)
const konversiFaktor = {
    // Berat
    'kg': 1,
    'kilogram': 1,
    'gram': 0.001,
    'g': 0.001,
    'gr': 0.001,
    'ons': 0.1,
    'hg': 0.1,
    'dag': 0.01,
    'kwintal': 100,
    'ton': 1000,
    'lbs': 0.453592,
    'oz': 0.0283495,
    
    // Volume
    'liter': 1,
    'l': 1,
    'ltr': 1,
    'mililiter': 0.001,
    'ml': 0.001,
    'gelas': 0.25,
    'sendok_makan': 0.015,
    'sendok_teh': 0.005,
    'galon': 3.785,
    
    // Pieces (diasumsikan 1:1 untuk konversi universal)
    'pcs': 1,
    'pc': 1,
    'buah': 1,
    'piece': 1,
    'pack': 1,
    'pak': 1,
    'box': 1,
    'botol': 1,
    'dus': 1,
    'bungkus': 1,
    'kaleng': 1,
    'sachet': 1,
    'tablet': 1,
    'kapsul': 1,
    'tube': 1,
    'potong': 1,
    'lembar': 1,
    'roll': 1,
    'meter': 1,
    'cm': 0.01,
    'mm': 0.001,
    'inch': 0.0254,
    'kodi': 20,
    'lusin': 12,
    'gross': 144,
    'rim': 500
};

// Initialize dropdowns
function initializeDropdowns() {
    const satuanAsal = document.getElementById('satuan_asal');
    const satuanTujuan = document.getElementById('satuan_tujuan');
    
    // Clear existing options
    satuanAsal.innerHTML = '<option value="">-- Pilih Satuan --</option>';
    satuanTujuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';
    
    // Add satuan options
    satuanData.forEach(satuan => {
        const optionAsal = new Option(satuan.nama, satuan.nama.toLowerCase());
        const optionTujuan = new Option(satuan.nama, satuan.nama.toLowerCase());
        
        satuanAsal.add(optionAsal);
        satuanTujuan.add(optionTujuan);
    });
    
    // Add common units if not in database
    const commonUnits = ['kg', 'gram', 'liter', 'ml', 'pcs', 'buah', 'potong', 'lusin'];
    commonUnits.forEach(unit => {
        if (!Array.from(satuanAsal.options).some(opt => opt.value === unit)) {
            satuanAsal.add(new Option(unit.toUpperCase(), unit));
            satuanTujuan.add(new Option(unit.toUpperCase(), unit));
        }
    });
}

// Konversi satuan
function konversiSatuan(jumlah, dari, ke) {
    if (!jumlah || !dari || !ke) return 0;
    
    const dariNormal = dari.toLowerCase().trim();
    const keNormal = ke.toLowerCase().trim();
    
    // Jika satuan sama
    if (dariNormal === keNormal) return jumlah;
    
    // Dapatkan faktor konversi
    const faktorDari = konversiFaktor[dariNormal] || 1;
    const faktorKe = konversiFaktor[keNormal] || 1;
    
    // Konversi: jumlah * (faktorDari / faktorKe)
    const hasil = jumlah * (faktorDari / faktorKe);
    
    return hasil;
}

// Update hasil konversi
function updateHasil() {
    const jumlah = parseFloat(document.getElementById('jumlah').value) || 0;
    const satuanAsal = document.getElementById('satuan_asal').value;
    const satuanTujuan = document.getElementById('satuan_tujuan').value;
    const hasilField = document.getElementById('hasil');
    const infoDiv = document.getElementById('infoKonversi');
    const infoText = document.getElementById('infoText');
    
    if (jumlah > 0 && satuanAsal && satuanTujuan) {
        const hasil = konversiSatuan(jumlah, satuanAsal, satuanTujuan);
        
        if (hasil > 0) {
            hasilField.value = formatNumber(hasil);
            
            // Show info
            const satuanAsalDisplay = satuanAsal.toUpperCase();
            const satuanTujuanDisplay = satuanTujuan.toUpperCase();
            infoText.textContent = `${formatNumber(jumlah)} ${satuanAsalDisplay} = ${formatNumber(hasil)} ${satuanTujuanDisplay}`;
            infoDiv.style.display = 'block';
            
            // Add animation
            hasilField.classList.add('border-success');
            setTimeout(() => {
                hasilField.classList.remove('border-success');
            }, 1000);
        } else {
            hasilField.value = 'Tidak dapat dikonversi';
            infoDiv.style.display = 'none';
        }
    } else {
        hasilField.value = '';
        infoDiv.style.display = 'none';
    }
}

// Format number
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(2) + ' jt';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(2) + ' rb';
    } else {
        return num.toFixed(4).replace(/\.?0+$/, '');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    initializeDropdowns();
    
    // Add event listeners
    document.getElementById('jumlah').addEventListener('input', updateHasil);
    document.getElementById('satuan_asal').addEventListener('change', updateHasil);
    document.getElementById('satuan_tujuan').addEventListener('change', updateHasil);
    
    // Auto-swap satuan
    document.getElementById('satuan_asal').addEventListener('dblclick', function() {
        const asal = this.value;
        const tujuan = document.getElementById('satuan_tujuan').value;
        
        if (asal && tujuan) {
            this.value = tujuan;
            document.getElementById('satuan_tujuan').value = asal;
            updateHasil();
        }
    });
    
    // Initial calculation
    updateHasil();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 's':
                e.preventDefault();
                document.getElementById('jumlah').focus();
                break;
            case 'a':
                e.preventDefault();
                document.getElementById('satuan_asal').focus();
                break;
            case 't':
                e.preventDefault();
                document.getElementById('satuan_tujuan').focus();
                break;
        }
    }
});
</script>
@endpush
@endsection

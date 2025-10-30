// Fungsi untuk memformat angka ke format Rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(angka).replace('Rp', '').trim();
}

// Hapus format Rupiah dan konversi ke number
function unformatRupiah(rupiah) {
    if (typeof rupiah === 'number') return rupiah;
    if (!rupiah) return 0;
    
    // Hapus semua karakter non-digit kecuali koma dan titik
    const cleanValue = rupiah.toString().replace(/[^\d,.]/g, '');
    
    // Jika tidak ada angka, kembalikan 0
    if (!cleanValue) return 0;
    
    // Ganti koma dengan titik untuk decimal dan hapus semua titik ribuan
    const numericValue = cleanValue.replace(/\./g, '').replace(',', '.');
    
    // Konversi ke number
    return parseFloat(numericValue) || 0;
}

// Hitung nilai residu (5% dari nilai perolehan)
function hitungNilaiResidu(nilaiPerolehan) {
    return nilaiPerolehan * 0.05; // 5% dari nilai perolehan
}

// Hitung penyusutan tahunan (Garis Lurus)
function hitungPenyusutan(nilaiPerolehan, nilaiResidu, umurManfaat) {
    if (!umurManfaat || umurManfaat <= 0) return 0;
    return (nilaiPerolehan - nilaiResidu) / umurManfaat;
}

// Format input dengan pemisah ribuan
function formatInputRupiah(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = value;
    updatePerhitungan();
}

// Update semua perhitungan
function updatePerhitungan() {
    // Ambil elemen input
    const hargaInput = document.getElementById('harga');
    const nilaiResiduInput = document.getElementById('nilai_residu_hidden');
    const umurManfaatInput = document.getElementById('useful_life_years');
    const acquisitionCostInput = document.getElementById('acquisition_cost');
    
    // Ambil elemen tampilan
    const hargaPerolehanDisplay = document.getElementById('harga_perolehan_display');
    const nilaiResiduDisplay = document.getElementById('nilai_residu_display');
    const nilaiDisusutkanDisplay = document.getElementById('nilai_disusutkan_display');
    const umurManfaatDisplay = document.getElementById('umur_manfaat_display');
    const penyusutanTahunanDisplay = document.getElementById('penyusutan_tahunan_display');
    const penyusutanBulananDisplay = document.getElementById('penyusutan_bulanan_display');
    
    // Ambil nilai perolehan
    const nilaiPerolehan = unformatRupiah(hargaInput?.value || '0');
    
    // Hitung nilai residu (5% dari nilai perolehan)
    const nilaiResidu = hitungNilaiResidu(nilaiPerolehan);
    
    // Ambil umur manfaat
    const umurManfaat = parseInt(umurManfaatInput?.value) || 0;
    
    // Hitung nilai yang disusutkan
    const nilaiDisusutkan = Math.max(0, nilaiPerolehan - nilaiResidu);
    
    // Hitung penyusutan tahunan dan bulanan
    const penyusutanTahunan = hitungPenyusutan(nilaiPerolehan, nilaiResidu, umurManfaat);
    const penyusutanBulanan = penyusutanTahunan / 12;
    
    // Update tampilan
    if (hargaPerolehanDisplay) {
        hargaPerolehanDisplay.textContent = formatRupiah(nilaiPerolehan);
    }
    
    if (nilaiResiduDisplay) {
        nilaiResiduDisplay.textContent = formatRupiah(nilaiResidu);
    }
    
    if (nilaiDisusutkanDisplay) {
        nilaiDisusutkanDisplay.textContent = formatRupiah(nilaiDisusutkan);
    }
    
    if (umurManfaatDisplay) {
        umurManfaatDisplay.textContent = umurManfaat;
    }
    
    if (penyusutanTahunanDisplay) {
        penyusutanTahunanDisplay.textContent = formatRupiah(penyusutanTahunan) + ' /tahun';
    }
    
    if (penyusutanBulananDisplay) {
        penyusutanBulananDisplay.textContent = formatRupiah(penyusutanBulanan) + ' /bulan';
    }
    
    // Update nilai tersembunyi untuk form submission
    if (nilaiResiduInput) {
        nilaiResiduInput.value = nilaiResidu;
    }
    
    if (acquisitionCostInput) {
        acquisitionCostInput.value = nilaiPerolehan;
    }
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi event listeners
    const hargaInput = document.getElementById('harga');
    const umurManfaatInput = document.getElementById('useful_life_years');
    
    if (hargaInput) {
        // Format input harga jika sudah ada nilai
        if (hargaInput.value) {
            formatInputRupiah(hargaInput);
        }
        
        // Tambahkan event listener untuk input harga
        hargaInput.addEventListener('input', function() {
            formatInputRupiah(this);
        });
    }
    
    if (umurManfaatInput) {
        umurManfaatInput.addEventListener('input', updatePerhitungan);
    }
    
    // Update perhitungan awal
    updatePerhitungan();
});

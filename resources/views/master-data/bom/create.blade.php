@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Bill of Materials (BOM)</h3>
        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="{{ route('master-data.bom.store') }}" method="POST" id="bomForm">
        @csrf
        
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">NAMA PRODUK</label>
                        <select name="produk_id" id="produk_id" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $produk)
                                <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead class="table-primary">
                            <tr>
                                <th width="25%">BAHAN BAKU</th>
                                <th width="10%">JUMLAH</th>
                                <th width="10%">SATUAN</th>
                                <th width="20%">HARGA SATUAN (RP)</th>
                                <th width="20%">SUBTOTAL (RP)</th>
                                <th width="10%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="bomTableBody">
                            <tr class="bom-row">
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-select" required>
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($bahanBakus as $bahan)
                                            <option value="{{ $bahan->id }}" 
                                                data-harga="{{ $bahan->harga_satuan ?? 0 }}" 
                                                data-satuan="{{ is_object($bahan->satuan) ? $bahan->satuan->kode : ($bahan->satuan ?? 'KG') }}"
                                                data-nama="{{ $bahan->nama_bahan ?? $bahan->nama }}">
                                                {{ $bahan->nama_bahan ?? $bahan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-input" 
                                        value="1" min="0.01" step="0.01" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuan-select" required>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="harga-display text-end">Rp 0</td>
                                <td class="subtotal-display text-end fw-bold">Rp 0</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <td colspan="4" class="text-end fw-bold">Total Bahan Baku</td>
                                <td class="text-end fw-bold" id="totalBahanBaku">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr class="table-info">
                                <td colspan="4" class="text-end">BTKL (60%)</td>
                                <td class="text-end" id="totalBTKL">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr class="table-info">
                                <td colspan="4" class="text-end">BOP (40%)</td>
                                <td class="text-end" id="totalBOP">Rp 0</td>
                                <td></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="4" class="text-end fw-bold">Harga Pokok Produksi</td>
                                <td class="text-end fw-bold" id="grandTotal">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary btn-sm" id="btnTambahBaris">
                    <i class="bi bi-plus"></i> Tambah Baris
                </button>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan BOM
            </button>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
// Data dari backend
const bahanBakuData = @json($bahanBakus);
const satuanData = @json($satuans);

// Konversi satuan
const konversi = {
    'KG': 1, 'G': 1000, 'GR': 1000, 'GRAM': 1000,
    'HG': 10, 'DAG': 100, 'ONS': 10,
    'L': 1, 'LITER': 1, 'ML': 1000,
    'PCS': 1, 'BUAH': 1, 'BTL': 1, 'BOTOL': 1
};

// Format rupiah
function formatRupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
}

// Hitung harga dan subtotal
function hitungBaris(row) {
    const bahanSelect = row.querySelector('.bahan-select');
    const jumlahInput = row.querySelector('.jumlah-input');
    const satuanSelect = row.querySelector('.satuan-select');
    const hargaDisplay = row.querySelector('.harga-display');
    const subtotalDisplay = row.querySelector('.subtotal-display');
    
    const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
    
    if (!selectedOption.value) {
        hargaDisplay.textContent = 'Rp 0';
        subtotalDisplay.textContent = 'Rp 0';
        hitungTotal();
        return;
    }
    
    const hargaPerSatuanUtama = parseFloat(selectedOption.dataset.harga) || 0;
    const satuanUtama = (selectedOption.dataset.satuan || 'KG').toUpperCase();
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const satuan = satuanSelect.value.toUpperCase();
    
    if (hargaPerSatuanUtama === 0) {
        const namaBahan = selectedOption.dataset.nama || selectedOption.text;
        alert('⚠️ PERHATIAN!\n\nBahan baku "' + namaBahan + '" belum memiliki harga.\n\nSilakan lakukan PEMBELIAN terlebih dahulu!');
        
        // Reset pilihan bahan baku agar tidak bisa ditambahkan
        bahanSelect.value = '';
        
        hargaDisplay.textContent = '-';
        subtotalDisplay.textContent = 'Rp 0';
        hitungTotal();
        return;
    }
    
    // Hitung harga per satuan yang dipilih
    let hargaPerSatuan;
    if (satuan === satuanUtama) {
        hargaPerSatuan = hargaPerSatuanUtama;
    } else {
        // Konversi: 1 KG = 1000 G
        // Jika harga per KG = 46139, maka harga per G = 46139 / 1000 = 46.139
        const faktorUtama = konversi[satuanUtama] || 1;  // KG = 1
        const faktorPilihan = konversi[satuan] || 1;      // G = 1000
        
        // Harga per satuan terpilih = Harga per satuan utama / (faktor pilihan / faktor utama)
        // Contoh: 46139 / (1000 / 1) = 46139 / 1000 = 46.139
        hargaPerSatuan = hargaPerSatuanUtama / (faktorPilihan / faktorUtama);
    }
    
    const subtotal = hargaPerSatuan * jumlah;
    
    hargaDisplay.textContent = formatRupiah(hargaPerSatuan);
    subtotalDisplay.textContent = formatRupiah(subtotal);
    
    hitungTotal();
}

// Hitung total
function hitungTotal() {
    let totalBahan = 0;
    
    document.querySelectorAll('.bom-row').forEach(row => {
        const subtotalText = row.querySelector('.subtotal-display').textContent;
        const subtotal = parseFloat(subtotalText.replace(/[^0-9]/g, '')) || 0;
        totalBahan += subtotal;
    });
    
    const btkl = totalBahan * 0.6;  // 60% dari total bahan baku
    const bop = totalBahan * 0.4;   // 40% dari total bahan baku
    const grandTotal = totalBahan + btkl + bop;
    
    document.getElementById('totalBahanBaku').textContent = formatRupiah(totalBahan);
    document.getElementById('totalBTKL').textContent = formatRupiah(btkl);
    document.getElementById('totalBOP').textContent = formatRupiah(bop);
    document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
}

// Attach events ke row
function attachEvents(row) {
    const bahanSelect = row.querySelector('.bahan-select');
    const jumlahInput = row.querySelector('.jumlah-input');
    const satuanSelect = row.querySelector('.satuan-select');
    const btnHapus = row.querySelector('.btn-hapus');
    
    bahanSelect.addEventListener('change', () => {
        // Set satuan default
        const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
        if (selectedOption.value) {
            const satuanUtama = selectedOption.dataset.satuan || 'KG';
            satuanSelect.value = satuanUtama;
        }
        hitungBaris(row);
    });
    
    jumlahInput.addEventListener('input', () => hitungBaris(row));
    satuanSelect.addEventListener('change', () => hitungBaris(row));
    
    btnHapus.addEventListener('click', () => {
        if (document.querySelectorAll('.bom-row').length <= 1) {
            alert('Minimal harus ada 1 bahan baku!');
            return;
        }
        if (confirm('Hapus baris ini?')) {
            row.remove();
            hitungTotal();
        }
    });
}

// Tambah baris baru
document.getElementById('btnTambahBaris').addEventListener('click', () => {
    const tbody = document.getElementById('bomTableBody');
    const firstRow = tbody.querySelector('.bom-row');
    const newRow = firstRow.cloneNode(true);
    
    // Reset values
    newRow.querySelector('.bahan-select').value = '';
    newRow.querySelector('.jumlah-input').value = '1';
    newRow.querySelector('.harga-display').textContent = 'Rp 0';
    newRow.querySelector('.subtotal-display').textContent = 'Rp 0';
    
    tbody.appendChild(newRow);
    attachEvents(newRow);
});

// Init
document.addEventListener('DOMContentLoaded', () => {
    console.log('=== BOM INIT ===');
    console.log('Bahan Baku Data:', bahanBakuData);
    
    const rows = document.querySelectorAll('.bom-row');
    console.log('Found rows:', rows.length);
    
    rows.forEach((row, index) => {
        console.log('Attaching events to row', index + 1);
        attachEvents(row);
    });
    
    console.log('=== INIT COMPLETE ===');
});
</script>
@endsection

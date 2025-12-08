@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Edit BOM: {{ $produk->nama_produk }} - Process Costing</h3>
        <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="{{ route('master-data.bom.update', $bom->id) }}" method="POST" id="bomForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="produk_id" value="{{ $bom->produk_id }}">

        <!-- Pilih Produk -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">NAMA PRODUK</label>
                        <input type="text" class="form-control" value="{{ $produk->nama_produk }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Bahan Baku (BBB) -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> 1. Biaya Bahan Baku (BBB)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30%">BAHAN BAKU</th>
                                <th width="12%">JUMLAH</th>
                                <th width="10%">SATUAN</th>
                                <th width="18%">HARGA SATUAN</th>
                                <th width="18%">SUBTOTAL</th>
                                <th width="8%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="bomTableBody">
                            @foreach($bomDetails as $detail)
                            @php
                                $bahanBaku = $detail->bahanBaku;
                                $satuanKode = $bahanBaku->satuan->kode ?? 'KG';
                            @endphp
                            <tr class="bom-row">
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-select" required>
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach($bahanBakus as $bahan)
                                            <option value="{{ $bahan->id }}" 
                                                data-harga="{{ $bahan->harga_satuan ?? 0 }}" 
                                                data-satuan="{{ is_object($bahan->satuan) ? $bahan->satuan->kode : ($bahan->satuan ?? 'KG') }}"
                                                {{ $bahan->id == $detail->bahan_baku_id ? 'selected' : '' }}>
                                                {{ $bahan->nama_bahan ?? $bahan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-input" 
                                        value="{{ $detail->jumlah }}" min="0.01" step="0.01" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuan-select" required>
                                        @foreach(\App\Models\Satuan::all() as $satuan)
                                            <option value="{{ $satuan->kode }}" {{ $satuan->kode == $detail->satuan ? 'selected' : '' }}>
                                                {{ $satuan->kode }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="harga-display text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                                <td class="subtotal-display text-end fw-bold">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-hapus-bahan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <td colspan="4" class="text-end fw-bold">Total Biaya Bahan Baku (BBB)</td>
                                <td class="text-end fw-bold" id="totalBBB">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambahBahan">
                    <i class="bi bi-plus"></i> Tambah Bahan Baku
                </button>
            </div>
        </div>

        <!-- Section 2: Proses Produksi (BTKL + BOP) -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> 2. Proses Produksi (BTKL + BOP)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="prosesTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">PROSES</th>
                                <th width="12%">DURASI</th>
                                <th width="8%">SATUAN</th>
                                <th width="15%">BIAYA BTKL</th>
                                <th width="15%">BIAYA BOP</th>
                                <th width="12%">TOTAL</th>
                                <th width="8%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="prosesTableBody">
                            <!-- Rows akan ditambahkan via JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="4" class="text-end fw-bold">Total BTKL</td>
                                <td class="text-end fw-bold" id="totalBTKL">Rp 0</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="table-info">
                                <td colspan="5" class="text-end fw-bold">Total BOP</td>
                                <td class="text-end fw-bold" id="totalBOP">Rp 0</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm" id="btnTambahProses">
                    <i class="bi bi-plus"></i> Tambah Proses Produksi
                </button>
                <small class="text-muted ms-2">* Pilih proses produksi untuk menghitung BTKL dan BOP secara otomatis</small>
            </div>
        </div>

        <!-- Section 3: Ringkasan HPP -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator"></i> 3. Ringkasan Harga Pokok Produksi (HPP)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td width="70%">Total Biaya Bahan Baku (BBB)</td>
                            <td class="text-end fw-bold" id="summaryBBB">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Total Biaya Tenaga Kerja Langsung (BTKL)</td>
                            <td class="text-end fw-bold" id="summaryBTKL">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Total Biaya Overhead Pabrik (BOP)</td>
                            <td class="text-end fw-bold" id="summaryBOP">Rp 0</td>
                        </tr>
                        <tr class="table-success">
                            <td class="fw-bold fs-5">HARGA POKOK PRODUKSI (HPP)</td>
                            <td class="text-end fw-bold fs-5" id="grandTotal">Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> Update BOM
            </button>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>
</div>


<!-- Data dari Backend -->
<script>
const bahanBakuData = @json($bahanBakus);
const satuanData = @json(\App\Models\Satuan::all());
const prosesProduksiData = @json(\App\Models\ProsesProduksi::with('prosesBops.komponenBop')->active()->get());
const existingProses = @json($bom->proses ?? []);

// Konversi satuan
const konversi = {
    'KG': 1, 'G': 1000, 'GR': 1000, 'GRAM': 1000,
    'HG': 10, 'DAG': 100, 'ONS': 10,
    'L': 1, 'LITER': 1, 'ML': 1000,
    'PCS': 1, 'BUAH': 1, 'BTL': 1, 'BOTOL': 1
};

function formatRupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
}

// ========== BAHAN BAKU ==========
function hitungBarisBahan(row) {
    const bahanSelect = row.querySelector('.bahan-select');
    const jumlahInput = row.querySelector('.jumlah-input');
    const satuanSelect = row.querySelector('.satuan-select');
    const hargaDisplay = row.querySelector('.harga-display');
    const subtotalDisplay = row.querySelector('.subtotal-display');
    
    const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
    
    if (!selectedOption.value) {
        hargaDisplay.textContent = 'Rp 0';
        subtotalDisplay.textContent = 'Rp 0';
        hitungTotalBBB();
        return;
    }
    
    const hargaPerSatuanUtama = parseFloat(selectedOption.dataset.harga) || 0;
    const satuanUtama = (selectedOption.dataset.satuan || 'KG').toUpperCase();
    const jumlah = parseFloat(jumlahInput.value) || 0;
    const satuan = satuanSelect.value.toUpperCase();
    
    let hargaPerSatuan = hargaPerSatuanUtama;
    if (satuan !== satuanUtama) {
        const faktorUtama = konversi[satuanUtama] || 1;
        const faktorPilihan = konversi[satuan] || 1;
        hargaPerSatuan = hargaPerSatuanUtama / (faktorPilihan / faktorUtama);
    }
    
    const subtotal = hargaPerSatuan * jumlah;
    
    hargaDisplay.textContent = formatRupiah(hargaPerSatuan);
    subtotalDisplay.textContent = formatRupiah(subtotal);
    
    hitungTotalBBB();
}

function hitungTotalBBB() {
    let totalBBB = 0;
    document.querySelectorAll('.bom-row').forEach(row => {
        const subtotalText = row.querySelector('.subtotal-display').textContent;
        totalBBB += parseFloat(subtotalText.replace(/[^0-9]/g, '')) || 0;
    });
    
    document.getElementById('totalBBB').textContent = formatRupiah(totalBBB);
    document.getElementById('summaryBBB').textContent = formatRupiah(totalBBB);
    hitungHPP();
}

function attachBahanEvents(row) {
    const bahanSelect = row.querySelector('.bahan-select');
    const jumlahInput = row.querySelector('.jumlah-input');
    const satuanSelect = row.querySelector('.satuan-select');
    const btnHapus = row.querySelector('.btn-hapus-bahan');
    
    bahanSelect.addEventListener('change', () => {
        const selectedOption = bahanSelect.options[bahanSelect.selectedIndex];
        if (selectedOption.value) {
            satuanSelect.value = selectedOption.dataset.satuan || 'KG';
        }
        hitungBarisBahan(row);
    });
    
    jumlahInput.addEventListener('input', () => hitungBarisBahan(row));
    satuanSelect.addEventListener('change', () => hitungBarisBahan(row));
    
    btnHapus.addEventListener('click', () => {
        if (document.querySelectorAll('.bom-row').length <= 1) {
            alert('Minimal harus ada 1 bahan baku!');
            return;
        }
        row.remove();
        hitungTotalBBB();
    });
}

// ========== PROSES PRODUKSI ==========
let prosesCounter = 0;

function tambahBarisProses(prosesId = '', durasi = 1) {
    prosesCounter++;
    const tbody = document.getElementById('prosesTableBody');
    
    let prosesOptions = '<option value="">-- Pilih Proses --</option>';
    prosesProduksiData.forEach(p => {
        const selected = p.id == prosesId ? 'selected' : '';
        prosesOptions += `<option value="${p.id}" 
            data-tarif-btkl="${p.tarif_btkl}" 
            data-satuan="${p.satuan_btkl}"
            data-bops='${JSON.stringify(p.proses_bops || [])}'
            ${selected}>${p.nama_proses} (Rp ${parseInt(p.tarif_btkl).toLocaleString('id-ID')}/${p.satuan_btkl})</option>`;
    });
    
    const row = document.createElement('tr');
    row.className = 'proses-row';
    row.innerHTML = `
        <td class="text-center">${prosesCounter}</td>
        <td>
            <select name="proses_id[]" class="form-select form-select-sm proses-select" required>
                ${prosesOptions}
            </select>
            <input type="hidden" name="proses_urutan[]" value="${prosesCounter}">
        </td>
        <td>
            <input type="number" name="proses_durasi[]" class="form-control form-control-sm durasi-input" 
                value="${durasi}" min="0.01" step="0.01" required>
        </td>
        <td class="satuan-proses text-center">-</td>
        <td class="biaya-btkl text-end">Rp 0</td>
        <td class="biaya-bop text-end">Rp 0</td>
        <td class="total-proses text-end fw-bold">Rp 0</td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm btn-hapus-proses">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    attachProsesEvents(row);
    
    if (prosesId) {
        hitungBarisProses(row);
    }
}

function hitungBarisProses(row) {
    const prosesSelect = row.querySelector('.proses-select');
    const durasiInput = row.querySelector('.durasi-input');
    const satuanDisplay = row.querySelector('.satuan-proses');
    const biayaBtklDisplay = row.querySelector('.biaya-btkl');
    const biayaBopDisplay = row.querySelector('.biaya-bop');
    const totalDisplay = row.querySelector('.total-proses');
    
    const selectedOption = prosesSelect.options[prosesSelect.selectedIndex];
    
    if (!selectedOption.value) {
        satuanDisplay.textContent = '-';
        biayaBtklDisplay.textContent = 'Rp 0';
        biayaBopDisplay.textContent = 'Rp 0';
        totalDisplay.textContent = 'Rp 0';
        hitungTotalProses();
        return;
    }
    
    const tarifBtkl = parseFloat(selectedOption.dataset.tarifBtkl) || 0;
    const satuan = selectedOption.dataset.satuan || 'jam';
    const durasi = parseFloat(durasiInput.value) || 0;
    
    // Hitung BTKL
    const biayaBtkl = durasi * tarifBtkl;
    
    // Hitung BOP dari komponen default
    let biayaBop = 0;
    try {
        const bops = JSON.parse(selectedOption.dataset.bops || '[]');
        bops.forEach(bop => {
            const kuantitas = (bop.kuantitas_default || 0) * durasi;
            const tarif = bop.komponen_bop?.tarif_per_satuan || 0;
            biayaBop += kuantitas * tarif;
        });
    } catch (e) {
        console.error('Error parsing BOP data:', e);
    }
    
    satuanDisplay.textContent = satuan;
    biayaBtklDisplay.textContent = formatRupiah(biayaBtkl);
    biayaBopDisplay.textContent = formatRupiah(biayaBop);
    totalDisplay.textContent = formatRupiah(biayaBtkl + biayaBop);
    
    hitungTotalProses();
}

function hitungTotalProses() {
    let totalBtkl = 0;
    let totalBop = 0;
    
    document.querySelectorAll('.proses-row').forEach(row => {
        const btklText = row.querySelector('.biaya-btkl').textContent;
        const bopText = row.querySelector('.biaya-bop').textContent;
        totalBtkl += parseFloat(btklText.replace(/[^0-9]/g, '')) || 0;
        totalBop += parseFloat(bopText.replace(/[^0-9]/g, '')) || 0;
    });
    
    document.getElementById('totalBTKL').textContent = formatRupiah(totalBtkl);
    document.getElementById('totalBOP').textContent = formatRupiah(totalBop);
    document.getElementById('summaryBTKL').textContent = formatRupiah(totalBtkl);
    document.getElementById('summaryBOP').textContent = formatRupiah(totalBop);
    
    hitungHPP();
}

function attachProsesEvents(row) {
    const prosesSelect = row.querySelector('.proses-select');
    const durasiInput = row.querySelector('.durasi-input');
    const btnHapus = row.querySelector('.btn-hapus-proses');
    
    prosesSelect.addEventListener('change', () => hitungBarisProses(row));
    durasiInput.addEventListener('input', () => hitungBarisProses(row));
    
    btnHapus.addEventListener('click', () => {
        row.remove();
        // Renumber
        prosesCounter = 0;
        document.querySelectorAll('.proses-row').forEach(r => {
            prosesCounter++;
            r.querySelector('td:first-child').textContent = prosesCounter;
            r.querySelector('input[name="proses_urutan[]"]').value = prosesCounter;
        });
        hitungTotalProses();
    });
}

// ========== HPP ==========
function hitungHPP() {
    const bbbText = document.getElementById('summaryBBB').textContent;
    const btklText = document.getElementById('summaryBTKL').textContent;
    const bopText = document.getElementById('summaryBOP').textContent;
    
    const bbb = parseFloat(bbbText.replace(/[^0-9]/g, '')) || 0;
    const btkl = parseFloat(btklText.replace(/[^0-9]/g, '')) || 0;
    const bop = parseFloat(bopText.replace(/[^0-9]/g, '')) || 0;
    
    const hpp = bbb + btkl + bop;
    document.getElementById('grandTotal').textContent = formatRupiah(hpp);
}

// ========== INIT ==========
document.addEventListener('DOMContentLoaded', () => {
    // Init bahan baku
    document.querySelectorAll('.bom-row').forEach(row => {
        attachBahanEvents(row);
        hitungBarisBahan(row);
    });
    
    // Tambah bahan baku
    document.getElementById('btnTambahBahan').addEventListener('click', () => {
        const tbody = document.getElementById('bomTableBody');
        const firstRow = tbody.querySelector('.bom-row');
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelector('.bahan-select').value = '';
        newRow.querySelector('.jumlah-input').value = '1';
        newRow.querySelector('.harga-display').textContent = 'Rp 0';
        newRow.querySelector('.subtotal-display').textContent = 'Rp 0';
        
        tbody.appendChild(newRow);
        attachBahanEvents(newRow);
    });
    
    // Tambah proses
    document.getElementById('btnTambahProses').addEventListener('click', () => {
        tambahBarisProses();
    });
    
    // Load existing proses atau tambah 1 default
    if (existingProses && existingProses.length > 0) {
        existingProses.forEach(p => {
            tambahBarisProses(p.proses_produksi_id, p.durasi);
        });
    } else if (prosesProduksiData.length > 0) {
        tambahBarisProses();
    }
});
</script>
@endsection

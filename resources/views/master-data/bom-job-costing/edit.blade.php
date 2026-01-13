@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white"><i class="bi bi-pencil me-2"></i>Edit HPP - {{ $produk->nama_produk }}</h3>
            <small class="text-light">Kode: {{ $produk->kode_produk }}</small>
        </div>
        <a href="{{ route('master-data.bom-job-costing.show', $produk->id) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('master-data.bom-job-costing.update', $produk->id) }}" method="POST" id="bomForm">
        @csrf
        @method('PUT')
        
        <!-- Info Produk -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-box me-2"></i>Informasi Produk</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-white">Produk</label>
                        <input type="text" class="form-control" value="{{ $produk->nama_produk }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-white">Jumlah Produk <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_produk" id="jumlahProduk" class="form-control" required min="1" value="{{ old('jumlah_produk', $bom->jumlah_produk) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-white">HPP/Unit Saat Ini</label>
                        <input type="text" class="form-control" value="Rp {{ number_format($bom->hpp_per_unit, 0, ',', '.') }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. BBB -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>1. Biaya Bahan Baku (BBB)</h5></div>
            <div class="card-body">
                <table class="table table-bordered" style="color: black !important;"><thead class="table-light"><tr><th width="35%">Bahan Baku</th><th width="15%">Jumlah</th><th width="10%">Satuan</th><th width="15%">Harga</th><th width="15%">Subtotal</th><th width="10%">Aksi</th></tr></thead>
                    <tbody id="bbbBody">
                        @foreach($bom->detailBBB as $d)
                        <tr class="bbb-row">
                            <td><select name="bbb_id[]" class="form-select form-select-sm bbb-select"><option value="">-- Pilih --</option>@foreach($bahanBakus as $bb)<option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan ?? 0 }}" data-satuan="{{ $bb->satuanRelation->kode ?? 'KG' }}" {{ $d->bahan_baku_id == $bb->id ? 'selected' : '' }}>{{ $bb->nama_bahan }}</option>@endforeach</select></td>
                            <td><input type="number" name="bbb_jumlah[]" class="form-control form-control-sm bbb-jumlah" value="{{ $d->jumlah }}" min="0" step="0.01"></td>
                            <td><select name="bbb_satuan[]" class="form-select form-select-sm bbb-satuan">
                                    <option value="{{ $d->satuan }}" selected>{{ $d->satuan }}</option>
                                    @foreach($satuans as $satuan)
                                        @if($satuan->kode != $d->satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                        @endif
                                    @endforeach
                                </select></td>
                            <td class="bbb-harga text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                            <td class="bbb-subtotal text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bbb"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                        @if($bom->detailBBB->isEmpty())
                        <tr class="bbb-row">
                            <td><select name="bbb_id[]" class="form-select form-select-sm bbb-select"><option value="">-- Pilih --</option>@foreach($bahanBakus as $bb)<option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan ?? 0 }}" data-satuan="{{ $bb->satuanRelation->kode ?? 'KG' }}">{{ $bb->nama_bahan }}</option>@endforeach</select></td>
                            <td><input type="number" name="bbb_jumlah[]" class="form-control form-control-sm bbb-jumlah" value="0" min="0" step="0.01"></td>
                            <td><select name="bbb_satuan[]" class="form-select form-select-sm bbb-satuan">
                                    <option value="">-- Satuan --</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                    @endforeach
                                </select></td>
                            <td class="bbb-harga text-end">Rp 0</td>
                            <td class="bbb-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bbb"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot><tr class="table-warning"><td colspan="4" class="text-end fw-bold text-dark">Total BBB</td><td class="text-end fw-bold text-dark" id="totalBBB">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td><td></td></tr></tfoot>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambahBBB"><i class="bi bi-plus"></i> Tambah</button>
            </div>
        </div>

        <!-- 2. BTKL -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-people me-2"></i>2. Biaya Tenaga Kerja Langsung (BTKL)</h5></div>
            <div class="card-body">
                <table class="table table-bordered" style="color: black !important;"><thead class="table-light"><tr><th width="35%">Tenaga Kerja</th><th width="15%">Durasi (Jam)</th><th width="15%">Tarif/Jam</th><th width="15%">Subtotal</th><th width="10%">Aksi</th></tr></thead>
                    <tbody id="btklBody">
                        @foreach($bom->detailBTKL as $d)
                        <tr class="btkl-row">
                            <td><select name="btkl_id[]" class="form-select form-select-sm btkl-select"><option value="">-- Pilih --</option>@foreach($btkls as $btkl)<option value="{{ $btkl->id }}" data-tarif="{{ $btkl->tarif_per_jam ?? 0 }}" {{ $d->btkl_id == $btkl->id ? 'selected' : '' }}>{{ $btkl->jabatan->nama ?? $btkl->kode_proses }}</option>@endforeach</select></td>
                            <td><input type="number" name="btkl_durasi[]" class="form-control form-control-sm btkl-durasi" value="{{ $d->durasi }}" min="0" step="0.01"></td>
                            <td class="btkl-tarif text-end">Rp {{ number_format($d->tarif_per_jam, 0, ',', '.') }}</td>
                            <td class="btkl-subtotal text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-btkl"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                        @if($bom->detailBTKL->isEmpty())
                        <tr class="btkl-row">
                            <td><select name="btkl_id[]" class="form-select form-select-sm btkl-select"><option value="">-- Pilih --</option>@foreach($btkls as $btkl)<option value="{{ $btkl->id }}" data-tarif="{{ $btkl->tarif_per_jam ?? 0 }}">{{ $btkl->jabatan->nama ?? $btkl->kode_proses }}</option>@endforeach</select></td>
                            <td><input type="number" name="btkl_durasi[]" class="form-control form-control-sm btkl-durasi" value="0" min="0" step="0.01"></td>
                            <td class="btkl-tarif text-end">Rp 0</td>
                            <td class="btkl-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-btkl"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot><tr class="table-success"><td colspan="3" class="text-end fw-bold text-dark">Total BTKL</td><td class="text-end fw-bold text-dark" id="totalBTKL">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td><td></td></tr></tfoot>
                </table>
                <button type="button" class="btn btn-outline-success btn-sm" id="btnTambahBTKL"><i class="bi bi-plus"></i> Tambah</button>
            </div>
        </div>

        <!-- 3. Bahan Penolong -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="bi bi-droplet me-2"></i>3. Bahan Penolong/Pendukung</h5></div>
            <div class="card-body">
                <table class="table table-bordered" style="color: black !important;"><thead class="table-light"><tr><th width="35%">Bahan</th><th width="15%">Jumlah</th><th width="10%">Satuan</th><th width="15%">Harga</th><th width="15%">Subtotal</th><th width="10%">Aksi</th></tr></thead>
                    <tbody id="bpBody">
                        @foreach($bom->detailBahanPendukung as $d)
                        <tr class="bp-row">
                            <td><select name="bp_id[]" class="form-select form-select-sm bp-select"><option value="">-- Pilih --</option>@foreach($bahanPendukungs as $bp)<option value="{{ $bp->id }}" data-harga="{{ $bp->harga_satuan ?? 0 }}" data-satuan="{{ $bp->satuanRelation->kode ?? 'PCS' }}" {{ $d->bahan_pendukung_id == $bp->id ? 'selected' : '' }}>{{ $bp->nama_bahan }}</option>@endforeach</select></td>
                            <td><input type="number" name="bp_jumlah[]" class="form-control form-control-sm bp-jumlah" value="{{ $d->jumlah }}" min="0" step="0.01"></td>
                            <td><select name="bp_satuan[]" class="form-select form-select-sm bp-satuan">
                                    <option value="{{ $d->satuan }}" selected>{{ $d->satuan }}</option>
                                    @foreach($satuans as $satuan)
                                        @if($satuan->kode != $d->satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                        @endif
                                    @endforeach
                                </select></td>
                            <td class="bp-harga text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                            <td class="bp-subtotal text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bp"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                        @if($bom->detailBahanPendukung->isEmpty())
                        <tr class="bp-row">
                            <td><select name="bp_id[]" class="form-select form-select-sm bp-select"><option value="">-- Pilih --</option>@foreach($bahanPendukungs as $bp)<option value="{{ $bp->id }}" data-harga="{{ $bp->harga_satuan ?? 0 }}" data-satuan="{{ $bp->satuanRelation->kode ?? 'PCS' }}">{{ $bp->nama_bahan }}</option>@endforeach</select></td>
                            <td><input type="number" name="bp_jumlah[]" class="form-control form-control-sm bp-jumlah" value="0" min="0" step="0.01"></td>
                            <td><select name="bp_satuan[]" class="form-select form-select-sm bp-satuan">
                                    <option value="">-- Satuan --</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                    @endforeach
                                </select></td>
                            <td class="bp-harga text-end">Rp 0</td>
                            <td class="bp-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bp"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot><tr class="table-info"><td colspan="4" class="text-end fw-bold text-dark">Total Bahan Penolong</td><td class="text-end fw-bold text-dark" id="totalBP">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td><td></td></tr></tfoot>
                </table>
                <button type="button" class="btn btn-outline-info btn-sm" id="btnTambahBP"><i class="bi bi-plus"></i> Tambah</button>
            </div>
        </div>

        <!-- Ringkasan Biaya Bahan -->
        <div class="card shadow-sm mb-3 border-dark">
            <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Biaya Bahan</h5></div>
            <div class="card-body">
                <table class="table table-bordered" style="color: black !important;">
                    <tr><td width="60%">Total Biaya Bahan Baku (BBB)</td><td class="text-end fw-bold" id="summaryBBB">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td></tr>
                    <tr><td>Total Biaya Tenaga Kerja Langsung (BTKL)</td><td class="text-end fw-bold" id="summaryBTKL">Rp {{ number_format($bom->total_btkl, 0, ',', '.') }}</td></tr>
                    <tr><td>Total Bahan Penolong</td><td class="text-end fw-bold" id="summaryBP">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr>
                    <tr class="table-info"><td class="fw-bold text-dark">Harga BOM</td><td class="text-end fw-bold text-dark" id="hargaBOM">Rp {{ number_format($bom->total_bbb + $bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr>
                    <tr class="table-primary"><td class="fw-bold fs-5 text-dark">TOTAL BIAYA BAHAN PER PCS</td><td class="text-end fw-bold fs-5 text-dark" id="totalBiayaBahan">Rp {{ number_format(($bom->total_bbb + $bom->total_bahan_pendukung) / max($bom->jumlah_produk, 1), 0, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i> Update HPP</button>
            <a href="{{ route('master-data.bom-job-costing.show', $produk->id) }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>
</div>

<script>
function formatRupiah(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function parseRupiah(s) { return parseFloat(String(s).replace(/[^0-9]/g, '')) || 0; }
function getJumlahProduk() { return parseInt(document.getElementById('jumlahProduk').value) || 1; }

// Convert price from base unit to selected unit
function convertPrice(basePrice, baseUnit, targetUnit) {
    if (baseUnit === targetUnit) return basePrice;
    
    // Define conversion factors (how many smaller units in one larger unit)
    const conversions = {
        'Berat': { 'KG': 1000, 'G': 1, 'MG': 0.001 },
        'Volume': { 'LTR': 1000, 'ML': 1 },
        'Unit': { 'PCS': 1, 'PACK': 10, 'BOX': 100 },
        'Energi': { 'WTT': 1 },
        'Ukuran': { 'SDT': 1 }
    };
    
    // Get category from data or determine by unit
    let category = arguments[3] || 'Unit';
    
    // Auto-detect category if not provided
    if (!arguments[3]) {
        const allUnits = Object.keys(conversions).flatMap(k => Object.keys(conversions[k]));
        if (allUnits.includes(baseUnit)) {
            for (const [cat, units] of Object.entries(conversions)) {
                if (Object.keys(units).includes(baseUnit)) {
                    category = cat;
                    break;
                }
            }
        }
    }
    
    const categoryConversions = conversions[category] || {};
    
    // Convert to smallest unit first, then to target unit
    const baseToSmallest = categoryConversions[baseUnit] || 1;
    const targetToSmallest = categoryConversions[targetUnit] || 1;
    
    // Convert base price to smallest unit, then to target unit
    const priceInSmallestUnit = basePrice / baseToSmallest;
    const convertedPrice = priceInSmallestUnit * targetToSmallest;
    
    return convertedPrice;
}

function hitungBBB(row) {
    const sel = row.querySelector('.bbb-select'), jml = row.querySelector('.bbb-jumlah'), sat = row.querySelector('.bbb-satuan');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { 
        row.querySelector('.bbb-harga').textContent = 'Rp 0'; 
        row.querySelector('.bbb-subtotal').textContent = 'Rp 0'; 
        hitungTotalBBB(); 
        return; 
    }
    
    const baseHarga = parseFloat(opt.dataset.harga) || 0;
    const baseUnit = opt.dataset.satuan || 'KG';
    const selectedUnit = sat.value;
    const category = opt.dataset.category || 'Berat';
    
    // Set satuan default dari data, tapi biarkan user ubah
    if (sat.value === '') {
        sat.value = baseUnit;
    }
    
    // Convert price to selected unit
    const convertedHarga = convertPrice(baseHarga, baseUnit, selectedUnit, category);
    
    row.querySelector('.bbb-harga').textContent = formatRupiah(convertedHarga);
    row.querySelector('.bbb-subtotal').textContent = formatRupiah((parseFloat(jml.value) || 0) * convertedHarga);
    hitungTotalBBB();
}
function hitungTotalBBB() {
    let t = 0; document.querySelectorAll('.bbb-row').forEach(r => t += parseRupiah(r.querySelector('.bbb-subtotal').textContent));
    document.getElementById('totalBBB').textContent = formatRupiah(t);
    document.getElementById('summaryBBB').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBBB(row) {
    row.querySelector('.bbb-select').addEventListener('change', () => hitungBBB(row));
    row.querySelector('.bbb-jumlah').addEventListener('input', () => hitungBBB(row));
    row.querySelector('.bbb-satuan').addEventListener('change', () => hitungBBB(row));
    row.querySelector('.btn-hapus-bbb').addEventListener('click', () => { row.remove(); hitungTotalBBB(); });
}

function hitungBTKL(row) {
    const sel = row.querySelector('.btkl-select'), dur = row.querySelector('.btkl-durasi');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { row.querySelector('.btkl-tarif').textContent = 'Rp 0'; row.querySelector('.btkl-subtotal').textContent = 'Rp 0'; hitungTotalBTKL(); return; }
    const tarif = parseFloat(opt.dataset.tarif) || 0;
    row.querySelector('.btkl-tarif').textContent = formatRupiah(tarif);
    row.querySelector('.btkl-subtotal').textContent = formatRupiah((parseFloat(dur.value) || 0) * tarif);
    hitungTotalBTKL();
}
function hitungTotalBTKL() {
    let t = 0; document.querySelectorAll('.btkl-row').forEach(r => t += parseRupiah(r.querySelector('.btkl-subtotal').textContent));
    document.getElementById('totalBTKL').textContent = formatRupiah(t);
    document.getElementById('summaryBTKL').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBTKL(row) {
    row.querySelector('.btkl-select').addEventListener('change', () => hitungBTKL(row));
    row.querySelector('.btkl-durasi').addEventListener('input', () => hitungBTKL(row));
    row.querySelector('.btn-hapus-btkl').addEventListener('click', () => { row.remove(); hitungTotalBTKL(); });
}

function hitungBP(row) {
    const sel = row.querySelector('.bp-select'), jml = row.querySelector('.bp-jumlah'), sat = row.querySelector('.bp-satuan');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { 
        row.querySelector('.bp-harga').textContent = 'Rp 0'; 
        row.querySelector('.bp-subtotal').textContent = 'Rp 0'; 
        hitungTotalBP(); 
        return; 
    }
    
    const baseHarga = parseFloat(opt.dataset.harga) || 0;
    const baseUnit = opt.dataset.satuan || 'PCS';
    const selectedUnit = sat.value;
    
    // Set satuan default dari data, tapi biarkan user ubah
    if (sat.value === '') {
        sat.value = baseUnit;
    }
    
    // Convert price to selected unit (auto-detect category)
    const convertedHarga = convertPrice(baseHarga, baseUnit, selectedUnit);
    
    row.querySelector('.bp-harga').textContent = formatRupiah(convertedHarga);
    row.querySelector('.bp-subtotal').textContent = formatRupiah((parseFloat(jml.value) || 0) * convertedHarga);
    hitungTotalBP();
}
function hitungTotalBP() {
    let t = 0; document.querySelectorAll('.bp-row').forEach(r => t += parseRupiah(r.querySelector('.bp-subtotal').textContent));
    document.getElementById('totalBP').textContent = formatRupiah(t);
    document.getElementById('summaryBP').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBP(row) {
    row.querySelector('.bp-select').addEventListener('change', () => hitungBP(row));
    row.querySelector('.bp-jumlah').addEventListener('input', () => hitungBP(row));
    row.querySelector('.bp-satuan').addEventListener('change', () => hitungBP(row));
    row.querySelector('.btn-hapus-bp').addEventListener('click', () => { row.remove(); hitungTotalBP(); });
}

function hitungBOP(row) {
    const sel = row.querySelector('.bop-select'), jml = row.querySelector('.bop-jumlah'), tarif = row.querySelector('.bop-tarif');
    const opt = sel.options[sel.selectedIndex];
    if (opt.value && !tarif.value) tarif.value = opt.dataset.tarif || 0;
    row.querySelector('.bop-subtotal').textContent = formatRupiah((parseFloat(jml.value) || 0) * (parseFloat(tarif.value) || 0));
    hitungTotalBOP();
}
function hitungTotalBOP() {
    let t = 0; document.querySelectorAll('.bop-row').forEach(r => t += parseRupiah(r.querySelector('.bop-subtotal').textContent));
    document.getElementById('totalBOP').textContent = formatRupiah(t);
    document.getElementById('summaryBOP').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBOP(row) {
    row.querySelector('.bop-select').addEventListener('change', () => hitungBOP(row));
    row.querySelector('.bop-jumlah').addEventListener('input', () => hitungBOP(row));
    row.querySelector('.bop-tarif').addEventListener('input', () => hitungBOP(row));
    row.querySelector('.btn-hapus-bop').addEventListener('click', () => { row.remove(); hitungTotalBOP(); });
}

function hitungHPP() {
    const bbb = parseRupiah(document.getElementById('summaryBBB').textContent);
    const btkl = parseRupiah(document.getElementById('summaryBTKL').textContent);
    const bp = parseRupiah(document.getElementById('summaryBP').textContent);
    const total = bbb + btkl + bp;
    const jumlahProduk = getJumlahProduk();
    
    // Update Harga BOM (same as total material cost)
    document.getElementById('hargaBOM').textContent = formatRupiah(total);
    
    // Update Total Biaya Bahan per unit
    const biayaPerUnit = jumlahProduk > 0 ? total / jumlahProduk : 0;
    document.getElementById('totalBiayaBahan').textContent = formatRupiah(biayaPerUnit);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bbb-row').forEach(r => attachBBB(r));
    document.querySelectorAll('.btkl-row').forEach(r => attachBTKL(r));
    document.querySelectorAll('.bp-row').forEach(r => attachBP(r));
    document.getElementById('jumlahProduk').addEventListener('input', hitungHPP);
    
    document.getElementById('btnTambahBBB').addEventListener('click', () => {
        const row = document.querySelector('.bbb-row').cloneNode(true);
        row.querySelector('.bbb-select').value = '';
        row.querySelector('.bbb-jumlah').value = '0';
        row.querySelector('.bbb-harga').textContent = 'Rp 0';
        row.querySelector('.bbb-subtotal').textContent = 'Rp 0';
        document.getElementById('bbbBody').appendChild(row);
        attachBBB(row);
    });
    document.getElementById('btnTambahBTKL').addEventListener('click', () => {
        const row = document.querySelector('.btkl-row').cloneNode(true);
        row.querySelector('.btkl-select').value = '';
        row.querySelector('.btkl-durasi').value = '0';
        row.querySelector('.btkl-tarif').textContent = 'Rp 0';
        row.querySelector('.btkl-subtotal').textContent = 'Rp 0';
        document.getElementById('btklBody').appendChild(row);
        attachBTKL(row);
    });
    document.getElementById('btnTambahBP').addEventListener('click', () => {
        const row = document.querySelector('.bp-row').cloneNode(true);
        row.querySelector('.bp-select').value = '';
        row.querySelector('.bp-jumlah').value = '0';
        row.querySelector('.bp-harga').textContent = 'Rp 0';
        row.querySelector('.bp-subtotal').textContent = 'Rp 0';
        document.getElementById('bpBody').appendChild(row);
        attachBP(row);
    });
});
</script>
@endsection

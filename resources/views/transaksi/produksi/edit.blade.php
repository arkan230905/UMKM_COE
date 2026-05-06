@extends('layouts.app')
@section('title', 'Edit Produksi')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">✏️ Edit Rencana Produksi — {{ $produksi->produk->nama_produk }}</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('transaksi.produksi.update', $produksi->id) }}">
                @csrf @method('PUT')

                {{-- Form Input --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">🏷️ Produk</label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $prod)
                                <option value="{{ $prod->id }}"
                                    data-coa-persediaan="{{ $prod->coa_persediaan_id ?? '' }}"
                                    {{ $prod->id == $produksi->produk_id ? 'selected' : '' }}>
                                    {{ $prod->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">📋 Akun COA Persediaan Barang Jadi</label>
                        <select name="coa_persediaan_barang_jadi_id" id="coa_persediaan_barang_jadi_id" class="form-select form-select-lg">
                            <option value="">-- Pilih Akun COA --</option>
                            @foreach(\App\Models\Coa::where('kode_akun', 'like', '11%')->orWhere('nama_akun', 'like', '%Persediaan%')->orWhere('nama_akun', 'like', '%Barang Jadi%')->orderBy('kode_akun')->get() as $coa)
                                <option value="{{ $coa->id }}" {{ $coa->id == $produksi->coa_persediaan_barang_jadi_id ? 'selected' : '' }}>
                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Data Produksi Bulanan --}}
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white"><h5 class="mb-0">📊 Data Produksi Bulanan</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📦 Jumlah Produksi dalam Sebulan</label>
                                <input type="number" name="jumlah_produksi_bulanan" id="jumlah_produksi_bulanan"
                                    step="1" min="1" class="form-control form-control-lg" required
                                    value="{{ (int)$produksi->jumlah_produksi_bulanan }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📅 Hari Memproduksi dalam Sebulan</label>
                                <input type="number" name="hari_produksi_bulanan" id="hari_produksi_bulanan"
                                    min="1" max="31" class="form-control form-control-lg" required
                                    value="{{ $produksi->hari_produksi_bulanan }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">📈 Jumlah Produksi Per Hari</label>
                                <input type="number" name="qty_produksi" id="qty_produksi"
                                    step="1" class="form-control form-control-lg" readonly
                                    value="{{ (int)$produksi->qty_produksi }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BOM Info (sama seperti create, diisi JS) --}}
                <div class="card bg-light mb-4" id="bom-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Informasi Harga Pokok Produksi Produk (Per Hari)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <strong>Harga Pokok Produk Per Hari:</strong> <span id="harga-pokok">Rp 0</span>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3" id="biaya-bahan-section" style="display: none;">
                            <div class="card-header bg-success text-white"><h6 class="mb-0">Biaya Bahan Per Produk</h6></div>
                            <div class="card-body">
                                <div class="col-md-12"><h6 class="text-success mb-3">Bahan Baku</h6><div id="bahan-baku-list"></div></div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <h5 class="mb-0">Total</h5><h5 class="mb-0 text-success" id="total-biaya-bahan">Rp 0</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3" id="btkl-section" style="display: none;">
                            <div class="card-header bg-info text-white"><h6 class="mb-0">Biaya Tenaga Kerja Langsung (BTKL)</h6></div>
                            <div class="card-body">
                                <table class="table table-sm"><thead><tr><th>Proses & Kapasitas</th><th>Nominal Biaya & Tarif</th><th>Total</th></tr></thead><tbody id="btkl-list"></tbody></table>
                            </div>
                        </div>
                        <div class="card mb-3" id="bop-section" style="display: none;">
                            <div class="card-header bg-warning text-dark"><h6 class="mb-0">Biaya Overhead Pabrik (BOP)</h6></div>
                            <div class="card-body">
                                <table class="table table-sm"><thead><tr><th>Proses</th><th>Nominal Biaya</th><th>Total</th></tr></thead><tbody id="bop-list"></tbody></table>
                            </div>
                        </div>
                        <div class="card" id="total-section" style="display: none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Total Biaya Produksi Per Hari</h4>
                                    <h4 class="mb-0 text-primary" id="total-keseluruhan">Rp 0</h4>
                                </div>
                            </div>
                        </div>

                        {{-- Preview Jurnal --}}
                        <div class="card mt-3" id="jurnal-preview-section" style="display: none;">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-book me-2"></i>Preview Jurnal Akuntansi</h6>
                                <small class="text-white-50">Jurnal yang akan dibuat saat produksi diproses</small>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0" style="table-layout:fixed; width:100%; font-size:12px;">
                                    <colgroup><col style="width:28%"><col style="width:22%"><col style="width:8%"><col style="width:21%"><col style="width:21%"></colgroup>
                                    <thead><tr class="table-secondary"><th class="py-2 ps-3">Keterangan</th><th class="py-2">Akun</th><th class="py-2 text-center">Ref</th><th class="py-2 text-end pe-3">Debit</th><th class="py-2 text-end pe-3">Kredit</th></tr></thead>
                                    <tbody>
                                        <tr class="table-primary"><td colspan="5" class="text-center fw-bold py-2">Produksi</td></tr>
                                        <tbody id="jurnal-produksi-body"></tbody>
                                        <tr class="table-info"><td colspan="5" class="text-center fw-bold py-2">BTKL WIP</td></tr>
                                        <tbody id="jurnal-btkl-body"></tbody>
                                        <tr class="table-warning"><td colspan="5" class="text-center fw-bold py-2">BOP WIP</td></tr>
                                        <tbody id="jurnal-bop-body"></tbody>
                                        <tr class="table-success"><td colspan="5" class="text-center fw-bold py-2">Sudah selesai produksi</td></tr>
                                        <tbody id="jurnal-wip-barangjadi-body"></tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.produksi.show', $produksi->id) }}" class="btn btn-secondary btn-lg">✖️ Batal</a>
                    <button type="submit" class="btn btn-warning btn-lg" id="submit-btn">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reuse semua JS dari create dengan initial data --}}
@push('scripts')
<script>
let currentBomData = null;

// Data awal dari server
const initialProdukId = {{ $produksi->produk_id }};
const initialQty      = {{ (int)$produksi->qty_produksi }};
const initialBulanan  = {{ (int)$produksi->jumlah_produksi_bulanan }};
const initialHari     = {{ $produksi->hari_produksi_bulanan }};

function formatRupiah(amount) {
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
}

function calculateDailyProduction() {
    const jumlahBulanan = parseFloat(document.getElementById('jumlah_produksi_bulanan').value) || 0;
    const hariBulanan   = parseFloat(document.getElementById('hari_produksi_bulanan').value) || 0;
    if (jumlahBulanan > 0 && hariBulanan > 0) {
        document.getElementById('qty_produksi').value = Math.round(jumlahBulanan / hariBulanan);
        calculateCostBreakdown();
    } else {
        document.getElementById('qty_produksi').value = '';
        hideAllSections();
    }
}

function calculateCostBreakdown() {
    const produkId = document.getElementById('produk_id').value;
    const qty = parseFloat(document.getElementById('qty_produksi').value) || 0;
    if (!produkId || !currentBomData || qty <= 0) { hideAllSections(); return; }

    document.getElementById('bom-info').style.display = 'block';
    document.getElementById('biaya-bahan-section').style.display = 'block';
    document.getElementById('btkl-section').style.display = 'block';
    document.getElementById('bop-section').style.display = 'block';
    document.getElementById('total-section').style.display = 'block';

    let totalBiayaBahan = 0;
    const bahanBakuHtml = currentBomData.biaya_bahan.bahan_baku.map((bahan, index) => {
        const totalPerProduksi = bahan.harga_per_unit * qty;
        const totalQtyTerpakai = bahan.qty * qty;
        let stockReduction = totalQtyTerpakai, stockUnit = bahan.satuan;
        if (bahan.satuan !== bahan.satuan_bahan) { stockReduction = `${totalQtyTerpakai} ${bahan.satuan}`; stockUnit = bahan.satuan_bahan; }
        totalBiayaBahan += totalPerProduksi;
        return `<div class="mb-2"><strong>${index+1}. ${bahan.nama}:</strong> ${formatRupiah(totalPerProduksi)}<br><small class="text-muted">(${formatRupiah(bahan.harga_per_unit)} per produk × ${qty} qty)</small>${bahan.konversi_info ? `<br><small class="text-warning">${bahan.konversi_info}</small>` : ''}<br><small class="text-danger">Stok berkurang: ${stockReduction} ${stockUnit}</small></div>`;
    }).join('');
    document.getElementById('bahan-baku-list').innerHTML = bahanBakuHtml || '<p class="text-muted">Tidak ada data bahan baku</p>';
    document.getElementById('total-biaya-bahan').textContent = formatRupiah(totalBiayaBahan);

    let totalBtkl = 0;
    const btklHtml = currentBomData.btkl.map(btkl => {
        const totalPerProduksi = btkl.harga_per_unit * qty;
        totalBtkl += totalPerProduksi;
        const kapasitasPerJam = btkl.kapasitas_per_jam || 1;
        const jamDiperlukanExact = qty / kapasitasPerJam;
        const jamDiperlukan = jamDiperlukanExact <= 1 ? 1 : Math.ceil(jamDiperlukanExact);
        let warningClass = '', warningText = '';
        if (jamDiperlukan > 8) { warningClass = 'text-danger'; warningText = ' ⚠️ Melebihi jam kerja normal!'; }
        else if (jamDiperlukan > 6) { warningClass = 'text-warning'; warningText = ' ⚠️ Mendekati batas jam kerja'; }
        return `<tr><td><strong>${btkl.nama}</strong><br><small class="text-info">Kapasitas: ${kapasitasPerJam} unit/jam</small><br><small class="${warningClass||'text-success'}">Jam diperlukan: ${jamDiperlukan} jam${warningText}</small></td><td>${formatRupiah(btkl.harga_per_unit)}<br><small class="text-muted">(${formatRupiah(btkl.harga_per_unit)} per unit × ${qty})</small>${btkl.tarif_per_jam ? `<br><small class="text-info">Tarif: ${formatRupiah(btkl.tarif_per_jam)}/jam</small>` : ''}</td><td class="fw-bold">${formatRupiah(totalPerProduksi)}</td></tr>`;
    }).join('');
    document.getElementById('btkl-list').innerHTML = btklHtml ? btklHtml + `<tr class="table-info"><td colspan="2" class="fw-bold">Total BTKL</td><td class="fw-bold">${formatRupiah(totalBtkl)}</td></tr>` : '<tr><td colspan="3" class="text-center text-muted">Tidak ada data BTKL</td></tr>';

    let totalBop = 0;
    const bopHtml = currentBomData.bop.map(bop => {
        const totalPerProduksi = bop.harga_per_unit * qty;
        totalBop += totalPerProduksi;
        let komponenHtml = '';
        if (bop.komponen && bop.komponen.length > 0) {
            komponenHtml = bop.komponen.map(k => { const totalK = k.rate_per_hour * qty; return `<tr class="table-light"><td class="ps-4 text-muted"><small>↳ ${k.nama}</small></td><td><small class="text-muted">${formatRupiah(k.rate_per_hour)} × ${qty}</small></td><td class="text-end"><small>${formatRupiah(totalK)}</small></td></tr>`; }).join('');
        }
        return `<tr class="fw-bold"><td>${bop.nama}</td><td><small class="text-muted">${formatRupiah(bop.harga_per_unit)} per unit × ${qty}</small></td><td class="fw-bold text-end">${formatRupiah(totalPerProduksi)}</td></tr>${komponenHtml}`;
    }).join('');
    document.getElementById('bop-list').innerHTML = bopHtml ? bopHtml + `<tr class="table-warning"><td colspan="2" class="fw-bold">Total BOP</td><td class="fw-bold text-end">${formatRupiah(totalBop)}</td></tr>` : '<tr><td colspan="3" class="text-center text-muted">Tidak ada data BOP</td></tr>';

    const totalKeseluruhan = totalBiayaBahan + totalBtkl + totalBop;
    document.getElementById('harga-pokok').textContent = formatRupiah(totalKeseluruhan);
    document.getElementById('total-keseluruhan').textContent = formatRupiah(totalKeseluruhan);

    // Preview Jurnal
    document.getElementById('jurnal-preview-section').style.display = 'block';
    const bom = currentBomData;
    const bdpBbbKode  = bom.coa_bdp_bbb?.kode  ?? '1171'; const bdpBbbNama  = bom.coa_bdp_bbb?.nama  ?? 'BDP - BBB';
    const bdpBtklKode = bom.coa_bdp_btkl?.kode ?? '1172'; const bdpBtklNama = bom.coa_bdp_btkl?.nama ?? 'BDP - BTKL';
    const bdpBopKode  = bom.coa_bdp_bop?.kode  ?? '1173'; const bdpBopNama  = bom.coa_bdp_bop?.nama  ?? 'BDP - BOP';
    const rowD = (ket, akunKode, akunNama, val) => `<tr><td class="ps-3">${ket}</td><td><span class="badge bg-secondary me-1">${akunKode}</span>${akunNama}</td><td class="text-center text-muted" style="font-size:10px">${akunKode}</td><td class="text-end pe-3 fw-semibold text-nowrap">${formatRupiah(val)}</td><td class="text-end pe-3"></td></tr>`;
    const rowK = (ket, akunKode, akunNama, val) => `<tr><td class="ps-5 text-muted">${ket}</td><td><span class="badge bg-secondary me-1">${akunKode}</span>${akunNama}</td><td class="text-center text-muted" style="font-size:10px">${akunKode}</td><td class="text-end pe-3"></td><td class="text-end pe-3 text-nowrap">${formatRupiah(val)}</td></tr>`;
    const empty5 = `<tr><td colspan="5" class="text-center text-muted ps-3">-</td></tr>`;

    let j1 = '';
    bom.biaya_bahan.bahan_baku.forEach(b => { const total = b.harga_per_unit * qty; if (total <= 0) return; j1 += rowD('Barang dalam proses - BBB', bdpBbbKode, bdpBbbNama, total); j1 += rowK(b.nama, b.coa_persediaan_kode ?? '114', b.coa_persediaan_nama ?? b.nama, total); });
    document.getElementById('jurnal-produksi-body').innerHTML = j1 || empty5;

    let j2a = '';
    if (totalBtkl > 0) { j2a += rowD('Barang dalam proses - BTKL', bdpBtklKode, bdpBtklNama, totalBtkl); bom.btkl.forEach(b => { const total = b.harga_per_unit * qty; if (total <= 0) return; j2a += rowK(`Hutang Gaji — ${b.nama}`, b.coa_kredit_kode ?? '211', b.coa_kredit_nama ?? 'Hutang Gaji', total); }); }
    document.getElementById('jurnal-btkl-body').innerHTML = j2a || empty5;

    let j2b = '';
    bom.bop.forEach(bop => { const totalBopProses = bop.harga_per_unit * qty; if (totalBopProses <= 0) return; j2b += rowD('Barang dalam proses - BOP', bdpBopKode, bdpBopNama, totalBopProses); if (bop.komponen && bop.komponen.length > 0) { bop.komponen.forEach(k => { const totalK = k.rate_per_hour * qty; if (totalK <= 0) return; j2b += rowK(`${bop.nama} — ${k.nama}`, k.kredit_kode ?? k.coa_kode ?? '53', k.kredit_nama ?? k.coa_nama ?? 'BOP', totalK); }); } else { j2b += rowK(bop.nama, '53', 'BOP', totalBopProses); } });
    document.getElementById('jurnal-bop-body').innerHTML = j2b || empty5;

    let j3 = '';
    if (totalKeseluruhan > 0) { const coaSelect = document.getElementById('coa_persediaan_barang_jadi_id'); const coaOpt = coaSelect.options[coaSelect.selectedIndex]; const coaText = coaOpt?.text || 'Pers. Barang Jadi'; const coaParts = coaText.split(' - '); const coaKode = coaParts[0]?.trim() ?? '116'; const coaNama = coaParts.slice(1).join(' - ').trim() || coaText; j3 += rowD('Persediaan Barang Jadi', coaKode, coaNama, totalKeseluruhan); if (totalBiayaBahan > 0) j3 += rowK('BDP - BBB', bdpBbbKode, bdpBbbNama, totalBiayaBahan); if (totalBtkl > 0) j3 += rowK('BDP - BTKL', bdpBtklKode, bdpBtklNama, totalBtkl); if (totalBop > 0) j3 += rowK('BDP - BOP', bdpBopKode, bdpBopNama, totalBop); }
    document.getElementById('jurnal-wip-barangjadi-body').innerHTML = j3 || empty5;
    document.getElementById('coa_persediaan_barang_jadi_id').onchange = () => calculateCostBreakdown();
}

function hideAllSections() {
    ['bom-info','biaya-bahan-section','btkl-section','bop-section','total-section','jurnal-preview-section'].forEach(id => { const el = document.getElementById(id); if (el) el.style.display = 'none'; });
}

document.getElementById('produk_id').addEventListener('change', function() {
    const produkId = this.value;
    if (!produkId) { currentBomData = null; hideAllSections(); return; }
    fetch(`/transaksi/produksi/get-bom-details/${produkId}?t=${Date.now()}`)
        .then(r => r.json()).then(data => { if (data.success) { currentBomData = data.breakdown; calculateCostBreakdown(); } else { currentBomData = null; hideAllSections(); alert('Data BOM tidak ditemukan: ' + data.message); } })
        .catch(err => { currentBomData = null; hideAllSections(); alert('Error: ' + err.message); });
});

document.getElementById('jumlah_produksi_bulanan').addEventListener('input', calculateDailyProduction);
document.getElementById('hari_produksi_bulanan').addEventListener('input', calculateDailyProduction);

// Load BOM data awal
window.addEventListener('DOMContentLoaded', () => {
    if (initialProdukId) {
        fetch(`/transaksi/produksi/get-bom-details/${initialProdukId}?t=${Date.now()}`)
            .then(r => r.json()).then(data => { if (data.success) { currentBomData = data.breakdown; calculateCostBreakdown(); } });
    }
});
</script>
@endpush
@endsection

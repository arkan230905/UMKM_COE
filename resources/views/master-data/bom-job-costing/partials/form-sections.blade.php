<!-- Section 1: BBB -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>1. Biaya Bahan Baku (BBB)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="bbbTable">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Bahan Baku</th>
                        <th width="12%">Jumlah/Jam</th>
                        <th width="10%">Satuan</th>
                        <th width="15%">Harga/Satuan</th>
                        <th width="15%">Subtotal</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bbbTableBody">
                    @if(isset($bom) && $bom->detailBBB->count() > 0)
                        @foreach($bom->detailBBB as $detail)
                        <tr class="bbb-row">
                            <td>
                                <select name="bbb_bahan_baku_id[]" class="form-select form-select-sm bbb-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($bahanBakus as $bb)
                                        <option value="{{ $bb->id }}" 
                                            data-harga="{{ $bb->harga_satuan ?? 0 }}"
                                            data-satuan="{{ $bb->satuan->kode ?? 'KG' }}"
                                            {{ $detail->bahan_baku_id == $bb->id ? 'selected' : '' }}>
                                            {{ $bb->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bbb_jumlah_per_jam[]" class="form-control form-control-sm bbb-jumlah" value="{{ $detail->jumlah_per_jam }}" min="0" step="0.01"></td>
                            <td><input type="text" name="bbb_satuan[]" class="form-control form-control-sm bbb-satuan" value="{{ $detail->satuan }}" readonly></td>
                            <td class="bbb-harga text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                            <td class="bbb-subtotal text-end fw-bold">Rp {{ number_format($detail->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bbb"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="bbb-row">
                            <td>
                                <select name="bbb_bahan_baku_id[]" class="form-select form-select-sm bbb-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($bahanBakus as $bb)
                                        <option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan ?? 0 }}" data-satuan="{{ $bb->satuan->kode ?? 'KG' }}">{{ $bb->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bbb_jumlah_per_jam[]" class="form-control form-control-sm bbb-jumlah" value="0" min="0" step="0.01"></td>
                            <td><input type="text" name="bbb_satuan[]" class="form-control form-control-sm bbb-satuan" value="KG" readonly></td>
                            <td class="bbb-harga text-end">Rp 0</td>
                            <td class="bbb-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bbb"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-warning">
                        <td colspan="4" class="text-end fw-bold">Total BBB</td>
                        <td class="text-end fw-bold" id="totalBBB">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnTambahBBB"><i class="bi bi-plus"></i> Tambah</button>
    </div>
</div>


<!-- Section 2: Bahan Pendukung -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-droplet me-2"></i>2. Bahan Pendukung</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="bpTable">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Bahan Pendukung</th>
                        <th width="12%">Jumlah/Jam</th>
                        <th width="10%">Satuan</th>
                        <th width="15%">Harga/Satuan</th>
                        <th width="15%">Subtotal</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bpTableBody">
                    @if(isset($bom) && $bom->detailBahanPendukung->count() > 0)
                        @foreach($bom->detailBahanPendukung as $detail)
                        <tr class="bp-row">
                            <td>
                                <select name="bp_bahan_pendukung_id[]" class="form-select form-select-sm bp-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($bahanPendukungs as $bp)
                                        <option value="{{ $bp->id }}" 
                                            data-harga="{{ $bp->harga_satuan ?? 0 }}"
                                            data-satuan="{{ $bp->satuan->kode ?? 'PCS' }}"
                                            {{ $detail->bahan_pendukung_id == $bp->id ? 'selected' : '' }}>
                                            {{ $bp->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bp_jumlah_per_jam[]" class="form-control form-control-sm bp-jumlah" value="{{ $detail->jumlah_per_jam }}" min="0" step="0.01"></td>
                            <td><input type="text" name="bp_satuan[]" class="form-control form-control-sm bp-satuan" value="{{ $detail->satuan }}" readonly></td>
                            <td class="bp-harga text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                            <td class="bp-subtotal text-end fw-bold">Rp {{ number_format($detail->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bp"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="bp-row">
                            <td>
                                <select name="bp_bahan_pendukung_id[]" class="form-select form-select-sm bp-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($bahanPendukungs as $bp)
                                        <option value="{{ $bp->id }}" data-harga="{{ $bp->harga_satuan ?? 0 }}" data-satuan="{{ $bp->satuan->kode ?? 'PCS' }}">{{ $bp->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bp_jumlah_per_jam[]" class="form-control form-control-sm bp-jumlah" value="0" min="0" step="0.01"></td>
                            <td><input type="text" name="bp_satuan[]" class="form-control form-control-sm bp-satuan" value="PCS" readonly></td>
                            <td class="bp-harga text-end">Rp 0</td>
                            <td class="bp-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bp"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td colspan="4" class="text-end fw-bold">Total Bahan Pendukung</td>
                        <td class="text-end fw-bold" id="totalBP">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="button" class="btn btn-outline-info btn-sm" id="btnTambahBP"><i class="bi bi-plus"></i> Tambah</button>
    </div>
</div>

<!-- Section 3: BTKL -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>3. BTKL</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="btklTable">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Nama Tenaga Kerja</th>
                        <th width="15%">Tarif/Jam</th>
                        <th width="12%">Jumlah Pekerja</th>
                        <th width="15%">Subtotal</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="btklTableBody">
                    @if(isset($bom) && $bom->detailBTKL->count() > 0)
                        @foreach($bom->detailBTKL as $detail)
                        <tr class="btkl-row">
                            <td><input type="text" name="btkl_nama[]" class="form-control form-control-sm" value="{{ $detail->nama_tenaga_kerja }}"></td>
                            <td><input type="number" name="btkl_tarif_per_jam[]" class="form-control form-control-sm btkl-tarif" value="{{ $detail->tarif_per_jam }}" min="0"></td>
                            <td><input type="number" name="btkl_jumlah_pekerja[]" class="form-control form-control-sm btkl-jumlah" value="{{ $detail->jumlah_pekerja }}" min="0" step="0.5"></td>
                            <td class="btkl-subtotal text-end fw-bold">Rp {{ number_format($detail->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-btkl"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="btkl-row">
                            <td><input type="text" name="btkl_nama[]" class="form-control form-control-sm" placeholder="Operator"></td>
                            <td><input type="number" name="btkl_tarif_per_jam[]" class="form-control form-control-sm btkl-tarif" value="0" min="0"></td>
                            <td><input type="number" name="btkl_jumlah_pekerja[]" class="form-control form-control-sm btkl-jumlah" value="1" min="0" step="0.5"></td>
                            <td class="btkl-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-btkl"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <td colspan="3" class="text-end fw-bold">Total BTKL</td>
                        <td class="text-end fw-bold" id="totalBTKL">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="button" class="btn btn-outline-success btn-sm" id="btnTambahBTKL"><i class="bi bi-plus"></i> Tambah</button>
    </div>
</div>


<!-- Section 4: Produk -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-box me-2"></i>4. Produk Output</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="produkTable">
                <thead class="table-light">
                    <tr>
                        <th width="50%">Produk</th>
                        <th width="20%">Jumlah Output</th>
                        <th width="20%">HPP/Unit</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="produkTableBody">
                    @if(isset($bom) && $bom->produks->count() > 0)
                        @foreach($bom->produks as $produk)
                        <tr class="produk-row">
                            <td>
                                <select name="produk_ids[]" class="form-select form-select-sm produk-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}" {{ $produk->id == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="produk_jumlah[]" class="form-control form-control-sm produk-jumlah" value="{{ $produk->pivot->jumlah_output }}" min="1"></td>
                            <td class="produk-hpp text-end">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-produk"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="produk-row">
                            <td>
                                <select name="produk_ids[]" class="form-select form-select-sm produk-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($produks as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="produk_jumlah[]" class="form-control form-control-sm produk-jumlah" value="1" min="1"></td>
                            <td class="produk-hpp text-end">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-produk"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline-warning btn-sm" id="btnTambahProduk"><i class="bi bi-plus"></i> Tambah</button>
    </div>
</div>

<!-- Ringkasan -->
<div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan HPP</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><td width="60%">Total BBB</td><td class="text-end fw-bold" id="summaryBBB">Rp 0</td></tr>
            <tr><td>Total Bahan Pendukung</td><td class="text-end fw-bold" id="summaryBP">Rp 0</td></tr>
            <tr><td>Total BTKL</td><td class="text-end fw-bold" id="summaryBTKL">Rp 0</td></tr>
            <tr class="table-primary"><td class="fw-bold fs-5">TOTAL HPP (Per Batch)</td><td class="text-end fw-bold fs-5" id="totalHPP">Rp 0</td></tr>
            <tr class="table-success"><td class="fw-bold fs-5">HPP PER UNIT</td><td class="text-end fw-bold fs-5 text-success" id="hppPerUnit">Rp 0</td></tr>
        </table>
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i> Simpan</button>
    <a href="{{ route('master-data.bom-job-costing.index') }}" class="btn btn-secondary btn-lg">Batal</a>
</div>

<script>
const bahanBakuData = @json($bahanBakus);
const bahanPendukungData = @json($bahanPendukungs);
const produkData = @json($produks);

function formatRupiah(angka) { return 'Rp ' + Math.round(angka).toLocaleString('id-ID'); }
function getDurasi() { return parseFloat(document.getElementById('durasiProses').value) || 1; }
function getJumlahOutput() { return parseInt(document.getElementById('jumlahOutput').value) || 1; }

function hitungBarisBBB(row) {
    const select = row.querySelector('.bbb-select');
    const jumlahInput = row.querySelector('.bbb-jumlah');
    const satuanInput = row.querySelector('.bbb-satuan');
    const hargaDisplay = row.querySelector('.bbb-harga');
    const subtotalDisplay = row.querySelector('.bbb-subtotal');
    const opt = select.options[select.selectedIndex];
    if (!opt.value) { hargaDisplay.textContent = 'Rp 0'; subtotalDisplay.textContent = 'Rp 0'; hitungTotalBBB(); return; }
    const harga = parseFloat(opt.dataset.harga) || 0;
    satuanInput.value = opt.dataset.satuan || 'KG';
    const subtotal = (parseFloat(jumlahInput.value) || 0) * getDurasi() * harga;
    hargaDisplay.textContent = formatRupiah(harga);
    subtotalDisplay.textContent = formatRupiah(subtotal);
    hitungTotalBBB();
}

function hitungTotalBBB() {
    let total = 0;
    document.querySelectorAll('.bbb-row').forEach(row => { total += parseFloat(row.querySelector('.bbb-subtotal').textContent.replace(/[^0-9]/g, '')) || 0; });
    document.getElementById('totalBBB').textContent = formatRupiah(total);
    document.getElementById('summaryBBB').textContent = formatRupiah(total);
    hitungHPP();
}

function attachBBBEvents(row) {
    row.querySelector('.bbb-select').addEventListener('change', () => hitungBarisBBB(row));
    row.querySelector('.bbb-jumlah').addEventListener('input', () => hitungBarisBBB(row));
    row.querySelector('.btn-hapus-bbb').addEventListener('click', () => { row.remove(); hitungTotalBBB(); });
}

function hitungBarisBP(row) {
    const select = row.querySelector('.bp-select');
    const jumlahInput = row.querySelector('.bp-jumlah');
    const satuanInput = row.querySelector('.bp-satuan');
    const hargaDisplay = row.querySelector('.bp-harga');
    const subtotalDisplay = row.querySelector('.bp-subtotal');
    const opt = select.options[select.selectedIndex];
    if (!opt.value) { hargaDisplay.textContent = 'Rp 0'; subtotalDisplay.textContent = 'Rp 0'; hitungTotalBP(); return; }
    const harga = parseFloat(opt.dataset.harga) || 0;
    satuanInput.value = opt.dataset.satuan || 'PCS';
    const subtotal = (parseFloat(jumlahInput.value) || 0) * getDurasi() * harga;
    hargaDisplay.textContent = formatRupiah(harga);
    subtotalDisplay.textContent = formatRupiah(subtotal);
    hitungTotalBP();
}

function hitungTotalBP() {
    let total = 0;
    document.querySelectorAll('.bp-row').forEach(row => { total += parseFloat(row.querySelector('.bp-subtotal').textContent.replace(/[^0-9]/g, '')) || 0; });
    document.getElementById('totalBP').textContent = formatRupiah(total);
    document.getElementById('summaryBP').textContent = formatRupiah(total);
    hitungHPP();
}

function attachBPEvents(row) {
    row.querySelector('.bp-select').addEventListener('change', () => hitungBarisBP(row));
    row.querySelector('.bp-jumlah').addEventListener('input', () => hitungBarisBP(row));
    row.querySelector('.btn-hapus-bp').addEventListener('click', () => { row.remove(); hitungTotalBP(); });
}

function hitungBarisBTKL(row) {
    const tarif = parseFloat(row.querySelector('.btkl-tarif').value) || 0;
    const jumlah = parseFloat(row.querySelector('.btkl-jumlah').value) || 0;
    const subtotal = tarif * jumlah * getDurasi();
    row.querySelector('.btkl-subtotal').textContent = formatRupiah(subtotal);
    hitungTotalBTKL();
}

function hitungTotalBTKL() {
    let total = 0;
    document.querySelectorAll('.btkl-row').forEach(row => { total += parseFloat(row.querySelector('.btkl-subtotal').textContent.replace(/[^0-9]/g, '')) || 0; });
    document.getElementById('totalBTKL').textContent = formatRupiah(total);
    document.getElementById('summaryBTKL').textContent = formatRupiah(total);
    hitungHPP();
}

function attachBTKLEvents(row) {
    row.querySelector('.btkl-tarif').addEventListener('input', () => hitungBarisBTKL(row));
    row.querySelector('.btkl-jumlah').addEventListener('input', () => hitungBarisBTKL(row));
    row.querySelector('.btn-hapus-btkl').addEventListener('click', () => { row.remove(); hitungTotalBTKL(); });
}

function hitungHPP() {
    const bbb = parseFloat(document.getElementById('summaryBBB').textContent.replace(/[^0-9]/g, '')) || 0;
    const bp = parseFloat(document.getElementById('summaryBP').textContent.replace(/[^0-9]/g, '')) || 0;
    const btkl = parseFloat(document.getElementById('summaryBTKL').textContent.replace(/[^0-9]/g, '')) || 0;
    const totalHPP = bbb + bp + btkl;
    const hppPerUnit = totalHPP / getJumlahOutput();
    document.getElementById('totalHPP').textContent = formatRupiah(totalHPP);
    document.getElementById('hppPerUnit').textContent = formatRupiah(hppPerUnit);
    hitungHPPProduk();
}

function hitungHPPProduk() {
    const hppPerUnit = parseFloat(document.getElementById('hppPerUnit').textContent.replace(/[^0-9]/g, '')) || 0;
    document.querySelectorAll('.produk-row').forEach(row => {
        const jumlah = parseInt(row.querySelector('.produk-jumlah').value) || 1;
        row.querySelector('.produk-hpp').textContent = formatRupiah(hppPerUnit * jumlah);
    });
}

function attachProdukEvents(row) {
    row.querySelector('.produk-jumlah').addEventListener('input', () => hitungHPPProduk());
    row.querySelector('.btn-hapus-produk').addEventListener('click', () => { row.remove(); });
}

function recalculateAll() {
    document.querySelectorAll('.bbb-row').forEach(row => hitungBarisBBB(row));
    document.querySelectorAll('.bp-row').forEach(row => hitungBarisBP(row));
    document.querySelectorAll('.btkl-row').forEach(row => hitungBarisBTKL(row));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bbb-row').forEach(row => attachBBBEvents(row));
    document.querySelectorAll('.bp-row').forEach(row => attachBPEvents(row));
    document.querySelectorAll('.btkl-row').forEach(row => attachBTKLEvents(row));
    document.querySelectorAll('.produk-row').forEach(row => attachProdukEvents(row));
    
    document.getElementById('durasiProses').addEventListener('input', recalculateAll);
    document.getElementById('jumlahOutput').addEventListener('input', hitungHPP);
    
    document.getElementById('btnTambahBBB').addEventListener('click', () => {
        const tbody = document.getElementById('bbbTableBody');
        const newRow = tbody.querySelector('.bbb-row').cloneNode(true);
        newRow.querySelector('.bbb-select').value = '';
        newRow.querySelector('.bbb-jumlah').value = '0';
        newRow.querySelector('.bbb-harga').textContent = 'Rp 0';
        newRow.querySelector('.bbb-subtotal').textContent = 'Rp 0';
        tbody.appendChild(newRow);
        attachBBBEvents(newRow);
    });
    
    document.getElementById('btnTambahBP').addEventListener('click', () => {
        const tbody = document.getElementById('bpTableBody');
        const newRow = tbody.querySelector('.bp-row').cloneNode(true);
        newRow.querySelector('.bp-select').value = '';
        newRow.querySelector('.bp-jumlah').value = '0';
        newRow.querySelector('.bp-harga').textContent = 'Rp 0';
        newRow.querySelector('.bp-subtotal').textContent = 'Rp 0';
        tbody.appendChild(newRow);
        attachBPEvents(newRow);
    });
    
    document.getElementById('btnTambahBTKL').addEventListener('click', () => {
        const tbody = document.getElementById('btklTableBody');
        const newRow = tbody.querySelector('.btkl-row').cloneNode(true);
        newRow.querySelector('input[name="btkl_nama[]"]').value = '';
        newRow.querySelector('.btkl-tarif').value = '0';
        newRow.querySelector('.btkl-jumlah').value = '1';
        newRow.querySelector('.btkl-subtotal').textContent = 'Rp 0';
        tbody.appendChild(newRow);
        attachBTKLEvents(newRow);
    });
    
    document.getElementById('btnTambahProduk').addEventListener('click', () => {
        const tbody = document.getElementById('produkTableBody');
        const newRow = tbody.querySelector('.produk-row').cloneNode(true);
        newRow.querySelector('.produk-select').value = '';
        newRow.querySelector('.produk-jumlah').value = '1';
        newRow.querySelector('.produk-hpp').textContent = 'Rp 0';
        tbody.appendChild(newRow);
        attachProdukEvents(newRow);
    });
    
    recalculateAll();
});
</script>
